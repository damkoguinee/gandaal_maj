<?php

namespace App\Entity;

use App\Repository\ControlEleveRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ControlEleveRepository::class)]
class ControlEleve
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'controlEleves')]
    private ?Event $event = null;

    #[ORM\ManyToOne(inversedBy: 'controlEleves')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Etablissement $etablissement = null;

    #[ORM\Column(length: 10)]
    private ?string $promo = null;

    #[ORM\ManyToOne(inversedBy: 'controlEleves')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $saisiePar = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateSaisie = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateControl = null;

    #[ORM\Column(nullable: true)]
    private ?float $duree = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\Column(length: 50)]
    private ?string $type = null;

    #[ORM\ManyToOne(inversedBy: 'controlEleves')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Inscription $inscription = null;

    #[ORM\Column(length: 30)]
    private ?string $etat = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $justificatif = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commentaireJustificatif = null;


    #[ORM\ManyToOne(inversedBy: 'controlesElevesJust')]
    private ?User $saisieJustificatif = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateJustificatif = null;

    #[ORM\Column(length: 50)]
    private ?string $trimestre = null;

    public function __construct()
    {
        $this->etat = 'non justifié';
        
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        $this->event = $event;

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

    public function getSaisiePar(): ?User
    {
        return $this->saisiePar;
    }

    public function setSaisiePar(?User $saisiePar): static
    {
        $this->saisiePar = $saisiePar;

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

    public function getDateControl(): ?\DateTimeInterface
    {
        return $this->dateControl;
    }

    public function setDateControl(\DateTimeInterface $dateControl): static
    {
        $this->dateControl = $dateControl;

        return $this;
    }

    public function getDuree(): ?float
    {
        return $this->duree;
    }

    public function setDuree(?float $duree): static
    {
        $this->duree = $duree;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;

        return $this;
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

    public function getInscription(): ?Inscription
    {
        return $this->inscription;
    }

    public function setInscription(?Inscription $inscription): static
    {
        $this->inscription = $inscription;

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    public function getJustificatif(): ?string
    {
        return $this->justificatif;
    }

    public function setJustificatif(?string $justificatif): static
    {
        $this->justificatif = $justificatif;

        return $this;
    }

    public function getCommentaireJustificatif(): ?string
    {
        return $this->commentaireJustificatif;
    }

    public function setCommentaireJustificatif(?string $commentaireJustificatif): static
    {
        $this->commentaireJustificatif = $commentaireJustificatif;

        return $this;
    }

    public function getSaisieJustificatif(): ?User
    {
        return $this->saisieJustificatif;
    }

    public function setSaisieJustificatif(?User $saisieJustificatif): static
    {
        $this->saisieJustificatif = $saisieJustificatif;

        return $this;
    }

    public function getDateJustificatif(): ?\DateTimeInterface
    {
        return $this->dateJustificatif;
    }

    public function setDateJustificatif(?\DateTimeInterface $dateJustificatif): static
    {
        $this->dateJustificatif = $dateJustificatif;

        return $this;
    }

    public function getTrimestre(): ?string
    {
        return $this->trimestre;
    }

    public function setTrimestre(string $trimestre): static
    {
        $this->trimestre = $trimestre;

        return $this;
    }

    
}
