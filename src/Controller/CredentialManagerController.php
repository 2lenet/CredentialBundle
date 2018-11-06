<?php

namespace Lle\CredentialBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Lle\CredentialBundle\Entity\Credential;
use Lle\CredentialBundle\Entity\Group;
use Lle\CredentialBundle\Entity\GroupRepository;
use Lle\CredentialBundle\Entity\GroupCredential;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use Symfony\Component\Routing\Annotation\Route;

class CredentialManagerController extends Controller
{
    /**
     * @Route("/admin/credential", name="admin_credential")
     * @Security("has_role('ROLE_CREDENTIAL_LIST') or has_role('ROLE_SUPER_ADMIN')")
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        $credentialRepository = $em->getRepository(Credential::class);
        $groupRepository = $em->getRepository(Group::class);

        $credentials = $credentialRepository->findAllOrdered();
        $groupes = $groupRepository->findAll();
        $groupCreds = $em->getRepository(GroupCredential::class)->findAll();
        $actives = [];
        foreach($groupCreds as $groupCred) {
            $actives[$groupCred->getGroupe()->getName().'-'.$groupCred->getCredential()->getRole()] = $groupCred->isAllowed();
        }
        return $this->render('@LleCredential/credential/index.html.twig', 
            [
                'credentials' => $credentials,
                'groupes' => $groupes,
                'actives' => $actives
            ]);
    }

    /**
     * @Route("/admin/credential/toggle", name="admin_credential_toggle")
     * @Security("has_role('ROLE_CREDENTIAL_TOGGLE') or has_role('ROLE_SUPER_ADMIN')")
     */
    public function toggle(Request $request)
    {
        $var = $request->request->get('id');
        list($group, $cred) = explode('-',$var);
        $em = $this->getDoctrine()->getManager();
        $group_cred = $em->getRepository(GroupCredential::class)->findOneGroupCred($group, $cred);
        if (!$group_cred) {
            $groupObj = $em->getRepository(Group::class)->findOneByName($group);
            $credential = $em->getRepository(Credential::class)->findOneByRole($cred);
            $group_cred =  new GroupCredential();
            $group_cred->setGroupe($groupObj);
            $group_cred->setCredential($credential);
            $group_cred->setAllowed(true);
        } else {
            $group_cred->setAllowed(! $group_cred->isAllowed);
        }

        $em->persist($group_cred);	
        $em->flush();            
        
        return new JsonResponse([]);
    }    
}
