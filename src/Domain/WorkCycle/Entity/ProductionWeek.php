<?php

namespace App\Domain\WorkCycle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'production_week')]
#[ORM\UniqueConstraint(name: 'uniq_production_week_year_week', columns: ['year', 'week_number'])]
class ProductionWeek
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private ?int $year = null;

    #[ORM\Column(type: 'integer')]
    private ?int $weekNumber = null;

    #[ORM\Column(type: 'string', length: 20)]
    private string $status = 'OPEN'; // OPEN, CLOSED

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $openedAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $closedAt = null;

    public function __construct()
    {
        $this->openedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): static
    {
        $this->year = $year;

        return $this;
    }

    public function getWeekNumber(): ?int
    {
        return $this->weekNumber;
    }

    public function setWeekNumber(int $weekNumber): static
    {
        $this->weekNumber = $weekNumber;

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

    public function getOpenedAt(): ?\DateTimeImmutable
    {
        return $this->openedAt;
    }

    public function setOpenedAt(\DateTimeImmutable $openedAt): static
    {
        $this->openedAt = $openedAt;

        return $this;
    }

    public function getClosedAt(): ?\DateTimeImmutable
    {
        return $this->closedAt;
    }

    public function setClosedAt(?\DateTimeImmutable $closedAt): static
    {
        $this->closedAt = $closedAt;

        return $this;
    }
}
