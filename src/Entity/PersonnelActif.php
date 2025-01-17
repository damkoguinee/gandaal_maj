<?php

namespace App\Entity;

use App\Repository\PersonnelActifRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PersonnelActifRepository::class)]
class PersonnelActif
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'personnelActifs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $personnel = null;

    #[ORM\Column(length: 100)]
    private ?string $type = null;

    #[ORM\Column(length: 10)]
    private ?string $promo = null;

    /**
     * @var Collection<int, PrimePersonnel>
     */
    #[ORM\OneToMany(targetEntity: PrimePersonnel::class, mappedBy: 'personnel', orphanRemoval:true, cascade:['remove'])]
    private Collection $primePersonnels;

    /**
     * @var Collection<int, AvanceSalairePersonnel>
     */
    #[ORM\OneToMany(targetEntity: AvanceSalairePersonnel::class, mappedBy: 'personnelActif', orphanRemoval:true, cascade:['remove'])]
    private Collection $avanceSalairePersonnels;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'enseignant', orphanRemoval:true, cascade:['remove'])]
    private Collection $events;

    /**
     * @var Collection<int, PaiementSalairePersonnel>
     */
    #[ORM\OneToMany(targetEntity: PaiementSalairePersonnel::class, mappedBy: 'personnelActif', orphanRemoval:true, cascade:['remove'])]
    private Collection $paiementSalairePersonnels;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $rattachement = null;

    /**
     * @var Collection<int, Cursus>
     */
    #[ORM\ManyToMany(targetEntity: Cursus::class, inversedBy: 'personnelActifs')]
    private Collection $rattachementPedagogie;

     

    public function __construct()
    {
        $this->primePersonnels = new ArrayCollection();
        $this->avanceSalairePersonnels = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->paiementSalairePersonnels = new ArrayCollection();
        $this->rattachementPedagogie = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPersonnel(): ?User
    {
        return $this->personnel;
    }

    public function setPersonnel(?User $personnel): static
    {
        $this->personnel = $personnel;

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

    public function getPromo(): ?string
    {
        return $this->promo;
    }

    public function setPromo(string $promo): static
    {
        $this->promo = $promo;

        return $this;
    }

    /**
     * @return Collection<int, PrimePersonnel>
     */
    public function getPrimePersonnels(): Collection
    {
        return $this->primePersonnels;
    }

    public function addPrimePersonnel(PrimePersonnel $primePersonnel): static
    {
        if (!$this->primePersonnels->contains($primePersonnel)) {
            $this->primePersonnels->add($primePersonnel);
            $primePersonnel->setPersonnel($this);
        }

        return $this;
    }

    public function removePrimePersonnel(PrimePersonnel $primePersonnel): static
    {
        if ($this->primePersonnels->removeElement($primePersonnel)) {
            // set the owning side to null (unless already changed)
            if ($primePersonnel->getPersonnel() === $this) {
                $primePersonnel->setPersonnel(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AvanceSalairePersonnel>
     */
    public function getAvanceSalairePersonnels(): Collection
    {
        return $this->avanceSalairePersonnels;
    }

    public function addAvanceSalairePersonnel(AvanceSalairePersonnel $avanceSalairePersonnel): static
    {
        if (!$this->avanceSalairePersonnels->contains($avanceSalairePersonnel)) {
            $this->avanceSalairePersonnels->add($avanceSalairePersonnel);
            $avanceSalairePersonnel->setPersonnelActif($this);
        }

        return $this;
    }

    public function removeAvanceSalairePersonnel(AvanceSalairePersonnel $avanceSalairePersonnel): static
    {
        if ($this->avanceSalairePersonnels->removeElement($avanceSalairePersonnel)) {
            // set the owning side to null (unless already changed)
            if ($avanceSalairePersonnel->getPersonnelActif() === $this) {
                $avanceSalairePersonnel->setPersonnelActif(null);
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
            $event->setEnseignant($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getEnseignant() === $this) {
                $event->setEnseignant(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PaiementSalairePersonnel>
     */
    public function getPaiementSalairePersonnels(): Collection
    {
        return $this->paiementSalairePersonnels;
    }

    public function addPaiementSalairePersonnel(PaiementSalairePersonnel $paiementSalairePersonnel): static
    {
        if (!$this->paiementSalairePersonnels->contains($paiementSalairePersonnel)) {
            $this->paiementSalairePersonnels->add($paiementSalairePersonnel);
            $paiementSalairePersonnel->setPersonnelActif($this);
        }

        return $this;
    }

    public function removePaiementSalairePersonnel(PaiementSalairePersonnel $paiementSalairePersonnel): static
    {
        if ($this->paiementSalairePersonnels->removeElement($paiementSalairePersonnel)) {
            // set the owning side to null (unless already changed)
            if ($paiementSalairePersonnel->getPersonnelActif() === $this) {
                $paiementSalairePersonnel->setPersonnelActif(null);
            }
        }

        return $this;
    }

    public function getRattachement(): ?string
    {
        return $this->rattachement;
    }

    public function setRattachement(?string $rattachement): static
    {
        $this->rattachement = $rattachement;

        return $this;
    }

    /**
     * @return Collection<int, Cursus>
     */
    public function getRattachementPedagogie(): Collection
    {
        return $this->rattachementPedagogie;
    }

    public function addRattachementPedagogie(Cursus $rattachementPedagogie): static
    {
        if (!$this->rattachementPedagogie->contains($rattachementPedagogie)) {
            $this->rattachementPedagogie->add($rattachementPedagogie);
        }

        return $this;
    }

    public function removeRattachementPedagogie(Cursus $rattachementPedagogie): static
    {
        $this->rattachementPedagogie->removeElement($rattachementPedagogie);

        return $this;
    }


}
