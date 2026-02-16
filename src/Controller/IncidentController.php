<?php

namespace App\Controller;

use App\Domain\Incidents\Entity\Incident;
use App\Domain\Incidents\Service\IncidentService;
use App\Domain\WorkCycle\Entity\Worker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/incidents')]
class IncidentController extends AbstractController
{
    public function __construct(
        private IncidentService $incidentService
    ) {
    }

    #[Route('/admin', name: 'admin_incidents', methods: ['GET'])]
    public function adminIndex(): Response
    {
        return $this->render('admin/incidents/index.html.twig');
    }

    #[Route('', name: 'incident_list', methods: ['GET'])]

    public function list(Request $request): JsonResponse
    {
        $all = $request->query->get('all') === 'true';
        $incidents = $all ? $this->incidentService->getAllIncidents() : $this->incidentService->getActiveIncidents();

        $data = [];
        foreach ($incidents as $incident) {
            $data[] = $this->serializeIncident($incident);
        }

        return new JsonResponse($data);
    }

    #[Route('/report', name: 'incident_report', methods: ['POST'])]
    public function report(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $title = $data['title'] ?? null;
        $description = $data['description'] ?? null;
        $priority = $data['priority'] ?? Incident::PRIORITY_NORMAL;

        if (!$title || !$description) {
            return new JsonResponse(['error' => 'Title and description are required'], Response::HTTP_BAD_REQUEST);
        }

        /** @var Worker $worker */
        $worker = $this->getUser();
        if (!$worker) {
            return new JsonResponse(['error' => 'Worker not found'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $incident = $this->incidentService->report($worker, $title, $description, $priority);
            return new JsonResponse($this->serializeIncident($incident), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}/approve', name: 'incident_approve', methods: ['POST'])]
    public function approve(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $comment = $data['comment'] ?? null;

        /** @var Worker $admin */
        $admin = $this->getUser();

        try {
            $incident = $this->incidentService->approve($id, $admin, $comment);
            return new JsonResponse($this->serializeIncident($incident));
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}/reject', name: 'incident_reject', methods: ['POST'])]
    public function reject(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $reason = $data['reason'] ?? null;

        if (!$reason) {
            return new JsonResponse(['error' => 'Reason is required for rejection'], Response::HTTP_BAD_REQUEST);
        }

        /** @var Worker $admin */
        $admin = $this->getUser();

        try {
            $incident = $this->incidentService->reject($id, $admin, $reason);
            return new JsonResponse($this->serializeIncident($incident));
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}/resolve', name: 'incident_resolve', methods: ['POST'])]
    public function resolve(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $comment = $data['comment'] ?? null;

        /** @var Worker $worker */
        $worker = $this->getUser();

        try {
            $incident = $this->incidentService->resolve($id, $worker, $comment);
            return new JsonResponse($this->serializeIncident($incident));
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/export/csv', name: 'incident_export_csv', methods: ['GET'])]
    public function exportCsv(): Response
    {
        $incidents = $this->incidentService->getAllIncidents();

        $fp = fopen('php://temp', 'w');
        fputcsv($fp, ['ID', 'Title', 'Description', 'Status', 'Priority', 'Reported By', 'Reported At', 'Handled By', 'Handled At', 'Comment/Reason']);

        foreach ($incidents as $incident) {
            fputcsv($fp, [
                $incident->getId(),
                $incident->getTitle(),
                $incident->getDescription(),
                $incident->getStatus(),
                $incident->getPriority(),
                $incident->getReportedBy()->getName(),
                $incident->getReportedAt()->format('Y-m-d H:i:s'),
                $incident->getHandledBy() ? $incident->getHandledBy()->getName() : '',
                $incident->getHandledAt() ? $incident->getHandledAt()->format('Y-m-d H:i:s') : '',
                $incident->getAdminComment()
            ]);
        }

        rewind($fp);
        $csv = stream_get_contents($fp);
        fclose($fp);

        $response = new Response($csv);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="incidents_' . date('YmdHis') . '.csv"');

        return $response;
    }

    private function serializeIncident(Incident $incident): array
    {
        return [
            'id' => $incident->getId(),
            'title' => $incident->getTitle(),
            'description' => $incident->getDescription(),
            'status' => $incident->getStatus(),
            'priority' => $incident->getPriority(),
            'reportedBy' => $incident->getReportedBy()->getName(),
            'reportedAt' => $incident->getReportedAt()->format('Y-m-d H:i:s'),
            'handledBy' => $incident->getHandledBy() ? $incident->getHandledBy()->getName() : null,
            'handledAt' => $incident->getHandledAt() ? $incident->getHandledAt()->format('Y-m-d H:i:s') : null,
            'adminComment' => $incident->getAdminComment(),
        ];
    }
}
