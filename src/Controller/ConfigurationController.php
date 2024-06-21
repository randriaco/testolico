<?php

namespace App\Controller;

use App\Entity\Configuration;
use App\Form\ConfigurationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConfigurationController extends AbstractController
{
    #[Route('/configuration', name: 'configuration')]
    public function configuration(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Suppose qu'il n'y a qu'une seule entrée de configuration
        $configuration = $entityManager->getRepository(Configuration::class)->find(1) ?? new Configuration();
        
        $form = $this->createForm(ConfigurationType::class, $configuration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
		{
            $entityManager->persist($configuration);
            $entityManager->flush();

            $this->addFlash('success', 
            'Le nombre total de places a été bien defini et enregistré avec succès.');
            
            return $this->redirectToRoute('configuration');
        }

        return $this->render('configuration.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}