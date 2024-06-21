<?php

namespace App\Entity;

use App\Repository\JoursMultiplesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JoursMultiplesRepository::class)]
class JoursMultiples
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $debutFermeture = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $finFermeture = null;

    #[ORM\Column(length: 255)]
    private ?string $motif = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDebutFermeture(): ?\DateTimeInterface
    {
        return $this->debutFermeture;
    }

    public function setDebutFermeture(\DateTimeInterface $debutFermeture): static
    {
        $this->debutFermeture = $debutFermeture;

        return $this;
    }

    public function getFinFermeture(): ?\DateTimeInterface
    {
        return $this->finFermeture;
    }

    public function setFinFermeture(\DateTimeInterface $finFermeture): static
    {
        $this->finFermeture = $finFermeture;

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