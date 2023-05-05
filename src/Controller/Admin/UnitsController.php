<?php

namespace App\Controller\Admin;

use App\Entity\Recipes;
use App\Entity\Unit;
use App\Form\UnitsFormType;
use App\Repository\UnitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/unites', name: 'admin_units_')]
class UnitsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(UnitRepository $unitsRepository): Response
    {
        $units = $unitsRepository->findAll();

        return $this->render('admin/units/index.html.twig', [
            'units' => $units,
        ]);
    }

    #[Route('/ajout', name: 'add')]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_RECIPE_ADMIN');

        $unit = new Unit();

        $form = $this->createForm(UnitsFormType::class, $unit);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $entityManager->persist($unit);
            $entityManager->flush();

            $this->addFlash('success', 'Unité ajouté avec succès');
            return $this->redirectToRoute('admin_units_index');
        }

        return $this->render('admin/units/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edition/{id}', name: 'edit')]
    public function edit(Unit $units, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('UNIT_EDIT', $units);

        $form = $this->createForm(UnitsFormType::class, $units);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $entityManager->persist($units);
            $entityManager->flush();

            $this->addFlash('success', 'Unité modifié avec succès');
            return $this->redirectToRoute('admin_units_index');
        }

        return $this->render('admin/units/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/suppression/{id}', name: 'delete')]
    public function delete(Unit $units): Response
    {
        $this->denyAccessUnlessGranted('UNIT_DELETE', $units);
        return $this->render('admin/units/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}
