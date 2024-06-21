<?php

namespace App\Entity;

use App\Repository\DiningTableRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DiningTableRepository::class)]
class DiningTable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $nom;

    #[ORM\Column(type: 'integer')]
    private $nombreChaise;

    #[ORM\ManyToOne(targetEntity: Emplacement::class, inversedBy: 'tables')]
    #[ORM\JoinColumn(nullable: false)]
    private $emplacement;

    #[ORM\OneToMany(mappedBy: 'table', targetEntity: Chaise::class, cascade: ['persist', 'remove'])]
    private $chaises;

    public function __construct()
    {
        $this->chaises = new ArrayCollection();
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

    public function getNombreChaise(): ?int
    {
        return $this->nombreChaise;
    }

    public function setNombreChaise(int $nombreChaise): self
    {
        $this->nombreChaise = $nombreChaise;

        return $this;
    }

    public function getEmplacement(): ?Emplacement
    {
        return $this->emplacement;
    }

    public function setEmplacement(?Emplacement $emplacement): self
    {
        $this->emplacement = $emplacement;

        return $this;
    }

    /**
     * @return Collection|Chaise[]
     */
    public function getChaises(): Collection
    {
        return $this->chaises;
    }

    public function addChaise(Chaise $chaise): self
    {
        if (!$this->chaises->contains($chaise)) {
            $this->chaises[] = $chaise;
            $chaise->setTable($this);
        }

        return $this;
    }

    public function removeChaise(Chaise $chaise): self
    {
        if ($this->chaises->removeElement($chaise)) {
            // set the owning side to null (unless already changed)
            if ($chaise->getTable() === $this) {
                $chaise->setTable(null);
            }
        }

        return $this;
    }
}