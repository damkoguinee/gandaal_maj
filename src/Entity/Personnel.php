<?php

namespace App\Entity;

use App\Repository\PersonnelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PersonnelRepository::class)]
class Personnel extends User
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
     * @var Collection<int, DocumentPersonnel>
     */
    #[ORM\OneToMany(targetEntity: DocumentPersonnel::class, mappedBy: 'personnel', orphanRemoval:true, cascade:['remove'])]
    private Collection $documentPersonnels;

    #[ORM\ManyToOne(inversedBy: 'personnels')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ConfigFonction $fonction = null;

    /**
     * @var Collection<int, Inscription>
     */
    #[ORM\OneToMany(targetEntity: Inscription::class, mappedBy: 'saisiePar')]
    private Collection $inscriptions;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $signature = null;

    
  

    public function __construct()
    {
        parent::__construct();
        $this->documentPersonnels = new ArrayCollection();
        $this->inscriptions = new ArrayCollection();
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
     * @return Collection<int, DocumentPersonnel>
     */
    public function getDocumentPersonnels(): Collection
    {
        return $this->documentPersonnels;
    }

    public function addDocumentPersonnel(DocumentPersonnel $documentPersonnel): static
    {
        if (!$this->documentPersonnels->contains($documentPersonnel)) {
            $this->documentPersonnels->add($documentPersonnel);
            $documentPersonnel->setPersonnel($this);
        }

        return $this;
    }

    public function removeDocumentPersonnel(DocumentPersonnel $documentPersonnel): static
    {
        if ($this->documentPersonnels->removeElement($documentPersonnel)) {
            // set the owning side to null (unless already changed)
            if ($documentPersonnel->getPersonnel() === $this) {
                $documentPersonnel->setPersonnel(null);
            }
        }

        return $this;
    }

    public function getFonction(): ?ConfigFonction
    {
        return $this->fonction;
    }

    public function setFonction(?ConfigFonction $fonction): static
    {
        $this->fonction = $fonction;

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
            $inscription->setSaisiePar($this);
        }

        return $this;
    }

    public function removeInscription(Inscription $inscription): static
    {
        if ($this->inscriptions->removeElement($inscription)) {
            // set the owning side to null (unless already changed)
            if ($inscription->getSaisiePar() === $this) {
                $inscription->setSaisiePar(null);
            }
        }

        return $this;
    }

    public function getSignature(): ?string
    {
        return $this->signature;
    }

    public function setSignature(?string $signature): static
    {
        $this->signature = $signature;

        return $this;
    }


}
