<?php

namespace App\Security;

use App\Entity\Participant;
use App\Entity\Sortie;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ParticipantVoter extends Voter
{
    public const VIEW = 'view';
    public const EDIT = 'edit';

    /**
     * @inheritDoc
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW, self::EDIT])) {
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
            default => throw new \LogicException('This code should not be reached!')
        };
    }

    /** Check utilisateur connecté (autorisation par défaut)
     * @param mixed $subject
     * @param Participant $user
     * @return bool
     */
    private function canView(Participant $subject, Participant $user): bool
    {
        // Si on peut editer on peut voir
        if ($this->canEdit($subject, $user)) {
            return true;
        }
        return true;
    }

    /** Check utilisateur autorisé à modifier les informations
     * @param mixed $subject
     * @param Participant $user
     * @return bool
     */
    private function canEdit(Participant $subject, Participant $user): bool
    {
        return $user === $subject || $user->isAdministrateur();
    }
}
