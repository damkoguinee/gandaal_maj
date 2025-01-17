<?php

namespace App\Entity;

use App\Repository\FiliationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FiliationRepository::class)]
class Filiation extends User
{
    #[ORM\Column(length: 10)]
    private ?string $lienFamilial = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $profession = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $lieuTravail = null;

    /**
     * @var Collection<int, Eleve>
     */
    #[ORM\ManyToMany(targetEntity: Eleve::class, inversedBy: 'filiations')]
    private Collection $eleve;

    public function __construct()
    {
        parent::__construct();
        $this->eleve = new ArrayCollection();
    }

   
    public function getLienFamilial(): ?string
    {
        return $this->lienFamilial;
    }

    public function setLienFamilial(string $lienFamilial): static
    {
        $this->lienFamilial = $lienFamilial;

        return $this;
    }

    

    public function getProfession(): ?string
    {
        return $this->profession;
    }

    public function setProfession(?string $profession): static
    {
        $this->profession = $profession;

        return $this;
    }

    public function getLieuTravail(): ?string
    {
        return $this->lieuTravail;
    }

    public function setLieuTravail(?string $lieuTravail): static
    {
        $this->lieuTravail = $lieuTravail;

        return $this;
    }

    /**
     * @return Collection<int, Eleve>
     */
    public function getEleve(): Collection
    {
        return $this->eleve;
    }

    public function addEleve(Eleve $eleve): static
    {
        if (!$this->eleve->contains($eleve)) {
            $this->eleve->add($eleve);
        }

        return $this;
    }

    public function removeEleve(Eleve $eleve): static
    {
        $this->eleve->removeElement($eleve);

        return $this;
    }

   
}
