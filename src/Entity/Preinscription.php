<?php

namespace App\Entity;

use App\Repository\PreinscriptionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PreinscriptionRepository::class)]
class Preinscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column(length: 150)]
    private ?string $prenom = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 15)]
    private ?string $telephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(length: 150)]
    private ?string $ville = null;

    #[ORM\Column(length: 150)]
    private ?string $pays = null;

    #[ORM\Column(length: 10)]
    private ?string $sexe = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateNaissance = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $lieuNaissance = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $ecoleOrigine = null;


    #[ORM\Column(length: 100)]
    private ?string $nomp = null;

    #[ORM\Column(length: 150)]
    private ?string $prenomp = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $emailp = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $telephonep = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $professionp = null;
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $lieuTravailp = null;

    #[ORM\Column(length: 100)]
    private ?string $nomm = null;

    #[ORM\Column(length: 150)]
    private ?string $prenomm = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $emailm = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $telephonem = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $professionm = null;
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $lieuTravailm = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $nomt = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $prenomt = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $emailt = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $telephonet = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $professiont = null;
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $lieuTravailt = null;

    #[ORM\Column(length: 10)]
    private ?string $promo = null;

    #[ORM\ManyToOne(inversedBy: 'preinscriptions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Etablissement $etablissement = null;

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

    public function setTelephone(string $telephone): static
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

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
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


    public function getNomp(): ?string
    {
        return $this->nomp;
    }

    public function setNomp(string $nomp): static
    {
        $this->nomp = $nomp;

        return $this;
    }

    public function getPrenomp(): ?string
    {
        return $this->prenomp;
    }

    public function setPrenomp(string $prenomp): static
    {
        $this->prenomp = $prenomp;

        return $this;
    }

    public function getEmailp(): ?string
    {
        return $this->emailp;
    }

    public function setEmailp(?string $emailp): static
    {
        $this->emailp = $emailp;

        return $this;
    }

    public function getTelephonep(): ?string
    {
        return $this->telephonep;
    }

    public function setTelephonep(string $telephonep): static
    {
        $this->telephonep = $telephonep;

        return $this;
    }

    public function getProfessionp(): ?string
    {
        return $this->professionp;
    }

    public function setProfessionp(string $professionp): static
    {
        $this->professionp = $professionp;

        return $this;
    }

    public function getLieuTravailp(): ?string
    {
        return $this->lieuTravailp;
    }

    public function setLieuTravailp(string $lieuTravailp): static
    {
        $this->lieuTravailp = $lieuTravailp;

        return $this;
    }


    public function getNomm(): ?string
    {
        return $this->nomm;
    }

    public function setNomm(string $nomm): static
    {
        $this->nomm = $nomm;

        return $this;
    }

    public function getPrenomm(): ?string
    {
        return $this->prenomm;
    }

    public function setPrenomm(string $prenomm): static
    {
        $this->prenomm = $prenomm;

        return $this;
    }

    public function getEmailm(): ?string
    {
        return $this->emailm;
    }

    public function setEmailm(?string $emailm): static
    {
        $this->emailm = $emailm;

        return $this;
    }

    public function getTelephonem(): ?string
    {
        return $this->telephonem;
    }

    public function setTelephonem(string $telephonem): static
    {
        $this->telephonem = $telephonem;

        return $this;
    }

    public function getProfessionm(): ?string
    {
        return $this->professionm;
    }

    public function setProfessionm(string $professionm): static
    {
        $this->professionm = $professionm;

        return $this;
    }

    public function getLieuTravailm(): ?string
    {
        return $this->lieuTravailm;
    }

    public function setLieuTravailm(string $lieuTravailm): static
    {
        $this->lieuTravailm = $lieuTravailm;

        return $this;
    }

    public function getNomt(): ?string
    {
        return $this->nomt;
    }

    public function setNomt(string $nomt): static
    {
        $this->nomt = $nomt;

        return $this;
    }

    public function getPrenomt(): ?string
    {
        return $this->prenomt;
    }

    public function setPrenomt(string $prenomt): static
    {
        $this->prenomt = $prenomt;

        return $this;
    }

    public function getEmailt(): ?string
    {
        return $this->emailt;
    }

    public function setEmailt(?string $emailt): static
    {
        $this->emailt = $emailt;

        return $this;
    }

    public function getTelephonet(): ?string
    {
        return $this->telephonet;
    }

    public function setTelephonet(string $telephonet): static
    {
        $this->telephonet = $telephonet;

        return $this;
    }

    public function getProfessiont(): ?string
    {
        return $this->professiont;
    }

    public function setProfessiont(string $professiont): static
    {
        $this->professiont = $professiont;

        return $this;
    }

    public function getLieuTravailt(): ?string
    {
        return $this->lieuTravailt;
    }

    public function setLieuTravailt(string $lieuTravailt): static
    {
        $this->lieuTravailt = $lieuTravailt;

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

    public function getEtablissement(): ?Etablissement
    {
        return $this->etablissement;
    }

    public function setEtablissement(?Etablissement $etablissement): static
    {
        $this->etablissement = $etablissement;

        return $this;
    }
}
