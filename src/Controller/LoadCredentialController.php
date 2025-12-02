<?php

namespace Lle\CredentialBundle\Controller;

use Lle\CredentialBundle\Exception\ConfigurationClientUrlNotDefinedException;
use Lle\CredentialBundle\Exception\ConfigurationProjectCodeNotDefinedException;
use Lle\CredentialBundle\Service\LoadCredentialService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class LoadCredentialController extends AbstractController
{
    public function __construct(
        protected LoadCredentialService $loadCredentialService,
    ) {
    }

    #[IsGranted('ROLE_ADMIN_DROITS')]
    #[Route('/load', name: 'admin_credential_load')]
    public function loadCredentials(): Response
    {
        try {
            $this->loadCredentialService->load();
        } catch (ConfigurationClientUrlNotDefinedException | ConfigurationProjectCodeNotDefinedException) {
            $this->addFlash('danger', 'You must defined client configuration');
        }

        return $this->redirectToRoute('admin_credential');
    }
}
