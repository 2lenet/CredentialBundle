<?php

namespace Lle\CredentialBundle\Service;

trait CredentialServiceTrait
{
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
