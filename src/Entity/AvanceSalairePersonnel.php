<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use App\Entity\MouvementCaisse;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\AvanceSalairePersonnelRepository;

#[ORM\Entity(repositoryClass: AvanceSalairePersonnelRepository::class)]
class AvanceSalairePersonnel extends MouvementCaisse
{

    #[ORM\ManyToOne(inversedBy: 'avanceSalairePersonnels')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PersonnelActif $personnelActif = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $periode = null;

    public function getPersonnelActif(): ?PersonnelActif
    {
        return $this->personnelActif;
    }

    public function setPersonnelActif(?PersonnelActif $personnelActif): static
    {
        $this->personnelActif = $personnelActif;

        return $this;
    }

    public function getPeriode(): ?\DateTimeInterface
    {
        return $this->periode;
    }

    public function setPeriode(\DateTimeInterface $periode): static
    {
        $this->periode = $periode;

        return $this;
    }
}
