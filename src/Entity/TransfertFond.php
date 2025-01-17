<?php

namespace App\Entity;

use App\Repository\TransfertFondRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransfertFondRepository::class)]
class TransfertFond extends MouvementCaisse
{

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $document = null;

    #[ORM\ManyToOne(inversedBy: 'transfertFonds')]
    private ?ConfigCaisse $caisseReception = null;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDocument(): ?string
    {
        return $this->document;
    }

    public function setDocument(?string $document): static
    {
        $this->document = $document;

        return $this;
    }

    public function getCaisseReception(): ?ConfigCaisse
    {
        return $this->caisseReception;
    }

    public function setCaisseReception(?ConfigCaisse $caisseReception): static
    {
        $this->caisseReception = $caisseReception;

        return $this;
    }
}
