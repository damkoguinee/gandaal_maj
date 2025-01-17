<?php

namespace App\Entity;

use App\Repository\InscriptionActiviteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InscriptionActiviteRepository::class)]
class InscriptionActivite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'inscriptionActivites')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Eleve $eleve = null;

    #[ORM\ManyToOne(inversedBy: 'inscriptionActivites')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Etablissement $etablissement = null;

    #[ORM\Column(length: 10)]
    private ?string $promo = null;

    #[ORM\Column(length: 15)]
    private ?string $typeEleve = null;

    #[ORM\Column(nullable: true)]
    private ?float $remise = null;

    #[ORM\Column(length: 10)]
    private ?string $statut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateInscription = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateSaisie = null;

    #[ORM\ManyToOne(inversedBy: 'activiteSaisie')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $saisiePar = null;

    #[ORM\ManyToOne(inversedBy: 'inscriptionActivites')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TarifActiviteScolaire $tarifActivite = null;

    /**
     * @var Collection<int, PaiementActiviteScolaire>
     */
    #[ORM\OneToMany(targetEntity: PaiementActiviteScolaire::class, mappedBy: 'inscription', orphanRemoval:true, cascade:['persist', 'remove'])]
    private Collection $paiementActiviteScolaires;

    public function __construct()
    {
        $this->paiementActiviteScolaires = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEleve(): ?Eleve
    {
        return $this->eleve;
    }

    public function setEleve(?Eleve $eleve): static
    {
        $this->eleve = $eleve;

        return $this;
    }


    public function getEtablissement(): ?Etablissement
    {
        return $this->etablissement;
    }

    public function setEtablissement(?Etablissement $etablissement): static
    {
        $this->etablissement = $etablissement;

        return $this;
    }

    public function getPromo(): ?string
    {
        return $this->promo;
    }

    public function setPromo(string $promo): static
    {
        $this->promo = $promo;

        return $this;
    }

    public function getTypeEleve(): ?string
    {
        return $this->typeEleve;
    }

    public function setTypeEleve(string $typeEleve): static
    {
        $this->typeEleve = $typeEleve;

        return $this;
    }

    public function getRemise(): ?float
    {
        return $this->remise;
    }

    public function setRemise(?float $remise): static
    {
        $this->remise = $remise;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getDateInscription(): ?\DateTimeInterface
    {
        return $this->dateInscription;
    }

    public function setDateInscription(\DateTimeInterface $dateInscription): static
    {
        $this->dateInscription = $dateInscription;

        return $this;
    }

    public function getDateSaisie(): ?\DateTimeInterface
    {
        return $this->dateSaisie;
    }

    public function setDateSaisie(\DateTimeInterface $dateSaisie): static
    {
        $this->dateSaisie = $dateSaisie;

        return $this;
    }

    public function getSaisiePar(): ?User
    {
        return $this->saisiePar;
    }

    public function setSaisiePar(?User $saisiePar): static
    {
        $this->saisiePar = $saisiePar;

        return $this;
    }

    public function getTarifActivite(): ?TarifActiviteScolaire
    {
        return $this->tarifActivite;
    }

    public function setTarifActivite(?TarifActiviteScolaire $tarifActivite): static
    {
        $this->tarifActivite = $tarifActivite;

        return $this;
    }

    /**
     * @return Collection<int, PaiementActiviteScolaire>
     */
    public function getPaiementActiviteScolaires(): Collection
    {
        return $this->paiementActiviteScolaires;
    }

    public function addPaiementActiviteScolaire(PaiementActiviteScolaire $paiementActiviteScolaire): static
    {
        if (!$this->paiementActiviteScolaires->contains($paiementActiviteScolaire)) {
            $this->paiementActiviteScolaires->add($paiementActiviteScolaire);
            $paiementActiviteScolaire->setInscription($this);
        }

        return $this;
    }

    public function removePaiementActiviteScolaire(PaiementActiviteScolaire $paiementActiviteScolaire): static
    {
        if ($this->paiementActiviteScolaires->removeElement($paiementActiviteScolaire)) {
            // set the owning side to null (unless already changed)
            if ($paiementActiviteScolaire->getInscription() === $this) {
                $paiementActiviteScolaire->setInscription(null);
            }
        }

        return $this;
    }

    
}
