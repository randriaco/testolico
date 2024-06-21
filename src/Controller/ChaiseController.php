<?php

namespace App\Controller;

use App\Entity\Chaise;
use App\Entity\Table;
use App\Form\ChaiseType;
use App\Repository\ChaiseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChaiseController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
	
	// ---------------liste chaise ----------------------
    #[Route('/liste-chaise', name: 'liste_chaise', methods: ['GET'])]
    public function index(ChaiseRepository $chaiseRepository): Response
    {
        return $this->render('chaise/index.html.twig', 
		[
            'chaises' => $chaiseRepository->findAll(),
        ]);
    }
	
	// -------------------- creer chaise ------------------
    #[Route('/creer-chaise', name: 'creer_chaise', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $chaise = new Chaise();
        $form = $this->createForm(ChaiseType::class, $chaise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
		{
            $this->entityManager->persist($chaise);
            $this->entityManager->flush();

            return $this->redirectToRoute('chaise_index');
        }

        return $this->render('admin/chaise/creer_chaise.html.twig', 
		[
            'chaise' => $chaise,
            'form' => $form->createView(),
        ]);
    }
	
	// -------------------detail chaise -------------------
    #[Route('/detail-chaise/{id}', name: 'detail_chaise', methods: ['GET'])]
    public function show(Chaise $chaise): Response
    {
        return $this->render('admin/chaise/detail_chaise.html.twig', 
		[
            'chaise' => $chaise,
        ]);
    }
	
	
	// ------------------ modifier chaise --------------------
    #[Route('/modifier-chaise/{id}/edit', name: 'modifier_chaise', methods: ['GET', 'POST'])]
    public function edit(Request $request, Chaise $chaise): Response
    {
        $form = $this->createForm(ChaiseType::class, $chaise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
		{
            $this->entityManager->flush();

            return $this->redirectToRoute('liste_chaise');
        }

        return $this->render('admin/chaise/modifier_chaise.html.twig', 
		[
            'chaise' => $chaise,
            'form' => $form->createView(),
        ]);
    }
	
	// ----------------------supprimer chaise -------------------
    #[Route('/supprimer-chaise/{id}', name: 'supprimer_chaise', methods: ['POST'])]
    public function delete(Request $request, Chaise $chaise): Response
    {
        if ($this->isCsrfTokenValid('delete'.$chaise->getId(), $request->request->get('_token'))) 
		{
            $this->entityManager->remove($chaise);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('liste_chaise');
    }
}