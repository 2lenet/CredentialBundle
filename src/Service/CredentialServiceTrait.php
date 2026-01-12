<?php

namespace Lle\CredentialBundle\Service;

use Lle\CredentialBundle\Entity\Credential;

trait CredentialServiceTrait
{
    /**
     * @param Credential[] $credentials
     */
    public function getCredentials(array $credentials): array
    {
        $data = [];
        /** @var array $locales */
        $locales = $this->parameterBag->get('locales');
        foreach ($credentials as $credential) {
            $labelTranslations = [];
            $typeTranslations = [];
            foreach ($locales as $locale) {
                if ($credential->getLabel()) {
                    $labelTranslations[$locale] = $this->translator->trans(
                        $credential->getLabel(),
                        locale: $locale
                    );
                }

                if ($credential->getType()) {
                    $typeTranslations[$locale] = $this->translator->trans(
                        $credential->getType(),
                        locale: $locale
                    );
                }
            }

            $data[] = [
                'role' => $credential->getRole(),
                'section' => $credential->getSection(),
                'label' => $credential->getLabel(),
                'type' => $credential->getType(),
                'statusList' => $credential->getStatusList(),
                'visible' => $credential->isVisible(),
                'labelTranslations' => $labelTranslations,
                'typeTranslations' => $typeTranslations,
            ];
        }

        return $data;
    }

    public function resetCache(): void
    {
        if ($this->cache->hasItem('group_credentials')) {
            $this->cache->deleteItem('group_credentials');
        }

        if ($this->cache->hasItem('all_credentials')) {
            $this->cache->deleteItem('all_credentials');
        }
    }
}
