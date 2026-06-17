<?php

namespace Lle\CredentialBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Lle\CredentialBundle\Entity\Group;
use Lle\CredentialBundle\Exception\ConfigurationClientUrlNotDefinedException;
use Lle\CredentialBundle\Exception\ConfigurationProjectCodeNotDefinedException;
use Lle\CredentialBundle\Exception\ConfigurationProjectTokenNotDefinedException;
use Lle\CredentialBundle\Exception\RemoteApiException;
use Lle\CredentialBundle\Service\ClientService;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsEntityListener(event: Events::postPersist, entity: Group::class)]
#[AsEntityListener(event: Events::preUpdate, entity: Group::class)]
#[AsEntityListener(event: Events::postUpdate, entity: Group::class)]
#[AsEntityListener(event: Events::postRemove, entity: Group::class)]
class GroupListener
{
    private bool $enabled = true;

    /** @var array<int, string> */
    private array $oldNames = [];

    public function __construct(
        protected ClientService $clientService,
        protected RequestStack $requestStack,
        protected TranslatorInterface $translator,
    ) {
    }

    private function addFlash(string $type, string $translationKey, array $parameters = []): void
    {
        if ($this->requestStack->getCurrentRequest() === null) {
            return;
        }

        try {
            $session = $this->requestStack->getSession();
            if ($session instanceof FlashBagAwareSessionInterface) {
                $message = $this->translator->trans($translationKey, $parameters, 'CredentialBundle');
                $session->getFlashBag()->add($type, $message);
            }
        } catch (SessionNotFoundException) {
        }
    }

    public function enable(): void
    {
        $this->enabled = true;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }

    public function postPersist(Group $group, PostPersistEventArgs $event): void
    {
        if (!$this->enabled) {
            return;
        }

        try {
            $this->clientService->createGroup($group);
        } catch (ConfigurationClientUrlNotDefinedException | ConfigurationProjectCodeNotDefinedException | ConfigurationProjectTokenNotDefinedException) {
            $this->addFlash('danger', 'flash.group.created.misconfigured');
        } catch (RemoteApiException $e) {
            $this->addFlash('warning', 'flash.group.created.remote_sync_failed', ['%error%' => $e->getMessage()]);
        }
    }

    public function preUpdate(Group $group, PreUpdateEventArgs $event): void
    {
        if ($event->hasChangedField('name')) {
            $this->oldNames[spl_object_id($group)] = (string) $event->getOldValue('name');
        }
    }

    public function postUpdate(Group $group, PostUpdateEventArgs $event): void
    {
        if (!$this->enabled) {
            return;
        }

        $oid = spl_object_id($group);
        $oldName = $this->oldNames[$oid] ?? (string) $group->getName();
        unset($this->oldNames[$oid]);

        try {
            $this->clientService->editGroup($oldName, $group);
        } catch (ConfigurationClientUrlNotDefinedException | ConfigurationProjectCodeNotDefinedException | ConfigurationProjectTokenNotDefinedException) {
            $this->addFlash('danger', 'flash.group.updated.misconfigured');
        } catch (RemoteApiException $e) {
            $this->addFlash('warning', 'flash.group.updated.remote_sync_failed', ['%error%' => $e->getMessage()]);
        }
    }

    public function postRemove(Group $group, PostRemoveEventArgs $event): void
    {
        if (!$this->enabled) {
            return;
        }

        try {
            $this->clientService->deleteGroup($group);
        } catch (ConfigurationClientUrlNotDefinedException | ConfigurationProjectCodeNotDefinedException | ConfigurationProjectTokenNotDefinedException) {
            $this->addFlash('danger', 'flash.group.deleted.misconfigured');
        } catch (RemoteApiException $e) {
            $this->addFlash('warning', 'flash.group.deleted.remote_sync_failed', ['%error%' => $e->getMessage()]);
        }
    }
}
