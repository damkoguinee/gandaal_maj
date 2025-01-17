<?php

namespace App\Entity;

use App\Repository\TarifActiviteScolaireRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TarifActiviteScolaireRepository::class)]
class TarifActiviteScolaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'tarifActiviteScolaires')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ConfigActiviteScolaire $activite = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 13, scale: 2, nullable: true)]
    private ?string $montant = null;

    #[ORM\Column(length: 50)]
    private ?string $type = null;

    #[ORM\Column(length: 10)]
    private ?string $promo = null;

    /**
     * @var Collection<int, InscriptionActivite>
     */
    #[ORM\OneToMany(targetEntity: InscriptionActivite::class, mappedBy: 'tarifActivite', orphanRemoval:true, cascade:['persist', 'remove'])]
    private Collection $inscriptionActivites;

   

    public function __construct()
    {
        $this->inscriptionActivites = new ArrayCollection();
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getActivite(): ?ConfigActiviteScolaire
    {
        return $this->activite;
    }

    public function setActivite(?ConfigActiviteScolaire $activite): static
    {
        $this->activite = $activite;

        return $this;
    }

    public function getMontant(): ?string
    {
        return $this->montant;
    }

    public function setMontant(?string $montant): static
    {
        $this->montant = $montant;

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
     * @return Collection<int, InscriptionActivite>
     */
    public function getInscriptionActivites(): Collection
    {
        return $this->inscriptionActivites;
    }

    public function addInscriptionActivite(InscriptionActivite $inscriptionActivite): static
    {
        if (!$this->inscriptionActivites->contains($inscriptionActivite)) {
            $this->inscriptionActivites->add($inscriptionActivite);
            $inscriptionActivite->setTarifActivite($this);
        }

        return $this;
    }

    public function removeInscriptionActivite(InscriptionActivite $inscriptionActivite): static
    {
        if ($this->inscriptionActivites->removeElement($inscriptionActivite)) {
            // set the owning side to null (unless already changed)
            if ($inscriptionActivite->getTarifActivite() === $this) {
                $inscriptionActivite->setTarifActivite(null);
            }
        }

        return $this;
    }

    
}
