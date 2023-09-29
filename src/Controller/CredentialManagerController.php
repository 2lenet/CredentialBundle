<?php

namespace Lle\CredentialBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Entity\Credential;
use Lle\CredentialBundle\Entity\Group;
use Lle\CredentialBundle\Entity\GroupCredential;
use Lle\CredentialBundle\Service\CredentialService;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CredentialManagerController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private CredentialService $credentialService,
    ) {
    }

    #[Route('/admin/credential', name: 'admin_credential')]
    #[IsGranted('ROLE_ADMIN_DROITS')]
    public function indexAction(): Response
    {
        $credentials = $this->em->getRepository(Credential::class)->findAllOrdered();
        $groupes = $this->em->getRepository(Group::class)->findAllOrdered();

        $groupCreds = $this->em->getRepository(GroupCredential::class)->findAll();

        $actives = [];
        $statusAllowed = [];

        foreach ($groupCreds as $groupCred) {
            $actives[$groupCred->getGroupe()->getName() . '-' . $groupCred->getCredential()->getRole()] =
                $groupCred->isAllowed();

            if ($groupCred->getCredential()->getListeStatus() !== null) {
                $statusAllowed[$groupCred->getGroupe()->getName() . '-' . $groupCred->getCredential()->getRole()] =
                    $groupCred->isStatusAllowed();
            }
        }

        return $this->render(
            '@LleCredential/credential/index.html.twig',
            [
                'credentials' => $credentials,
                'groupes' => $groupes,
                'actives' => $actives,
                'statusAllowed' => $statusAllowed,
            ]
        );
    }

    #[Route('/admin/credential/toggle', name: 'admin_credential_toggle')]
    #[IsGranted('ROLE_ADMIN_DROITS')]
    public function toggleAction(Request $request, CacheItemPoolInterface $cache): JsonResponse
    {
        $cache->deleteItem('group_credentials');
        $var = $request->request->get('id');
        [$group, $cred] = explode('-', $var);

        /** @var ?GroupCredential $groupCred */
        $groupCred = $this->em->getRepository(GroupCredential::class)->findOneGroupCred($group, $cred);

        if (!$groupCred) {
            $groupObj = $this->em->getRepository(Group::class)->findOneBy(['name' => $group]);
            $credential = $this->em->getRepository(Credential::class)->findOneBy(['role' => $cred]);

            $groupCred = new GroupCredential();
            $groupCred->setGroupe($groupObj);
            $groupCred->setCredential($credential);
            $groupCred->setAllowed(true);
        } else {
            $groupCred->setAllowed(!$groupCred->isAllowed());
        }

        $this->em->persist($groupCred);
        $this->em->flush();

        return new JsonResponse([]);
    }

    #[Route('/admin/credential/toggle_all', name: 'admin_credential_toggle_all')]
    #[IsGranted('ROLE_ADMIN_DROITS')]
    public function toggleAllAction(Request $request, CacheItemPoolInterface $cache): JsonResponse
    {
        $cache->deleteItem('group_credentials');
        $group = $request->request->getInt('group');
        $rubrique = $request->request->get('rubrique');
        $checked = $request->request->getBoolean('checked');

        if ($rubrique) {
            $credentials = $this->em
                ->getRepository(Credential::class)
                ->findBy(["rubrique" => $rubrique]);
        } else {
            $credentials = $this->em
                ->getRepository(Credential::class)
                ->findAll();
        }

        $group = $this->em->find(Group::class, $group);

        $groupCredentialRepository = $this->em->getRepository(GroupCredential::class);

        $groupCredentials = $groupCredentialRepository->findByGroup($group);

        $this->credentialService->toggleAll($groupCredentials, $credentials, $group, $checked);

        $groupCredentialRepository->updateCredentials($group, $credentials, $checked);

        $this->em->flush();

        return new JsonResponse([]);
    }

    #[Route('/admin/credential/allowed_status', name: 'admin_credential_allowed_status')]
    #[IsGranted('ROLE_ADMIN_DROITS')]
    public function allowedStatusAction(Request $request, CacheItemPoolInterface $cache): JsonResponse
    {
        $cache->deleteItem("group_credentials");

        $var = $request->request->get("id");

        [$group, $cred, $status] = explode("-", $var);

        /** @var ?GroupCredential $groupCred */
        $groupCred = $this->em->getRepository(GroupCredential::class)->findOneGroupCred($group, $cred);

        if (!$groupCred) {
            $groupObj = $this->em->getRepository(Group::class)->findOneBy(['name' => $group]);
            $credential = $this->em->getRepository(Credential::class)->findOneBy(['role' => $cred]);

            $groupCred = new GroupCredential();
            $groupCred->setGroupe($groupObj);
            $groupCred->setCredential($credential);
            $groupCred->setAllowed(false);
            $groupCred->setStatusAllowed(true);
        } else {
            $groupCred->setStatusAllowed(!$groupCred->isStatusAllowed());
        }

        $this->em->persist($groupCred);
        $this->em->flush();

        return new JsonResponse([]);
    }

    #[Route('/admin/credential/allowed_for_status', name: 'admin_credential_allowed_for_status')]
    #[IsGranted('ROLE_ADMIN_DROITS')]
    public function allowedByStatusAction(Request $request, CacheItemPoolInterface $cache): JsonResponse
    {
        $cache->deleteItem("group_credentials");

        $var = $request->request->get("id");

        [$group, $cred, $status] = explode("-", $var);
        $statusCred = $cred . "_" . strtoupper($status);

        /** @var ?GroupCredential $groupCred */
        $groupCred = $this->em->getRepository(GroupCredential::class)->findOneGroupCred($group, $statusCred);

        if (!$groupCred) {
            $this->credentialService->allowedByStatus($group, $cred, $status);
        } else {
            $groupCred->setAllowed(!$groupCred->isAllowed());
        }

        $this->em->persist($groupCred);
        $this->em->flush();

        return new JsonResponse([]);
    }
}
