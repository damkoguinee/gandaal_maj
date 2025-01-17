<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    private ?PersonnelActif $enseignant = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ClasseRepartition $classe = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Matiere $matiere = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $start = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $end = null;

    #[ORM\Column(nullable: true)]
    private ?bool $allDay = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $url = null;

    #[ORM\Column(nullable: true)]
    private ?array $className = null;

    #[ORM\Column(length: 7, nullable: true)]
    private ?string $backgroundColor = null;

    #[ORM\Column(length: 7, nullable: true)]
    private ?string $borderColor = null;

    #[ORM\Column(length: 7, nullable: true)]
    private ?string $textColor = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $saisiePar = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateSaisie = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Etablissement $etablissement = null;

    #[ORM\Column(length: 10)]
    private ?string $promo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?float $duree = null;

    /**
     * @var Collection<int, HeureTravaille>
     */
    #[ORM\OneToMany(targetEntity: HeureTravaille::class, mappedBy: 'event')]
    private Collection $heureTravailles;

    /**
     * @var Collection<int, ControlEleve>
     */
    #[ORM\OneToMany(targetEntity: ControlEleve::class, mappedBy: 'event')]
    private Collection $controlEleves;

    public function __construct()
    {
        $this->heureTravailles = new ArrayCollection();
        $this->controlEleves = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEnseignant(): ?PersonnelActif
    {
        return $this->enseignant;
    }

    public function setEnseignant(?PersonnelActif $enseignant): static
    {
        $this->enseignant = $enseignant;

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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(\DateTimeInterface $start): static
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(\DateTimeInterface $end): static
    {
        $this->end = $end;

        return $this;
    }

    public function isAllDay(): ?bool
    {
        return $this->allDay;
    }

    public function setAllDay(?bool $allDay): static
    {
        $this->allDay = $allDay;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getClassName(): ?array
    {
        return $this->className;
    }

    public function setClassName(?array $className): static
    {
        $this->className = $className;

        return $this;
    }

    public function getBackgroundColor(): ?string
    {
        return $this->backgroundColor;
    }

    public function setBackgroundColor(?string $backgroundColor): static
    {
        $this->backgroundColor = $backgroundColor;

        return $this;
    }

    public function getBorderColor(): ?string
    {
        return $this->borderColor;
    }

    public function setBorderColor(?string $borderColor): static
    {
        $this->borderColor = $borderColor;

        return $this;
    }

    public function getTextColor(): ?string
    {
        return $this->textColor;
    }

    public function setTextColor(?string $textColor): static
    {
        $this->textColor = $textColor;

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

    public function getDateSaisie(): ?\DateTimeInterface
    {
        return $this->dateSaisie;
    }

    public function setDateSaisie(\DateTimeInterface $dateSaisie): static
    {
        $this->dateSaisie = $dateSaisie;

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

    public function getPromo(): ?string
    {
        return $this->promo;
    }

    public function setPromo(string $promo): static
    {
        $this->promo = $promo;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDuree(): ?float
    {
        return $this->duree;
    }

    public function setDuree(float $duree): static
    {
        $this->duree = $duree;

        return $this;
    }

    /**
     * @return Collection<int, HeureTravaille>
     */
    public function getHeureTravailles(): Collection
    {
        return $this->heureTravailles;
    }

    public function addHeureTravaille(HeureTravaille $heureTravaille): static
    {
        if (!$this->heureTravailles->contains($heureTravaille)) {
            $this->heureTravailles->add($heureTravaille);
            $heureTravaille->setEvent($this);
        }

        return $this;
    }

    public function removeHeureTravaille(HeureTravaille $heureTravaille): static
    {
        if ($this->heureTravailles->removeElement($heureTravaille)) {
            // set the owning side to null (unless already changed)
            if ($heureTravaille->getEvent() === $this) {
                $heureTravaille->setEvent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ControlEleve>
     */
    public function getControlEleves(): Collection
    {
        return $this->controlEleves;
    }

    public function addControlElefe(ControlEleve $controlElefe): static
    {
        if (!$this->controlEleves->contains($controlElefe)) {
            $this->controlEleves->add($controlElefe);
            $controlElefe->setEvent($this);
        }

        return $this;
    }

    public function removeControlElefe(ControlEleve $controlElefe): static
    {
        if ($this->controlEleves->removeElement($controlElefe)) {
            // set the owning side to null (unless already changed)
            if ($controlElefe->getEvent() === $this) {
                $controlElefe->setEvent(null);
            }
        }

        return $this;
    }
}
