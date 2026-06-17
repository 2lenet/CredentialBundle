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
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsEntityListener(event: Events::postPersist, entity: Group::class)]
#[AsEntityListener(event: Events::preUpdate, entity: Group::class)]
#[AsEntityListener(event: Events::postUpdate, entity: Group::class)]
#[AsEntityListener(event: Events::postRemove, entity: Group::class)]
class GroupListener implements EventSubscriberInterface
{
    private bool $enabled = true;

    /** @var array<int, string> */
    private array $oldNames = [];

    /** @var list<array{string, string}> */
    private array $pendingFlashes = [];

    public function __construct(
        protected ClientService $clientService,
        protected RequestStack $requestStack,
        protected TranslatorInterface $translator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', -100],
        ];
    }

    /**
     * Flashes are deferred and added here instead of directly in the Doctrine events.
     *
     * Root cause (crudit): TraitCrudController::new() and edit() call render() BEFORE
     * getBrickResponseCollector()->handle(). The render includes _flash.html.twig which
     * calls flashBag.all(), consuming every flash already in the session. Any flash added
     * during flush() (i.e. inside getBrickBuilder()->build()) is therefore lost before the
     * redirect. The delete action is unaffected because it redirects directly without render().
     *
     * Fix in crudit: TraitCrudController::new() / edit() should not render when the form was
     * submitted successfully — they should delegate rendering entirely to
     * getBrickResponseCollector()->handle(), or _flash.html.twig should use peekAll()
     * (non-destructive) instead of all().
     *
     * Until crudit is fixed, flashes are buffered in $pendingFlashes and committed to the
     * session here, on kernel.response at priority -100 (after render, before SessionListener
     * saves the session at priority -1000).
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest() || $this->pendingFlashes === []) {
            return;
        }

        try {
            $session = $this->requestStack->getSession();
            if ($session instanceof FlashBagAwareSessionInterface) {
                foreach ($this->pendingFlashes as [$type, $message]) {
                    $session->getFlashBag()->add($type, $message);
                }
            }
        } catch (SessionNotFoundException) {
        } finally {
            $this->pendingFlashes = [];
        }
    }

    private function addFlash(string $type, string $translationKey, array $parameters = []): void
    {
        if ($this->requestStack->getCurrentRequest() === null) {
            return;
        }

        $message = $this->translator->trans($translationKey, $parameters, 'CredentialBundle');
        $this->pendingFlashes[] = [$type, $message];
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
