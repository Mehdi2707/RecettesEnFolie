<?php

namespace App\Repository;

use App\Entity\ConsultationUserRecipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ConsultationUserRecipe>
 *
 * @method ConsultationUserRecipe|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConsultationUserRecipe|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConsultationUserRecipe[]    findAll()
 * @method ConsultationUserRecipe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConsultationUserRecipeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConsultationUserRecipe::class);
    }

    public function save(ConsultationUserRecipe $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ConsultationUserRecipe $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findRecentlyConsultedRecipes($user, $currentRecipe, $maxResult = 12)
    {
        return $this->createQueryBuilder('c')
            ->select('c, r')
            ->leftJoin('c.recipe', 'r')
            ->andWhere('c.user = :user')
            ->andWhere('r != :currentRecipe')
            ->orderBy('c.consultedAt', 'DESC')
            ->setMaxResults($maxResult)
            ->setParameter('user', $user)
            ->setParameter('currentRecipe', $currentRecipe)
            ->getQuery()->getResult();
    }

//    /**
//     * @return ConsultationUserRecipe[] Returns an array of ConsultationUserRecipe objects
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

//    public function findOneBySomeField($value): ?ConsultationUserRecipe
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
