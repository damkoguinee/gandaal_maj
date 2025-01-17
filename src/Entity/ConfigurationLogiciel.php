<?php

namespace App\Entity;

use App\Repository\ConfigurationLogicielRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConfigurationLogicielRepository::class)]
class ConfigurationLogiciel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $color = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $backgroundColor = null;

    #[ORM\Column(length: 110, nullable: true)]
    private ?string $longLogo = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $largLogo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cheminSauvegarde = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cheminMysql = null;

    #[ORM\ManyToOne(inversedBy: 'configurationLogiciels')]
    private ?ConfigCaisse $caisseDefaut = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $documentEleve = null;

    #[ORM\ManyToOne(inversedBy: 'configurationLogiciels')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Etablissement $etablissement = null;

    #[ORM\ManyToOne(inversedBy: 'configurationLogiciels')]
    private ?ConfigModePaiement $modePaieDefaut = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $formatBulletin = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getBackgroundColor(): ?string
    {
        return $this->backgroundColor;
    }

    public function setBackgroundColor(?string $backgroundColor): static
    {
        $this->backgroundColor = $backgroundColor;

        return $this;
    }


    public function getLongLogo(): ?string
    {
        return $this->longLogo;
    }

    public function setLongLogo(?string $longLogo): static
    {
        $this->longLogo = $longLogo;

        return $this;
    }

    public function getLargLogo(): ?string
    {
        return $this->largLogo;
    }

    public function setLargLogo(?string $largLogo): static
    {
        $this->largLogo = $largLogo;

        return $this;
    }

    public function getCheminSauvegarde(): ?string
    {
        return $this->cheminSauvegarde;
    }

    public function setCheminSauvegarde(?string $cheminSauvegarde): static
    {
        $this->cheminSauvegarde = $cheminSauvegarde;

        return $this;
    }

    public function getCheminMysql(): ?string
    {
        return $this->cheminMysql;
    }

    public function setCheminMysql(?string $cheminMysql): static
    {
        $this->cheminMysql = $cheminMysql;

        return $this;
    }

    public function getCaisseDefaut(): ?ConfigCaisse
    {
        return $this->caisseDefaut;
    }

    public function setCaisseDefaut(?ConfigCaisse $caisseDefaut): static
    {
        $this->caisseDefaut = $caisseDefaut;

        return $this;
    }

    public function getDocumentEleve(): ?string
    {
        return $this->documentEleve;
    }

    public function setDocumentEleve(?string $documentEleve): static
    {
        $this->documentEleve = $documentEleve;

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

    public function getModePaieDefaut(): ?ConfigModePaiement
    {
        return $this->modePaieDefaut;
    }

    public function setModePaieDefaut(?ConfigModePaiement $modePaieDefaut): static
    {
        $this->modePaieDefaut = $modePaieDefaut;

        return $this;
    }

    public function getFormatBulletin(): ?string
    {
        return $this->formatBulletin;
    }

    public function setFormatBulletin(?string $formatBulletin): static
    {
        $this->formatBulletin = $formatBulletin;

        return $this;
    }
}
