<?php

namespace App\Entity;

use App\Repository\EtablissementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use PSpell\Config;

#[ORM\Entity(repositoryClass: EtablissementRepository::class)]
class Etablissement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 50)]
    private ?string $telephone = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column(length: 100)]
    private ?string $pays = null;

    #[ORM\Column(length: 100)]
    private ?string $region = null;

    #[ORM\Column(length: 100)]
    private ?string $secteur = null;

    #[ORM\Column(length: 100)]
    private ?string $ire = null;

    #[ORM\Column(length: 100)]
    private ?string $dpe = null;

    #[ORM\Column(length: 5)]
    private ?string $initial = null;

    #[ORM\Column(length: 100)]
    private ?string $devise = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $agrement = null;

    #[ORM\ManyToOne(inversedBy: 'etablissements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entreprise $entreprise = null;

    /**
     * @var Collection<int, Cursus>
     */
    #[ORM\OneToMany(targetEntity: Cursus::class, mappedBy: 'etablissement')]
    private Collection $cursuses;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(length: 50)]
    private ?string $lieu = null;

    /**
     * @var Collection<int, TranchePaiement>
     */
    #[ORM\OneToMany(targetEntity: TranchePaiement::class, mappedBy: 'etablissement')]
    private Collection $tranchePaiements;

    /**
     * @var Collection<int, FraisInscription>
     */
    #[ORM\OneToMany(targetEntity: FraisInscription::class, mappedBy: 'etablissement')]
    private Collection $fraisInscriptions;

    /**
     * @var Collection<int, FraisScolarite>
     */
    #[ORM\OneToMany(targetEntity: FraisScolarite::class, mappedBy: 'etablissement')]
    private Collection $fraisScolarites;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'etablissement')]
    private Collection $users;

    /**
     * @var Collection<int, Inscription>
     */
    #[ORM\OneToMany(targetEntity: Inscription::class, mappedBy: 'etablissement')]
    private Collection $inscriptions;

    /**
     * @var Collection<int, Preinscription>
     */
    #[ORM\OneToMany(targetEntity: Preinscription::class, mappedBy: 'etablissement')]
    private Collection $preinscriptions;

    /**
     * @var Collection<int, Caisse>
     */
    #[ORM\OneToMany(targetEntity: ConfigCaisse::class, mappedBy: 'etablissement')]
    private Collection $caisses;

    /**
     * @var Collection<int, PaiementEleve>
     */
    #[ORM\OneToMany(targetEntity: PaiementEleve::class, mappedBy: 'etablissement')]
    private Collection $paiementEleves;

    /**
     * @var Collection<int, MouvementCaisse>
     */
    #[ORM\OneToMany(targetEntity: MouvementCaisse::class, mappedBy: 'etablissement')]
    private Collection $mouvementCaisses;

    /**
     * @var Collection<int, MouvementCollaborateur>
     */
    #[ORM\OneToMany(targetEntity: MouvementCollaborateur::class, mappedBy: 'etablissement')]
    private Collection $mouvementCollaborateurs;

    /**
     * @var Collection<int, ConfigActiviteScolaire>
     */
    #[ORM\OneToMany(targetEntity: ConfigActiviteScolaire::class, mappedBy: 'etablissement')]
    private Collection $configActiviteScolaires;

    /**
     * @var Collection<int, InscriptionActivite>
     */
    #[ORM\OneToMany(targetEntity: InscriptionActivite::class, mappedBy: 'etablissement')]
    private Collection $inscriptionActivites;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'etablissement')]
    private Collection $events;

    /**
     * @var Collection<int, HeureTravaille>
     */
    #[ORM\OneToMany(targetEntity: HeureTravaille::class, mappedBy: 'etablissement')]
    private Collection $heureTravailles;

    /**
     * @var Collection<int, ControlEleve>
     */
    #[ORM\OneToMany(targetEntity: ControlEleve::class, mappedBy: 'etablissement')]
    private Collection $controlEleves;

    /**
     * @var Collection<int, FonctionnementScolaire>
     */
    #[ORM\OneToMany(targetEntity: FonctionnementScolaire::class, mappedBy: 'etablissement')]
    private Collection $fonctionnementScolaires;

    /**
     * @var Collection<int, ConfigurationLogiciel>
     */
    #[ORM\OneToMany(targetEntity: ConfigurationLogiciel::class, mappedBy: 'etablissement')]
    private Collection $configurationLogiciels;

    /**
     * @var Collection<int, ConfigurationModule>
     */
    #[ORM\OneToMany(targetEntity: ConfigurationModule::class, mappedBy: 'etablissement')]
    private Collection $configurationModules;

    // /**
    //  * @var Collection<int, PaiementSalairePersonnel>
    //  */
    // #[ORM\OneToMany(targetEntity: PaiementSalairePersonnel::class, mappedBy: 'etablissement')]
    // private Collection $paiementSalairePersonnels;

    // /**
    //  * @var Collection<int, PaiementEleve>
    //  */
    // #[ORM\OneToMany(targetEntity: PaiementEleve::class, mappedBy: 'etablissement')]
    // private Collection $paiementEleves;

    


    public function __construct()
    {
        $this->cursuses = new ArrayCollection();
        $this->tranchePaiements = new ArrayCollection();
        $this->fraisInscriptions = new ArrayCollection();
        $this->fraisScolarites = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->inscriptions = new ArrayCollection();
        $this->preinscriptions = new ArrayCollection();
        $this->caisses = new ArrayCollection();
        // $this->paiementEleves = new ArrayCollection();
        $this->mouvementCaisses = new ArrayCollection();
        $this->mouvementCollaborateurs = new ArrayCollection();
        $this->configActiviteScolaires = new ArrayCollection();
        $this->inscriptionActivites = new ArrayCollection();
        $this->events = new ArrayCollection();
        // $this->paiementSalairePersonnels = new ArrayCollection();
        $this->heureTravailles = new ArrayCollection();
        $this->controlEleves = new ArrayCollection();
        $this->fonctionnementScolaires = new ArrayCollection();
        $this->configurationLogiciels = new ArrayCollection();
        $this->configurationModules = new ArrayCollection();
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

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(string $pays): static
    {
        $this->pays = $pays;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(string $region): static
    {
        $this->region = $region;

        return $this;
    }

    public function getSecteur(): ?string
    {
        return $this->secteur;
    }

    public function setSecteur(string $secteur): static
    {
        $this->secteur = $secteur;

        return $this;
    }

    public function getIre(): ?string
    {
        return $this->ire;
    }

    public function setIre(string $ire): static
    {
        $this->ire = $ire;

        return $this;
    }

    public function getDpe(): ?string
    {
        return $this->dpe;
    }

    public function setDpe(string $dpe): static
    {
        $this->dpe = $dpe;

        return $this;
    }

    public function getInitial(): ?string
    {
        return $this->initial;
    }

    public function setInitial(string $initial): static
    {
        $this->initial = $initial;

        return $this;
    }

    public function getDevise(): ?string
    {
        return $this->devise;
    }

    public function setDevise(string $devise): static
    {
        $this->devise = $devise;

        return $this;
    }

    public function getAgrement(): ?string
    {
        return $this->agrement;
    }

    public function setAgrement(?string $agrement): static
    {
        $this->agrement = $agrement;

        return $this;
    }

    public function getEntreprise(): ?Entreprise
    {
        return $this->entreprise;
    }

    public function setEntreprise(?Entreprise $entreprise): static
    {
        $this->entreprise = $entreprise;

        return $this;
    }

    /**
     * @return Collection<int, Cursus>
     */
    public function getCursuses(): Collection
    {
        return $this->cursuses;
    }

    public function addCursus(Cursus $cursus): static
    {
        if (!$this->cursuses->contains($cursus)) {
            $this->cursuses->add($cursus);
            $cursus->setEtablissement($this);
        }

        return $this;
    }

    public function removeCursus(Cursus $cursus): static
    {
        if ($this->cursuses->removeElement($cursus)) {
            // set the owning side to null (unless already changed)
            if ($cursus->getEtablissement() === $this) {
                $cursus->setEtablissement(null);
            }
        }

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(string $lieu): static
    {
        $this->lieu = $lieu;

        return $this;
    }

    /**
     * @return Collection<int, TranchePaiement>
     */
    public function getTranchePaiements(): Collection
    {
        return $this->tranchePaiements;
    }

    public function addTranchePaiement(TranchePaiement $tranchePaiement): static
    {
        if (!$this->tranchePaiements->contains($tranchePaiement)) {
            $this->tranchePaiements->add($tranchePaiement);
            $tranchePaiement->setEtablissement($this);
        }

        return $this;
    }

    public function removeTranchePaiement(TranchePaiement $tranchePaiement): static
    {
        if ($this->tranchePaiements->removeElement($tranchePaiement)) {
            // set the owning side to null (unless already changed)
            if ($tranchePaiement->getEtablissement() === $this) {
                $tranchePaiement->setEtablissement(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FraisInscription>
     */
    public function getFraisInscriptions(): Collection
    {
        return $this->fraisInscriptions;
    }

    public function addFraisInscription(FraisInscription $fraisInscription): static
    {
        if (!$this->fraisInscriptions->contains($fraisInscription)) {
            $this->fraisInscriptions->add($fraisInscription);
            $fraisInscription->setEtablissement($this);
        }

        return $this;
    }

    public function removeFraisInscription(FraisInscription $fraisInscription): static
    {
        if ($this->fraisInscriptions->removeElement($fraisInscription)) {
            // set the owning side to null (unless already changed)
            if ($fraisInscription->getEtablissement() === $this) {
                $fraisInscription->setEtablissement(null);
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
            $fraisScolarite->setEtablissement($this);
        }

        return $this;
    }

    public function removeFraisScolarite(FraisScolarite $fraisScolarite): static
    {
        if ($this->fraisScolarites->removeElement($fraisScolarite)) {
            // set the owning side to null (unless already changed)
            if ($fraisScolarite->getEtablissement() === $this) {
                $fraisScolarite->setEtablissement(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setEtablissement($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getEtablissement() === $this) {
                $user->setEtablissement(null);
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
            $inscription->setEtablissement($this);
        }

        return $this;
    }

    public function removeInscription(Inscription $inscription): static
    {
        if ($this->inscriptions->removeElement($inscription)) {
            // set the owning side to null (unless already changed)
            if ($inscription->getEtablissement() === $this) {
                $inscription->setEtablissement(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Preinscription>
     */
    public function getPreinscriptions(): Collection
    {
        return $this->preinscriptions;
    }

    public function addPreinscription(Preinscription $preinscription): static
    {
        if (!$this->preinscriptions->contains($preinscription)) {
            $this->preinscriptions->add($preinscription);
            $preinscription->setEtablissement($this);
        }

        return $this;
    }

    public function removePreinscription(Preinscription $preinscription): static
    {
        if ($this->preinscriptions->removeElement($preinscription)) {
            // set the owning side to null (unless already changed)
            if ($preinscription->getEtablissement() === $this) {
                $preinscription->setEtablissement(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Caisse>
     */
    public function getCaisses(): Collection
    {
        return $this->caisses;
    }

    public function addCaiss(ConfigCaisse $caiss): static
    {
        if (!$this->caisses->contains($caiss)) {
            $this->caisses->add($caiss);
            $caiss->setEtablissement($this);
        }

        return $this;
    }

    public function removeCaiss(ConfigCaisse $caiss): static
    {
        if ($this->caisses->removeElement($caiss)) {
            // set the owning side to null (unless already changed)
            if ($caiss->getEtablissement() === $this) {
                $caiss->setEtablissement(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PaiementEleve>
     */
    public function getPaiementEleves(): Collection
    {
        return $this->paiementEleves;
    }

    // public function addPaiementElefe(PaiementEleve $paiementElefe): static
    // {
    //     if (!$this->paiementEleves->contains($paiementElefe)) {
    //         $this->paiementEleves->add($paiementElefe);
    //         $paiementElefe->setEtablissement($this);
    //     }

    //     return $this;
    // }

    // public function removePaiementElefe(PaiementEleve $paiementElefe): static
    // {
    //     if ($this->paiementEleves->removeElement($paiementElefe)) {
    //         // set the owning side to null (unless already changed)
    //         if ($paiementElefe->getEtablissement() === $this) {
    //             $paiementElefe->setEtablissement(null);
    //         }
    //     }

    //     return $this;
    // }

    public function addPaiementElefe(PaiementEleve $paiementElefe): static
    {
        if (!$this->paiementEleves->contains($paiementElefe)) {
            $this->paiementEleves->add($paiementElefe);
            $paiementElefe->setEtablissement($this);
        }

        return $this;
    }

    public function removePaiementElefe(PaiementEleve $paiementElefe): static
    {
        if ($this->paiementEleves->removeElement($paiementElefe)) {
            // set the owning side to null (unless already changed)
            if ($paiementElefe->getEtablissement() === $this) {
                $paiementElefe->setEtablissement(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MouvementCaisse>
     */
    public function getMouvementCaisses(): Collection
    {
        return $this->mouvementCaisses;
    }

    public function addMouvementCaiss(MouvementCaisse $mouvementCaiss): static
    {
        if (!$this->mouvementCaisses->contains($mouvementCaiss)) {
            $this->mouvementCaisses->add($mouvementCaiss);
            $mouvementCaiss->setEtablissement($this);
        }

        return $this;
    }

    public function removeMouvementCaiss(MouvementCaisse $mouvementCaiss): static
    {
        if ($this->mouvementCaisses->removeElement($mouvementCaiss)) {
            // set the owning side to null (unless already changed)
            if ($mouvementCaiss->getEtablissement() === $this) {
                $mouvementCaiss->setEtablissement(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MouvementCollaborateur>
     */
    public function getMouvementCollaborateurs(): Collection
    {
        return $this->mouvementCollaborateurs;
    }

    public function addMouvementCollaborateur(MouvementCollaborateur $mouvementCollaborateur): static
    {
        if (!$this->mouvementCollaborateurs->contains($mouvementCollaborateur)) {
            $this->mouvementCollaborateurs->add($mouvementCollaborateur);
            $mouvementCollaborateur->setEtablissement($this);
        }

        return $this;
    }

    public function removeMouvementCollaborateur(MouvementCollaborateur $mouvementCollaborateur): static
    {
        if ($this->mouvementCollaborateurs->removeElement($mouvementCollaborateur)) {
            // set the owning side to null (unless already changed)
            if ($mouvementCollaborateur->getEtablissement() === $this) {
                $mouvementCollaborateur->setEtablissement(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ConfigActiviteScolaire>
     */
    public function getConfigActiviteScolaires(): Collection
    {
        return $this->configActiviteScolaires;
    }

    public function addConfigActiviteScolaire(ConfigActiviteScolaire $configActiviteScolaire): static
    {
        if (!$this->configActiviteScolaires->contains($configActiviteScolaire)) {
            $this->configActiviteScolaires->add($configActiviteScolaire);
            $configActiviteScolaire->setEtablissement($this);
        }

        return $this;
    }

    public function removeConfigActiviteScolaire(ConfigActiviteScolaire $configActiviteScolaire): static
    {
        if ($this->configActiviteScolaires->removeElement($configActiviteScolaire)) {
            // set the owning side to null (unless already changed)
            if ($configActiviteScolaire->getEtablissement() === $this) {
                $configActiviteScolaire->setEtablissement(null);
            }
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
            $inscriptionActivite->setEtablissement($this);
        }

        return $this;
    }

    public function removeInscriptionActivite(InscriptionActivite $inscriptionActivite): static
    {
        if ($this->inscriptionActivites->removeElement($inscriptionActivite)) {
            // set the owning side to null (unless already changed)
            if ($inscriptionActivite->getEtablissement() === $this) {
                $inscriptionActivite->setEtablissement(null);
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
            $event->setEtablissement($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getEtablissement() === $this) {
                $event->setEtablissement(null);
            }
        }

        return $this;
    }

    // /**
    //  * @return Collection<int, PaiementSalairePersonnel>
    //  */
    // public function getPaiementSalairePersonnels(): Collection
    // {
    //     return $this->paiementSalairePersonnels;
    // }

    // public function addPaiementSalairePersonnel(PaiementSalairePersonnel $paiementSalairePersonnel): static
    // {
    //     if (!$this->paiementSalairePersonnels->contains($paiementSalairePersonnel)) {
    //         $this->paiementSalairePersonnels->add($paiementSalairePersonnel);
    //         $paiementSalairePersonnel->setEtablissement($this);
    //     }

    //     return $this;
    // }

    // public function removePaiementSalairePersonnel(PaiementSalairePersonnel $paiementSalairePersonnel): static
    // {
    //     if ($this->paiementSalairePersonnels->removeElement($paiementSalairePersonnel)) {
    //         // set the owning side to null (unless already changed)
    //         if ($paiementSalairePersonnel->getEtablissement() === $this) {
    //             $paiementSalairePersonnel->setEtablissement(null);
    //         }
    //     }

    //     return $this;
    // }

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
            $heureTravaille->setEtablissement($this);
        }

        return $this;
    }

    public function removeHeureTravaille(HeureTravaille $heureTravaille): static
    {
        if ($this->heureTravailles->removeElement($heureTravaille)) {
            // set the owning side to null (unless already changed)
            if ($heureTravaille->getEtablissement() === $this) {
                $heureTravaille->setEtablissement(null);
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
            $controlElefe->setEtablissement($this);
        }

        return $this;
    }

    public function removeControlElefe(ControlEleve $controlElefe): static
    {
        if ($this->controlEleves->removeElement($controlElefe)) {
            // set the owning side to null (unless already changed)
            if ($controlElefe->getEtablissement() === $this) {
                $controlElefe->setEtablissement(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FonctionnementScolaire>
     */
    public function getFonctionnementScolaires(): Collection
    {
        return $this->fonctionnementScolaires;
    }

    public function addFonctionnementScolaire(FonctionnementScolaire $fonctionnementScolaire): static
    {
        if (!$this->fonctionnementScolaires->contains($fonctionnementScolaire)) {
            $this->fonctionnementScolaires->add($fonctionnementScolaire);
            $fonctionnementScolaire->setEtablissement($this);
        }

        return $this;
    }

    public function removeFonctionnementScolaire(FonctionnementScolaire $fonctionnementScolaire): static
    {
        if ($this->fonctionnementScolaires->removeElement($fonctionnementScolaire)) {
            // set the owning side to null (unless already changed)
            if ($fonctionnementScolaire->getEtablissement() === $this) {
                $fonctionnementScolaire->setEtablissement(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ConfigurationLogiciel>
     */
    public function getConfigurationLogiciels(): Collection
    {
        return $this->configurationLogiciels;
    }

    public function addConfigurationLogiciel(ConfigurationLogiciel $configurationLogiciel): static
    {
        if (!$this->configurationLogiciels->contains($configurationLogiciel)) {
            $this->configurationLogiciels->add($configurationLogiciel);
            $configurationLogiciel->setEtablissement($this);
        }

        return $this;
    }

    public function removeConfigurationLogiciel(ConfigurationLogiciel $configurationLogiciel): static
    {
        if ($this->configurationLogiciels->removeElement($configurationLogiciel)) {
            // set the owning side to null (unless already changed)
            if ($configurationLogiciel->getEtablissement() === $this) {
                $configurationLogiciel->setEtablissement(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ConfigurationModule>
     */
    public function getConfigurationModules(): Collection
    {
        return $this->configurationModules;
    }

    public function addConfigurationModule(ConfigurationModule $configurationModule): static
    {
        if (!$this->configurationModules->contains($configurationModule)) {
            $this->configurationModules->add($configurationModule);
            $configurationModule->setEtablissement($this);
        }

        return $this;
    }

    public function removeConfigurationModule(ConfigurationModule $configurationModule): static
    {
        if ($this->configurationModules->removeElement($configurationModule)) {
            // set the owning side to null (unless already changed)
            if ($configurationModule->getEtablissement() === $this) {
                $configurationModule->setEtablissement(null);
            }
        }

        return $this;
    }

    

    
}
