<?php

namespace Lle\CredentialBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Entity\Credential;
use Lle\CredentialBundle\Entity\Group;
use Lle\CredentialBundle\Entity\GroupCredential;
use Lle\CredentialBundle\Service\CredentialService;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CredentialManagerController extends AbstractController
{
    public function __construct(
        #[AutowireIterator('credential.warmup')] protected iterable $warmuppers,
        private EntityManagerInterface $em,
        private CacheItemPoolInterface $cache,
        private CredentialService $credentialService,
    ) {
    }

    #[Route('/admin/credential', name: 'admin_credential')]
    #[IsGranted('ROLE_ADMIN_DROITS')]
    public function indexAction(): Response
    {
        $credentials = $this->em->getRepository(Credential::class)->findAllOrdered();
        $groupes = $this->em->getRepository(Group::class)->findByProjectExceptSuperAdmin();

        $groupCreds = $this->em->getRepository(GroupCredential::class)->findAll();

        $cruditStudioUrl =
            $this->container->get('parameter_bag')->get('lle_credential.crudit_studio_public_url')
            . '/project/project/'
            . $this->container->get('parameter_bag')->get('lle_credential.project_name');

        $loadUrl =
            $this->container->get('parameter_bag')->get('lle_credential.crudit_studio_url')
            . '/api/credential/pull/'
            . $this->container->get('parameter_bag')->get('lle_credential.project_name');

        $actives = [];
        $statusAllowed = [];

        foreach ($groupCreds as $groupCred) {
            if ($groupCred->getGroupe() && $groupCred->getCredential()) {
                $actives[$groupCred->getGroupe()->getName() . '-' . $groupCred->getCredential()->getRole()] =
                    $groupCred->isAllowed();

                if ($groupCred->getCredential()->getListeStatus() !== null) {
                    $statusAllowed[$groupCred->getGroupe()->getName() . '-' . $groupCred->getCredential()->getRole()] =
                        $groupCred->isStatusAllowed();
                }
            }
        }

        return $this->render(
            '@LleCredential/credential/index.html.twig',
            [
                'credentials' => $credentials,
                'groupes' => $groupes,
                'actives' => $actives,
                'statusAllowed' => $statusAllowed,
                'crudit_studio_url' => $cruditStudioUrl,
                'load_url' => $loadUrl,
            ]
        );
    }

    #[Route('/admin/credential/load', name: 'admin_credential_load')]
    #[IsGranted('ROLE_ADMIN_DROITS')]
    public function loadCredentials(): Response
    {
        $reponse = $this->credentialService->loadCredentials();

        if ($this->cache->hasItem('group_credentials')) {
            $this->cache->deleteItem('group_credentials');
        }

        if ($this->cache->hasItem('all_credentials')) {
            $this->cache->deleteItem('all_credentials');
        }

        if ($reponse->getStatusCode() == 200) {
            return new Response([], Response::HTTP_OK);
        }

        return new Response([], Response::HTTP_BAD_REQUEST);
    }
}
