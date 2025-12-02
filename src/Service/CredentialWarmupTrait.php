<?php

namespace Lle\CredentialBundle\Service;

use Lle\CredentialBundle\Entity\Credential;

trait CredentialWarmupTrait
{
    protected function checkAndCreateCredential(
        string $role,
        ?string $section,
        ?string $label,
        ?array $statusList = null,
        ?bool $visible = true,
        ?string $type = null,
    ): void {
        $credential = $this->entityManager->getRepository(Credential::class)->findOneBy(['role' => $role]);
        if ($credential) {
            $this->credentialFactory->update($credential, $section, $label, $statusList, $visible, $type);
        } else {
            $this->credentialFactory->create($role, $section, $label, $statusList, $visible, $type);
        }

        $this->entityManager->flush();
    }
}
