<?php

namespace App\Security;

use App\Entity\Participant;
use App\Entity\Sortie;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SortieVoter extends Voter
{
    public const VIEW = 'view_sortie';
    public const EDIT = 'edit_sortie';
    public const SUBSCRIBE = 'subscribe';
    public const UNSUBSCRIBE = 'unsubscribe';
    public const CANCELSORTIE = 'cancel_sortie';

    /**
     * @inheritDoc
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (
            !in_array($attribute, [
                self::VIEW,
                self::EDIT,
                self::SUBSCRIBE,
                self::UNSUBSCRIBE,
                self::CANCELSORTIE])
        ) {
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

        /** @var Sortie $sortie */
        $sortie = $subject;

        return match ($attribute) {
            self::VIEW => $this->canView($sortie, $user),
            self::EDIT => $this->canEdit($sortie, $user),
            self::SUBSCRIBE => $this->canSubscribe($sortie, $user),
            self::UNSUBSCRIBE => $this->canUnsubscribe($sortie, $user),
            self::CANCELSORTIE => $this->canCancel($sortie, $user),
            default => throw new \LogicException('This code should not be reached!')
        };
    }

    /** Check utilisateur connecté (autorisation par défaut)
     * @param mixed $sortie
     * @param Participant $user
     * @return bool
     */
    private function canView(Sortie $sortie, Participant $user): bool
    {
        // Si on peut editer on peut voir
        if ($this->canEdit($sortie, $user)) {
            return true;
        }
        return true;
    }

    /** Check utilisateur autorisé à modifier les informations
     * @param mixed $sortie
     * @param Participant $user
     * @return bool
     */
    private function canEdit(Sortie $sortie, Participant $user): bool
    {
        return ($user === $sortie->getOrganisateur() || $user->isAdministrateur());
    }

    /** Check si l'utilisateur peut s'inscrire à la sortie
     * Il faut que la sortie soit ouverte et le nombre d'inscrit < nombre de place
     * Et que le participant ne soit pas deja inscrit.
     * @param Sortie $sortie
     * @param Participant $user
     * @return void
     */
    private function canSubscribe(Sortie $sortie, Participant $user): bool
    {
        if (
            count($sortie->getParticipants()) < $sortie->getNbInscriptionsMax()
            && $sortie->getEtat()->getLibelle() === 'Ouverte'
            && !$sortie->getParticipants()->contains($user)
        ) {
            return true;
        }
        return false;
    }

    /** Check si l'utilisateur peut se desinscrire à la sortie
     * Il faut que la sortie soit ouverte ou cloturée
     * et la date du jour < date limite
     * et utilisateur inscrit
     *
     * @param Sortie $sortie
     * @param Participant $user
     * @return void
     */
    private function canUnsubscribe(Sortie $sortie, Participant $user): bool
    {
        if (
            ($sortie->getEtat()->getLibelle() === 'Ouverte' || $sortie->getEtat()->getLibelle() === 'Clôturée')
            && $sortie->getDateHeureDebut() > new \DateTime('now')
            && $sortie->getParticipants()->contains($user)
        ) {
            return true;
        }
        return false;
    }

    /** Peut Annuler une sortie
     * Sortie clôturée et date de début non passée et est l'organisateur
     * @param Sortie $sortie
     * @param Participant $user
     * @return bool
     */
    private function canCancel(Sortie $sortie, Participant $user): bool
    {
        if (
            ($sortie->getEtat()->getLibelle() === 'Clôturée')
            && $sortie->getDateHeureDebut() > new \DateTime('now')
            && $this->canEdit($sortie, $user)
        ) {
            return true;
        }
        return false;
    }
}
