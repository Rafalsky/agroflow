<?php

namespace App\Domain\Scoring\Entity;

use App\Domain\Scoring\Repository\ScoreLedgerRepository;
use App\Domain\WorkCycle\Entity\TaskInstance;
use App\Domain\WorkCycle\Entity\Worker;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScoreLedgerRepository::class)]
#[ORM\Table(name: 'score_ledger')]
class ScoreLedger
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Worker::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Worker $worker = null;

    #[ORM\Column]
    private ?int $amount = null;

    #[ORM\Column(length: 255)]
    private ?string $reason = null;

    #[ORM\ManyToOne(targetEntity: TaskInstance::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?TaskInstance $taskInstance = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct(Worker $worker, int $amount, string $reason, ?TaskInstance $taskInstance = null)
    {
        $this->worker = $worker;
        $this->amount = $amount;
        $this->reason = $reason;
        $this->taskInstance = $taskInstance;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWorker(): ?Worker
    {
        return $this->worker;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function getTaskInstance(): ?TaskInstance
    {
        return $this->taskInstance;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
