<?php

namespace App\Service;

use App\Entity\Categories;
use Doctrine\ORM\EntityManagerInterface;

class CategoriesService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getAllCategories()
    {
        $categoryRepository = $this->entityManager->getRepository(Categories::class);
        return $categoryRepository->findAll();
    }
}