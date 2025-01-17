<?php

namespace App\Entity;

use App\Repository\EleveRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EleveRepository::class)]
class Eleve extends User
{
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $ecoleOrigine = null;

    /** 
     * @var Collection<int, DocumentEleve>
     */
    #[ORM\OneToMany(targetEntity: DocumentEleve::class, mappedBy: 'eleve', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $documentEleves;

    /**
     * @var Collection<int, Inscription>
     */
    #[ORM\OneToMany(targetEntity: Inscription::class, mappedBy: 'eleve', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $inscriptions;

    /**
     * @var Collection<int, Filiation>
     */
    #[ORM\ManyToMany(targetEntity: Filiation::class, mappedBy: 'eleve', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $filiations;

    /**
     * @var Collection<int, Tuteur>
     */
    #[ORM\ManyToMany(targetEntity: Tuteur::class, mappedBy: 'eleve', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $tuteurs;

    /**
     * @var Collection<int, LienFamilial>
     */
    #[ORM\ManyToMany(targetEntity: LienFamilial::class, mappedBy: 'eleve', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $lienFamilials;

    /**
     * @var Collection<int, InscriptionActivite>
     */
    #[ORM\OneToMany(targetEntity: InscriptionActivite::class, mappedBy: 'eleve', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $inscriptionActivites;



   
    public function __construct()
    {
        parent::__construct();
        $this->documentEleves = new ArrayCollection();
        $this->inscriptions = new ArrayCollection();
        $this->filiations = new ArrayCollection();
        $this->tuteurs = new ArrayCollection();
        $this->lienFamilials = new ArrayCollection();
        $this->inscriptionActivites = new ArrayCollection();
        
    }

    public function getEcoleOrigine(): ?string
    {
        return $this->ecoleOrigine;
    }

    public function setEcoleOrigine(?string $ecoleOrigine): static
    {
        $this->ecoleOrigine = $ecoleOrigine;

        return $this;
    }

    /**
     * @return Collection<int, DocumentEleve>
     */
    public function getDocumentEleves(): Collection
    {
        return $this->documentEleves;
    }

    public function addDocumentEleve(DocumentEleve $documentEleve): static
    {
        if (!$this->documentEleves->contains($documentEleve)) {
            $this->documentEleves->add($documentEleve);
            $documentEleve->setEleve($this);
        }

        return $this;
    }

    public function removeDocumentEleve(DocumentEleve $documentEleve): static
    {
        if ($this->documentEleves->removeElement($documentEleve)) {
            if ($documentEleve->getEleve() === $this) {
                $documentEleve->setEleve(null);
            }
        }

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
            $inscription->setEleve($this);
        }

        return $this;
    }

    public function removeInscription(Inscription $inscription): static
    {
        if ($this->inscriptions->removeElement($inscription)) {
            if ($inscription->getEleve() === $this) {
                $inscription->setEleve(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Filiation>
     */
    public function getFiliations(): Collection
    {
        return $this->filiations;
    }

    public function addFiliation(Filiation $filiation): static
    {
        if (!$this->filiations->contains($filiation)) {
            $this->filiations->add($filiation);
            $filiation->addEleve($this);
        }

        return $this;
    }

    public function removeFiliation(Filiation $filiation): static
    {
        if ($this->filiations->removeElement($filiation)) {
            $filiation->removeEleve($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Tuteur>
     */
    public function getTuteurs(): Collection
    {
        return $this->tuteurs;
    }

    public function addTuteur(Tuteur $tuteur): static
    {
        if (!$this->tuteurs->contains($tuteur)) {
            $this->tuteurs->add($tuteur);
            $tuteur->addEleve($this);
        }

        return $this;
    }

    public function removeTuteur(Tuteur $tuteur): static
    {
        if ($this->tuteurs->removeElement($tuteur)) {
            $tuteur->removeEleve($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, LienFamilial>
     */
    public function getLienFamilials(): Collection
    {
        return $this->lienFamilials;
    }

    public function addLienFamilial(LienFamilial $lienFamilial): static
    {
        if (!$this->lienFamilials->contains($lienFamilial)) {
            $this->lienFamilials->add($lienFamilial);
            $lienFamilial->addEleve($this);
        }

        return $this;
    }

    public function removeLienFamilial(LienFamilial $lienFamilial): static
    {
        if ($this->lienFamilials->removeElement($lienFamilial)) {
            $lienFamilial->removeEleve($this);
        }

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
            $inscriptionActivite->setEleve($this);
        }

        return $this;
    }

    public function removeInscriptionActivite(InscriptionActivite $inscriptionActivite): static
    {
        if ($this->inscriptionActivites->removeElement($inscriptionActivite)) {
            // set the owning side to null (unless already changed)
            if ($inscriptionActivite->getEleve() === $this) {
                $inscriptionActivite->setEleve(null);
            }
        }

        return $this;
    }    
    
}
