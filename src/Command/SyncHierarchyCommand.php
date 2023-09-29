<?php

namespace Lle\CredentialBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Entity\Credential;
use Lle\CredentialBundle\Entity\Group;
use Lle\CredentialBundle\Entity\GroupCredential;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncHierarchyCommand extends Command
{
    public const ROLE_GROUPE = 'role_groupe';

    protected static $defaultName = 'lle:credential:sync-hierarchy';

    public function __construct(
        private EntityManagerInterface $em,
        private array $hierarchy = [],
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('sync hierachy security')
            ->addArgument(self::ROLE_GROUPE, InputArgument::REQUIRED, 'Role racine');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $roles = [];

        foreach ($this->em->getRepository(Credential::class)->findAll() as $r) {
            $roles[$r->getRole()] = $r;
        }

        $rootRole = $input->getArgument(self::ROLE_GROUPE);
        $list = [];

        $groupe = $this->em->getRepository(Group::class)
            ->findOneBy(['name' => str_replace('ROLE_', '', $rootRole)]);
        if (!$groupe) {
            $groupe = new Group();
            $groupe->setName(str_replace('ROLE_', '', $rootRole));
            $groupe->setIsRole(true);
            $groupe->setActif(true);
            $groupe->setRequiredRole('');
            $groupe->setTri(0);
            $groupe->setLibelle(strtolower((string)$groupe->getName()));

            $this->em->persist($groupe);
            $this->em->flush();
        }

        $this->generateListRoles($rootRole, $list);

        foreach ($list as $role) {
            $credential = $roles[$role] ?? new Credential();
            $credential->setTri(0);

            $roles[$role] = $credential;
            $r = explode('_', $role);

            $credential->setRubrique($r[1] ?? 'other');
            /** @var string $libelle */
            $libelle = str_replace(
                (string)$credential->getRubrique(),
                '',
                str_replace('ROLE_', '', $role)
            );
            $credential->setLibelle(strtolower($libelle));
            $credential->setLibelle(ucfirst(trim(str_replace('_', ' ', (string)$credential->getLibelle()))));
            $credential->setRole($role);

            $this->em->persist($credential);

            $assoc = $this->em->getRepository(GroupCredential::class)
                ->findOneBy(['credential' => $credential, 'groupe' => $groupe]) ?? new GroupCredential();
            $assoc->setCredential($credential);
            $assoc->setGroupe($groupe);
            $assoc->setAllowed(true);

            $this->em->persist($assoc);
        }

        $this->em->flush();

        return Command::SUCCESS;
    }

    private function generateListRoles(string $role, mixed &$list): void
    {
        if (isset($this->hierarchy[$role])) {
            foreach ($this->hierarchy[$role] as $srole) {
                $this->generateListRoles($srole, $list);
            }
        } else {
            $list[] = $role;
        }
    }
}
