<?php

namespace Lle\CredentialBundle\Controller;

use Lle\CredentialBundle\Entity\Credential;
use Lle\CredentialBundle\Entity\Group;
use Lle\CredentialBundle\Exception\ConfigurationClientUrlNotDefinedException;
use Lle\CredentialBundle\Exception\ConfigurationProjectCodeNotDefinedException;
use Lle\CredentialBundle\Exception\ConfigurationProjectTokenNotDefinedException;
use Lle\CredentialBundle\Exception\RemoteApiException;
use Lle\CredentialBundle\Service\CredentialService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class CredentialController extends AbstractController
{
    public function __construct(
        protected CredentialService $credentialService,
        protected Security $security,
        protected TranslatorInterface $translator,
    ) {
    }

    #[IsGranted('ROLE_CREDENTIAL_ACTION_TOGGLEGROUP')]
    #[Route('/toggle-group/{group}/{check}')]
    public function toggleGroup(Group $group, bool $check): JsonResponse
    {
        try {
            $this->credentialService->toggleGroup($group, $check);
        } catch (ConfigurationClientUrlNotDefinedException | ConfigurationProjectCodeNotDefinedException | ConfigurationProjectTokenNotDefinedException) {
            return new JsonResponse(['error' => $this->translator->trans('flash.credential.misconfigured', [], 'CredentialBundle')], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (RemoteApiException $e) {
            return new JsonResponse(['remoteError' => $this->translator->trans('flash.credential.toggle_group.remote_sync_failed', ['%error%' => $e->getMessage()], 'CredentialBundle')]);
        }

        return new JsonResponse();
    }

    #[IsGranted('ROLE_CREDENTIAL_ACTION_TOGGLERUBRIQUE')]
    #[Route('/toggle-section/{section}/{group}/{check}')]
    public function toggleSection(string $section, Group $group, bool $check): JsonResponse
    {
        try {
            $this->credentialService->toggleSection($section, $group, $check);
        } catch (ConfigurationClientUrlNotDefinedException | ConfigurationProjectCodeNotDefinedException | ConfigurationProjectTokenNotDefinedException) {
            return new JsonResponse(['error' => $this->translator->trans('flash.credential.misconfigured', [], 'CredentialBundle')], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (RemoteApiException $e) {
            return new JsonResponse(['remoteError' => $this->translator->trans('flash.credential.toggle_section.remote_sync_failed', ['%error%' => $e->getMessage()], 'CredentialBundle')]);
        }

        return new JsonResponse();
    }

    #[IsGranted('ROLE_CREDENTIAL_ACTION_TOGGLECREDENTIAL')]
    #[Route('/toggle-credential/{credential}/{group}/{check}')]
    public function toggleCredential(Credential $credential, Group $group, bool $check): JsonResponse
    {
        try {
            $this->credentialService->toggleCredential($credential, $group, $check);
        } catch (ConfigurationClientUrlNotDefinedException | ConfigurationProjectCodeNotDefinedException | ConfigurationProjectTokenNotDefinedException) {
            return new JsonResponse(['error' => $this->translator->trans('flash.credential.misconfigured', [], 'CredentialBundle')], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (RemoteApiException $e) {
            return new JsonResponse(['remoteError' => $this->translator->trans('flash.credential.toggle_credential.remote_sync_failed', ['%error%' => $e->getMessage()], 'CredentialBundle')]);
        }

        return new JsonResponse();
    }

    #[IsGranted('ROLE_CREDENTIAL_ACTION_ALLOWSTATUS')]
    #[Route('/allow-status/{credential}/{group}/{check}')]
    public function allowStatus(Credential $credential, Group $group, bool $check): JsonResponse
    {
        try {
            $this->credentialService->allowStatus($credential, $group, $check);
        } catch (ConfigurationClientUrlNotDefinedException | ConfigurationProjectCodeNotDefinedException | ConfigurationProjectTokenNotDefinedException) {
            return new JsonResponse(['error' => $this->translator->trans('flash.credential.misconfigured', [], 'CredentialBundle')], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (RemoteApiException $e) {
            return new JsonResponse(['remoteError' => $this->translator->trans('flash.credential.allow_status.remote_sync_failed', ['%error%' => $e->getMessage()], 'CredentialBundle')]);
        }

        return new JsonResponse();
    }

    #[IsGranted('ROLE_CREDENTIAL_ACTION_ALLOWSTATUS')]
    #[Route('/allow-for-status/{credential}/{group}/{status}/{check}')]
    public function allowForStatus(Credential $credential, Group $group, string $status, bool $check): JsonResponse
    {
        try {
            $this->credentialService->allowForStatus($credential, $group, $status, $check);
        } catch (ConfigurationClientUrlNotDefinedException | ConfigurationProjectCodeNotDefinedException | ConfigurationProjectTokenNotDefinedException) {
            return new JsonResponse(['error' => $this->translator->trans('flash.credential.misconfigured', [], 'CredentialBundle')], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (RemoteApiException $e) {
            return new JsonResponse(['remoteError' => $this->translator->trans('flash.credential.allow_for_status.remote_sync_failed', ['%error%' => $e->getMessage()], 'CredentialBundle')]);
        }

        return new JsonResponse();
    }
}
