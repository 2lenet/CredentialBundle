<?php

namespace Lle\CredentialBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Entity\Credential;
use Lle\CredentialBundle\Entity\Group;
use Lle\CredentialBundle\Entity\GroupCredential;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CredentialManagerController extends AbstractController
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected ParameterBagInterface $parameterBag,
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

                if ($groupCredential->getCredential()->getStatusList() !== null) {
                    $statusAllowed[$groupCredential->getGroupe()->getName() . '-' . $groupCredential->getCredential()->getRole()] =
                        $groupCredential->isStatusAllowed();
                }
            }
        }

        $url = null;
        /** @var ?string $clientUrl */
        $clientUrl = $this->parameterBag->get('lle_credential.client_url');
        /** @var ?string $clientPublicUrl */
        $clientPublicUrl = $this->parameterBag->get('lle_credential.client_public_url');
        /** @var ?string $projectCode */
        $projectCode = $this->parameterBag->get('lle_credential.project_code');
        if ($clientPublicUrl && $projectCode) {
            $url = $clientPublicUrl . '/project/project/redirect-to-show/' . $projectCode . '#matrice';
        }

        return $this->render(
            '@LleCredential/credential/index.html.twig',
            [
                'canLoad' => $clientUrl && $projectCode,
                'url' => $url,
                'credentialsBySections' => $this->getCredentialsBySections(),
                'groups' => $groups,
                'actives' => $actives,
                'statusAllowed' => $statusAllowed,
            ]
        );
    }

    public function getCredentialsBySections(): array
    {
        $result = [];
        $credentials = $this->em->getRepository(Credential::class)->findBy(['visible' => true], [
            'section' => 'ASC',
            'type' => 'ASC'
        ]);
        foreach ($credentials as $credential) {
            if ($credential->getSection()) {
                if (!array_key_exists($credential->getSection(), $result)) {
                    $result[$credential->getSection()] = [];
                }

                $result[$credential->getSection()][] = $credential;
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
