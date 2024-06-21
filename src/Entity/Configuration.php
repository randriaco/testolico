<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ConfigurationRepository;

#[ORM\Entity(repositoryClass: ConfigurationRepository::class)]
class Configuration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private ?int $placesTotal = null;

    // Getters et Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlacesTotal(): ?int
    {
        return $this->placesTotal;
    }

    public function setPlacesTotal(int $placesTotal): self
    {
        $this->placesTotal = $placesTotal;

        return $this;
    }
}