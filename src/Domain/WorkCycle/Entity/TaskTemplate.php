<?php

namespace App\Domain\WorkCycle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'task_template')]
#[ORM\Index(columns: ['recurring', 'active'], name: 'idx_task_template_recurring_active')]
class TaskTemplate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'integer')]
    private int $points = 0;

    #[ORM\Column(type: 'string', length: 20)]
    private string $priority = 'NORMAL'; // NORMAL, URGENT

    #[ORM\Column(type: 'integer')]
    private int $weekday = 1; // 1=Mon, 7=Sun

    #[ORM\Column(type: 'boolean')]
    private bool $recurring = false;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $category = null;

    #[ORM\Column(type: 'boolean')]
    private bool $active = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function setPoints(int $points): static
    {
        $this->points = $points;

        return $this;
    }

    public function getPriority(): string
    {
        return $this->priority;
    }

    public function setPriority(string $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    public function getWeekday(): int
    {
        return $this->weekday;
    }

    public function setWeekday(int $weekday): static
    {
        $this->weekday = $weekday;

        return $this;
    }

    public function isRecurring(): bool
    {
        return $this->recurring;
    }

    public function setRecurring(bool $recurring): static
    {
        $this->recurring = $recurring;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }
}
