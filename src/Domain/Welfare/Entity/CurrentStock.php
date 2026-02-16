<?php

namespace App\Domain\Welfare\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'welfare_current_stock')]
class CurrentStock
{
    public const CATEGORY_SOWS = 'SOWS';
    public const CATEGORY_PIGLETS = 'PIGLETS';
    public const CATEGORY_FATTENERS = 'FATTENERS';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    private string $category;

    #[ORM\Column]
    private int $amount = 0;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct(string $category, int $amount = 0)
    {
        $this->category = $category;
        $this->amount = $amount;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
