<?php

namespace App\Entity;

use App\Repository\FormationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormationRepository::class)]
class Formation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $nom = null;

    #[ORM\Column(length: 15)]
    private ?string $code = null;

    /**
     * @var Collection<int, ClasseRepartition>
     */
    #[ORM\OneToMany(targetEntity: ClasseRepartition::class, mappedBy: 'formation')]
    private Collection $classeRepartitions;

    /**
     * @var Collection<int, Matiere>
     */
    #[ORM\OneToMany(targetEntity: Matiere::class, mappedBy: 'formation')]
    private Collection $matieres;

    /**
     * @var Collection<int, FraisScolarite>
     */
    #[ORM\OneToMany(targetEntity: FraisScolarite::class, mappedBy: 'formation')]
    private Collection $fraisScolarites;

    #[ORM\ManyToOne(inversedBy: 'formations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cursus $cursus = null;

    #[ORM\Column(length: 50)]
    private ?string $classe = null;

    public function __construct()
    {
        $this->classeRepartitions = new ArrayCollection();
        $this->matieres = new ArrayCollection();
        $this->fraisScolarites = new ArrayCollection();
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return Collection<int, ClasseRepartition>
     */
    public function getClasseRepartitions(): Collection
    {
        return $this->classeRepartitions;
    }

    public function addClasseRepartition(ClasseRepartition $classeRepartition): static
    {
        if (!$this->classeRepartitions->contains($classeRepartition)) {
            $this->classeRepartitions->add($classeRepartition);
            $classeRepartition->setFormation($this);
        }

        return $this;
    }

    public function removeClasseRepartition(ClasseRepartition $classeRepartition): static
    {
        if ($this->classeRepartitions->removeElement($classeRepartition)) {
            // set the owning side to null (unless already changed)
            if ($classeRepartition->getFormation() === $this) {
                $classeRepartition->setFormation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Matiere>
     */
    public function getMatieres(): Collection
    {
        return $this->matieres;
    }

    public function addMatiere(Matiere $matiere): static
    {
        if (!$this->matieres->contains($matiere)) {
            $this->matieres->add($matiere);
            $matiere->setFormation($this);
        }

        return $this;
    }

    public function removeMatiere(Matiere $matiere): static
    {
        if ($this->matieres->removeElement($matiere)) {
            // set the owning side to null (unless already changed)
            if ($matiere->getFormation() === $this) {
                $matiere->setFormation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FraisScolarite>
     */
    public function getFraisScolarites(): Collection
    {
        return $this->fraisScolarites;
    }

    public function addFraisScolarite(FraisScolarite $fraisScolarite): static
    {
        if (!$this->fraisScolarites->contains($fraisScolarite)) {
            $this->fraisScolarites->add($fraisScolarite);
            $fraisScolarite->setFormation($this);
        }

        return $this;
    }

    public function removeFraisScolarite(FraisScolarite $fraisScolarite): static
    {
        if ($this->fraisScolarites->removeElement($fraisScolarite)) {
            // set the owning side to null (unless already changed)
            if ($fraisScolarite->getFormation() === $this) {
                $fraisScolarite->setFormation(null);
            }
        }

        return $this;
    }

    public function getCursus(): ?Cursus
    {
        return $this->cursus;
    }

    public function setCursus(?Cursus $cursus): static
    {
        $this->cursus = $cursus;

        return $this;
    }

    public function getClasse(): ?string
    {
        return $this->classe;
    }

    public function setClasse(string $classe): static
    {
        $this->classe = $classe;

        return $this;
    }
}
