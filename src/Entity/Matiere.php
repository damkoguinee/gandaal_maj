<?php

namespace App\Entity;

use App\Repository\MatiereRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MatiereRepository::class)]
class Matiere
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'matieres')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Formation $formation = null;

    #[ORM\Column(length: 150)]
    private ?string $nom = null;

    #[ORM\Column]
    private ?float $coef = null;

    #[ORM\ManyToOne(inversedBy: 'matieres')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CategorieMatiere $categorie = null;

    #[ORM\Column(nullable: true)]
    private ?float $nombreHeure = null;

    #[ORM\Column(length: 50)]
    private ?string $codeMatiere = null;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'matiere')]
    private Collection $events;

    /**
     * @var Collection<int, DevoirEleve>
     */
    #[ORM\OneToMany(targetEntity: DevoirEleve::class, mappedBy: 'matiere')]
    private Collection $devoirEleves;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $etatPedagogie = null;

    // /**
    //  * @var Collection<int, Enseignant>
    //  */
    // #[ORM\ManyToMany(targetEntity: Enseignant::class, mappedBy: 'matiere')]
    // private Collection $enseignants;

    public function __construct()
    {
        // $this->enseignants = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->devoirEleves = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFormation(): ?Formation
    {
        return $this->formation;
    }

    public function setFormation(?Formation $formation): static
    {
        $this->formation = $formation;

        return $this;
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

    public function getCoef(): ?float
    {
        return $this->coef;
    }

    public function setCoef(float $coef): static
    {
        $this->coef = $coef;

        return $this;
    }

    public function getCategorie(): ?CategorieMatiere
    {
        return $this->categorie;
    }

    public function setCategorie(?CategorieMatiere $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getNombreHeure(): ?float
    {
        return $this->nombreHeure;
    }

    public function setNombreHeure(?float $nombreHeure): static
    {
        $this->nombreHeure = $nombreHeure;

        return $this;
    }

    public function getCodeMatiere(): ?string
    {
        return $this->codeMatiere;
    }

    public function setCodeMatiere(string $codeMatiere): static
    {
        $this->codeMatiere = $codeMatiere;

        return $this;
    }

    // /**
    //  * @return Collection<int, Enseignant>
    //  */
    // public function getEnseignants(): Collection
    // {
    //     return $this->enseignants;
    // }

    // public function addEnseignant(Enseignant $enseignant): static
    // {
    //     if (!$this->enseignants->contains($enseignant)) {
    //         $this->enseignants->add($enseignant);
    //         $enseignant->addMatiere($this);
    //     }

    //     return $this;
    // }

    // public function removeEnseignant(Enseignant $enseignant): static
    // {
    //     if ($this->enseignants->removeElement($enseignant)) {
    //         $enseignant->removeMatiere($this);
    //     }

    //     return $this;
    // }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setMatiere($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getMatiere() === $this) {
                $event->setMatiere(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, DevoirEleve>
     */
    public function getDevoirEleves(): Collection
    {
        return $this->devoirEleves;
    }

    public function addDevoirElefe(DevoirEleve $devoirElefe): static
    {
        if (!$this->devoirEleves->contains($devoirElefe)) {
            $this->devoirEleves->add($devoirElefe);
            $devoirElefe->setMatiere($this);
        }

        return $this;
    }

    public function removeDevoirElefe(DevoirEleve $devoirElefe): static
    {
        if ($this->devoirEleves->removeElement($devoirElefe)) {
            // set the owning side to null (unless already changed)
            if ($devoirElefe->getMatiere() === $this) {
                $devoirElefe->setMatiere(null);
            }
        }

        return $this;
    }

    public function getEtatPedagogie(): ?string
    {
        return $this->etatPedagogie;
    }

    public function setEtatPedagogie(?string $etatPedagogie): static
    {
        $this->etatPedagogie = $etatPedagogie;

        return $this;
    }
}
