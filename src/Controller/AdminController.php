<?php

namespace App\Controller;

use App\Domain\Audit\Entity\AuditLog;
use App\Domain\Audit\Service\AuditLogger;
use App\Domain\Scoring\Repository\ScoreLedgerRepository;
use App\Domain\Welfare\Service\WelfareService;
use App\Domain\WorkCycle\Entity\TaskTemplate;
use App\Domain\WorkCycle\Entity\Worker;
use App\Domain\WorkCycle\Service\WeekGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    public function __construct(
        private AuditLogger $auditLogger,
        private EntityManagerInterface $entityManager,
        private WeekGenerator $weekGenerator,
        private WelfareService $welfareService,
        private ScoreLedgerRepository $scoreRepository
    ) {
    }

    #[Route('/debug-db', name: 'debug_db')]
    public function debugDb(\Doctrine\ORM\EntityManagerInterface $em): Response
    {
        $conn = $em->getConnection();
        $params = $conn->getParams();

        // Mask password
        if (isset($params['password'])) {
            $params['password'] = '***';
        }

        try {
            $tables = $conn->createSchemaManager()->listTableNames();
        } catch (\Exception $e) {
            $tables = ['Error: ' . $e->getMessage()];
        }

        return $this->json([
            'connection_params' => $params,
            'tables' => $tables,
            'database_url_env' => $_ENV['DATABASE_URL'] ?? 'NOT_SET',
        ]);
    }

    #[Route('', name: 'dashboard')]
    public function index(): Response
    {
        $now = new \DateTimeImmutable();
        $year = (int) $now->format('o');
        $week = (int) $now->format('W');

        $productionWeek = $this->weekGenerator->getOrCreateWeek($year, $week);

        // 1. Week Data
        $productionWeek = $this->weekGenerator->getOrCreateWeek($year, $week);
        $weekData = [
            'id' => $productionWeek->getId(),
            'year' => $productionWeek->getYear(),
            'weekNumber' => $productionWeek->getWeekNumber(),
            'status' => $productionWeek->getStatus(),
        ];

        // 1.1 Week Tasks
        $tasks = $this->entityManager->getRepository(\App\Domain\WorkCycle\Entity\TaskInstance::class)->findBy(
            ['week' => $productionWeek],
            ['weekdaySnapshot' => 'ASC', 'prioritySnapshot' => 'DESC']
        );
        $tasksData = array_map(fn($t) => [
            'id' => $t->getId(),
            'name' => $t->getNameSnapshot(),
            'points' => $t->getPointsSnapshot(),
            'priority' => $t->getPrioritySnapshot(),
            'weekday' => $t->getWeekdaySnapshot(),
            'status' => $t->getStatus(),
            'worker_id' => $t->getWorker()?->getId(),
            'worker_name' => $t->getWorker()?->getShortName(),
            'doneAt' => $t->getDoneAt()?->format('Y-m-d H:i:s'),
        ], $tasks);

        // 1.2 Pending Tasks from previous weeks
        $pendingTasks = $this->entityManager->createQueryBuilder()
            ->select('t')
            ->from(\App\Domain\WorkCycle\Entity\TaskInstance::class, 't')
            ->join('t.week', 'w')
            ->where('t.status = :status')
            ->andWhere('w.id != :currentWeekId')
            ->setParameter('status', 'PENDING')
            ->setParameter('currentWeekId', $productionWeek->getId())
            ->orderBy('w.year', 'ASC')
            ->addOrderBy('w.weekNumber', 'ASC')
            ->getQuery()
            ->getResult();

        $pendingTasksData = array_map(fn($t) => [
            'id' => $t->getId(),
            'name' => $t->getNameSnapshot(),
            'points' => $t->getPointsSnapshot(),
            'priority' => $t->getPrioritySnapshot(),
            'weekday' => $t->getWeekdaySnapshot(),
            'weekNumber' => $t->getWeek()->getWeekNumber(),
            'year' => $t->getWeek()->getYear(),
            'status' => $t->getStatus(),
        ], $pendingTasks);

        // 2. Workers
        $workers = $this->entityManager->getRepository(Worker::class)->findBy([], ['name' => 'ASC']);
        $workersData = array_map(fn(Worker $w) => [
            'id' => $w->getId(),
            'name' => $w->getName(),
            'shortName' => $w->getShortName(),
            'active' => $w->isActive(),
            'accessToken' => $w->getAccessToken(), // Admin can see tokens
        ], $workers);

        // 3. Task Templates
        $templates = $this->entityManager->getRepository(TaskTemplate::class)->findBy([], ['weekday' => 'ASC', 'priority' => 'DESC']);
        $templatesData = array_map(fn(TaskTemplate $t) => [
            'id' => $t->getId(),
            'name' => $t->getName(),
            'points' => $t->getPoints(),
            'priority' => $t->getPriority(),
            'weekday' => $t->getWeekday(),
            'recurring' => $t->isRecurring(),
            'active' => $t->isActive(),
            'category' => $t->getCategory(),
            'defaultWorkerId' => $t->getDefaultWorker()?->getId(),
        ], $templates);

        // 4. Audit Logs
        $logs = $this->entityManager->getRepository(AuditLog::class)->findBy([], ['createdAt' => 'DESC'], 50);
        $logsData = array_map(fn(AuditLog $l) => [
            'id' => $l->getId(),
            'entityType' => $l->getEntityType(),
            'entityId' => $l->getEntityId(),
            'eventType' => $l->getEventType(),
            'payload' => $l->getPayload(),
            'actorType' => $l->getActorType(),
            'createdAt' => $l->getCreatedAt()->format('Y-m-d H:i:s'),
        ], $logs);

        // 5. Scoring
        $leaderboard = $this->scoreRepository->getLeaderboardForWeek($year, $week);

        // 6. Welfare
        $welfareState = $this->welfareService->getCurrentState();

        return $this->render('admin/index.html.twig', [
            'initialData' => [
                'week' => $weekData,
                'tasks' => $tasksData,
                'pendingTasks' => $pendingTasksData,
                'workers' => $workersData,
                'templates' => $templatesData,
                'auditLogs' => $logsData,
                'leaderboard' => $leaderboard,
                'welfare' => $welfareState,
            ]
        ]);
    }
}
