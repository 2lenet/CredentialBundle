<?php

namespace Lle\CredentialBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Lle\CredentialBundle\Entity\Credential;
use Lle\CredentialBundle\Entity\Group;
use Lle\CredentialBundle\Entity\GroupRepository;

use Symfony\Component\Routing\Annotation\Route;

class CredentialManagerController extends Controller
{
    /**
     * @Route("/admin/credential", name="admin_credential")
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        $credentialRepository = $em->getRepository(Credential::class);
        $groupRepository = $em->getRepository(Group::class);

        $credentials = $credentialRepository->findAll();
        $groupes = $groupRepository->findAll();
        return $this->render('@LleCredential/credential/index.html.twig', 
            ['credentials' => $credentials,
            'groupes' => $groupes]);
    }

}
