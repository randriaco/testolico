<?php

namespace App\Controller;

use App\Entity\Emplacement;
use App\Entity\DiningTable;
use App\Form\EmplacementType;
use App\Repository\EmplacementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EmplacementController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    // --------------- liste emplacement --------------
	#[Route('/liste-emplacement', name: 'liste_emplacement', methods: ['GET'])]
    public function index(EmplacementRepository $emplacementRepository): Response
    {
        return $this->render('admin/emplacement/liste_emplacement.html.twig', 
		[
            'emplacements' => $emplacementRepository->findAll(),
        ]);
    }

    // ----------------- creer emplacement ---------------
	#[Route('/creer-emplacement', name: 'creer_emplacement', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $emplacement = new Emplacement();
        $form = $this->createForm(EmplacementType::class, $emplacement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
		{
            // Créer les tables pour l'emplacement
            for ($i = 1; $i <= $emplacement->getNombreTable(); $i++) {
                $table = new DiningTable();
                $table->setNom('Table ' . $i);
                $table->setNombreChaise(0); // Initialiser avec 0 chaises
                $emplacement->addTable($table);
            }

            $this->entityManager->persist($emplacement);
            $this->entityManager->flush();

            return $this->redirectToRoute('liste_emplacement');
        }

        return $this->render('admin/emplacement/creer_emplacement.html.twig', 
		[
            'emplacement' => $emplacement,
            'form' => $form->createView(),
        ]);
    }

    // --------------- detail emplacement -----------------
	#[Route('/detail-emplacement{id}', name: 'detail_emplacement', methods: ['GET'])]
    public function show(Emplacement $emplacement): Response
    {
        return $this->render('admin/emplacement/detail_emplacement.html.twig', 
		[
            'emplacement' => $emplacement,
        ]);
    }

    // ------------------modifier emplacement ----------------
	#[Route('/modifier-emplacement/{id}/edit', name: 'modifier_emplacement', methods: ['GET', 'POST'])]
    public function edit(Request $request, Emplacement $emplacement): Response
    {
        $form = $this->createForm(EmplacementType::class, $emplacement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('liste_emplacement');
        }

        return $this->render('admin/emplacement/modifier_emplacement.html.twig', 
		[
            'emplacement' => $emplacement,
            'form' => $form->createView(),
        ]);
    }

    // ------------------ supprimer emplacement ---------------------
	#[Route('/supprimer-emplacement/{id}', name: 'supprimer_emplacement', methods: ['POST'])]
    public function delete(Request $request, Emplacement $emplacement): Response
    {
        if ($this->isCsrfTokenValid('delete'.$emplacement->getId(), $request->request->get('_token'))) 
		{
            $this->entityManager->remove($emplacement);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('liste_emplacement');
    }
}