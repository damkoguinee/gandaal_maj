<?php

namespace App\Entity;

use App\Repository\ConfigDeviseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConfigDeviseRepository::class)]
class ConfigDevise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    private ?string $nom = null;

    /**
     * @var Collection<int, PaiementEleve>
     */
    #[ORM\OneToMany(targetEntity: PaiementEleve::class, mappedBy: 'devise')]
    private Collection $paiementEleves;

    /**
     * @var Collection<int, MouvementCaisse>
     */
    #[ORM\OneToMany(targetEntity: MouvementCaisse::class, mappedBy: 'devise')]
    private Collection $mouvementCaisses;

    /**
     * @var Collection<int, MouvementCollaborateur>
     */
    #[ORM\OneToMany(targetEntity: MouvementCollaborateur::class, mappedBy: 'devise')]
    private Collection $mouvementCollaborateurs;


    public function __construct()
    {
        $this->paiementEleves = new ArrayCollection();
        $this->mouvementCaisses = new ArrayCollection();
        $this->mouvementCollaborateurs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

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
            $paiementElefe->setDevise($this);
        }

        return $this;
    }

    public function removePaiementElefe(PaiementEleve $paiementElefe): static
    {
        if ($this->paiementEleves->removeElement($paiementElefe)) {
            // set the owning side to null (unless already changed)
            if ($paiementElefe->getDevise() === $this) {
                $paiementElefe->setDevise(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MouvementCaisse>
     */
    public function getMouvementCaisses(): Collection
    {
        return $this->mouvementCaisses;
    }

    public function addMouvementCaiss(MouvementCaisse $mouvementCaiss): static
    {
        if (!$this->mouvementCaisses->contains($mouvementCaiss)) {
            $this->mouvementCaisses->add($mouvementCaiss);
            $mouvementCaiss->setDevise($this);
        }

        return $this;
    }

    public function removeMouvementCaiss(MouvementCaisse $mouvementCaiss): static
    {
        if ($this->mouvementCaisses->removeElement($mouvementCaiss)) {
            // set the owning side to null (unless already changed)
            if ($mouvementCaiss->getDevise() === $this) {
                $mouvementCaiss->setDevise(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MouvementCollaborateur>
     */
    public function getMouvementCollaborateurs(): Collection
    {
        return $this->mouvementCollaborateurs;
    }

    public function addMouvementCollaborateur(MouvementCollaborateur $mouvementCollaborateur): static
    {
        if (!$this->mouvementCollaborateurs->contains($mouvementCollaborateur)) {
            $this->mouvementCollaborateurs->add($mouvementCollaborateur);
            $mouvementCollaborateur->setDevise($this);
        }

        return $this;
    }

    public function removeMouvementCollaborateur(MouvementCollaborateur $mouvementCollaborateur): static
    {
        if ($this->mouvementCollaborateurs->removeElement($mouvementCollaborateur)) {
            // set the owning side to null (unless already changed)
            if ($mouvementCollaborateur->getDevise() === $this) {
                $mouvementCollaborateur->setDevise(null);
            }
        }

        return $this;
    }
}
