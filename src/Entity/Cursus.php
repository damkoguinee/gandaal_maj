<?php

namespace App\Entity;

use App\Repository\CursusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CursusRepository::class)]
class Cursus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $nom = null;

    /**
     * @var Collection<int, NiveauClasse>
     */
    #[ORM\OneToMany(targetEntity: NiveauClasse::class, mappedBy: 'cursus')]
    private Collection $niveauClasses;

    #[ORM\ManyToOne(inversedBy: 'cursuses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Etablissement $etablissement = null;

    /**
     * @var Collection<int, FraisInscription>
     */
    #[ORM\OneToMany(targetEntity: FraisInscription::class, mappedBy: 'cursus')]
    private Collection $fraisInscriptions;

    /**
     * @var Collection<int, Formation>
     */
    #[ORM\OneToMany(targetEntity: Formation::class, mappedBy: 'cursus')]
    private Collection $formations;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $fonctionnement = null;

    /**
     * @var Collection<int, PersonnelActif>
     */
    #[ORM\ManyToMany(targetEntity: PersonnelActif::class, mappedBy: 'rattachementPedagogie')]
    private Collection $personnelActifs;

    /**
     * @var Collection<int, ConfigurationModule>
     */
    #[ORM\ManyToMany(targetEntity: ConfigurationModule::class, mappedBy: 'cursus')]
    private Collection $configurationModules;

    

    public function __construct()
    {
        $this->niveauClasses = new ArrayCollection();
        $this->fraisInscriptions = new ArrayCollection();
        $this->formations = new ArrayCollection();
        $this->personnelActifs = new ArrayCollection();
        $this->configurationModules = new ArrayCollection();
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
     * @return Collection<int, NiveauClasse>
     */
    public function getNiveauClasses(): Collection
    {
        return $this->niveauClasses;
    }

    public function addNiveauClass(NiveauClasse $niveauClass): static
    {
        if (!$this->niveauClasses->contains($niveauClass)) {
            $this->niveauClasses->add($niveauClass);
            $niveauClass->setCursus($this);
        }

        return $this;
    }

    public function removeNiveauClass(NiveauClasse $niveauClass): static
    {
        if ($this->niveauClasses->removeElement($niveauClass)) {
            // set the owning side to null (unless already changed)
            if ($niveauClass->getCursus() === $this) {
                $niveauClass->setCursus(null);
            }
        }

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
     * @return Collection<int, FraisInscription>
     */
    public function getFraisInscriptions(): Collection
    {
        return $this->fraisInscriptions;
    }

    public function addFraisInscription(FraisInscription $fraisInscription): static
    {
        if (!$this->fraisInscriptions->contains($fraisInscription)) {
            $this->fraisInscriptions->add($fraisInscription);
            $fraisInscription->setCursus($this);
        }

        return $this;
    }

    public function removeFraisInscription(FraisInscription $fraisInscription): static
    {
        if ($this->fraisInscriptions->removeElement($fraisInscription)) {
            // set the owning side to null (unless already changed)
            if ($fraisInscription->getCursus() === $this) {
                $fraisInscription->setCursus(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Formation>
     */
    public function getFormations(): Collection
    {
        return $this->formations;
    }

    public function addFormation(Formation $formation): static
    {
        if (!$this->formations->contains($formation)) {
            $this->formations->add($formation);
            $formation->setCursus($this);
        }

        return $this;
    }

    public function removeFormation(Formation $formation): static
    {
        if ($this->formations->removeElement($formation)) {
            // set the owning side to null (unless already changed)
            if ($formation->getCursus() === $this) {
                $formation->setCursus(null);
            }
        }

        return $this;
    }

    public function getFonctionnement(): ?string
    {
        return $this->fonctionnement;
    }

    public function setFonctionnement(?string $fonctionnement): static
    {
        $this->fonctionnement = $fonctionnement;

        return $this;
    }

    /**
     * @return Collection<int, PersonnelActif>
     */
    public function getPersonnelActifs(): Collection
    {
        return $this->personnelActifs;
    }

    public function addPersonnelActif(PersonnelActif $personnelActif): static
    {
        if (!$this->personnelActifs->contains($personnelActif)) {
            $this->personnelActifs->add($personnelActif);
            $personnelActif->addRattachementPedagogie($this);
        }

        return $this;
    }

    public function removePersonnelActif(PersonnelActif $personnelActif): static
    {
        if ($this->personnelActifs->removeElement($personnelActif)) {
            $personnelActif->removeRattachementPedagogie($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, ConfigurationModule>
     */
    public function getConfigurationModules(): Collection
    {
        return $this->configurationModules;
    }

    public function addConfigurationModule(ConfigurationModule $configurationModule): static
    {
        if (!$this->configurationModules->contains($configurationModule)) {
            $this->configurationModules->add($configurationModule);
            $configurationModule->addCursu($this);
        }

        return $this;
    }

    public function removeConfigurationModule(ConfigurationModule $configurationModule): static
    {
        if ($this->configurationModules->removeElement($configurationModule)) {
            $configurationModule->removeCursu($this);
        }

        return $this;
    }
}
