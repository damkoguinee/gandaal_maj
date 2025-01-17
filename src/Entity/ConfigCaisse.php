<?php

namespace App\Entity;

use App\Repository\ConfigCaisseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConfigCaisseRepository::class)]
class ConfigCaisse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column(length: 15)]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numero = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $document = null;

    #[ORM\ManyToOne(inversedBy: 'caisses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Etablissement $etablissement = null;

    /**
     * @var Collection<int, PaiementEleve>
     */
    #[ORM\OneToMany(targetEntity: PaiementEleve::class, mappedBy: 'caisse')]
    private Collection $paiementEleves;

    /**
     * @var Collection<int, MouvementCaisse>
     */
    #[ORM\OneToMany(targetEntity: MouvementCaisse::class, mappedBy: 'caisse')]
    private Collection $mouvementCaisses;

    /**
     * @var Collection<int, TransfertFond>
     */
    #[ORM\OneToMany(targetEntity: TransfertFond::class, mappedBy: 'caisseReception')]
    private Collection $transfertFonds;

    /**
     * @var Collection<int, ConfigurationLogiciel>
     */
    #[ORM\OneToMany(targetEntity: ConfigurationLogiciel::class, mappedBy: 'caisseDefaut')]
    private Collection $configurationLogiciels;

    

    public function __construct()
    {
        $this->paiementEleves = new ArrayCollection();
        $this->mouvementCaisses = new ArrayCollection();
        $this->transfertFonds = new ArrayCollection();
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(?string $numero): static
    {
        $this->numero = $numero;

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

    public function getEtablissement(): ?Etablissement
    {
        return $this->etablissement;
    }

    public function setEtablissement(?Etablissement $etablissement): static
    {
        $this->etablissement = $etablissement;

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
            $paiementElefe->setCaisse($this);
        }

        return $this;
    }

    public function removePaiementElefe(PaiementEleve $paiementElefe): static
    {
        if ($this->paiementEleves->removeElement($paiementElefe)) {
            // set the owning side to null (unless already changed)
            if ($paiementElefe->getCaisse() === $this) {
                $paiementElefe->setCaisse(null);
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
            $mouvementCaiss->setCaisse($this);
        }

        return $this;
    }

    public function removeMouvementCaiss(MouvementCaisse $mouvementCaiss): static
    {
        if ($this->mouvementCaisses->removeElement($mouvementCaiss)) {
            // set the owning side to null (unless already changed)
            if ($mouvementCaiss->getCaisse() === $this) {
                $mouvementCaiss->setCaisse(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TransfertFond>
     */
    public function getTransfertFonds(): Collection
    {
        return $this->transfertFonds;
    }

    public function addTransfertFond(TransfertFond $transfertFond): static
    {
        if (!$this->transfertFonds->contains($transfertFond)) {
            $this->transfertFonds->add($transfertFond);
            $transfertFond->setCaisseReception($this);
        }

        return $this;
    }

    public function removeTransfertFond(TransfertFond $transfertFond): static
    {
        if ($this->transfertFonds->removeElement($transfertFond)) {
            // set the owning side to null (unless already changed)
            if ($transfertFond->getCaisseReception() === $this) {
                $transfertFond->setCaisseReception(null);
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
            $configurationLogiciel->setCaisseDefaut($this);
        }

        return $this;
    }

    public function removeConfigurationLogiciel(ConfigurationLogiciel $configurationLogiciel): static
    {
        if ($this->configurationLogiciels->removeElement($configurationLogiciel)) {
            // set the owning side to null (unless already changed)
            if ($configurationLogiciel->getCaisseDefaut() === $this) {
                $configurationLogiciel->setCaisseDefaut(null);
            }
        }

        return $this;
    }

    
}
