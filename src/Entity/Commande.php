<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\User; // Assurez-vous que le chemin d'accès est correct pour votre entité User

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'commandes')]
    private ?User $user = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $date;

    #[ORM\Column(type: 'float')]
    private float $montant;

    // Getters et Setters

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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): self
    {
        $this->montant = $montant;
        return $this;
    }

    // ------------------------Dans l'entité Commande ------------------------

    /**
     * @var Collection|CommandeProduit[]
     */
    #[ORM\OneToMany(mappedBy: 'commande', targetEntity: CommandeProduit::class, cascade: ['persist', 'remove'])]
    private Collection $commandeProduits;

    // Constructeur
    public function __construct()
    {
        $this->commandeProduits = new ArrayCollection();
    }

    public function getCommandeProduits(): Collection
    {
        return $this->commandeProduits;
    }

    // Méthodes pour ajouter/supprimer des CommandeProduit
    public function addCommandeProduit(CommandeProduit $commandeProduit): self
    {
        if (!$this->commandeProduits->contains($commandeProduit)) {
            $this->commandeProduits[] = $commandeProduit;
            $commandeProduit->setCommande($this);
        }

        return $this;
    }

    public function removeCommandeProduit(CommandeProduit $commandeProduit): self
    {
        if ($this->commandeProduits->removeElement($commandeProduit)) {
            // set the owning side to null (unless already changed)
            if ($commandeProduit->getCommande() === $this) {
                $commandeProduit->setCommande(null);
            }
        }

        return $this;
    }

    // ----------ajout champ date_reservation---------------

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dateReservation = null;

    // Getters et setters pour dateReservation
    public function getDateReservation(): ?\DateTimeInterface
    {
        return $this->dateReservation;
    }

    public function setDateReservation(?\DateTimeInterface $dateReservation): self
    {
        $this->dateReservation = $dateReservation;

        return $this;
    }

    // -------------------------ajout champ status --------------

    #[ORM\Column(type: 'string', length: 25)]
    private string $status = 'a emporter'; // Default value 'a emporter'

    // Getters and Setters for status
    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    // ---------------------ajout relation avec reservation --------------------
    #[ORM\OneToOne(targetEntity: Reservation::class, inversedBy: "commande")]
    #[ORM\JoinColumn(name: "reservation_id", referencedColumnName: "id", nullable: true)]
    private ?Reservation $reservation = null;

    // Getters et setters
    public function getReservation(): ?Reservation
    {
        return $this->reservation;
    }

    public function setReservation(?Reservation $reservation): self
    {
        $this->reservation = $reservation;
        return $this;
    }

}