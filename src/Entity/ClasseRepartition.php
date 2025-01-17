<?php

namespace App\Entity;

use App\Repository\ClasseRepartitionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClasseRepartitionRepository::class)]
class ClasseRepartition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column(length: 4)]
    private ?string $promo = null;

    #[ORM\ManyToOne(inversedBy: 'classeRepartitions')]
    private ?User $responsable = null;

    #[ORM\ManyToOne(inversedBy: 'classeRepartitions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Formation $formation = null;

    /**
     * @var Collection<int, Inscription>
     */
    #[ORM\OneToMany(targetEntity: Inscription::class, mappedBy: 'classe')]
    private Collection $inscriptions;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'classe')]
    private Collection $events;

    /**
     * @var Collection<int, DevoirEleve>
     */
    #[ORM\OneToMany(targetEntity: DevoirEleve::class, mappedBy: 'classe')]
    private Collection $devoirEleves;

    public function __construct()
    {
        $this->inscriptions = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->devoirEleves = new ArrayCollection();
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

    public function getPromo(): ?string
    {
        return $this->promo;
    }

    public function setPromo(string $promo): static
    {
        $this->promo = $promo;

        return $this;
    }

    public function getResponsable(): ?User
    {
        return $this->responsable;
    }

    public function setResponsable(?User $responsable): static
    {
        $this->responsable = $responsable;

        return $this;
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

    /**
     * @return Collection<int, Inscription>
     */
    public function getInscriptions(): Collection
    {
        return $this->inscriptions;
    }

    public function addInscription(Inscription $inscription): static
    {
        if (!$this->inscriptions->contains($inscription)) {
            $this->inscriptions->add($inscription);
            $inscription->setClasse($this);
        }

        return $this;
    }

    public function removeInscription(Inscription $inscription): static
    {
        if ($this->inscriptions->removeElement($inscription)) {
            // set the owning side to null (unless already changed)
            if ($inscription->getClasse() === $this) {
                $inscription->setClasse(null);
            }
        }

        return $this;
    }

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
            $event->setClasse($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getClasse() === $this) {
                $event->setClasse(null);
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
            $devoirElefe->setClasse($this);
        }

        return $this;
    }

    public function removeDevoirElefe(DevoirEleve $devoirElefe): static
    {
        if ($this->devoirEleves->removeElement($devoirElefe)) {
            // set the owning side to null (unless already changed)
            if ($devoirElefe->getClasse() === $this) {
                $devoirElefe->setClasse(null);
            }
        }

        return $this;
    }

}
