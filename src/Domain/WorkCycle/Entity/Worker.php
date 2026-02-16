<?php

namespace App\Domain\WorkCycle\Entity;

use App\Domain\WorkCycle\Repository\WorkerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: WorkerRepository::class)]
#[ORM\Table(name: 'worker')]
class Worker implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    private ?string $shortName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $accessToken = null;

    #[ORM\Column]
    private ?bool $active = true;

    #[ORM\OneToMany(targetEntity: TaskInstance::class, mappedBy: 'worker')]
    private Collection $tasks;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
    }

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

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setShortName(string $shortName): static
    {
        $this->shortName = $shortName;

        return $this;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(?string $accessToken): static
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return Collection<int, TaskInstance>
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(TaskInstance $task): static
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks->add($task);
            $task->setWorker($this);
        }

        return $this;
    }

    public function removeTask(TaskInstance $task): static
    {
        if ($this->tasks->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getWorker() === $this) {
                $task->setWorker(null);
            }
        }

        return $this;
    }

    // UserInterface implementation
    public function getUserIdentifier(): string
    {
        return (string) $this->id;
    }

    public function getRoles(): array
    {
        // Workers have a simple ROLE_WORKER role
        return ['ROLE_WORKER'];
    }

    public function eraseCredentials(): void
    {
        // Nothing to erase - we use access tokens
    }
}
