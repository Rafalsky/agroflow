<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WorkerEntryController extends AbstractController
{
    #[Route('/w/', name: 'worker_entry', methods: ['GET', 'POST'])]
    public function entry(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $token = $request->request->get('access_token');
            if ($token) {
                return $this->redirectToRoute('worker_login', ['accessToken' => trim($token)]);
            }
            $this->addFlash('error', 'Wprowadź prawidłowy kod dostępu.');
        }

        return $this->render('worker/entry.html.twig');
    }
}