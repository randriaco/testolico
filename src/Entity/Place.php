<?php

namespace App\Entity;

use App\Repository\PlaceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlaceRepository::class)]
class Place
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $placesTotal = null;

    #[ORM\Column]
    private ?int $placesReservees = null;

    #[ORM\Column]
    private ?int $placesLiberees = null;

    #[ORM\Column]
    private ?int $placesDispo = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlacesTotal(): ?int
    {
        return $this->placesTotal;
    }

    public function setPlacesTotal(int $placesTotal): static
    {
        $this->placesTotal = $placesTotal;

        return $this;
    }

    public function getPlacesReservees(): ?int
    {
        return $this->placesReservees;
    }

    public function setPlacesReservees(int $placesReservees): static
    {
        $this->placesReservees = $placesReservees;

        return $this;
    }

    public function getPlacesLiberees(): ?int
    {
        return $this->placesLiberees;
    }

    public function setPlacesLiberees(int $placesLiberees): static
    {
        $this->placesLiberees = $placesLiberees;

        return $this;
    }

    public function getPlacesDispo(): ?int
    {
        return $this->placesDispo;
    }

    public function setPlacesDispo(int $placesDispo): static
    {
        $this->placesDispo = $placesDispo;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }
}
