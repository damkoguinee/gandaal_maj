<?php

namespace App\Entity;

use App\Repository\ConfigurationModuleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConfigurationModuleRepository::class)]
class ConfigurationModule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

   

    /**
     * @var Collection<int, Cursus>
     */
    #[ORM\ManyToMany(targetEntity: Cursus::class, inversedBy: 'configurationModules')]
    private Collection $cursus;

    #[ORM\ManyToOne(inversedBy: 'configurationModules')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Etablissement $etablissement = null;

    #[ORM\Column(length: 50)]
    private ?string $periode = null;

    public function __construct()
    {
        $this->cursus = new ArrayCollection();
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
     * @return Collection<int, Cursus>
     */
    public function getCursus(): Collection
    {
        return $this->cursus;
    }

    public function addCursu(Cursus $cursu): static
    {
        if (!$this->cursus->contains($cursu)) {
            $this->cursus->add($cursu);
        }

        return $this;
    }

    public function removeCursu(Cursus $cursu): static
    {
        $this->cursus->removeElement($cursu);

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

    public function getPeriode(): ?string
    {
        return $this->periode;
    }

    public function setPeriode(string $periode): static
    {
        $this->periode = $periode;

        return $this;
    }
}
