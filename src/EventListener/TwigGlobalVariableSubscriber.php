<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;
use App\Repository\RestaurantRepository;

class TwigGlobalVariableSubscriber implements EventSubscriberInterface
{
    private $twig;
    private $restaurantRepository;

    public function __construct(Environment $twig, RestaurantRepository $restaurantRepository)
    {
        $this->twig = $twig;
        $this->restaurantRepository = $restaurantRepository;
    }

    public function onKernelController(ControllerEvent $event)
    {
        // Vous pouvez ajuster l'ID ou la méthode de récupération selon vos besoins
        $restaurant = $this->restaurantRepository->find(1); 
        
        if ($restaurant) {
            $this->twig->addGlobal('restaurant', $restaurant);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}
