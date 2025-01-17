<?php

namespace App\Entity;

use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\EnseignantRepository;

#[ORM\Entity(repositoryClass: EnseignantRepository::class)]
class Enseignant extends User
{
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $numeroCompte = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $agenceBanque = null;

    /**
     * @var Collection<int, DocumentEnseignant>
     */
    #[ORM\OneToMany(targetEntity: DocumentEnseignant::class, mappedBy: 'enseignant', orphanRemoval:true, cascade:['remove'])]
    private Collection $documentEnseignants;

    // /**
    //  * @var Collection<int, Matiere>
    //  */
    // #[ORM\ManyToMany(targetEntity: Matiere::class, inversedBy: 'enseignants')]
    // private Collection $matiere;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $niveau = null;

    public function __construct()
    {
        parent::__construct();
        $this->documentEnseignants = new ArrayCollection();
        // $this->matiere = new ArrayCollection();
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeInterface $dateFin): static
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getNumeroCompte(): ?string
    {
        return $this->numeroCompte;
    }

    public function setNumeroCompte(?string $numeroCompte): static
    {
        $this->numeroCompte = $numeroCompte;

        return $this;
    }

    public function getAgenceBanque(): ?string
    {
        return $this->agenceBanque;
    }

    public function setAgenceBanque(?string $agenceBanque): static
    {
        $this->agenceBanque = $agenceBanque;

        return $this;
    }

    /**
     * @return Collection<int, DocumentEnseignant>
     */
    public function getDocumentEnseignants(): Collection
    {
        return $this->documentEnseignants;
    }

    public function addDocumentEnseignant(DocumentEnseignant $documentEnseignant): static
    {
        if (!$this->documentEnseignants->contains($documentEnseignant)) {
            $this->documentEnseignants->add($documentEnseignant);
            $documentEnseignant->setEnseignant($this);
        }

        return $this;
    }

    public function removeDocumentEnseignant(DocumentEnseignant $documentEnseignant): static
    {
        if ($this->documentEnseignants->removeElement($documentEnseignant)) {
            // set the owning side to null (unless already changed)
            if ($documentEnseignant->getEnseignant() === $this) {
                $documentEnseignant->setEnseignant(null);
            }
        }

        return $this;
    }

    // /**
    //  * @return Collection<int, Matiere>
    //  */
    // public function getMatiere(): Collection
    // {
    //     return $this->matiere;
    // }

    // public function addMatiere(Matiere $matiere): static
    // {
    //     if (!$this->matiere->contains($matiere)) {
    //         $this->matiere->add($matiere);
    //     }

    //     return $this;
    // }

    // public function removeMatiere(Matiere $matiere): static
    // {
    //     $this->matiere->removeElement($matiere);

    //     return $this;
    // }

    public function getNiveau(): ?string
    {
        return $this->niveau;
    }

    public function setNiveau(?string $niveau): static
    {
        $this->niveau = $niveau;

        return $this;
    }
}
