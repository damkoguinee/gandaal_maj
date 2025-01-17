<?php

namespace App\Entity;

use App\Repository\ConfigModePaiementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConfigModePaiementRepository::class)]
class ConfigModePaiement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    /**
     * @var Collection<int, PaiementEleve>
     */
    #[ORM\OneToMany(targetEntity: PaiementEleve::class, mappedBy: 'modePaie')]
    private Collection $paiementEleves;

    /**
     * @var Collection<int, MouvementCaisse>
     */
    #[ORM\OneToMany(targetEntity: MouvementCaisse::class, mappedBy: 'modePaie')]
    private Collection $mouvementCaisses;

    /**
     * @var Collection<int, ConfigurationLogiciel>
     */
    #[ORM\OneToMany(targetEntity: ConfigurationLogiciel::class, mappedBy: 'modePaieDefaut')]
    private Collection $configurationLogiciels;

   

    public function __construct()
    {
        $this->paiementEleves = new ArrayCollection();
        $this->mouvementCaisses = new ArrayCollection();
        $this->configurationLogiciels = new ArrayCollection();
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
            $paiementElefe->setModePaie($this);
        }

        return $this;
    }

    public function removePaiementElefe(PaiementEleve $paiementElefe): static
    {
        if ($this->paiementEleves->removeElement($paiementElefe)) {
            // set the owning side to null (unless already changed)
            if ($paiementElefe->getModePaie() === $this) {
                $paiementElefe->setModePaie(null);
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
            $mouvementCaiss->setModePaie($this);
        }

        return $this;
    }

    public function removeMouvementCaiss(MouvementCaisse $mouvementCaiss): static
    {
        if ($this->mouvementCaisses->removeElement($mouvementCaiss)) {
            // set the owning side to null (unless already changed)
            if ($mouvementCaiss->getModePaie() === $this) {
                $mouvementCaiss->setModePaie(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ConfigurationLogiciel>
     */
    public function getConfigurationLogiciels(): Collection
    {
        return $this->configurationLogiciels;
    }

    public function addConfigurationLogiciel(ConfigurationLogiciel $configurationLogiciel): static
    {
        if (!$this->configurationLogiciels->contains($configurationLogiciel)) {
            $this->configurationLogiciels->add($configurationLogiciel);
            $configurationLogiciel->setModePaieDefaut($this);
        }

        return $this;
    }

    public function removeConfigurationLogiciel(ConfigurationLogiciel $configurationLogiciel): static
    {
        if ($this->configurationLogiciels->removeElement($configurationLogiciel)) {
            // set the owning side to null (unless already changed)
            if ($configurationLogiciel->getModePaieDefaut() === $this) {
                $configurationLogiciel->setModePaieDefaut(null);
            }
        }

        return $this;
    }

    
}
