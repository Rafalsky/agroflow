<?php

namespace App\Domain\Welfare\Entity;

use App\Domain\WorkCycle\Entity\Worker;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'welfare_stock_change')]
class StockChange
{
    public const REASON_BIRTH = 'BIRTH';
    public const REASON_DEATH = 'DEATH';
    public const REASON_SALE = 'SALE';
    public const REASON_PURCHASE = 'PURCHASE';
    public const REASON_ADJUSTMENT = 'ADJUSTMENT';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private string $category;

    #[ORM\Column]
    private int $delta;

    #[ORM\Column(length: 50)]
    private string $reason;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $note = null;

    #[ORM\Column]
    private int $previousAmount;

    #[ORM\Column]
    private int $newAmount;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(targetEntity: Worker::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Worker $worker = null;

    public function __construct(
        string $category,
        int $delta,
        string $reason,
        int $previousAmount,
        int $newAmount,
        ?string $note = null,
        ?Worker $worker = null
    ) {
        $this->category = $category;
        $this->delta = $delta;
        $this->reason = $reason;
        $this->previousAmount = $previousAmount;
        $this->newAmount = $newAmount;
        $this->note = $note;
        $this->worker = $worker;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getDelta(): int
    {
        return $this->delta;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function getPreviousAmount(): int
    {
        return $this->previousAmount;
    }

    public function getNewAmount(): int
    {
        return $this->newAmount;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getWorker(): ?Worker
    {
        return $this->worker;
    }
}
