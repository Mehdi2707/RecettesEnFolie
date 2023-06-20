<?php

namespace App\Repository;

use App\Entity\Comments;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comments>
 *
 * @method Comments|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comments|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comments[]    findAll()
 * @method Comments[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comments::class);
    }

    public function save(Comments $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Comments $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAdditionalComments($slug, $offset, $limit = 5)
    {
        $query = $this->createQueryBuilder('c')
            ->select('c')
            ->leftJoin('c.recipes', 'r')
            ->andWhere('r.slug = :slug')
            ->setParameter('slug', $slug)
            ->orderBy('c.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery();

        return $query->getResult();
    }

    public function findCommentsPaginated(int $offset, string $slug, int $limit = 5, $ajax = false): array
    {
        $limit = abs($limit);
        $result = [];

        $query = $this->createQueryBuilder('c')
            ->leftJoin('c.user', 'u')
            ->leftJoin('c.parent', 'p')
            ->leftJoin('c.replies', 'rpl')
            ->leftJoin('rpl.user', 'rplU')
            ->leftJoin('rpl.parent', 'rplP')
            ->leftJoin('rpl.replies', 'rplR')
            ->leftJoin('rplR.user', 'rplRU')
            ->leftJoin('rplR.parent', 'rplRP')
            ->join('c.recipes', 'r')
            ->where("r.slug = :slug")
            ->andWhere('c.parent IS NULL')
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult(($offset * $limit) - $limit)
            ->setParameter('slug', $slug);

        $query->addSelect('PARTIAL u.{id, username}'); // Ajouter l'utilisateur à la sélection
        $query->addSelect('p'); // Ajouter le parent à la sélection
        $query->addSelect('rpl');
        $query->addSelect('rplU');
        $query->addSelect('rplP');
        $query->addSelect('rplR');
        $query->addSelect('rplRU');
        $query->addSelect('rplRP');

        $paginator = new Paginator($query);
        $ajax ? $data = $paginator->getQuery()->getArrayResult() : $data = $paginator->getQuery()->getResult();

        $pages = ceil($paginator->count() / $limit);

        $result['data'] = $data;
        $result['pages'] = $pages;
        $result['offset'] = $offset;
        $result['limit'] = $limit;

        return $result;
    }

//    /**
//     * @return Comments[] Returns an array of Comments objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Comments
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
