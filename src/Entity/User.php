<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
#[ORM\InheritanceType("JOINED")]
#[ORM\DiscriminatorColumn(name:"type", type:"string")]
#[ORM\DiscriminatorMap([
    "eleve" => Eleve::class,
    "filiation" => Filiation::class,
    "tuteur" => Tuteur::class,
    'enseignant' => Enseignant::class,
    'personnel' => Personnel::class,
    'developpeur' => Developpeur::class
])]
abstract class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $username = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * @var Collection<int, ClasseRepartition>
     */
    #[ORM\OneToMany(targetEntity: ClasseRepartition::class, mappedBy: 'responsable')]
    private Collection $classeRepartitions;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column(length: 150)]
    private ?string $prenom = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(length: 150)]
    private ?string $ville = null;

    #[ORM\Column(length: 150)]
    private ?string $pays = null;

    #[ORM\Column(length: 20)]
    private ?string $matricule = null;

    

    #[ORM\Column(length: 10)]
    private ?string $sexe = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateNaissance = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $lieuNaissance = null;

    #[ORM\Column(length: 10)]
    private ?string $statut = null;

    /**
     * @var Collection<int, Salaire>
     */
    #[ORM\OneToMany(targetEntity: Salaire::class, mappedBy: 'user', orphanRemoval:true, cascade:['remove'])]
    private Collection $salaires;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(length: 100)]
    private ?string $typeUser = null;

    /**
     * @var Collection<int, PersonnelActif>
     */
    #[ORM\OneToMany(targetEntity: PersonnelActif::class, mappedBy: 'personnel')]
    private Collection $personnelActifs;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Etablissement $etablissement = null;

    /**
     * @var Collection<int, PaiementEleve>
     */
    #[ORM\OneToMany(targetEntity: PaiementEleve::class, mappedBy: 'saisiePar')]
    private Collection $paiementEleves;

    /**
     * @var Collection<int, MouvementCaisse>
     */
    #[ORM\OneToMany(targetEntity: MouvementCaisse::class, mappedBy: 'saisiePar')]
    private Collection $mouvementCaisses;

    /**
     * @var Collection<int, Versement>
     */
    #[ORM\OneToMany(targetEntity: Versement::class, mappedBy: 'collaborateur')]
    private Collection $versements;

    /**
     * @var Collection<int, Decaissement>
     */
    #[ORM\OneToMany(targetEntity: Decaissement::class, mappedBy: 'collaborateur')]
    private Collection $decaissements;

    /**
     * @var Collection<int, MouvementCollaborateur>
     */
    #[ORM\OneToMany(targetEntity: MouvementCollaborateur::class, mappedBy: 'collaborateur')]
    private Collection $mouvementCollaborateurs;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $nationalite = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $categorie = null;

    /**
     * @var Collection<int, InscriptionActivite>
     */
    #[ORM\OneToMany(targetEntity: InscriptionActivite::class, mappedBy: 'saisiePar')]
    private Collection $activiteSaisie;

    /**
     * @var Collection<int, HistoriqueSuppression>
     */
    #[ORM\OneToMany(targetEntity: HistoriqueSuppression::class, mappedBy: 'saisiePar', orphanRemoval:true, cascade:['remove'])]
    private Collection $historiqueSuppressions;

    /**
     * @var Collection<int, PrimePersonnel>
     */
    #[ORM\OneToMany(targetEntity: PrimePersonnel::class, mappedBy: 'saisiePar')]
    private Collection $primePersonnels;

    /**
     * @var Collection<int, HistoriqueSuppression>
     */
    #[ORM\OneToMany(targetEntity: HistoriqueSuppression::class, mappedBy: 'user', orphanRemoval:true, cascade:['remove'])]
    private Collection $historiqueSuppresionsUser;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'saisiePar')]
    private Collection $events;

    /**
     * @var Collection<int, HeureTravaille>
     */
    #[ORM\OneToMany(targetEntity: HeureTravaille::class, mappedBy: 'saisiePar')]
    private Collection $heureTravailles;

    /**
     * @var Collection<int, ControlEleve>
     */
    #[ORM\OneToMany(targetEntity: ControlEleve::class, mappedBy: 'saisiePar')]
    private Collection $controlEleves;

    /**
     * @var Collection<int, ControlEleve>
     */
    #[ORM\OneToMany(targetEntity: ControlEleve::class, mappedBy: 'saisieJustificatif')]
    private Collection $controlesElevesJust;

    /**
     * @var Collection<int, DevoirEleve>
     */
    #[ORM\OneToMany(targetEntity: DevoirEleve::class, mappedBy: 'enseignant')]
    private Collection $devoirEleves;

    /**
     * @var Collection<int, NoteEleve>
     */
    #[ORM\OneToMany(targetEntity: NoteEleve::class, mappedBy: 'saisiePar')]
    private Collection $noteEleves;
   


    public function __construct()
    {
        $this->classeRepartitions = new ArrayCollection();
        $this->salaires = new ArrayCollection();
        $this->personnelActifs = new ArrayCollection();
        $this->paiementEleves = new ArrayCollection();
        $this->mouvementCaisses = new ArrayCollection();
        $this->versements = new ArrayCollection();
        $this->decaissements = new ArrayCollection();
        $this->mouvementCollaborateurs = new ArrayCollection();
        $this->activiteSaisie = new ArrayCollection();
        $this->historiqueSuppressions = new ArrayCollection();
        $this->primePersonnels = new ArrayCollection();
        $this->historiqueSuppresionsUser = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->heureTravailles = new ArrayCollection();
        $this->controlEleves = new ArrayCollection();
        $this->controlesElevesJust = new ArrayCollection();
        $this->photo = 'default.jpg';
        $this->devoirEleves = new ArrayCollection();
        $this->noteEleves = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, ClasseRepartition>
     */
    public function getClasseRepartitions(): Collection
    {
        return $this->classeRepartitions;
    }

    public function addClasseRepartition(ClasseRepartition $classeRepartition): static
    {
        if (!$this->classeRepartitions->contains($classeRepartition)) {
            $this->classeRepartitions->add($classeRepartition);
            $classeRepartition->setResponsable($this);
        }

        return $this;
    }

    public function removeClasseRepartition(ClasseRepartition $classeRepartition): static
    {
        if ($this->classeRepartitions->removeElement($classeRepartition)) {
            // set the owning side to null (unless already changed)
            if ($classeRepartition->getResponsable() === $this) {
                $classeRepartition->setResponsable(null);
            }
        }

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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

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

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): static
    {
        $this->ville = $ville;

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

    public function getMatricule(): ?string
    {
        return $this->matricule;
    }

    public function setMatricule(string $matricule): static
    {
        $this->matricule = $matricule;

        return $this;
    }

    

    public function getSexe(): ?string
    {
        return $this->sexe;
    }

    public function setSexe(string $sexe): static
    {
        $this->sexe = $sexe;

        return $this;
    }

    public function getDateNaissance(): ?\DateTimeInterface
    {
        return $this->dateNaissance;
    }

    public function setDateNaissance(?\DateTimeInterface $dateNaissance): static
    {
        $this->dateNaissance = $dateNaissance;

        return $this;
    }

    public function getLieuNaissance(): ?string
    {
        return $this->lieuNaissance;
    }

    public function setLieuNaissance(?string $lieuNaissance): static
    {
        $this->lieuNaissance = $lieuNaissance;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * @return Collection<int, Salaire>
     */
    public function getSalaires(): Collection
    {
        return $this->salaires;
    }

    public function addSalaire(Salaire $salaire): static
    {
        if (!$this->salaires->contains($salaire)) {
            $this->salaires->add($salaire);
            $salaire->setUser($this);
        }

        return $this;
    }

    public function removeSalaire(Salaire $salaire): static
    {
        if ($this->salaires->removeElement($salaire)) {
            // set the owning side to null (unless already changed)
            if ($salaire->getUser() === $this) {
                $salaire->setUser(null);
            }
        }

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getTypeUser(): ?string
    {
        return $this->typeUser;
    }

    public function setTypeUser(string $typeUser): static
    {
        $this->typeUser = $typeUser;

        return $this;
    }

    /**
     * @return Collection<int, PersonnelActif>
     */
    public function getPersonnelActifs(): Collection
    {
        return $this->personnelActifs;
    }

    public function addPersonnelActif(PersonnelActif $personnelActif): static
    {
        if (!$this->personnelActifs->contains($personnelActif)) {
            $this->personnelActifs->add($personnelActif);
            $personnelActif->setPersonnel($this);
        }

        return $this;
    }

    public function removePersonnelActif(PersonnelActif $personnelActif): static
    {
        if ($this->personnelActifs->removeElement($personnelActif)) {
            // set the owning side to null (unless already changed)
            if ($personnelActif->getPersonnel() === $this) {
                $personnelActif->setPersonnel(null);
            }
        }

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


    public function getNomComplet(): ?string
    {
        return mb_convert_case($this->prenom, MB_CASE_TITLE, 'UTF-8') . ' ' . mb_strtoupper($this->nom, 'UTF-8');
    }


    public function getAge(): ?int
    {
        if ($this->dateNaissance === null) {
            return null;
        }

        $now = new \DateTime();
        $age = $now->diff($this->dateNaissance)->y;

        return $age;
    }

    /**
     * @return Collection<int, PaiementEleve>
     */
    public function getPaiementEleves(): Collection
    {
        return $this->paiementEleves;
    }

    public function addPaiementElefe(PaiementEleve $paiementElefe): static
    {
        if (!$this->paiementEleves->contains($paiementElefe)) {
            $this->paiementEleves->add($paiementElefe);
            $paiementElefe->setSaisiePar($this);
        }

        return $this;
    }

    public function removePaiementElefe(PaiementEleve $paiementElefe): static
    {
        if ($this->paiementEleves->removeElement($paiementElefe)) {
            // set the owning side to null (unless already changed)
            if ($paiementElefe->getSaisiePar() === $this) {
                $paiementElefe->setSaisiePar(null);
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
            $mouvementCaiss->setSaisiePar($this);
        }

        return $this;
    }

    public function removeMouvementCaiss(MouvementCaisse $mouvementCaiss): static
    {
        if ($this->mouvementCaisses->removeElement($mouvementCaiss)) {
            // set the owning side to null (unless already changed)
            if ($mouvementCaiss->getSaisiePar() === $this) {
                $mouvementCaiss->setSaisiePar(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Versement>
     */
    public function getVersements(): Collection
    {
        return $this->versements;
    }

    public function addVersement(Versement $versement): static
    {
        if (!$this->versements->contains($versement)) {
            $this->versements->add($versement);
            $versement->setCollaborateur($this);
        }

        return $this;
    }

    public function removeVersement(Versement $versement): static
    {
        if ($this->versements->removeElement($versement)) {
            // set the owning side to null (unless already changed)
            if ($versement->getCollaborateur() === $this) {
                $versement->setCollaborateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Decaissement>
     */
    public function getDecaissements(): Collection
    {
        return $this->decaissements;
    }

    public function addDecaissement(Decaissement $decaissement): static
    {
        if (!$this->decaissements->contains($decaissement)) {
            $this->decaissements->add($decaissement);
            $decaissement->setCollaborateur($this);
        }

        return $this;
    }

    public function removeDecaissement(Decaissement $decaissement): static
    {
        if ($this->decaissements->removeElement($decaissement)) {
            // set the owning side to null (unless already changed)
            if ($decaissement->getCollaborateur() === $this) {
                $decaissement->setCollaborateur(null);
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
            $mouvementCollaborateur->setCollaborateur($this);
        }

        return $this;
    }

    public function removeMouvementCollaborateur(MouvementCollaborateur $mouvementCollaborateur): static
    {
        if ($this->mouvementCollaborateurs->removeElement($mouvementCollaborateur)) {
            // set the owning side to null (unless already changed)
            if ($mouvementCollaborateur->getCollaborateur() === $this) {
                $mouvementCollaborateur->setCollaborateur(null);
            }
        }

        return $this;
    }

    public function getNationalite(): ?string
    {
        return $this->nationalite;
    }

    public function setNationalite(?string $nationalite): static
    {
        $this->nationalite = $nationalite;

        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(?string $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    /**
     * @return Collection<int, InscriptionActivite>
     */
    public function getActiviteSaisie(): Collection
    {
        return $this->activiteSaisie;
    }

    public function addActiviteSaisie(InscriptionActivite $activiteSaisie): static
    {
        if (!$this->activiteSaisie->contains($activiteSaisie)) {
            $this->activiteSaisie->add($activiteSaisie);
            $activiteSaisie->setSaisiePar($this);
        }

        return $this;
    }

    public function removeActiviteSaisie(InscriptionActivite $activiteSaisie): static
    {
        if ($this->activiteSaisie->removeElement($activiteSaisie)) {
            // set the owning side to null (unless already changed)
            if ($activiteSaisie->getSaisiePar() === $this) {
                $activiteSaisie->setSaisiePar(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, HistoriqueSuppression>
     */
    public function getHistoriqueSuppressions(): Collection
    {
        return $this->historiqueSuppressions;
    }

    public function addHistoriqueSuppression(HistoriqueSuppression $historiqueSuppression): static
    {
        if (!$this->historiqueSuppressions->contains($historiqueSuppression)) {
            $this->historiqueSuppressions->add($historiqueSuppression);
            $historiqueSuppression->setSaisiePar($this);
        }

        return $this;
    }

    public function removeHistoriqueSuppression(HistoriqueSuppression $historiqueSuppression): static
    {
        if ($this->historiqueSuppressions->removeElement($historiqueSuppression)) {
            // set the owning side to null (unless already changed)
            if ($historiqueSuppression->getSaisiePar() === $this) {
                $historiqueSuppression->setSaisiePar(null);
            }
        }

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
            $primePersonnel->setSaisiePar($this);
        }

        return $this;
    }

    public function removePrimePersonnel(PrimePersonnel $primePersonnel): static
    {
        if ($this->primePersonnels->removeElement($primePersonnel)) {
            // set the owning side to null (unless already changed)
            if ($primePersonnel->getSaisiePar() === $this) {
                $primePersonnel->setSaisiePar(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, HistoriqueSuppression>
     */
    public function getHistoriqueSuppresionsUser(): Collection
    {
        return $this->historiqueSuppresionsUser;
    }

    public function addHistoriqueSuppresionsUser(HistoriqueSuppression $historiqueSuppresionsUser): static
    {
        if (!$this->historiqueSuppresionsUser->contains($historiqueSuppresionsUser)) {
            $this->historiqueSuppresionsUser->add($historiqueSuppresionsUser);
            $historiqueSuppresionsUser->setUser($this);
        }

        return $this;
    }

    public function removeHistoriqueSuppresionsUser(HistoriqueSuppression $historiqueSuppresionsUser): static
    {
        if ($this->historiqueSuppresionsUser->removeElement($historiqueSuppresionsUser)) {
            // set the owning side to null (unless already changed)
            if ($historiqueSuppresionsUser->getUser() === $this) {
                $historiqueSuppresionsUser->setUser(null);
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
            $event->setSaisiePar($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getSaisiePar() === $this) {
                $event->setSaisiePar(null);
            }
        }

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
            $heureTravaille->setSaisiePar($this);
        }

        return $this;
    }

    public function removeHeureTravaille(HeureTravaille $heureTravaille): static
    {
        if ($this->heureTravailles->removeElement($heureTravaille)) {
            // set the owning side to null (unless already changed)
            if ($heureTravaille->getSaisiePar() === $this) {
                $heureTravaille->setSaisiePar(null);
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
            $controlElefe->setSaisiePar($this);
        }

        return $this;
    }

    public function removeControlElefe(ControlEleve $controlElefe): static
    {
        if ($this->controlEleves->removeElement($controlElefe)) {
            // set the owning side to null (unless already changed)
            if ($controlElefe->getSaisiePar() === $this) {
                $controlElefe->setSaisiePar(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ControlEleve>
     */
    public function getControlesElevesJust(): Collection
    {
        return $this->controlesElevesJust;
    }

    public function addControlesElevesJust(ControlEleve $controlesElevesJust): static
    {
        if (!$this->controlesElevesJust->contains($controlesElevesJust)) {
            $this->controlesElevesJust->add($controlesElevesJust);
            $controlesElevesJust->setSaisieJustificatif($this);
        }

        return $this;
    }

    public function removeControlesElevesJust(ControlEleve $controlesElevesJust): static
    {
        if ($this->controlesElevesJust->removeElement($controlesElevesJust)) {
            // set the owning side to null (unless already changed)
            if ($controlesElevesJust->getSaisieJustificatif() === $this) {
                $controlesElevesJust->setSaisieJustificatif(null);
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
            $devoirElefe->setEnseignant($this);
        }

        return $this;
    }

    public function removeDevoirElefe(DevoirEleve $devoirElefe): static
    {
        if ($this->devoirEleves->removeElement($devoirElefe)) {
            // set the owning side to null (unless already changed)
            if ($devoirElefe->getEnseignant() === $this) {
                $devoirElefe->setEnseignant(null);
            }
        }

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
            $noteElefe->setSaisiePar($this);
        }

        return $this;
    }

    public function removeNoteElefe(NoteEleve $noteElefe): static
    {
        if ($this->noteEleves->removeElement($noteElefe)) {
            // set the owning side to null (unless already changed)
            if ($noteElefe->getSaisiePar() === $this) {
                $noteElefe->setSaisiePar(null);
            }
        }

        return $this;
    }
   
}
