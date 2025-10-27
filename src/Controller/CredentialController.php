<?php

namespace Lle\CredentialBundle\Controller\Credential;

use Lle\CredentialBundle\Entity\Credential;
use Lle\CredentialBundle\Entity\Group;
use Lle\CredentialBundle\Service\CredentialService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/credential')]
class CredentialController extends AbstractController
{
    public function __construct(
        protected CredentialService $credentialService,
        protected Security $security,
    ) {
    }

    #[IsGranted('ROLE_CREDENTIAL_ACTION_TOGGLEGROUP')]
    #[Route('/toggle-group/{group}/{check}')]
    public function toggleGroup(group $group, bool $check): JsonResponse
    {
        $this->credentialService->toggleGroup($group, $check);

        return new JsonResponse();
    }

    #[IsGranted('ROLE_CREDENTIAL_ACTION_TOGGLERUBRIQUE')]
    #[Route('/toggle-rubrique/{rubrique}/{group}/{check}')]
    public function toggleRubrique(string $rubrique, Group $group, bool $check): JsonResponse
    {
        $this->credentialService->toggleRubrique($rubrique, $group, $check);

        return new JsonResponse();
    }

    #[IsGranted('ROLE_CREDENTIAL_ACTION_TOGGLECREDENTIAL')]
    #[Route('/toggle-credential/{credential}/{group}/{check}')]
    public function toggleCredential(Credential $credential, Group $group, bool $check): JsonResponse
    {
        $this->credentialService->toggleCredential($credential, $group, $check);

        return new JsonResponse();
    }

    #[IsGranted('ROLE_CREDENTIAL_ACTION_ALLOWSTATUS')]
    #[Route('/allow-status/{credential}/{group}/{check}')]
    public function allowStatus(Credential $credential, Group $group, bool $check): JsonResponse
    {
        $this->credentialService->allowStatus($credential, $group, $check);

        return new JsonResponse();
    }

    #[IsGranted('ROLE_CREDENTIAL_ACTION_ALLOWSTATUS')]
    #[Route('/allow-for-status/{credential}/{group}/{status}/{check}')]
    public function allowForStatus(Credential $credential, Group $group, string $status, bool $check): JsonResponse
    {
        $this->credentialService->allowForStatus($credential, $group, $status, $check);

        return new JsonResponse();
    }
}
