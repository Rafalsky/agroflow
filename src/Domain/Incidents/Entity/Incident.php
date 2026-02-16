<?php

namespace App\Domain\Incidents\Entity;

use App\Domain\WorkCycle\Entity\Worker;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'incident')]
class Incident
{
    public const STATUS_NEW = 'NEW';
    public const STATUS_APPROVED = 'APPROVED';
    public const STATUS_REJECTED = 'REJECTED';
    public const STATUS_RESOLVED = 'RESOLVED';

    public const PRIORITY_LOW = 'LOW';
    public const PRIORITY_NORMAL = 'NORMAL';
    public const PRIORITY_HIGH = 'HIGH';
    public const PRIORITY_URGENT = 'URGENT';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\Column(length: 50)]
    private string $status = self::STATUS_NEW;

    #[ORM\Column(length: 50)]
    private string $priority = self::PRIORITY_NORMAL;

    #[ORM\ManyToOne(targetEntity: Worker::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Worker $reportedBy;

    #[ORM\Column]
    private \DateTimeImmutable $reportedAt;

    #[ORM\ManyToOne(targetEntity: Worker::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Worker $handledBy = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $handledAt = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $adminComment = null;

    public function __construct(Worker $reportedBy, string $title, string $description, string $priority = self::PRIORITY_NORMAL)
    {
        $this->reportedBy = $reportedBy;
        $this->title = $title;
        $this->description = $description;
        $this->priority = $priority;
        $this->reportedAt = new \DateTimeImmutable();
        $this->status = self::STATUS_NEW;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getPriority(): string
    {
        return $this->priority;
    }

    public function setPriority(string $priority): self
    {
        $this->priority = $priority;
        return $this;
    }

    public function getReportedBy(): Worker
    {
        return $this->reportedBy;
    }

    public function getReportedAt(): \DateTimeImmutable
    {
        return $this->reportedAt;
    }

    public function getHandledBy(): ?Worker
    {
        return $this->handledBy;
    }

    public function setHandledBy(?Worker $handledBy): self
    {
        $this->handledBy = $handledBy;
        return $this;
    }

    public function getHandledAt(): ?\DateTimeImmutable
    {
        return $this->handledAt;
    }

    public function setHandledAt(?\DateTimeImmutable $handledAt): self
    {
        $this->handledAt = $handledAt;
        return $this;
    }

    public function getAdminComment(): ?string
    {
        return $this->adminComment;
    }

    public function setAdminComment(?string $adminComment): self
    {
        $this->adminComment = $adminComment;
        return $this;
    }
}
