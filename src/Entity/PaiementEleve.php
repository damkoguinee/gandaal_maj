<?php

namespace App\Entity;

use App\Repository\PaiementEleveRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaiementEleveRepository::class)]
class PaiementEleve extends MouvementCaisse
{  
    #[ORM\Column(length: 50)]
    private ?string $typePaie = null;

    #[ORM\Column(length: 50)]
    private ?string $origine = null;

    #[ORM\ManyToOne(inversedBy: 'paiementEleves')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Inscription $inscription = null;


    public function getTypePaie(): ?string
    {
        return $this->typePaie;
    }

    public function setTypePaie(string $typePaie): static
    {
        $this->typePaie = $typePaie;

        return $this;
    }

    public function getOrigine(): ?string
    {
        return $this->origine;
    }

    public function setOrigine(string $origine): static
    {
        $this->origine = $origine;

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
}
