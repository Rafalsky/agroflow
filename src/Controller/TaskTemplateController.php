<?php

namespace App\Controller;

use App\Domain\Audit\Service\AuditLogger;
use App\Domain\WorkCycle\Entity\TaskTemplate;
use App\Form\TaskTemplateType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/task-templates', name: 'admin_task_template_')]
class TaskTemplateController extends AbstractController
{
    private AuditLogger $auditLogger;

    public function __construct(AuditLogger $auditLogger)
    {
        $this->auditLogger = $auditLogger;
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $taskTemplates = $entityManager
            ->getRepository(TaskTemplate::class)
            ->findBy([], ['weekday' => 'ASC', 'priority' => 'DESC']);

        return $this->render('admin/task_template/index.html.twig', [
            'task_templates' => $taskTemplates,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $taskTemplate = new TaskTemplate();
        $form = $this->createForm(TaskTemplateType::class, $taskTemplate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($taskTemplate);
            $entityManager->flush();

            $this->auditLogger->log(
                entityType: 'task_template',
                entityId: (string) $taskTemplate->getId(),
                eventType: 'task_template.created',
                payload: ['name' => $taskTemplate->getName()]
            );

            return $this->redirectToRoute('admin_task_template_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/task_template/new.html.twig', [
            'task_template' => $taskTemplate,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TaskTemplate $taskTemplate, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TaskTemplateType::class, $taskTemplate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->auditLogger->log(
                entityType: 'task_template',
                entityId: (string) $taskTemplate->getId(),
                eventType: 'task_template.updated',
                payload: ['name' => $taskTemplate->getName()]
            );

            return $this->redirectToRoute('admin_task_template_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/task_template/edit.html.twig', [
            'task_template' => $taskTemplate,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, TaskTemplate $taskTemplate, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $taskTemplate->getId(), $request->request->get('_token'))) {
            $id = $taskTemplate->getId();
            $entityManager->remove($taskTemplate);
            $entityManager->flush();

            $this->auditLogger->log(
                entityType: 'task_template',
                entityId: (string) $id,
                eventType: 'task_template.deleted'
            );
        }

        return $this->redirectToRoute('admin_task_template_index', [], Response::HTTP_SEE_OTHER);
    }
}
