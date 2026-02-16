<?php

namespace App\Domain\WorkCycle\Repository;

use App\Domain\WorkCycle\Entity\TaskInstance;
use App\Domain\WorkCycle\Entity\Worker;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TaskInstanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaskInstance::class);
    }

    /**
     * Find active tasks for a worker (current and future weeks, not done)
     */
    public function findActiveByWorker(Worker $worker): array
    {
        $now = new \DateTimeImmutable();
        $currentYear = (int) $now->format('o'); // ISO year
        $currentWeek = (int) $now->format('W'); // ISO week

        return $this->createQueryBuilder('t')
            ->join('t.week', 'w')
            ->where('t.worker = :worker')
            ->andWhere('t.status != :done')
            ->andWhere('(w.year > :year OR (w.year = :year AND w.weekNumber >= :week))')
            ->setParameter('worker', $worker)
            ->setParameter('done', 'DONE')
            ->setParameter('year', $currentYear)
            ->setParameter('week', $currentWeek)
            ->orderBy('w.year', 'ASC')
            ->addOrderBy('w.weekNumber', 'ASC')
            ->addOrderBy('t.weekdaySnapshot', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find pending tasks for a worker (from previous weeks, not done)
     */
    public function findPendingByWorker(Worker $worker): array
    {
        $now = new \DateTimeImmutable();
        $currentYear = (int) $now->format('o'); // ISO year
        $currentWeek = (int) $now->format('W'); // ISO week

        return $this->createQueryBuilder('t')
            ->join('t.week', 'w')
            ->where('t.worker = :worker')
            ->andWhere('t.status = :pending')
            ->andWhere('(w.year < :year OR (w.year = :year AND w.weekNumber < :week))')
            ->setParameter('worker', $worker)
            ->setParameter('pending', 'PENDING')
            ->setParameter('year', $currentYear)
            ->setParameter('week', $currentWeek)
            ->orderBy('w.year', 'DESC')
            ->addOrderBy('w.weekNumber', 'DESC')
            ->addOrderBy('t.weekdaySnapshot', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
