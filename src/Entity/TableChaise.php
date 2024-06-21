<?php

namespace App\Entity;

use App\Repository\TableChaiseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TableChaiseRepository::class)]
class TableChaise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $numeroTable = null;

    #[ORM\Column]
    private ?int $nombreChaise = null;

    #[ORM\Column(length: 255)]
    private ?string $emplacement = null;

    // Ajout du champ 'active' de type booléen
    #[ORM\Column(type: 'boolean')]
    private ?bool $active = true;  // par défaut, les chaises sont actives

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroTable(): ?int
    {
        return $this->numeroTable;
    }

    public function setNumeroTable(int $numeroTable): static
    {
        $this->numeroTable = $numeroTable;

        return $this;
    }

    public function getNombreChaise(): ?int
    {
        return $this->nombreChaise;
    }

    public function setNombreChaise(int $nombreChaise): static
    {
        $this->nombreChaise = $nombreChaise;

        return $this;
    }

    public function getEmplacement(): ?string
    {
        return $this->emplacement;
    }

    public function setEmplacement(string $emplacement): static
    {
        $this->emplacement = $emplacement;

        return $this;
    }

    // ajout getters et setters champ active

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }
}
