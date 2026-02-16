<?php

namespace App\Domain\Incidents\Service;

use App\Domain\Audit\Service\AuditLogger;
use App\Domain\Incidents\Entity\Incident;
use App\Domain\WorkCycle\Entity\Worker;
use Doctrine\ORM\EntityManagerInterface;

class IncidentService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AuditLogger $auditLogger
    ) {
    }

    public function report(Worker $reporter, string $title, string $description, string $priority = Incident::PRIORITY_NORMAL): Incident
    {
        $incident = new Incident($reporter, $title, $description, $priority);

        $this->entityManager->persist($incident);
        $this->entityManager->flush();

        $this->auditLogger->log(
            sprintf('Incident reported: %s', $title),
            'incident',
            $incident->getId(),
            ['reported_by' => $reporter->getName(), 'priority' => $priority]
        );

        return $incident;
    }

    public function approve(int $id, Worker $admin, ?string $comment = null): Incident
    {
        $incident = $this->getIncident($id);

        if ($incident->getStatus() !== Incident::STATUS_NEW) {
            throw new \LogicException('Only NEW incidents can be approved.');
        }

        $incident->setStatus(Incident::STATUS_APPROVED);
        $incident->setHandledBy($admin);
        $incident->setHandledAt(new \DateTimeImmutable());
        $incident->setAdminComment($comment);

        $this->entityManager->flush();

        $this->auditLogger->log(
            sprintf('Incident approved: %s', $incident->getTitle()),
            'incident',
            $incident->getId(),
            ['approved_by' => $admin->getName(), 'comment' => $comment]
        );

        return $incident;
    }

    public function reject(int $id, Worker $admin, string $reason): Incident
    {
        $incident = $this->getIncident($id);

        if ($incident->getStatus() !== Incident::STATUS_NEW) {
            throw new \LogicException('Only NEW incidents can be rejected.');
        }

        $incident->setStatus(Incident::STATUS_REJECTED);
        $incident->setHandledBy($admin);
        $incident->setHandledAt(new \DateTimeImmutable());
        $incident->setAdminComment($reason);

        $this->entityManager->flush();

        $this->auditLogger->log(
            sprintf('Incident rejected: %s', $incident->getTitle()),
            'incident',
            $incident->getId(),
            ['rejected_by' => $admin->getName(), 'reason' => $reason]
        );

        return $incident;
    }

    public function resolve(int $id, Worker $worker, ?string $comment = null): Incident
    {
        $incident = $this->getIncident($id);

        if ($incident->getStatus() !== Incident::STATUS_APPROVED) {
            throw new \LogicException('Only APPROVED incidents can be resolved.');
        }

        $incident->setStatus(Incident::STATUS_RESOLVED);
        $incident->setHandledBy($worker);
        $incident->setHandledAt(new \DateTimeImmutable());
        if ($comment) {
            $incident->setAdminComment($incident->getAdminComment() . "\nResolution note: " . $comment);
        }

        $this->entityManager->flush();

        $this->auditLogger->log(
            sprintf('Incident resolved: %s', $incident->getTitle()),
            'incident',
            $incident->getId(),
            ['resolved_by' => $worker->getName(), 'comment' => $comment]
        );

        return $incident;
    }

    public function getActiveIncidents(): array
    {
        return $this->entityManager->getRepository(Incident::class)
            ->findBy(['status' => [Incident::STATUS_NEW, Incident::STATUS_APPROVED]], ['reportedAt' => 'DESC']);
    }

    public function getAllIncidents(): array
    {
        return $this->entityManager->getRepository(Incident::class)
            ->findBy([], ['reportedAt' => 'DESC']);
    }

    private function getIncident(int $id): Incident
    {
        $incident = $this->entityManager->getRepository(Incident::class)->find($id);

        if (!$incident) {
            throw new \InvalidArgumentException(sprintf('Incident with ID %d not found.', $id));
        }

        return $incident;
    }
}
