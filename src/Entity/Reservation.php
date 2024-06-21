<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ReservationRepository;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateReservation = null;

    #[ORM\Column(length: 255)]
    private ?string $horaireReservation = null;

    #[ORM\Column]
    private ?int $nombreCouverts = null;

    // Getters and setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getDateReservation(): ?\DateTimeInterface
    {
        return $this->dateReservation;
    }

    public function setDateReservation(\DateTimeInterface $dateReservation): self
    {
        $this->dateReservation = $dateReservation;

        return $this;
    }

    public function getHoraireReservation(): ?string
    {
        return $this->horaireReservation;
    }

    public function setHoraireReservation(string $horaireReservation): self
    {
        $this->horaireReservation = $horaireReservation;

        return $this;
    }

    public function getNombreCouverts(): ?int
    {
        return $this->nombreCouverts;
    }

    public function setNombreCouverts(int $nombreCouverts): self
    {
        $this->nombreCouverts = $nombreCouverts;

        return $this;
    }

    // --------------------- ajout relation avec commande --------------
    #[ORM\OneToOne(targetEntity: Commande::class, mappedBy: 'reservation')]
    private ?Commande $commande = null;

    // Getters et setters
    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(?Commande $commande): self
    {
        $this->commande = $commande;
        return $this;
    }
}