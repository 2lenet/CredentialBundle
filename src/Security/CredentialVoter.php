<?php

namespace Lle\CredentialBundle\Security;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Entity\Credential;
use Lle\CredentialBundle\Entity\GroupCredential;
use Psr\Cache\CacheItemPoolInterface;
use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorTokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Service\ResetInterface;

class CredentialVoter extends Voter implements ResetInterface
{
    public const CACHE_KEY_GROUP_RIGHTS = 'group_credentials';
    public const CACHE_KEY_ROLES = 'all_credentials';
    public const CACHE_KEY_VERSION = 'credentials_version';

    private bool $checked = true;

    private ?string $version = null;

    public function __construct(
        private EntityManagerInterface $em,
        private CacheItemPoolInterface $cache,
        private array $groupRights = [],
        private array $roles = [],
    ) {
        $this->load();
    }

    /**
     * Le voter survit aux requêtes sous un runtime à processus long (FrankenPHP worker) : à chaque
     * requête, la première décision compare le numéro de version du cache au sien, ce qui coûte
     * une lecture minuscule, et ne relit les droits que si un chargement les a remplacés.
     */
    public function reset(): void
    {
        $this->checked = false;
    }

    private function refreshIfStale(): void
    {
        if ($this->checked) {
            return;
        }

        $this->checked = true;

        if ($this->readVersion() !== $this->version) {
            $this->groupRights = [];
            $this->roles = [];
            $this->load();
        }
    }

    private function readVersion(): ?string
    {
        $version = $this->cache->getItem(self::CACHE_KEY_VERSION)->get();

        return is_string($version) ? $version : null;
    }

    private function load(): void
    {
        // lue avant les droits : un chargement concurrent fait diverger la version, donc relire
        $this->version = $this->readVersion();

        $cachedGroupRights = $this->cache->getItem(self::CACHE_KEY_GROUP_RIGHTS);

        if (!$cachedGroupRights->isHit()) {
            $groupCreds = $this->em->getRepository(GroupCredential::class)->findAll();

            foreach ($groupCreds as $groupCred) {
                if ($groupCred->isAllowed()) {
                    /** @var string $groupName */
                    $groupName = $groupCred->getGroupe()?->getName();
                    $credName = $groupCred->getCredential()?->getRole();
                    $credListeStatus = $groupCred->getCredential()?->getStatusList();

                    if (!array_key_exists($groupName, $this->groupRights)) {
                        $this->groupRights[$groupName] = [];
                    }

                    $this->groupRights[$groupName][$credName] = [
                        "listeStatus" => $credListeStatus,
                        "statusAllowed" => $groupCred->isStatusAllowed(),
                    ];
                }
            }

            $cachedGroupRights->set($this->groupRights);
            $this->cache->save($cachedGroupRights);
        } else {
            $this->groupRights = $cachedGroupRights->get();
        }

        $cachedRoles = $this->cache->getItem(self::CACHE_KEY_ROLES);

        if (!$cachedRoles->isHit()) {
            $allCreds = $this->em->getRepository(Credential::class)->findAll();

            foreach ($allCreds as $cred) {
                $this->roles[] = $cred->getRole();
            }

            $cachedRoles->set($this->roles);
            $this->cache->save($cachedRoles);
        } else {
            $this->roles = $cachedRoles->get();
        }
    }

    protected function supports(?string $attribute, mixed $subject): bool
    {
        $this->refreshIfStale();

        // vote on every role in database
        return in_array($attribute, $this->roles);
    }

    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token,
        ?Vote $vote = null,
    ): bool {
        if (interface_exists(TwoFactorTokenInterface::class)) {
            if ($token instanceof TwoFactorTokenInterface) {
                return false;
            }
        }

        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        $roles = $user->getRoles();

        // ROLE_SUPER_ADMIN can do anything! The power!
        if (in_array('ROLE_SUPER_ADMIN', $roles)) {
            return true;
        }

        foreach ($roles as $role) {
            $groupName = str_replace("ROLE_", "", $role);

            if ($this->getVote($attribute, $subject, $groupName)) {
                return true;
            }
        }

        return false;
    }

    public function getVote(?string $attribute, mixed $subject, ?string $groupName): bool
    {
        if (
            isset($this->groupRights[$groupName]) && array_key_exists(
                (string)$attribute,
                $this->groupRights[$groupName]
            )
        ) {
            $credential = $this->groupRights[$groupName][$attribute];

            if ($subject && $credential["listeStatus"] && $credential["statusAllowed"]) {
                $statusPropertyValue = $subject->getStatusPropertyValue();
                $attribute .= "_" . strtoupper($statusPropertyValue);

                return isset($this->groupRights[$groupName][$attribute]);
            }
        }

        return
            isset($this->groupRights[$groupName])
            && array_key_exists((string)$attribute, $this->groupRights[$groupName]);
    }
}
