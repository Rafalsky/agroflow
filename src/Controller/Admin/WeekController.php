<?php

namespace App\Controller\Admin;

use App\Domain\Audit\Service\AuditLogger;
use App\Domain\WorkCycle\Entity\ProductionWeek;
use App\Domain\WorkCycle\Entity\TaskInstance;
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
        private AuditLogger $auditLogger
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

        return $this->render('admin/week/show.html.twig', [
            'week' => $productionWeek,
            'tasks' => $tasks,
            'pending_tasks' => $pendingTasks,
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

        $this->entityManager->flush();

        $this->auditLogger->log(
            entityType: 'task_instance',
            entityId: (string) $task->getId(),
            eventType: 'task_instance.undone',
            payload: ['name' => $task->getNameSnapshot()]
        );

        return new JsonResponse(['status' => 'PENDING']);
    }
}
