<?php

namespace App\Controller;

use App\Domain\Welfare\Service\WelfareService;
use App\Domain\WorkCycle\Entity\Worker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/welfare')]
class WelfareController extends AbstractController
{
    public function __construct(
        private WelfareService $welfareService
    ) {
    }

    #[Route('/data', name: 'welfare_data', methods: ['GET'])]
    public function getData(): JsonResponse
    {
        $history = $this->welfareService->getRecentHistory();
        $historyData = [];

        foreach ($history as $change) {
            $historyData[] = [
                'id' => $change->getId(),
                'category' => $change->getCategory(),
                'delta' => $change->getDelta(),
                'reason' => $change->getReason(),
                'note' => $change->getNote(),
                'previousAmount' => $change->getPreviousAmount(),
                'newAmount' => $change->getNewAmount(),
                'createdAt' => $change->getCreatedAt()->format('Y-m-d H:i:s'),
                'workerName' => $change->getWorker() ? $change->getWorker()->getShortName() : 'System',
            ];
        }

        return new JsonResponse([
            'state' => $this->welfareService->getCurrentState(),
            'history' => $historyData,
        ]);
    }

    #[Route('/change', name: 'welfare_change', methods: ['POST'])]
    public function applyChange(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $category = $data['category'] ?? null;
        $delta = (int) ($data['delta'] ?? 0);
        $reason = $data['reason'] ?? 'ADJUSTMENT';
        $note = $data['note'] ?? null;

        if (!$category) {
            return new JsonResponse(['error' => 'Category is required'], Response::HTTP_BAD_REQUEST);
        }

        /** @var Worker|null $worker */
        $worker = $this->getUser();

        try {
            $this->welfareService->applyChange($category, $delta, $reason, $note, $worker);

            return $this->getData();
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
