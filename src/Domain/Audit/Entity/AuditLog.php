<?php

namespace App\Domain\Audit\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'audit_log')]
#[ORM\Index(columns: ['created_at'], name: 'idx_audit_log_created_at')]
#[ORM\Index(columns: ['entity_type'], name: 'idx_audit_log_entity_type')]
class AuditLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $entityType = null;

    #[ORM\Column(length: 255)]
    private ?string $entityId = null;

    #[ORM\Column(length: 255)]
    private ?string $eventType = null;

    #[ORM\Column(type: 'json', options: ['jsonb' => true])]
    private array $payload = [];

    #[ORM\Column(length: 50)]
    private string $actorType = 'SYSTEM';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $actorId = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $ipAddress = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntityType(): ?string
    {
        return $this->entityType;
    }

    public function setEntityType(string $entityType): static
    {
        $this->entityType = $entityType;

        return $this;
    }

    public function getEntityId(): ?string
    {
        return $this->entityId;
    }

    public function setEntityId(string $entityId): static
    {
        $this->entityId = $entityId;

        return $this;
    }

    public function getEventType(): ?string
    {
        return $this->eventType;
    }

    public function setEventType(string $eventType): static
    {
        $this->eventType = $eventType;

        return $this;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function setPayload(array $payload): static
    {
        $this->payload = $payload;

        return $this;
    }

    public function getActorType(): string
    {
        return $this->actorType;
    }

    public function setActorType(string $actorType): static
    {
        $this->actorType = $actorType;

        return $this;
    }

    public function getActorId(): ?string
    {
        return $this->actorId;
    }

    public function setActorId(?string $actorId): static
    {
        $this->actorId = $actorId;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): static
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }
}
