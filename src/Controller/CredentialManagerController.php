<?php

namespace Lle\CredentialBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Entity\Credential;
use Lle\CredentialBundle\Entity\Group;
use Lle\CredentialBundle\Entity\GroupCredential;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CredentialManagerController extends AbstractController
{
    public function __construct(
        protected EntityManagerInterface $em,
    ) {
    }

    #[IsGranted('ROLE_ADMIN_DROITS')]
    #[Route('/', name: 'admin_credential')]
    public function indexAction(): Response
    {
        $groups = $this->em->getRepository(Group::class)->findByProjectExceptSuperAdmin();
        $groupCredentials = $this->em->getRepository(GroupCredential::class)->findAll();

        $actives = [];
        $statusAllowed = [];
        foreach ($groupCredentials as $groupCredential) {
            if ($groupCredential->getGroupe() && $groupCredential->getCredential()) {
                $actives[$groupCredential->getGroupe()->getName() . '-' . $groupCredential->getCredential()->getRole()] =
                    $groupCredential->isAllowed();

                if ($groupCredential->getCredential()->getListeStatus() !== null) {
                    $statusAllowed[$groupCredential->getGroupe()->getName() . '-' . $groupCredential->getCredential()->getRole()] =
                        $groupCredential->isStatusAllowed();
                }
            }
        }

        return $this->render(
            '@LleCredential/credential/index.html.twig',
            [
                'credentialsByRubriques' => $this->getCredentialsByRubriques(),
                'groups' => $groups,
                'actives' => $actives,
                'statusAllowed' => $statusAllowed,
            ]
        );
    }

    public function getCredentialsByRubriques(): array
    {
        $result = [];
        $credentials = $this->em->getRepository(Credential::class)->findBy([], ['rubrique' => 'ASC', 'tri' => 'ASC']);
        foreach ($credentials as $credential) {
            if ($credential->getRubrique()) {
                if (!array_key_exists($credential->getRubrique(), $result)) {
                    $result[$credential->getRubrique()] = [];
                }

                $result[$credential->getRubrique()][] = $credential;
            } else {
                if (!array_key_exists('Others', $result)) {
                    $result['Others'] = [];
                }

                $result['Others'][] = $credential;
            }
        }

        return $result;
    }
}
