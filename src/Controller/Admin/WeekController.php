<?php

namespace App\Controller\Admin;

use App\Domain\Audit\Service\AuditLogger;
use App\Domain\Scoring\Service\ScoringService;
use App\Domain\WorkCycle\Entity\ProductionWeek;
use App\Domain\WorkCycle\Entity\TaskInstance;
use App\Domain\WorkCycle\Entity\Worker;
use App\Domain\WorkCycle\Service\WeekGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/week', name: 'admin_week_')]
class WeekController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private WeekGenerator $weekGenerator,
        private AuditLogger $auditLogger,
        private ScoringService $scoringService
    ) {
    }

    #[Route('/current', name: 'current')]
    public function current(): Response
    {
        $now = new \DateTimeImmutable();
        $year = (int) $now->format('o');
        $week = (int) $now->format('W');

        return $this->redirectToRoute('admin_week_show', ['year' => $year, 'week' => $week]);
    }

    #[Route('/{year}/{week}', name: 'show', requirements: ['year' => '\d+', 'week' => '\d+'])]
    public function show(int $year, int $week): Response
    {
        $productionWeek = $this->weekGenerator->getOrCreateWeek($year, $week);

        // Fetch tasks for this week
        $tasks = $this->entityManager->getRepository(TaskInstance::class)->findBy(
            ['week' => $productionWeek],
            ['weekdaySnapshot' => 'ASC', 'prioritySnapshot' => 'DESC']
        );

        // Fetch pending tasks from previous weeks
        $pendingTasks = $this->entityManager->createQueryBuilder()
            ->select('t')
            ->from(TaskInstance::class, 't')
            ->join('t.week', 'w')
            ->where('t.status = :status')
            ->andWhere('w.id != :currentWeekId')
            ->setParameter('status', 'PENDING')
            ->setParameter('currentWeekId', $productionWeek->getId())
            ->orderBy('w.year', 'ASC')
            ->addOrderBy('w.weekNumber', 'ASC')
            ->getQuery()
            ->getResult();

        // Calculate Prev/Next week
        $date = (new \DateTime())->setISODate($year, $week);
        $prevDate = (clone $date)->modify('-1 week');
        $nextDate = (clone $date)->modify('+1 week');

        $prevWeek = ['year' => (int) $prevDate->format('o'), 'week' => (int) $prevDate->format('W')];
        $nextWeek = ['year' => (int) $nextDate->format('o'), 'week' => (int) $nextDate->format('W')];

        $weekData = [
            'id' => $productionWeek->getId(),
            'year' => $productionWeek->getYear(),
            'weekNumber' => $productionWeek->getWeekNumber(),
            'status' => $productionWeek->getStatus(),
        ];

        $tasksData = array_map(fn(TaskInstance $t) => [
            'id' => $t->getId(),
            'name' => $t->getNameSnapshot(),
            'points' => $t->getPointsSnapshot(),
            'priority' => $t->getPrioritySnapshot(),
            'weekday' => $t->getWeekdaySnapshot(),
            'status' => $t->getStatus(),
            'worker_id' => $t->getWorker()?->getId(),
            'worker_name' => $t->getWorker()?->getShortName(),
        ], $tasks);

        $pendingTasksData = array_map(fn(TaskInstance $t) => [
            'id' => $t->getId(),
            'name' => $t->getNameSnapshot(),
            'points' => $t->getPointsSnapshot(),
            'priority' => $t->getPrioritySnapshot(),
            'weekday' => $t->getWeekdaySnapshot(),
            'weekNumber' => $t->getWeek()->getWeekNumber(),
            'year' => $t->getWeek()->getYear(),
            'status' => $t->getStatus(),
            'worker_id' => $t->getWorker()?->getId(),
            'worker_name' => $t->getWorker()?->getShortName(),
        ], $pendingTasks);

        // Fetch active workers
        $workers = $this->entityManager->getRepository(Worker::class)->findBy(['active' => true]);
        $workersData = array_map(fn(Worker $w) => [
            'id' => $w->getId(),
            'name' => $w->getName(),
            'shortName' => $w->getShortName(),
        ], $workers);

        // Fetch scoring data for this week
        $weekLeaderboard = $this->scoringService->getLeaderboardForWeek($year, $week);
        $teamTotal = $this->scoringService->getTeamTotalForWeek($year, $week);

        return $this->render('admin/week/show.html.twig', [
            'week' => $productionWeek,
            'weekData' => $weekData,
            'tasksData' => $tasksData,
            'pendingTasksData' => $pendingTasksData,
            'workersData' => $workersData,
            'weekLeaderboard' => $weekLeaderboard,
            'teamTotal' => $teamTotal,
            // Keep original variables for Twig fallback if needed, but we focus on Vue now
            'tasks' => $tasks,
            'pending_tasks' => $pendingTasks,
            'prev_week' => $prevWeek,
            'next_week' => $nextWeek,
        ]);
    }

    #[Route('/{year}/{week}/generate', name: 'generate', methods: ['POST'])]
    public function generate(int $year, int $week): Response
    {
        $productionWeek = $this->weekGenerator->getOrCreateWeek($year, $week);
        $count = $this->weekGenerator->generateRecurringTasks($productionWeek);

        $this->addFlash('success', "Wygenerowano {$count} zadań cyklicznych.");

        return $this->redirectToRoute('admin_week_show', ['year' => $year, 'week' => $week]);
    }

    #[Route('/task/{id}/done', name: 'task_done', methods: ['POST'])]
    public function taskDone(TaskInstance $task): Response
    {
        $task->setStatus('DONE');
        $task->setDoneAt(new \DateTimeImmutable());

        $this->scoringService->addPointsForTask($task);

        $this->entityManager->flush();

        $this->auditLogger->log(
            entityType: 'task_instance',
            entityId: (string) $task->getId(),
            eventType: 'task_instance.done',
            payload: ['name' => $task->getNameSnapshot()]
        );

        return new JsonResponse(['status' => 'DONE']);
    }

    #[Route('/task/{id}/undo', name: 'task_undo', methods: ['POST'])]
    public function taskUndo(TaskInstance $task): Response
    {
        $task->setStatus('PENDING');
        $task->setDoneAt(null);

        $this->scoringService->removePointsForTask($task);

        $this->entityManager->flush();

        $this->auditLogger->log(
            entityType: 'task_instance',
            entityId: (string) $task->getId(),
            eventType: 'task_instance.undone',
            payload: ['name' => $task->getNameSnapshot()]
        );

        return new JsonResponse(['status' => 'PENDING']);
    }
    #[Route('/{year}/{week}/close', name: 'close', methods: ['POST'])]
    public function closeWeek(int $year, int $week, EntityManagerInterface $entityManager): Response
    {
        $productionWeek = $this->weekGenerator->getOrCreateWeek($year, $week);

        if ($productionWeek->getStatus() !== 'OPEN') {
            return $this->redirectToRoute('admin_week_show', ['year' => $year, 'week' => $week]);
        }

        $productionWeek->setStatus('CLOSED');
        $productionWeek->setClosedAt(new \DateTimeImmutable());

        $entityManager->flush();

        $this->auditLogger->log(
            entityType: 'production_week',
            entityId: (string) $productionWeek->getId(),
            eventType: 'week.closed'
        );

        return $this->redirectToRoute('admin_week_show', ['year' => $year, 'week' => $week]);
    }

    #[Route('/task/{id}/assign', name: 'task_assign', methods: ['POST'])]
    public function assignTask(TaskInstance $task, Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);
        $workerId = $data['worker_id'] ?? null;

        if ($workerId) {
            $worker = $entityManager->getRepository(Worker::class)->find($workerId);
            $task->setWorker($worker);
        } else {
            $task->setWorker(null);
        }

        $entityManager->flush();

        $this->auditLogger->log(
            entityType: 'task_instance',
            entityId: (string) $task->getId(),
            eventType: 'task.assigned',
            payload: ['worker_id' => $workerId]
        );

        return new JsonResponse(['success' => true, 'worker_id' => $workerId]);
    }
}
