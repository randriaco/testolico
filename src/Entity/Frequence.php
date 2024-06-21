<?php

namespace App\Entity;

use App\Repository\FrequenceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FrequenceRepository::class)]
class Frequence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $intervalle = null;

    // ----------------debut : date butoire--------------------------

    #[ORM\Column(type: 'integer', nullable: true)]
    private $dateButoir;

    // Getter et Setter pour dateButoir
    public function getDateButoir(): ?int
    {
        return $this->dateButoir;
    }

    public function setDateButoir(?int $dateButoir): self
    {
        $this->dateButoir = $dateButoir;
        return $this;
    }


    // ----------------fin : date butoire---------------------------

    #[ORM\OneToMany(mappedBy: 'frequence', targetEntity: Horaire::class)]
    private Collection $horaires;

    public function __construct()
    {
        $this->horaires = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIntervalle(): ?int
    {
        return $this->intervalle;
    }

    public function setIntervalle(int $intervalle): static
    {
        $this->intervalle = $intervalle;

        return $this;
    }

    /**
     * @return Collection<int, Horaire>
     */
    public function getHoraires(): Collection
    {
        return $this->horaires;
    }

    public function addHoraire(Horaire $horaire): static
    {
        if (!$this->horaires->contains($horaire)) {
            $this->horaires->add($horaire);
            $horaire->setFrequence($this);
        }

        return $this;
    }

    public function removeHoraire(Horaire $horaire): static
    {
        if ($this->horaires->removeElement($horaire)) {
            // set the owning side to null (unless already changed)
            if ($horaire->getFrequence() === $this) {
                $horaire->setFrequence(null);
            }
        }

        return $this;
    }
}
