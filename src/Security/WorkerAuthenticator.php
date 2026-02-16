<?php

namespace App\Security;

use App\Domain\WorkCycle\Entity\Worker;
use App\Domain\WorkCycle\Repository\WorkerRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class WorkerAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private WorkerRepository $workerRepository,
        private RouterInterface $router
    ) {
    }

    public function supports(Request $request): ?bool
    {
        // Support routes that have accessToken parameter
        return $request->attributes->has('accessToken');
    }

    public function authenticate(Request $request): Passport
    {
        $token = $request->attributes->get('accessToken');

        if (!$token) {
            throw new CustomUserMessageAuthenticationException('No access token provided.');
        }

        return new SelfValidatingPassport(
            new UserBadge($token, function ($token) {
                $worker = $this->workerRepository->findOneBy(['accessToken' => $token]);

                if (!$worker) {
                    throw new CustomUserMessageAuthenticationException('Invalid access token.');
                }

                if (!$worker->isActive()) {
                    throw new CustomUserMessageAuthenticationException('Worker account is inactive.');
                }

                return $worker;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Redirect to worker tasks view after successful authentication
        return new RedirectResponse($this->router->generate('worker_tasks'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new Response('Authentication failed: ' . $exception->getMessage(), Response::HTTP_FORBIDDEN);
    }
}
