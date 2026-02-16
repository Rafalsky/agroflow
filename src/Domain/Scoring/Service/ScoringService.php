<?php

namespace App\Domain\Scoring\Service;

use App\Domain\Scoring\Entity\ScoreLedger;
use App\Domain\WorkCycle\Entity\TaskInstance;
use App\Domain\WorkCycle\Entity\Worker;
use Doctrine\ORM\EntityManagerInterface;

class ScoringService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private \App\Domain\Scoring\Repository\ScoreLedgerRepository $scoreLedgerRepository
    ) {
    }

    /**
     * @return array<array{worker_id: int, name: string, total_amount: int}>
     */
    public function getLeaderboardForWeek(int $year, int $weekNumber): array
    {
        return $this->scoreLedgerRepository->getLeaderboardForWeek($year, $weekNumber);
    }

    public function getTeamTotalForWeek(int $year, int $weekNumber): int
    {
        return $this->scoreLedgerRepository->getTeamTotalForWeek($year, $weekNumber);
    }


    public function addPoints(Worker $worker, int $amount, string $reason, ?TaskInstance $taskInstance = null): void
    {
        $ledger = new ScoreLedger($worker, $amount, $reason, $taskInstance);
        $this->entityManager->persist($ledger);
    }

    /**
     * Shortcut to remove points when a task is undone
     */
    public function removePointsForTask(TaskInstance $taskInstance): void
    {
        if (!$taskInstance->getWorker()) {
            return;
        }

        // We create a negative transaction
        $this->addPoints(
            $taskInstance->getWorker(),
            -$taskInstance->getPointsSnapshot(),
            sprintf('Undo task: %s', $taskInstance->getNameSnapshot()),
            $taskInstance
        );
    }

    /**
     * Shortcut to add points when a task is done
     */
    public function addPointsForTask(TaskInstance $taskInstance): void
    {
        if (!$taskInstance->getWorker()) {
            return;
        }

        $this->addPoints(
            $taskInstance->getWorker(),
            $taskInstance->getPointsSnapshot(),
            sprintf('Completed task: %s', $taskInstance->getNameSnapshot()),
            $taskInstance
        );
    }
}
