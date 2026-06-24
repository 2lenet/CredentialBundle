<?php

namespace Lle\CredentialBundle\Controller;

use Lle\CredentialBundle\Exception\ConfigurationClientUrlNotDefinedException;
use Lle\CredentialBundle\Exception\ConfigurationProjectCodeNotDefinedException;
use Lle\CredentialBundle\Exception\ConfigurationProjectTokenNotDefinedException;
use Lle\CredentialBundle\Exception\RemoteApiException;
use Lle\CredentialBundle\Service\LoadCredentialService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoadCredentialController extends AbstractController
{
    public function __construct(
        protected LoadCredentialService $loadCredentialService,
        protected TranslatorInterface $translator,
    ) {
    }

    #[IsGranted('ROLE_CREDENTIAL_ACTION_UPDATE')]
    #[Route('/load', name: 'admin_credential_load')]
    public function loadCredentials(): Response
    {
        try {
            $this->loadCredentialService->load();
        } catch (ConfigurationClientUrlNotDefinedException | ConfigurationProjectCodeNotDefinedException | ConfigurationProjectTokenNotDefinedException) {
            $this->addFlash('danger', $this->translator->trans('flash.load.misconfigured', [], 'CredentialBundle'));
        } catch (RemoteApiException $e) {
            $this->addFlash('danger', $this->translator->trans('flash.load.remote_error', ['%error%' => $e->getMessage()], 'CredentialBundle'));
        }

        return $this->redirectToRoute('admin_credential');
    }
}
