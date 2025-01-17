<?php

namespace App\Entity;

use App\Repository\PaiementSalairePersonnelRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaiementSalairePersonnelRepository::class)]
class PaiementSalairePersonnel extends MouvementCaisse
{
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $periode = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commentaires = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2)]
    private ?string $salaireBrut = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: true)]
    private ?string $prime = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: true)]
    private ?string $avanceSalaire = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: true)]
    private ?string $cotisation = null;

    #[ORM\Column(nullable: true)]
    private ?float $heures = null;

    #[ORM\ManyToOne(inversedBy: 'paiementSalairePersonnels')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PersonnelActif $personnelActif = null;

    #[ORM\Column(nullable: true)]
    private ?float $tauxHoraire = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $compteBancaire = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $banqueVirement = null;


    public function getPeriode(): ?\DateTimeInterface
    {
        return $this->periode;
    }

    public function setPeriode(\DateTimeInterface $periode): static
    {
        $this->periode = $periode;

        return $this;
    }

    public function getCommentaires(): ?string
    {
        return $this->commentaires;
    }

    public function setCommentaires(?string $commentaires): static
    {
        $this->commentaires = $commentaires;

        return $this;
    }

    public function getSalaireBrut(): ?string
    {
        return $this->salaireBrut;
    }

    public function setSalaireBrut(string $salaireBrut): static
    {
        $this->salaireBrut = $salaireBrut;

        return $this;
    }

    public function getPrime(): ?string
    {
        return $this->prime;
    }

    public function setPrime(?string $prime): static
    {
        $this->prime = $prime;

        return $this;
    }

    public function getAvanceSalaire(): ?string
    {
        return $this->avanceSalaire;
    }

    public function setAvanceSalaire(?string $avanceSalaire): static
    {
        $this->avanceSalaire = $avanceSalaire;

        return $this;
    }

    public function getCotisation(): ?string
    {
        return $this->cotisation;
    }

    public function setCotisation(?string $cotisation): static
    {
        $this->cotisation = $cotisation;

        return $this;
    }

    public function getHeures(): ?float
    {
        return $this->heures;
    }

    public function setHeures(?float $heures): static
    {
        $this->heures = $heures;

        return $this;
    }

    public function getPersonnelActif(): ?PersonnelActif
    {
        return $this->personnelActif;
    }

    public function setPersonnelActif(?PersonnelActif $personnelActif): static
    {
        $this->personnelActif = $personnelActif;

        return $this;
    }

    public function getTauxHoraire(): ?float
    {
        return $this->tauxHoraire;
    }

    public function setTauxHoraire(?float $tauxHoraire): static
    {
        $this->tauxHoraire = $tauxHoraire;

        return $this;
    }

    public function getCompteBancaire(): ?string
    {
        return $this->compteBancaire;
    }

    public function setCompteBancaire(?string $compteBancaire): static
    {
        $this->compteBancaire = $compteBancaire;

        return $this;
    }

    public function getBanqueVirement(): ?string
    {
        return $this->banqueVirement;
    }

    public function setBanqueVirement(?string $banqueVirement): static
    {
        $this->banqueVirement = $banqueVirement;

        return $this;
    }
}
