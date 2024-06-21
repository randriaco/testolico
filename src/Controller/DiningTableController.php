<?php

namespace App\Controller;

use App\Entity\DiningTable;
use App\Entity\Chaise;
use App\Entity\Emplacement;
use App\Form\DiningTableType;
use App\Form\ChaiseType;
use App\Repository\DiningTableRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Routing\Annotation\Route;

class DiningTableController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/liste-table', name: 'liste_table', methods: ['GET'])]
    public function index(DiningTableRepository $diningTableRepository): Response
    {
        return $this->render('admin/table/liste_table.html.twig', 
		[
            'tables' => $diningTableRepository->findAll(),
        ]);
    }

    // #[Route('/creer-table', name: 'creer_table', methods: ['GET', 'POST'])]
    // public function new(Request $request): Response
    // {
    //     $table = new DiningTable();
    //     $form = $this->createForm(DiningTableType::class, $table);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) 
	// 	{
    //         $this->entityManager->persist($table);
    //         $this->entityManager->flush();

    //         return $this->redirectToRoute('liste_table');
    //     }

    //     return $this->render('admin/table/creer_table.html.twig', 
	// 	[
    //         'table' => $table,
    //         'form' => $form->createView(),
    //     ]);
    // }

    #[Route('/creer-table/{id}', name: 'creer_table', methods: ['GET', 'POST'])]
    public function creerTable(Request $request, Emplacement $emplacement): Response
    {
        $nombreTables = $emplacement->getNombreTable();
        $forms = [];
        
        for ($i = 1; $i <= $nombreTables; $i++) 
        {
            $table = new DiningTable();
            $form = $this->createForm(DiningTableType::class, $table, 
            [
                'action' => $this->generateUrl('creer_table_submit', ['id' => $emplacement->getId()])
            ]);
            $forms[] = $form->createView();
        }

        return $this->render('admin/table/creer_table.html.twig', 
        [
            'emplacement' => $emplacement,
            'forms' => $forms,
        ]);
    }


    #[Route('/creer-table-submit/{id}', name: 'creer_table_submit', methods: ['POST'])]
    public function creerTableSubmit(Request $request, Emplacement $emplacement): Response
    {
        $nombreTables = $emplacement->getNombreTable();

        for ($i = 1; $i <= $nombreTables; $i++) 
        {
            $table = new DiningTable();
            $form = $this->createForm(DiningTableType::class, $table);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) 
            {
                $table->setEmplacement($emplacement);
                $this->entityManager->persist($table);
            }
        }

        $this->entityManager->flush();

        return $this->redirectToRoute('detail_emplacement', ['id' => $emplacement->getId()]);
    }

    // ---------------------detail table -----------------------
	#[Route('/detail-table/{id}', name: 'detail_table', methods: ['GET'])]
    public function show(DiningTable $table): Response
    {
        return $this->render('admin/table/detail_table.html.twig', 
		[
            'table' => $table,
        ]);
    }

    // ------------------- modifier table ----------------------------
	#[Route('/modifier-table/{id}/edit', name: 'modifier_table', methods: ['GET', 'POST'])]
    public function edit(Request $request, DiningTable $table): Response
    {
        $form = $this->createForm(DiningTableType::class, $table);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
		{
            $this->entityManager->flush();

            return $this->redirectToRoute('liste_table');
        }

        return $this->render('admin/table/modifier_table.html.twig', 
		[
            'table' => $table,
            'form' => $form->createView(),
        ]);
    }

    // ----------------------supprimer table --------------------
	#[Route('/supprimer-table/{id}', name: 'supprimer_table', methods: ['POST'])]
    public function delete(Request $request, DiningTable $table): Response
    {
        if ($this->isCsrfTokenValid('delete'.$table->getId(), $request->request->get('_token'))) 
		{
            $this->entityManager->remove($table);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('liste_table');
    }

    // ----------------------- table ajouter chaise ----------------------
	#[Route('/table-ajouter-chaise/{id}', name: 'table_ajouter_chaise', methods: ['GET', 'POST'])]
    public function addChaises(Request $request, DiningTable $table): Response
    {
        $form = $this->createFormBuilder()
            ->add('nombreChaises', IntegerType::class, 
			[
                'label' => 'Nombre de chaises',
                'attr' => ['class' => 'form-control']
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
		{
            $data = $form->getData();
            $nombreChaises = $data['nombreChaises'];

            for ($i = 1; $i <= $nombreChaises; $i++) {
                $chaise = new Chaise();
                $chaise->setNumero('Chaise ' . ($table->getChaises()->count() + $i));
                $chaise->setTable($table);
                $this->entityManager->persist($chaise);
            }

            $this->entityManager->flush();

            return $this->redirectToRoute('detail_table', ['id' => $table->getId()]);
        }

        return $this->render('admin/chaise/creer_chaise.html.twig', 
		[
            'form' => $form->createView(),
            'table' => $table,
        ]);
    }
}