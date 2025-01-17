<?php

namespace App\Entity;

use App\Repository\DevoirEleveRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DevoirEleveRepository::class)]
class DevoirEleve
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\ManyToOne(inversedBy: 'devoirEleves')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ClasseRepartition $classe = null;

    #[ORM\ManyToOne(inversedBy: 'devoirEleves')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Matiere $matiere = null;

    #[ORM\ManyToOne(inversedBy: 'devoirEleves')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $enseignant = null;

    #[ORM\Column(length: 100)]
    private ?string $typeDevoir = null;

    #[ORM\Column]
    private ?float $coef = null;

    #[ORM\Column]
    private ?int $periode = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)] // Changement ici
    private ?\DateTimeInterface $dateDevoir = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateSaisie = null;

    #[ORM\Column(length: 10)]
    private ?string $promo = null;

    #[ORM\ManyToOne(inversedBy: 'devoirEleves')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $saisiePar = null;

    /**
     * @var Collection<int, NoteEleve>
     */
    #[ORM\OneToMany(targetEntity: NoteEleve::class, mappedBy: 'devoir')]
    private Collection $noteEleves;


    public function __construct()
    {
        $this->noteEleves = new ArrayCollection();
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

    public function getClasse(): ?ClasseRepartition
    {
        return $this->classe;
    }

    public function setClasse(?ClasseRepartition $classe): static
    {
        $this->classe = $classe;

        return $this;
    }

    public function getMatiere(): ?Matiere
    {
        return $this->matiere;
    }

    public function setMatiere(?Matiere $matiere): static
    {
        $this->matiere = $matiere;

        return $this;
    }

    public function getEnseignant(): ?User
    {
        return $this->enseignant;
    }

    public function setEnseignant(?User $enseignant): static
    {
        $this->enseignant = $enseignant;

        return $this;
    }

    public function getTypeDevoir(): ?string
    {
        return $this->typeDevoir;
    }

    public function setTypeDevoir(string $typeDevoir): static
    {
        $this->typeDevoir = $typeDevoir;

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

    public function getPeriode(): ?int
    {
        return $this->periode;
    }

    public function setPeriode(int $periode): static
    {
        $this->periode = $periode;

        return $this;
    }

    public function getDateDevoir(): ?\DateTimeInterface
    {
        return $this->dateDevoir;
    }

    public function setDateDevoir(\DateTimeInterface $dateDevoir): static
    {
        $this->dateDevoir = $dateDevoir;

        return $this;
    }

    public function getDateSaisie(): ?\DateTimeInterface
    {
        return $this->dateSaisie;
    }

    public function setDateSaisie(\DateTimeInterface $dateSaisie): static
    {
        $this->dateSaisie = $dateSaisie;

        return $this;
    }

    public function getPromo(): ?string
    {
        return $this->promo;
    }

    public function setPromo(string $promo): static
    {
        $this->promo = $promo;

        return $this;
    }

    public function getSaisiePar(): ?User
    {
        return $this->saisiePar;
    }

    public function setSaisiePar(?User $saisiePar): static
    {
        $this->saisiePar = $saisiePar;

        return $this;
    }

    /**
     * @return Collection<int, NoteEleve>
     */
    public function getNoteEleves(): Collection
    {
        return $this->noteEleves;
    }

    public function addNoteElefe(NoteEleve $noteElefe): static
    {
        if (!$this->noteEleves->contains($noteElefe)) {
            $this->noteEleves->add($noteElefe);
            $noteElefe->setDevoir($this);
        }

        return $this;
    }

    public function removeNoteElefe(NoteEleve $noteElefe): static
    {
        if ($this->noteEleves->removeElement($noteElefe)) {
            // set the owning side to null (unless already changed)
            if ($noteElefe->getDevoir() === $this) {
                $noteElefe->setDevoir(null);
            }
        }

        return $this;
    }

    
}
