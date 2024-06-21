<?php

namespace App\Entity;

use App\Repository\ChaiseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChaiseRepository::class)]
class Chaise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'boolean')]
    private $reservee = false;

    #[ORM\Column(type: 'string', length: 255)]
    private $numero;

    #[ORM\ManyToOne(targetEntity: DiningTable::class, inversedBy: 'chaises')]
    #[ORM\JoinColumn(nullable: false)]
    private $table;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isReservee(): ?bool
    {
        return $this->reservee;
    }

    public function setReservee(bool $reservee): self
    {
        $this->reservee = $reservee;

        return $this;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): self
    {
        $this->numero = $numero;

        return $this;
    }

    public function getTable(): ?DiningTable
    {
        return $this->table;
    }

    public function setTable(?DiningTable $table): self
    {
        $this->table = $table;

        return $this;
    }
}