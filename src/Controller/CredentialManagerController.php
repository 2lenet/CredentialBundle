<?php

namespace Lle\CredentialBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Lle\CredentialBundle\Entity\Credential;
use Lle\CredentialBundle\Entity\Group;
use Lle\CredentialBundle\Entity\GroupRepository;
use Lle\CredentialBundle\Entity\GroupCredential;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Cache\Adapter\AdapterInterface;

use Symfony\Component\Routing\Annotation\Route;

class CredentialManagerController extends AbstractController
{
    /** @var EntityManagerInterface */
    private $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    /**
     * @Route("/admin/credential", name="admin_credential")
     * @Security("is_granted('ROLE_ADMIN_DROITS')")
     */
    public function index()
    {
        $credentialRepository = $this->em->getRepository(Credential::class);
        $groupRepository = $this->em->getRepository(Group::class);

        $credentials = $credentialRepository->findAllOrdered();
        $groupes = $groupRepository->findAllOrdered();

        $groupCreds = $this->em->getRepository(GroupCredential::class)->findAll();

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
     * @Security("is_granted('ROLE_ADMIN_DROITS')")
     */
    public function toggle(Request $request, AdapterInterface $cache)
    {
        $cache->deleteItem('group_credentials');
        $var = $request->request->get('id');
        list($group, $cred) = explode('-',$var);
        $group_cred = $this->em->getRepository(GroupCredential::class)->findOneGroupCred($group, $cred);

        if (!$group_cred) {
            $groupObj = $this->em->getRepository(Group::class)->findOneByName($group);
            $credential = $this->em->getRepository(Credential::class)->findOneByRole($cred);

            $group_cred =  new GroupCredential();
            $group_cred->setGroupe($groupObj);
            $group_cred->setCredential($credential);
            $group_cred->setAllowed(true);
        } else {
            $group_cred->setAllowed(! $group_cred->isAllowed());
        }

        $this->em->persist($group_cred);
        $this->em->flush();

        return new JsonResponse([]);
    }

    /**
     * @Route("/admin/credential/toggle_all", name="admin_credential_toggle_all")
     * @Security("is_granted('ROLE_ADMIN_DROITS')")
     */
    public function toggleAll(Request $request, AdapterInterface $cache)
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

        $existingCredentials = $groupCredentialRepository->findByGroup($group, "credential");

        /** @var Credential $credential */
        foreach ($credentials as $credential) {
            if (!array_key_exists($credential->getId(), $existingCredentials)) {
                $groupCredential =  new GroupCredential();
                $groupCredential->setGroupe($group);
                $groupCredential->setCredential($credential);
                $groupCredential->setAllowed($checked);

                $this->em->persist($groupCredential);
            }
        }

        $groupCredentialRepository->updateCredentials($group, $credentials, $checked);

        $this->em->flush();

        return new JsonResponse([]);
    }
}
