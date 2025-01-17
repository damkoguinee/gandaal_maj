<?php

namespace App\Entity;

use App\Repository\ConfigActiviteScolaireRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConfigActiviteScolaireRepository::class)]
class ConfigActiviteScolaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    /**
     * @var Collection<int, TarifActiviteScolaire>
     */
    #[ORM\OneToMany(targetEntity: TarifActiviteScolaire::class, mappedBy: 'activite')]
    private Collection $tarifActiviteScolaires;


    #[ORM\ManyToOne(inversedBy: 'configActiviteScolaires')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Etablissement $etablissement = null;

    

    public function __construct()
    {
        $this->tarifActiviteScolaires = new ArrayCollection();
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
     * @return Collection<int, TarifActiviteScolaire>
     */
    public function getTarifActiviteScolaires(): Collection
    {
        return $this->tarifActiviteScolaires;
    }

    public function addTarifActiviteScolaire(TarifActiviteScolaire $tarifActiviteScolaire): static
    {
        if (!$this->tarifActiviteScolaires->contains($tarifActiviteScolaire)) {
            $this->tarifActiviteScolaires->add($tarifActiviteScolaire);
            $tarifActiviteScolaire->setActivite($this);
        }

        return $this;
    }

    public function removeTarifActiviteScolaire(TarifActiviteScolaire $tarifActiviteScolaire): static
    {
        if ($this->tarifActiviteScolaires->removeElement($tarifActiviteScolaire)) {
            // set the owning side to null (unless already changed)
            if ($tarifActiviteScolaire->getActivite() === $this) {
                $tarifActiviteScolaire->setActivite(null);
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

    
}
