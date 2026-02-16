<?php

namespace App\Domain\Welfare\Service;

use App\Domain\Welfare\Entity\CurrentStock;
use App\Domain\Welfare\Entity\StockChange;
use App\Domain\WorkCycle\Entity\Worker;
use Doctrine\ORM\EntityManagerInterface;

class WelfareService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function applyChange(
        string $category,
        int $delta,
        string $reason,
        ?string $note = null,
        ?Worker $worker = null
    ): StockChange {
        $repository = $this->entityManager->getRepository(CurrentStock::class);
        $stock = $repository->findOneBy(['category' => $category]);

        if (!$stock) {
            $stock = new CurrentStock($category, 0);
            $this->entityManager->persist($stock);
        }

        $previousAmount = $stock->getAmount();
        $newAmount = $previousAmount + $delta;

        if ($newAmount < 0) {
            throw new \InvalidArgumentException(sprintf(
                'Cannot reduce stock for %s below zero. Current: %d, Delta: %d',
                $category,
                $previousAmount,
                $delta
            ));
        }

        $stock->setAmount($newAmount);

        $change = new StockChange(
            $category,
            $delta,
            $reason,
            $previousAmount,
            $newAmount,
            $note,
            $worker
        );

        $this->entityManager->persist($change);
        $this->entityManager->flush();

        return $change;
    }

    /**
     * @return array<string, int>
     */
    public function getCurrentState(): array
    {
        $repository = $this->entityManager->getRepository(CurrentStock::class);
        $stocks = $repository->findAll();

        $state = [
            CurrentStock::CATEGORY_SOWS => 0,
            CurrentStock::CATEGORY_PIGLETS => 0,
            CurrentStock::CATEGORY_FATTENERS => 0,
        ];

        foreach ($stocks as $stock) {
            $state[$stock->getCategory()] = $stock->getAmount();
        }

        return $state;
    }

    /**
     * @return StockChange[]
     */
    public function getRecentHistory(int $limit = 10): array
    {
        return $this->entityManager->getRepository(StockChange::class)
            ->findBy([], ['createdAt' => 'DESC'], $limit);
    }
}
