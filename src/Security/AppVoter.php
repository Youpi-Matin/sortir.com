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
    public const SUBSCRIBE = 'subscribe';
    public const UNSUBSCRIBE = 'unsubscribe';

    public const CANCELSORTIE = 'cancel';


    /**
     * @inheritDoc
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::ADMIN, self::SUBSCRIBE, self::UNSUBSCRIBE, self::CANCELSORTIE])) {
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
            self::SUBSCRIBE => $this->canSubscribe($subject, $user),
            self::UNSUBSCRIBE => $this->canUnsubscribe($subject, $user),
            self::CANCELSORTIE => $this->canCancel($subject, $user),
            default => throw new \LogicException('This code should not be reached!')
        };
    }

    /** Check utilisateur connecté (autorisation par défaut)
     * @param mixed $subject
     * @param Participant $user
     * @return bool
     */
    private function canView(mixed $subject, Participant $user): bool
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

    /** Check user isAdmin
     * @param Participant $user
     * @return bool|null
     */
    private function isAdmin(Participant $user)
    {
        return $user->isAdministrateur();
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
