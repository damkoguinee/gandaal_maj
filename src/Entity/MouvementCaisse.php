<?php

namespace App\Entity;

use App\Repository\MouvementCaisseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MouvementCaisseRepository::class)]
#[ORM\InheritanceType("JOINED")]
#[ORM\DiscriminatorColumn(name:"type", type:"string")]
#[ORM\DiscriminatorMap([
    "scolarite" => PaiementEleve::class,
    "depense" => Depense::class,
    "recette" => Recette::class,
    "transfert" => TransfertFond::class,
    "versement" => Versement::class,
    "decaissement" => Decaissement::class,
    "activite" => PaiementActiviteScolaire::class,
    "avance" => AvanceSalairePersonnel::class,
    "salaire" => PaiementSalairePersonnel::class,
])]
abstract class MouvementCaisse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementCaisses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ConfigCaisse $caisse = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementCaisses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ConfigCategorieOperation $categorieOperation = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementCaisses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ConfigCompteOperation $compteOperation = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementCaisses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ConfigDevise $devise = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementCaisses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ConfigModePaiement $modePaie = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: true)]
    private ?string $montant = null;

    #[ORM\Column(length: 50)]
    private ?string $typeMouvement = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $numeroPaie = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $banquePaie = null;

    #[ORM\Column(nullable: true)]
    private ?float $taux = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $reference = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $etatOperation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateOperation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateSaisie = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementCaisses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $saisiePar = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementCaisses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Etablissement $etablissement = null;

    #[ORM\Column(length: 10)]
    private ?string $promo = null;

    

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCaisse(): ?ConfigCaisse
    {
        return $this->caisse;
    }

    public function setCaisse(?ConfigCaisse $caisse): static
    {
        $this->caisse = $caisse;

        return $this;
    }

    public function getCategorieOperation(): ?ConfigCategorieOperation
    {
        return $this->categorieOperation;
    }

    public function setCategorieOperation(?ConfigCategorieOperation $categorieOperation): static
    {
        $this->categorieOperation = $categorieOperation;

        return $this;
    }

    public function getCompteOperation(): ?ConfigCompteOperation
    {
        return $this->compteOperation;
    }

    public function setCompteOperation(?ConfigCompteOperation $compteOperation): static
    {
        $this->compteOperation = $compteOperation;

        return $this;
    }

    public function getDevise(): ?ConfigDevise
    {
        return $this->devise;
    }

    public function setDevise(?ConfigDevise $devise): static
    {
        $this->devise = $devise;

        return $this;
    }

    public function getModePaie(): ?ConfigModePaiement
    {
        return $this->modePaie;
    }

    public function setModePaie(?ConfigModePaiement $modePaie): static
    {
        $this->modePaie = $modePaie;

        return $this;
    }

    public function getMontant(): ?string
    {
        return $this->montant;
    }

    public function setMontant(?string $montant): static
    {
        $this->montant = $montant;

        return $this;
    }

    public function getTypeMouvement(): ?string
    {
        return $this->typeMouvement;
    }

    public function setTypeMouvement(string $typeMouvement): static
    {
        $this->typeMouvement = $typeMouvement;

        return $this;
    }

    public function getNumeroPaie(): ?string
    {
        return $this->numeroPaie;
    }

    public function setNumeroPaie(?string $numeroPaie): static
    {
        $this->numeroPaie = $numeroPaie;

        return $this;
    }

    public function getBanquePaie(): ?string
    {
        return $this->banquePaie;
    }

    public function setBanquePaie(?string $banquePaie): static
    {
        $this->banquePaie = $banquePaie;

        return $this;
    }

    public function getTaux(): ?float
    {
        return $this->taux;
    }

    public function setTaux(?float $taux): static
    {
        $this->taux = $taux;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getEtatOperation(): ?string
    {
        return $this->etatOperation;
    }

    public function setEtatOperation(?string $etatOperation): static
    {
        $this->etatOperation = $etatOperation;

        return $this;
    }

    public function getDateOperation(): ?\DateTimeInterface
    {
        return $this->dateOperation;
    }

    public function setDateOperation(\DateTimeInterface $dateOperation): static
    {
        $this->dateOperation = $dateOperation;

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

    
}
