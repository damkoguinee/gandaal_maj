<?php

namespace App\Entity;

use App\Repository\InscriptionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InscriptionRepository::class)]
class Inscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'inscriptions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Personnel $saisiePar = null;

    #[ORM\ManyToOne(inversedBy: 'inscriptions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Eleve $eleve = null;

    #[ORM\ManyToOne(inversedBy: 'inscriptions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ClasseRepartition $classe = null;

    #[ORM\Column(length: 50)]
    private ?string $type = null;

    #[ORM\Column(nullable: true)]
    private ?float $remiseInscription = null;

    #[ORM\Column(nullable: true)]
    private ?float $remiseScolarite = null;

    #[ORM\Column(length: 10)]
    private ?string $promo = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateInscription = null;

    #[ORM\Column(length: 10)]
    private ?string $statut = null;

    #[ORM\Column(length: 10)]
    private ?string $etatScol = null;

    #[ORM\ManyToOne(inversedBy: 'inscriptions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Etablissement $etablissement = null;

    /**
     * @var Collection<int, PaiementEleve>
     */
    #[ORM\OneToMany(targetEntity: PaiementEleve::class, mappedBy: 'inscription', orphanRemoval:true, cascade:['persist', 'remove'])]
    private Collection $paiementEleves;

    /**
     * @var Collection<int, Recette>
     */
    #[ORM\OneToMany(targetEntity: Recette::class, mappedBy: 'inscription')]
    private Collection $recettes;

    /**
     * @var Collection<int, ControlEleve>
     */
    #[ORM\OneToMany(targetEntity: ControlEleve::class, mappedBy: 'inscription')]
    private Collection $controlEleves;

    /**
     * @var Collection<int, NoteEleve>
     */
    #[ORM\OneToMany(targetEntity: NoteEleve::class, mappedBy: 'inscription')]
    private Collection $noteEleves;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $etatPedagogie = null;

    

    public function __construct()
    {
        $this->paiementEleves = new ArrayCollection();
        $this->recettes = new ArrayCollection();
        $this->controlEleves = new ArrayCollection();
        $this->noteEleves = new ArrayCollection();
    }

    
    
    

    public function getId(): ?int
    {
        return $this->id;
    }

    

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getRemiseInscription(): ?float
    {
        return $this->remiseInscription;
    }

    public function setRemiseInscription(?float $remiseInscription): static
    {
        $this->remiseInscription = $remiseInscription;

        return $this;
    }

    public function getRemiseScolarite(): ?float
    {
        return $this->remiseScolarite;
    }

    public function setRemiseScolarite(?float $remiseScolarite): static
    {
        $this->remiseScolarite = $remiseScolarite;

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

    public function getDateInscription(): ?\DateTimeInterface
    {
        return $this->dateInscription;
    }

    public function setDateInscription(\DateTimeInterface $dateInscription): static
    {
        $this->dateInscription = $dateInscription;

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

    public function getEtatScol(): ?string
    {
        return $this->etatScol;
    }

    public function setEtatScol(string $etatScol): static
    {
        $this->etatScol = $etatScol;

        return $this;
    }

    public function getSaisiePar(): ?Personnel
    {
        return $this->saisiePar;
    }

    public function setSaisiePar(?Personnel $saisiePar): static
    {
        $this->saisiePar = $saisiePar;

        return $this;
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

    public function getClasse(): ?ClasseRepartition
    {
        return $this->classe;
    }

    public function setClasse(?ClasseRepartition $classe): static
    {
        $this->classe = $classe;

        return $this;
    }

    public function getPromotion(): ?string
    {
        if ($this->promo !== null && is_numeric($this->promo)) {
            $currentYear = (int)$this->promo;
            $previousYear = $currentYear - 1;
            return "{$previousYear}-{$currentYear}";
        }
        return null;
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

    /**
     * @return Collection<int, PaiementEleve>
     */
    public function getPaiementEleves(): Collection
    {
        return $this->paiementEleves;
    }

    public function addPaiementElefe(PaiementEleve $paiementElefe): static
    {
        if (!$this->paiementEleves->contains($paiementElefe)) {
            $this->paiementEleves->add($paiementElefe);
            $paiementElefe->setInscription($this);
        }

        return $this;
    }

    public function removePaiementElefe(PaiementEleve $paiementElefe): static
    {
        if ($this->paiementEleves->removeElement($paiementElefe)) {
            // set the owning side to null (unless already changed)
            if ($paiementElefe->getInscription() === $this) {
                $paiementElefe->setInscription(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Recette>
     */
    public function getRecettes(): Collection
    {
        return $this->recettes;
    }

    public function addRecette(Recette $recette): static
    {
        if (!$this->recettes->contains($recette)) {
            $this->recettes->add($recette);
            $recette->setInscription($this);
        }

        return $this;
    }

    public function removeRecette(Recette $recette): static
    {
        if ($this->recettes->removeElement($recette)) {
            // set the owning side to null (unless already changed)
            if ($recette->getInscription() === $this) {
                $recette->setInscription(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ControlEleve>
     */
    public function getControlEleves(): Collection
    {
        return $this->controlEleves;
    }

    public function addControlElefe(ControlEleve $controlElefe): static
    {
        if (!$this->controlEleves->contains($controlElefe)) {
            $this->controlEleves->add($controlElefe);
            $controlElefe->setInscription($this);
        }

        return $this;
    }

    public function removeControlElefe(ControlEleve $controlElefe): static
    {
        if ($this->controlEleves->removeElement($controlElefe)) {
            // set the owning side to null (unless already changed)
            if ($controlElefe->getInscription() === $this) {
                $controlElefe->setInscription(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, NoteEleve>
     */
    public function getNoteEleves(): Collection
    {
        return $this->noteEleves;
    }

    public function addNoteElefe(NoteEleve $noteElefe): static
    {
        if (!$this->noteEleves->contains($noteElefe)) {
            $this->noteEleves->add($noteElefe);
            $noteElefe->setInscription($this);
        }

        return $this;
    }

    public function removeNoteElefe(NoteEleve $noteElefe): static
    {
        if ($this->noteEleves->removeElement($noteElefe)) {
            // set the owning side to null (unless already changed)
            if ($noteElefe->getInscription() === $this) {
                $noteElefe->setInscription(null);
            }
        }

        return $this;
    }

    public function getEtatPedagogie(): ?string
    {
        return $this->etatPedagogie;
    }

    public function setEtatPedagogie(?string $etatPedagogie): static
    {
        $this->etatPedagogie = $etatPedagogie;

        return $this;
    }
}
