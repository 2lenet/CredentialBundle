<?php

namespace Lle\CredentialBundle\Controller;

use Lle\CredentialBundle\Service\LoadCredentialService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class LoadCredentialController extends AbstractController
{
    public function __construct(
        protected LoadCredentialService $loadCredentialService,
    ) {
    }

    #[Route('/admin/credential/load', name: 'admin_credential_load')]
    #[IsGranted('ROLE_ADMIN_DROITS')]
    public function loadCredentials(): Response
    {
        $this->loadCredentialService->load();

        return new Response();
    }
}
