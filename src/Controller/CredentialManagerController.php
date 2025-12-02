<?php

namespace Lle\CredentialBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Entity\Credential;
use Lle\CredentialBundle\Entity\Group;
use Lle\CredentialBundle\Entity\GroupCredential;
use Lle\CredentialBundle\Form\DumpCredentialsType;
use Lle\CredentialBundle\Form\LoadCredentialsType;
use Lle\CredentialBundle\Service\CredentialService;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class CredentialManagerController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private CredentialService $credentialService,
        private TranslatorInterface $translator,
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
            ]
        );
    }

    #[Route('/admin/credential/toggle', name: 'admin_credential_toggle')]
    #[IsGranted('ROLE_ADMIN_DROITS')]
    public function toggleAction(Request $request, CacheItemPoolInterface $cache): JsonResponse
    {
        $cache->deleteItem('group_credentials');
        /** @var string $var */
        $var = $request->request->get('id');
        [$group, $cred] = explode('-', $var);

        /** @var ?GroupCredential $groupCred */
        $groupCred = $this->em->getRepository(GroupCredential::class)->findOneGroupCred($group, $cred);

        if (!$groupCred) {
            /** @var Group $groupObj */
            $groupObj = $this->em->getRepository(Group::class)->findOneBy(['name' => $group]);
            /** @var Credential $credential */
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

        /** @var Group $group */
        $group = $this->em->find(Group::class, $group);

        $groupCredentialRepository = $this->em->getRepository(GroupCredential::class);

        $groupCredentials = $groupCredentialRepository->findBy(['groupe' => $group]);

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

        /** @var string $var */
        $var = $request->request->get("id");

        [$group, $cred, $status] = explode("-", $var);
        /** @var ?GroupCredential $groupCred */
        $groupCred = $this->em->getRepository(GroupCredential::class)->findOneGroupCred($group, $cred);

        if (!$groupCred) {
            /** @var Group $groupObj */
            $groupObj = $this->em->getRepository(Group::class)->findOneBy(['name' => $group]);
            /** @var Credential $credential */
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

        /** @var string $var */
        $var = $request->request->get("id");

        [$group, $cred, $status] = explode("-", $var);
        $statusCred = $cred . "_" . strtoupper($status);

        /** @var ?GroupCredential $groupCred */
        $groupCred = $this->em->getRepository(GroupCredential::class)->findOneGroupCred($group, $statusCred);
        if (!$groupCred) {
            $groupCred = $this->credentialService->allowedByStatus($group, $statusCred, $cred);
        } else {
            $groupCred->setAllowed(!$groupCred->isAllowed());
        }

        /** @var GroupCredential $groupCred */
        $this->em->persist($groupCred);
        $this->em->flush();

        return new JsonResponse([]);
    }

    #[Route('/admin/credential/save', name: 'admin_credential_save')]
    #[IsGranted('ROLE_ADMIN_DROITS')]
    public function saveCredentials(Request $request): Response
    {
        $form = $this->createForm(DumpCredentialsType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $projectDir */
            $projectDir = $this->getParameter('kernel.project_dir');
            $directory = $projectDir . '/data/credential/';
            $filename = $directory . $form->get('filename')->getData() . '.json';

            $this->credentialService->dumpCredentials($filename);

            return $this->file($filename);
        }

        return $this->render('@LleCredential/credential/dump_credentials.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/credential/load', name: 'admin_credential_load')]
    #[IsGranted('ROLE_ADMIN_DROITS')]
    public function loadCredentials(Request $request): Response
    {
        $form = $this->createForm(LoadCredentialsType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('file')->getData();

            /** @var string $projectDir */
            $projectDir = $this->getParameter('kernel.project_dir');
            $directory = $projectDir . '/data/credential/';
            $filename = $form->get('filename')->getData() . '.json';

            try {
                $file->move($directory, $filename);
            } catch (FileException $e) {
                $this->addFlash('danger', $this->translator->trans('text.move_file_error', [], 'CredentialBundle', 'fr'));

                return $this->redirectToRoute('admin_credential_load');
            }

            $this->credentialService->loadCredentials($directory . $filename);

            $this->addFlash('success', $this->translator->trans('text.import_file_success', [], 'CredentialBundle', 'fr'));

            return $this->redirectToRoute('admin_credential');
        }

        return $this->render('@LleCredential/credential/load_credentials.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
