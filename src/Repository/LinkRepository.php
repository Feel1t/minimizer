<?php

namespace App\Repository;

use App\Entity\Link;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Link>
 *
 * @method Link|null find($id, $lockMode = null, $lockVersion = null)
 * @method Link|null findOneBy(array $criteria, array $orderBy = null)
 * @method Link[]    findAll()
 * @method Link[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LinkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Link::class);
    }

    public function add(Link $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Link $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getActiveLinkByHash(string $hash): ?Link
    {
        $builder = $this->createQueryBuilder('l');

        return $builder->where(
            $builder->expr()->eq('l.hash', ':hash'),
            $builder->expr()->gt('l.dueDate', ':now')
        )
            ->setParameter('hash', $hash)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findActiveLinks(int $limit): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.dueDate > :now')
            ->orderBy('l.createDate', 'DESC')
            ->setParameter('now', new \DateTime())
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findOutdatedLinks(int $limit): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.dueDate < :now')
            ->orderBy('l.createDate', 'DESC')
            ->setParameter('now', new \DateTime())
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}