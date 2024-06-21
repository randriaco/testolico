<?php

namespace App\Entity;

use App\Repository\HoraireRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HoraireRepository::class)]
class Horaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $jour = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $ouvertureMatin = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $fermetureMatin = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $ouvertureSoir = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $fermetureSoir = null;

    // #[ORM\ManyToOne(targetEntity: Frequence::class)]
    #[ORM\ManyToOne(targetEntity: Frequence::class, inversedBy: "horaires")]
    #[ORM\JoinColumn(nullable: false)]
    private $frequence;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJour(): ?string
    {
        return $this->jour;
    }

    public function setJour(string $jour): static
    {
        $this->jour = $jour;

        return $this;
    }

    public function getOuvertureMatin(): ?\DateTimeInterface
    {
        return $this->ouvertureMatin;
    }

    public function setOuvertureMatin(\DateTimeInterface $ouvertureMatin): static
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

    public function getFrequence(): ?Frequence
    {
        return $this->frequence;
    }

    public function setFrequence(?Frequence $frequence): static
    {
        $this->frequence = $frequence;

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
}
