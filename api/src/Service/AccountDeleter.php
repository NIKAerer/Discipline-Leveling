<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\DisciplineTrackingRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Supprime un compte et toutes ses données.
 *
 * Aucune suppression en cascade n'est configurée entre User et DisciplineTracking,
 * donc les lignes liées (et leurs Quest/Activity/LolMatch) sont supprimées
 * à la main, dans l'ordre des dépendances, avant l'utilisateur lui-même.
 */
class AccountDeleter
{
    public function __construct(
        private EntityManagerInterface $em,
        private DisciplineTrackingRepository $disciplineTrackingRepository,
    ) {
    }

    public function delete(User $user): void
    {
        foreach ($this->disciplineTrackingRepository->findBy(['user' => $user]) as $tracking) {
            foreach ($tracking->getQuests() as $quest) {
                foreach ($quest->getActivities() as $activity) {
                    $this->em->remove($activity);
                }
                $this->em->remove($quest);
            }

            foreach ($tracking->getLolMatches() as $lolMatch) {
                $this->em->remove($lolMatch);
            }

            $this->em->remove($tracking);
        }

        $this->em->remove($user);
        $this->em->flush();
    }
}
