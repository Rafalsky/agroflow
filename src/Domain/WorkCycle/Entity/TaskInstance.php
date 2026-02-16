<?php

namespace App\Domain\WorkCycle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'task_instance')]
#[ORM\Index(columns: ['week_id'], name: 'idx_task_instance_week')]
#[ORM\Index(columns: ['status'], name: 'idx_task_instance_status')]
class TaskInstance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ProductionWeek::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProductionWeek $week = null;

    #[ORM\ManyToOne(targetEntity: TaskTemplate::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?TaskTemplate $template = null;

    // Snapshots
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $nameSnapshot = null;

    #[ORM\Column(type: 'integer')]
    private int $pointsSnapshot = 0;

    #[ORM\Column(type: 'string', length: 20)]
    private string $prioritySnapshot = 'NORMAL';

    #[ORM\Column(type: 'integer')]
    private int $weekdaySnapshot = 1;

    // Execution
    #[ORM\Column(type: 'string', length: 20)]
    private string $status = 'PENDING'; // PENDING, DONE

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $doneAt = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    private ?Worker $worker = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWeek(): ?ProductionWeek
    {
        return $this->week;
    }

    public function setWeek(?ProductionWeek $week): static
    {
        $this->week = $week;

        return $this;
    }

    public function getTemplate(): ?TaskTemplate
    {
        return $this->template;
    }

    public function setTemplate(?TaskTemplate $template): static
    {
        $this->template = $template;

        return $this;
    }

    public function getNameSnapshot(): ?string
    {
        return $this->nameSnapshot;
    }

    public function setNameSnapshot(string $nameSnapshot): static
    {
        $this->nameSnapshot = $nameSnapshot;

        return $this;
    }

    public function getPointsSnapshot(): int
    {
        return $this->pointsSnapshot;
    }

    public function setPointsSnapshot(int $pointsSnapshot): static
    {
        $this->pointsSnapshot = $pointsSnapshot;

        return $this;
    }

    public function getPrioritySnapshot(): string
    {
        return $this->prioritySnapshot;
    }

    public function setPrioritySnapshot(string $prioritySnapshot): static
    {
        $this->prioritySnapshot = $prioritySnapshot;

        return $this;
    }

    public function getWeekdaySnapshot(): int
    {
        return $this->weekdaySnapshot;
    }

    public function setWeekdaySnapshot(int $weekdaySnapshot): static
    {
        $this->weekdaySnapshot = $weekdaySnapshot;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getDoneAt(): ?\DateTimeImmutable
    {
        return $this->doneAt;
    }

    public function setDoneAt(?\DateTimeImmutable $doneAt): static
    {
        $this->doneAt = $doneAt;

        return $this;
    }

    public function getWorker(): ?Worker
    {
        return $this->worker;
    }

    public function setWorker(?Worker $worker): static
    {
        $this->worker = $worker;

        return $this;
    }
}
