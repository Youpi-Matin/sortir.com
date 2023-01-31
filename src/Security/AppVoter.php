<?php

namespace App\Security;

use App\Entity\Participant;
use App\Entity\Sortie;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AppVoter extends Voter
{
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const ADMIN = 'admin';


    /**
     * @inheritDoc
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::ADMIN])) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof Participant) {
            // the user must be logged in; if not, deny access
            return false;
        }

        return match ($attribute) {
            self::VIEW => $this->canView($subject, $user),
            self::EDIT => $this->canEdit($subject, $user),
            self::ADMIN => $this->isAdmin($user),
            default => throw new \LogicException('This code should not be reached!')
        };
    }

    private function canView(mixed $subject, Participant $user): bool
    {
        // Si on peut editer on peut voir
        if ($this->canEdit($subject, $user)) {
            return true;
        }

        return true;
    }

    private function canEdit(mixed $subject, Participant $user): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        if ($subject instanceof Sortie) {
            return ($user === $subject->getOrganisateur()) || $user->isAdministrateur();
        }

        if ($subject instanceof Participant) {
            return $user === $subject;
        }

        return false;
    }

    private function isAdmin(Participant $user)
    {
        return $user->isAdministrateur();
    }
}
