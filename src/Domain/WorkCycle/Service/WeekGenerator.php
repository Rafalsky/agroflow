<?php

namespace App\Domain\WorkCycle\Service;

use App\Domain\Audit\Service\AuditLogger;
use App\Domain\WorkCycle\Entity\ProductionWeek;
use App\Domain\WorkCycle\Entity\TaskInstance;
use App\Domain\WorkCycle\Entity\TaskTemplate;
use Doctrine\ORM\EntityManagerInterface;

class WeekGenerator
{
    private EntityManagerInterface $entityManager;
    private AuditLogger $auditLogger;

    public function __construct(EntityManagerInterface $entityManager, AuditLogger $auditLogger)
    {
        $this->entityManager = $entityManager;
        $this->auditLogger = $auditLogger;
    }

    public function getOrCreateWeek(int $year, int $weekNumber): ProductionWeek
    {
        $week = $this->entityManager->getRepository(ProductionWeek::class)->findOneBy([
            'year' => $year,
            'weekNumber' => $weekNumber,
        ]);

        if (!$week) {
            $week = new ProductionWeek();
            $week->setYear($year);
            $week->setWeekNumber($weekNumber);
            $week->setStatus('OPEN');

            $this->entityManager->persist($week);
            $this->entityManager->flush();

            $this->auditLogger->log(
                entityType: 'production_week',
                entityId: (string) $week->getId(),
                eventType: 'week.created',
                payload: ['year' => $year, 'weekNumber' => $weekNumber]
            );
        }

        return $week;
    }

    public function generateRecurringTasks(ProductionWeek $week): int
    {
        // 1. Fetch active recurring templates
        $templates = $this->entityManager->getRepository(TaskTemplate::class)->findBy([
            'recurring' => true,
            'active' => true,
        ]);

        $createdCount = 0;

        foreach ($templates as $template) {
            // 2. Check explicitly for existence (Idempotency check)
            $existing = $this->entityManager->getRepository(TaskInstance::class)->findOneBy([
                'week' => $week,
                'template' => $template,
            ]);

            if ($existing) {
                continue;
            }

            // 3. Create instance
            $instance = new TaskInstance();
            $instance->setWeek($week);
            $instance->setTemplate($template);

            // Snapshot data
            $instance->setNameSnapshot($template->getName());
            $instance->setPointsSnapshot($template->getPoints());
            $instance->setPrioritySnapshot($template->getPriority());
            $instance->setWeekdaySnapshot($template->getWeekday());

            $instance->setStatus('PENDING');

            $this->entityManager->persist($instance);
            $createdCount++;
        }

        if ($createdCount > 0) {
            $this->entityManager->flush();

            $this->auditLogger->log(
                entityType: 'production_week',
                entityId: (string) $week->getId(),
                eventType: 'week.tasks_generated',
                payload: ['count' => $createdCount]
            );
        }

        return $createdCount;
    }
}
