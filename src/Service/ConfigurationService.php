<?php

namespace App\Service;

use App\Entity\Configuration;
use Doctrine\ORM\EntityManagerInterface;

class ConfigurationService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getPlacesTotal(): ?int
    {
        $configuration = $this->entityManager->getRepository(Configuration::class)->find(1);

        return $configuration ? $configuration->getPlacesTotal() : null;
    }
}