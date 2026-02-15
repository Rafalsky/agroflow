<?php

namespace App\Domain\Audit\Service;

use App\Domain\Audit\Entity\AuditLog;
use Doctrine\ORM\EntityManagerInterface;

class AuditLogger
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function log(
        string $entityType,
        string $entityId,
        string $eventType,
        array $payload = [],
        string $actorType = 'SYSTEM',
        ?string $actorId = null,
        ?string $ipAddress = null
    ): void {
        $auditLog = new AuditLog();
        $auditLog->setEntityType($entityType)
            ->setEntityId($entityId)
            ->setEventType($eventType)
            ->setPayload($payload)
            ->setActorType($actorType)
            ->setActorId($actorId)
            ->setIpAddress($ipAddress);

        $this->entityManager->persist($auditLog);
        $this->entityManager->flush();
    }
}
