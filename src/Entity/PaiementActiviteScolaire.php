<?php

namespace App\Entity;

use App\Repository\PaiementActiviteScolaireRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaiementActiviteScolaireRepository::class)]
class PaiementActiviteScolaire extends MouvementCaisse
{
    

    #[ORM\Column(length: 50)]
    private ?string $periode = null;

    #[ORM\ManyToOne(inversedBy: 'paiementActiviteScolaires')]
    #[ORM\JoinColumn(nullable: false)]
    private ?InscriptionActivite $inscription = null;

    

    public function getPeriode(): ?string
    {
        return $this->periode;
    }

    public function setPeriode(string $periode): static
    {
        $this->periode = $periode;

        return $this;
    }

    public function getInscription(): ?InscriptionActivite
    {
        return $this->inscription;
    }

    public function setInscription(?InscriptionActivite $inscription): static
    {
        $this->inscription = $inscription;

        return $this;
    }

    
}
