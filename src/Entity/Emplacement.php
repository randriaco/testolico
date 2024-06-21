<?php

namespace App\Entity;

use App\Repository\EmplacementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmplacementRepository::class)]
class Emplacement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $nom;

    #[ORM\Column(type: 'integer')]
    private $nombreTable;

    #[ORM\OneToMany(mappedBy: 'emplacement', targetEntity: DiningTable::class, cascade: ['persist', 'remove'])]
    private $tables;

    public function __construct()
    {
        $this->tables = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getNombreTable(): ?int
    {
        return $this->nombreTable;
    }

    public function setNombreTable(int $nombreTable): self
    {
        $this->nombreTable = $nombreTable;

        return $this;
    }

    /**
     * @return Collection|Table[]
     */
    public function getTables(): Collection
    {
        return $this->tables;
    }

    public function addTable(DiningTable $table): self
    {
        if (!$this->tables->contains($table)) {
            $this->tables[] = $table;
            $table->setEmplacement($this);
        }

        return $this;
    }

    public function removeTable(DiningTable $table): self
    {
        if ($this->tables->removeElement($table)) {
            // set the owning side to null (unless already changed)
            if ($table->getEmplacement() === $this) {
                $table->setEmplacement(null);
            }
        }

        return $this;
    }
}