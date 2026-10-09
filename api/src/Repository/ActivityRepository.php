<?php

namespace App\Repository;

use App\Entity\Activity;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Activity>
 */
class ActivityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Activity::class);
    }

    /**
     * Toutes les validations de quêtes d'un utilisateur, des plus récentes
     * aux plus anciennes. On peut limiter à une discipline.
     *
     * @return Activity[]
     */
    public function findForUser(User $user, ?int $disciplineId = null): array
    {
        $qb = $this->createQueryBuilder('a')
            ->addSelect('q', 't', 'd')
            ->join('a.quest', 'q')
            ->join('q.disciplineTracking', 't')
            ->join('t.discipline', 'd')
            ->andWhere('t.user = :user')
            ->setParameter('user', $user)
            ->orderBy('a.date', 'DESC')
            ->addOrderBy('a.id', 'DESC');

        if ($disciplineId !== null) {
            $qb->andWhere('d.id = :disciplineId')->setParameter('disciplineId', $disciplineId);
        }

        return $qb->getQuery()->getResult();
    }
}
