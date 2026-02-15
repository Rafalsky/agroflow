<?php

namespace App\Controller;

use App\Domain\Audit\Service\AuditLogger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    private AuditLogger $auditLogger;

    public function __construct(AuditLogger $auditLogger)
    {
        $this->auditLogger = $auditLogger;
    }

    #[Route('/debug-db', name: 'debug_db')]
    public function debugDb(\Doctrine\ORM\EntityManagerInterface $em): Response
    {
        $conn = $em->getConnection();
        $params = $conn->getParams();

        // Mask password
        if (isset($params['password'])) {
            $params['password'] = '***';
        }

        try {
            $tables = $conn->createSchemaManager()->listTableNames();
        } catch (\Exception $e) {
            $tables = ['Error: ' . $e->getMessage()];
        }

        return $this->json([
            'connection_params' => $params,
            'tables' => $tables,
            'database_url_env' => $_ENV['DATABASE_URL'] ?? 'NOT_SET',
        ]);
    }

    #[Route('', name: 'dashboard')]
    public function index(): Response
    {
        $this->auditLogger->log(
            entityType: 'admin',
            entityId: 'dashboard',
            eventType: 'admin.opened',
            payload: ['page' => 'dashboard']
        );

        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
}
