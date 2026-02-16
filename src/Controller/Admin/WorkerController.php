<?php

namespace App\Controller\Admin;

use App\Domain\Audit\Service\AuditLogger;
use App\Domain\WorkCycle\Entity\Worker;
use App\Domain\WorkCycle\Repository\WorkerRepository;
use App\Form\WorkerType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/admin/workers', name: 'admin_worker_')]
class WorkerController extends AbstractController
{
    private AuditLogger $auditLogger;

    public function __construct(AuditLogger $auditLogger)
    {
        $this->auditLogger = $auditLogger;
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(WorkerRepository $workerRepository): Response
    {
        return $this->render('admin/worker/index.html.twig', [
            'workers' => $workerRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $worker = new Worker();
        $form = $this->createForm(WorkerType::class, $worker);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Generate Access Token (simple UUID for now)
            $worker->setAccessToken(Uuid::v7()->toRfc4122());
            
            $entityManager->persist($worker);
            $entityManager->flush();

            $this->auditLogger->log(
                entityType: 'worker',
                entityId: (string) $worker->getId(),
                eventType: 'worker.created',
                payload: ['name' => $worker->getName()]
            );

            return $this->redirectToRoute('admin_worker_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/worker/new.html.twig', [
            'worker' => $worker,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Worker $worker, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(WorkerType::class, $worker);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->auditLogger->log(
                entityType: 'worker',
                entityId: (string) $worker->getId(),
                eventType: 'worker.updated',
                payload: ['name' => $worker->getName()]
            );

            return $this->redirectToRoute('admin_worker_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/worker/edit.html.twig', [
            'worker' => $worker,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Worker $worker, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$worker->getId(), $request->request->get('_token'))) {
            $id = $worker->getId();
            $entityManager->remove($worker);
            $entityManager->flush();

            $this->auditLogger->log(
                entityType: 'worker',
                entityId: (string) $id,
                eventType: 'worker.deleted'
            );
        }

        return $this->redirectToRoute('admin_worker_index', [], Response::HTTP_SEE_OTHER);
    }
}
