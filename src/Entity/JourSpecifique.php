<?php

namespace App\Entity;

use App\Repository\JourSpecifiqueRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JourSpecifiqueRepository::class)]
class JourSpecifique
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $ouvertureMatin = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $fermetureMatin = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $ouvertureSoir = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $fermetureSoir = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column(length: 255)]
    private ?string $motif = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getOuvertureMatin(): ?\DateTimeInterface
    {
        return $this->ouvertureMatin;
    }

    public function setOuvertureMatin(?\DateTimeInterface $ouvertureMatin): static
    {
        $this->ouvertureMatin = $ouvertureMatin;

        return $this;
    }

    public function getFermetureMatin(): ?\DateTimeInterface
    {
        return $this->fermetureMatin;
    }

    public function setFermetureMatin(?\DateTimeInterface $fermetureMatin): static
    {
        $this->fermetureMatin = $fermetureMatin;

        return $this;
    }

    // -------------debut : ajout frequence avec getter et setter----------------

    #[ORM\ManyToOne(targetEntity: Frequence::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $frequence;

    // Ajoutez les getters et setters correspondants
    public function getFrequence(): ?Frequence
    {
        return $this->frequence;
    }

    public function setFrequence(?Frequence $frequence): self
    {
        $this->frequence = $frequence;

        return $this;
    }
    
    // -------------fin : ajout frequence avec getter et setter-----------------

    public function getOuvertureSoir(): ?\DateTimeInterface
    {
        return $this->ouvertureSoir;
    }
    
    public function setOuvertureSoir(?\DateTimeInterface $ouvertureSoir): static
    {
        $this->ouvertureSoir = $ouvertureSoir;

        return $this;
    }

    public function getFermetureSoir(): ?\DateTimeInterface
    {
        return $this->fermetureSoir;
    }

    public function setFermetureSoir(?\DateTimeInterface $fermetureSoir): static
    {
        $this->fermetureSoir = $fermetureSoir;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getMotif(): ?string
    {
        return $this->motif;
    }

    public function setMotif(string $motif): static
    {
        $this->motif = $motif;

        return $this;
    }
}
