<?php

namespace App\Controller;

use App\Entity\TableChaise;
use App\Form\TableChaiseType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TableChaiseController extends AbstractController
{
    #[Route('/creer-table-chaise', name: 'creer_table_chaise')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $tableChaise = new TableChaise();
        $form = $this->createForm(TableChaiseType::class, $tableChaise);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) 
        {
            $entityManager->persist($tableChaise);
            $entityManager->flush();
            
            //message flash
            $this->addFlash
            (
                'success',
                'Les tables et chaises ont été bien enregistrées avec succès.'
            );

            return $this->redirectToRoute('creer_table_chaise');
        }

        // Récupérer toutes les chaises de la base de données
        $chaises = $entityManager->getRepository(TableChaise::class)->findAll();

        return $this->render('admin/creer_table_chaise.html.twig', 
        [
            'form' => $form->createView(),
            'chaises' => $chaises, // Passer la liste des chaises au template
        ]);
        
    }

    // modifier table chaise
    #[Route('/modifier-table-chaise/{id}', name: 'modifier_table_chaise')]
    public function modifier(Request $request, 
                            TableChaise $tableChaise, 
                            EntityManagerInterface $entityManager): Response
    {
        // Récupérer toutes les chaises de la base de données
        $chaises = $entityManager->getRepository(TableChaise::class)->findAll();
        
        $form = $this->createForm(TableChaiseType::class, $tableChaise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $entityManager->persist($tableChaise);
            $entityManager->flush();

            $this->addFlash('success', 'Les modifications ont été enregistrées avec succès.');

            return $this->redirectToRoute('modifier_table_chaise', ['id' => $tableChaise->getId()]);
        }

        return $this->render('admin/modifier_table_chaise.html.twig', 
        [
            'form' => $form->createView(),
            'chaises' => $chaises,
        ]);
    }

    // supprimer table chaise
    #[Route('/supprimer-table-chaise/{id}', name: 'supprimer_table_chaise')]
    public function supprimer(Request $request, TableChaise $tableChaise, EntityManagerInterface $entityManager): Response
    {
        
        // Supprimez l'entité et redirigez vers la liste des tables et chaises
        $entityManager->remove($tableChaise);
        $entityManager->flush();

        // message
        $this->addFlash('success', 'La suppression de la table et chaise été enregistrées avec succès.');

        // Redirection après la suppression
        return $this->redirectToRoute('creer_table_chaise');
        
    }

    //liste des tables et chaises
    #[Route('/liste-table-chaise', name: 'liste_table_chaise')]
    public function liste(EntityManagerInterface $entityManager): Response
    {
        // Récupérer toutes les chaises de la base de données
        $tables = $entityManager->getRepository(TableChaise::class)->findAll();

        $data = [];
        foreach ($tables as $table) 
        {
            $data[$table->getId()] = 
            [
                'numeroTable' => $table->getNumeroTable(),
                'nombreChaise' => $table->getNombreChaise(),
                'emplacement' => $table->getEmplacement(),
                'active' => $table->isActive(),  // Utilise isActive() pour obtenir l'état actif
            ];
        }

         // Grouper les tables par emplacement
        $groupedTables = [];
        foreach ($tables as $table) 
        {
            $groupedTables[$table->getEmplacement()][] = $table;
        }

        return $this->render('admin/liste_table_chaise.html.twig', 
        [
            'tables' => $data,
            'groupedTables' => $groupedTables,
        ]);
    }

    #[Route('/confirmer-reservation-table', name: 'confirmer_reservation_table', methods: ['POST'] )]
    public function confirmerReservation(Request $request,  EntityManagerInterface $entityManager): Response
    {
        $postData = $request->request->all();
    
        $emplacements = $postData['emplacement'] ?? [];
        $tables = $postData['table'] ?? [];
        $chaises = $postData['chaise'] ?? [];
    
        $reservationDetails = [];
        
        $emplacementChecked = [];
        $tableChecked = [];
        $tablesChaises = [];
        
        // ------ debut : gestion des erreurs avec message ----------------
        
        
        
        // ------ fin : gestion des erreurs avec message ----------------
        
        // ----------------- debut : programmation checkbox -------------------------
    
        foreach ($emplacements as $emplacement) 
        {		
            $tablesParEmplacement = array_filter($tables, function($table) use ($emplacement) 
            {
                return strpos($table, "{$emplacement}_") === 0;
            });
        
            $tablesDetails = [];    
            $chaisesDict = [];
        
            foreach ($chaises as $chaise) 
            {
                $chaiseParts = explode('_', $chaise);
                $tableId = $chaiseParts[1] ?? '';
        
                if (!isset($chaisesDict[$tableId])) 
                {
                    $chaisesDict[$tableId] = [];
                }
    
                $chaisesDict[$tableId][] = $chaise;
            }
        
            foreach ($tablesParEmplacement as $table) 
            {
                $tableId = explode('_', $table)[1];
        
                $tablesDetails[] = 
                [
                    'tableId' => $tableId,
                    'chaises' => $chaisesDict[$tableId] ?? [],
                ];
            }
        
            $reservationDetails[] = 
            [
                'emplacement' => $emplacement,
                'tables' => $tablesDetails,
            ];
        }
    
        // -----------------fin : programmation checkbox -------------------------

        // ------------------- debut : checkbox --------------------

        $postData = $request->request->all();

        // Mettre à jour le statut des chaises comme étant inactives après la réservation
        foreach ($postData['chaise'] as $chaiseId) 
        {
            $chaise = $entityManager->getRepository(TableChaise::class)->find($chaiseId);
            if ($chaise) 
            {
                $chaise->setActive(false);
                $entityManager->persist($chaise);
            }
        }
        
        $entityManager->flush();

        // Rediriger après mise à jour
        // return $this->redirectToRoute('liste_table_chaise');
        // ------------------- fin : checkbox --------------------
    
        return $this->render('admin/confirmation_reservation_table.html.twig', 
        [
            'reservationDetails' => $reservationDetails,
        ]);
    }

    #[Route('/reinitialiser-chaises', name: 'reinitialiser_chaises')]
    public function reinitialiserChaises(EntityManagerInterface $entityManager): Response
    {
        $chaises = $entityManager->getRepository(TableChaise::class)->findAll();
	
        foreach ($chaises as $chaise) 
        {
            $chaise->setActive(true);
            $entityManager->persist($chaise);
        }
        
        $entityManager->flush();
    
        return $this->redirectToRoute('liste_table_chaise');
    }
}
