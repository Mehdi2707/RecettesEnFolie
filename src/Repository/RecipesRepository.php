<?php

namespace App\Repository;

use App\Entity\Recipes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Recipes>
 *
 * @method Recipes|null find($id, $lockMode = null, $lockVersion = null)
 * @method Recipes|null findOneBy(array $criteria, array $orderBy = null)
 * @method Recipes[]    findAll()
 * @method Recipes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecipesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recipes::class);
    }

    public function save(Recipes $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Recipes $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findRecentRecipesValidated(): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.recipeStatus', 'rs')
            ->where('rs.name = :status')
            ->setParameter('status', 'valide')
            ->orderBy('r.createdAt', 'desc')
            ->getQuery()
            ->getResult();
    }

    public function findRecipesValidatedUser($user): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.recipeStatus', 'rs')
            ->leftJoin('r.user', 'u')
            ->where('rs.name = :status')
            ->andWhere('u = :user')
            ->setParameter('user', $user)
            ->setParameter('status', 'valide')
            ->getQuery()
            ->getResult();
    }

    public function findRecipesOfsCategoryValidated($slug): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.recipeStatus', 'rs')
            ->leftJoin('r.categories', 'c')
            ->where('c.slug = :slug')
            ->andWhere('rs.name = :status')
            ->setParameter('status', 'valide')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getResult();
    }

    public function findRecipesPaginated(int $page, string $slug, int $limit = 12): array
    {
        $limit = abs($limit);
        $result = [];

        $query = $this->createQueryBuilder('r')
            ->join('r.categories', 'c')
            ->leftJoin('r.recipeStatus', 'rs')
            ->where("c.slug = '$slug'")
            ->andWhere('rs.name = :status')
            ->setParameter('status', 'valide')

        ->setMaxResults($limit)
        ->setFirstResult(($page * $limit) - $limit);

        $paginator = new Paginator($query);
        $data = $paginator->getQuery()->getResult();

        $pages = ceil($paginator->count() / $limit);

        $result['data'] = $data;
        $result['pages'] = $pages;
        $result['page'] = $page;
        $result['limit'] = $limit;

        return $result;
    }

    public function searchRecipes(int $page, string $search, int $limit = 12): array
    {
        $limit = abs($limit);
        $result = [];

        $query = $this->createQueryBuilder('r')
            ->leftJoin('r.recipeStatus', 'rs')
            ->where('MATCH_AGAINST(r.title, r.description) AGAINST (:search boolean)>0')
            ->andWhere('rs.name = :status')
            ->setParameter('status', 'valide')
            ->setParameter('search', $search)
            ->setMaxResults($limit)
            ->setFirstResult(($page * $limit) - $limit);

        if(empty($query->getQuery()->getResult()))
            $query = $this->createQueryBuilder('r')
                ->leftJoin('r.recipeStatus', 'rs')
                ->where('r.title LIKE :search')
                ->orWhere('r.description LIKE :search')
                ->andWhere('rs.name = :status')
                ->setParameter('status', 'valide')
                ->setParameter('search', '%' . $search . '%')
                ->setMaxResults($limit)
                ->setFirstResult(($page * $limit) - $limit);

        $paginator = new Paginator($query);
        $data = $paginator->getQuery()->getResult();

        $pages = ceil($paginator->count() / $limit);

        $result['data'] = $data;
        $result['pages'] = $pages;
        $result['page'] = $page;
        $result['limit'] = $limit;

        return $result;
    }

    public function findBestRecipesOfsCategory($childCategory, $recipe, $maxResult = 12): array
    {
        $query = $this->createQueryBuilder('r')
            ->select('r')
            ->leftJoin('r.notes', 'n', 'WITH', 'n.id IS NOT NULL')
            ->leftJoin('r.recipeStatus', 'rs')
            ->where('r.categories = :subCategory')
            ->andWhere('rs.name = :status')
            ->andWhere('r != :currentRecipe')
            ->setParameter('status', 'valide')
            ->setParameter('subCategory', $childCategory)
            ->setParameter('currentRecipe', $recipe)
            ->groupBy('r.id')
            ->orderBy('AVG(n.value)', 'DESC')
            ->setMaxResults($maxResult);

        return $query->getQuery()->getResult();
    }

//    /**
//     * @return Recipes[] Returns an array of Recipes objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Recipes
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
