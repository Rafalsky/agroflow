<?php

namespace App\Controller;

use App\Domain\Audit\Service\AuditLogger;
use App\Domain\Scoring\Repository\ScoreLedgerRepository;
use App\Domain\Scoring\Service\ScoringService;
use App\Domain\WorkCycle\Entity\TaskInstance;
use App\Domain\WorkCycle\Entity\Worker;
use App\Domain\WorkCycle\Repository\TaskInstanceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/worker')]
class WorkerController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TaskInstanceRepository $taskRepository,
        private AuditLogger $auditLogger,
        private ScoringService $scoringService,
        private ScoreLedgerRepository $scoreRepository
    ) {
    }

    #[Route('/{accessToken}', name: 'worker_login')]
    public function login(string $accessToken): Response
    {
        // Authenticator handles the login, we just redirect
        // This route triggers WorkerAuthenticator::supports() 
        // which checks for accessToken in route attributes
        return $this->redirectToRoute('worker_tasks');
    }

    #[Route('/tasks', name: 'worker_tasks')]
    public function tasks(#[CurrentUser] ?Worker $worker): Response
    {
        if (!$worker) {
            return $this->redirectToRoute('worker_login', ['accessToken' => 'invalid']);
        }

        $now = new \DateTimeImmutable();
        $currentYear = (int) $now->format('o');
        $currentWeekNumber = (int) $now->format('W');

        // Fetch ALL pending tasks for the team
        $allPendingTasks = $this->entityManager->createQueryBuilder()
            ->select('t', 'w')
            ->from(TaskInstance::class, 't')
            ->join('t.week', 'w')
            ->where('t.status = :pending')
            ->setParameter('pending', 'PENDING')
            ->orderBy('w.year', 'ASC')
            ->addOrderBy('w.weekNumber', 'ASC')
            ->addOrderBy('t.weekdaySnapshot', 'ASC')
            ->addOrderBy('t.prioritySnapshot', 'DESC')
            ->getQuery()
            ->getResult();

        $tasksData = array_map(function (TaskInstance $t) use ($currentYear, $currentWeekNumber) {
            $week = $t->getWeek();
            $isOverdue = ($week->getYear() < $currentYear) ||
                ($week->getYear() === $currentYear && $week->getWeekNumber() < $currentWeekNumber);

            return [
                'id' => $t->getId(),
                'name' => $t->getNameSnapshot(),
                'points' => $t->getPointsSnapshot(),
                'priority' => $t->getPrioritySnapshot(),
                'weekday' => $t->getWeekdaySnapshot(),
                'weekNumber' => $week->getWeekNumber(),
                'year' => $week->getYear(),
                'status' => $t->getStatus(),
                'isOverdue' => $isOverdue,
            ];
        }, $allPendingTasks);

        $totalPoints = $this->scoreRepository->getTotalPoints($worker);
        $weekPoints = $this->scoreRepository->getWorkerTotalForWeek($worker, $currentYear, $currentWeekNumber);

        return $this->render('worker/tasks.html.twig', [
            'worker' => $worker,
            'totalPoints' => $totalPoints,
            'weekPoints' => $weekPoints,
            'tasksData' => $tasksData,
        ]);
    }

    #[Route('/task/{id}/done', name: 'worker_task_done', methods: ['POST'])]
    public function markDone(TaskInstance $task, #[CurrentUser] ?Worker $worker): Response
    {
        if (!$worker || $task->getWorker() !== $worker) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $task->setStatus('DONE');

        $this->scoringService->addPointsForTask($task);

        $this->entityManager->flush();

        $this->auditLogger->log(
            entityType: 'task_instance',
            entityId: (string) $task->getId(),
            eventType: 'task.done_by_worker',
            payload: ['worker_id' => $worker->getId()]
        );

        return new JsonResponse(['success' => true, 'status' => 'DONE']);
    }

    #[Route('/task/{id}/undo', name: 'worker_task_undo', methods: ['POST'])]
    public function markPending(TaskInstance $task, #[CurrentUser] ?Worker $worker): Response
    {
        if (!$worker || $task->getWorker() !== $worker) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $task->setStatus('PENDING');

        $this->scoringService->removePointsForTask($task);

        $this->entityManager->flush();

        $this->auditLogger->log(
            entityType: 'task_instance',
            entityId: (string) $task->getId(),
            eventType: 'task.undo_by_worker',
            payload: ['worker_id' => $worker->getId()]
        );

        return new JsonResponse(['success' => true, 'status' => 'PENDING']);
    }
}
