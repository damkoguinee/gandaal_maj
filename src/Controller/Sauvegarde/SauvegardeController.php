<?php

namespace App\Controller\Sauvegarde;

use App\Entity\Etablissement;
use App\Repository\UserRepository;
use App\Repository\StockRepository;
use App\Repository\ProductsRepository;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\FacturationRepository;
use App\Repository\ModePaiementRepository;
use App\Repository\CommandeProductRepository;
use App\Repository\ConfigurationLogicielRepository;
use App\Repository\MouvementCaisseRepository;
use App\Repository\MouvementProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

#[Route('/sauvegarde')]
class SauvegardeController extends AbstractController
{

    #[Route('/bdd/{etablissement}', name: 'app_sauvegarde_bdd')]
    public function sauvegardeBdd(EntityManagerInterface $em, Etablissement $etablissement, EntrepriseRepository $entrepriseRepository, ConfigurationLogicielRepository $configurationLogicielRep): Response
    {
        $entreprise = $etablissement->getEntreprise()->getNom();
        $configuration = $configurationLogicielRep->findOneBy(['etablissement' => $etablissement]);
        $chemin_sauvegarde = $configuration->getCheminSauvegarde();
        $chemin_mysql = $configuration->getCheminMysql();
        // dd($chemin_sauvegarde);
        // Format de la date (année-mois-jour_heure-minute-seconde)
        $date = date('Y-m-d_H-i-s');

        // Chemin où vous souhaitez sauvegarder le dump, avec la date incluse dans le nom du fichier
        // $cheminDump = "D:/data/projets_symfony/maj_bdd/sauvegarde_{$entreprise}_{$date}.sql";
        $cheminDump = $chemin_sauvegarde."/sauvegarde_{$entreprise}_{$date}.sql";

        // Chemin complet vers mysqldump
        // $cheminMysqldump = 'C:/wamp64/bin/mysql/mysql5.7.26/bin/mysqldump.exe'; // Remplacez ce chemin par le chemin de votre installation de mysqldump
        $cheminMysqldump = $chemin_mysql."/mysqldump.exe"; // Remplacez ce chemin par le chemin de votre installation de mysqldump

        // Récupérer les informations de connexion depuis le fichier .env
        $databaseUrl = $_ENV['DATABASE_URL'];

        // Parse l'URL de connexion pour obtenir les informations nécessaires
        $urlComponents = parse_url($databaseUrl);

        // Extraire les informations nécessaires
        $user = $urlComponents['user'];
        $password = $urlComponents['pass'] ?? ''; // Peut être vide
        $host = $urlComponents['host'];
        $port = $urlComponents['port'] ?? 3306; // Port par défaut si non spécifié
        $dbname = ltrim($urlComponents['path'], '/');

        // Construire la commande mysqldump avec le chemin complet et la redirection du mot de passe
        $commande = [
            $cheminMysqldump,
            '-u', $user,
            // '-p' . $password, // Pour le mot de passe, pas d'espace après -p à activer si le mot de passe est defini
            '-h', $host,
            '-P', $port,
            $dbname
        ];

        // Créer un objet Process pour exécuter la commande
        $process = new Process($commande);
        $process->setTimeout(3600); // Timeout d'une heure (si nécessaire)

        // Rediriger la sortie vers le fichier de dump
        $process->setTty(false);
        $process->run(function ($type, $buffer) use ($cheminDump) {
            if (Process::ERR === $type) {
                // Gérer les erreurs ici si nécessaire
            } else {
                // $this->addFlash("warning", "Echec de la sauvegarde : Le chemin d'accès ou la clé USB n'est pas disponible");

                // Pour la redirection correcte, la sortie est écrite directement dans le fichier
                file_put_contents($cheminDump, $buffer, FILE_APPEND);
            }
        });

        // Vérifier si l'opération s'est bien déroulée
        if (!$process->isSuccessful()) {
            // En cas d'erreur, capturer l'exception et retourner une réponse d'erreur
            $this->addFlash("warning", "Echec de la sauvegarde");
            return new Response("Échec du dump de la base de données : " . $process->getErrorOutput(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }else{
            $this->addFlash("success", "Sauvegarde effectuée avec succée :)");
            
        }

        // Si nécessaire, persist les changements dans la base de données avec EntityManager
        $em->flush();

        // Redirige vers une autre route après le succès du dump
        return $this->redirectToRoute('app_logout', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/maj/bdd/securise/{etablissement}', name: 'app_admin_reajustement_donnees_maj_bb_securise')]
    public function majBddSecurise(EntityManagerInterface $em, Etablissement $etablissement, EntrepriseRepository $entrepriseRepository): Response
    {
        $entreprise = $etablissement->getEntreprise()->getNom();
        $date = date('Y-m-d_H-i-s');

        // Chemin où vous souhaitez sauvegarder le dump, avec la date incluse dans le nom du fichier
        $cheminDump = "D:/data/projets_symfony/maj_bdd/{$entreprise}_dump_{$date}.sql";

        // Chemin complet vers mysqldump
        $cheminMysqldump = 'C:/wamp64/bin/mysql/mysql5.7.26/bin/mysqldump.exe'; // Remplacez ce chemin par le chemin de votre installation de mysqldump

        // Récupérer les informations de connexion depuis le fichier .env
        $databaseUrl = $_ENV['DATABASE_URL'];

        // Parse l'URL de connexion pour obtenir les informations nécessaires
        $urlComponents = parse_url($databaseUrl);

        // Extraire les informations nécessaires
        $user = $urlComponents['user'];
        $password = $urlComponents['pass'] ?? ''; // Peut être vide
        $host = $urlComponents['host'];
        $port = $urlComponents['port'] ?? 3306; // Port par défaut si non spécifié
        $dbname = ltrim($urlComponents['path'], '/');

        // Construire la commande mysqldump avec le chemin complet et la redirection du mot de passe
        $commande = [
            $cheminMysqldump,
            '-u', $user,
            // '-p' . $password, // Pour le mot de passe, pas d'espace après -p à activer si le mot de passe est defini
            '-h', $host,
            '-P', $port,
            $dbname
        ];

        // Créer un objet Process pour exécuter la commande
        $process = new Process($commande);
        $process->setTimeout(3600); // Timeout d'une heure (si nécessaire)

        // Rediriger la sortie vers le fichier de dump
        $process->setTty(false);
        $process->run(function ($type, $buffer) use ($cheminDump) {
            if (Process::ERR === $type) {
                // Gérer les erreurs ici si nécessaire
            } else {
                // Pour la redirection correcte, la sortie est écrite directement dans le fichier
                file_put_contents($cheminDump, $buffer, FILE_APPEND);
            }
        });

        // Vérifier si l'opération s'est bien déroulée
        if (!$process->isSuccessful()) {
            // En cas d'erreur, capturer l'exception et retourner une réponse d'erreur
            return new Response("Échec du dump de la base de données : " . $process->getErrorOutput(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Si nécessaire, persist les changements dans la base de données avec EntityManager
        $em->flush();

        // Redirige vers une autre route après le succès du dump
        return $this->redirectToRoute('app_gandaal_home', [], Response::HTTP_SEE_OTHER);
    }

    
}

