<?php

namespace App\Domain\Scoring\Repository;

use App\Domain\Scoring\Entity\ScoreLedger;
use App\Domain\WorkCycle\Entity\Worker;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ScoreLedger>
 */
class ScoreLedgerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ScoreLedger::class);
    }

    public function getTotalPoints(Worker $worker): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('SUM(s.amount)')
            ->where('s.worker = :worker')
            ->setParameter('worker', $worker)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return array<array{worker_id: int, name: string, total_amount: int}>
     */
    public function getLeaderboardForWeek(int $year, int $weekNumber): array
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('w.id as worker_id', 'w.name', 'SUM(s.amount) as total_amount')
            ->from(ScoreLedger::class, 's')
            ->join('s.worker', 'w')
            ->join('s.taskInstance', 't')
            ->join('t.week', 'pw')
            ->where('pw.year = :year')
            ->andWhere('pw.weekNumber = :week')
            ->groupBy('w.id')
            ->orderBy('total_amount', 'DESC')
            ->setParameter('year', $year)
            ->setParameter('week', $weekNumber)
            ->getQuery()
            ->getResult();
    }

    public function getTeamTotalForWeek(int $year, int $weekNumber): int
    {
        return (int) $this->getEntityManager()->createQueryBuilder()
            ->select('SUM(s.amount)')
            ->from(ScoreLedger::class, 's')
            ->join('s.taskInstance', 't')
            ->join('t.week', 'pw')
            ->where('pw.year = :year')
            ->andWhere('pw.weekNumber = :week')
            ->setParameter('year', $year)
            ->setParameter('week', $weekNumber)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getWorkerTotalForWeek(Worker $worker, int $year, int $weekNumber): int
    {
        return (int) $this->getEntityManager()->createQueryBuilder()
            ->select('SUM(s.amount)')
            ->from(ScoreLedger::class, 's')
            ->join('s.taskInstance', 't')
            ->join('t.week', 'pw')
            ->where('s.worker = :worker')
            ->andWhere('pw.year = :year')
            ->andWhere('pw.weekNumber = :week')
            ->setParameter('worker', $worker)
            ->setParameter('year', $year)
            ->setParameter('week', $weekNumber)
            ->getQuery()
            ->getSingleScalarResult();
    }
}

