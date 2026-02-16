<?php

namespace App\Controller\Admin;

use App\Domain\Scoring\Repository\ScoreLedgerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/scoring', name: 'admin_scoring_')]
class ScoringController extends AbstractController
{
    public function __construct(
        private ScoreLedgerRepository $scoreRepository
    ) {
    }

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $leaderboard = $this->scoreRepository->getLeaderboard();

        // Fetch last 20 transactions
        $recentTransactions = $this->scoreRepository->findBy([], ['createdAt' => 'DESC'], 20);

        return $this->render('admin/scoring/index.html.twig', [
            'leaderboard' => $leaderboard,
            'recentTransactions' => $recentTransactions,
        ]);
    }
}
