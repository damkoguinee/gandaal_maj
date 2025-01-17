<?php

namespace App\Controller\Gandaal\Administration\Excel;

use App\Entity\Etablissement;
use App\Entity\LieuxVentes;
use App\Repository\CommandeProductRepository;
use App\Repository\InscriptionRepository;
use App\Repository\MouvementCaisseRepository;
use App\Repository\MouvementCollaborateurRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

#[Route('/gandaal/administration/excel')]

class ExcelController extends AbstractController
{
    #[Route('/inscription/{etablissement}', name: 'app_gandaal_administration_excel_index')]
    public function exportEleve(Etablissement $etablissement, EntityManagerInterface $em, InscriptionRepository $inscriptionRep): Response
    {

        $inscriptions = $inscriptionRep->findBy(['etablissement' => $etablissement]);  
        
        // Créer un nouveau document Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Ajouter des données à l'Excel
        $sheet->setCellValue('A1', 'N°');
        $sheet->setCellValue('B1', 'nom');
        $sheet->setCellValue('C1', 'prénom');
        $sheet->setCellValue('D1', 'matricule');
        $sheet->setCellValue('E1', 'date de naissance');
        $sheet->setCellValue('F1', 'téléphone');
        $sheet->setCellValue('G1', 'classe');
        $sheet->setCellValue('H1', 'ecole origine');
        $sheet->setCellValue('I1', 'type');
        $sheet->setCellValue('J1', 'remise inscription');
        $sheet->setCellValue('K1', 'remise scolarité');
        $sheet->setCellValue('L1', 'promo');
        $sheet->setCellValue('M1', 'date inscription');
        $sheet->setCellValue('N1', 'statut');
        $sheet->setCellValue('O1', 'etat scolarité');
        $sheet->setCellValue('P1', 'etat pédagogie');
        $sheet->setCellValue('Q1', 'saisie');
        $sheet->setCellValue('R1', 'etablissement');
        $sheet->setCellValue('S1', 'username');
        $sheet->setCellValue('T1', 'email');
        $sheet->setCellValue('U1', 'adresse');
        $sheet->setCellValue('V1', 'sexe');
        $sheet->setCellValue('w1', 'lieu de naissance');
        $sheet->setCellValue('X1', 'nationalité');
        $sheet->setCellValue('Y1', 'père');
        $sheet->setCellValue('Z1', 'mère');

        // dd($inscriptions);

        $row = 2;
        foreach ($inscriptions as $key => $inscription) {
            $pere = null;
            $mere = null;
            foreach ($inscription->getEleve()->getFiliations() as $filiation) {
                if ($filiation->getLienFamilial() === 'père') {
                    $pere = $filiation->getPrenom();
                } elseif ($filiation->getLienFamilial() === 'mère') {
                    $mere = strtoupper($filiation->getNom()).' '.ucwords($filiation->getPrenom());
                }
            }
            // Remplir les cellules avec les données des inscriptions
            $sheet->setCellValue('A' . $row, ($key + 1)); // Numéro
            $sheet->setCellValue('B' . $row, $inscription->getEleve()->getNom()); // Nom
            $sheet->setCellValue('C' . $row, $inscription->getEleve()->getPrenom()); // Prénom
            $sheet->setCellValue('D' . $row, $inscription->getEleve()->getMatricule()); // Matricule
            $dateNaissance = $inscription->getEleve()->getDateNaissance();
            if ($dateNaissance instanceof \DateTime) {
                $sheet->setCellValue('E' . $row, $dateNaissance->format('d/m/Y'));
            } else {
                $sheet->setCellValue('E' . $row, 'N/A'); // Mettre "N/A" si ce n'est pas une date valide
            }
            $sheet->setCellValue('F' . $row, $inscription->getEleve()->getTelephone()); // Téléphone
            $sheet->setCellValue('G' . $row, $inscription->getClasse()->getNom()); // Classe
            $sheet->setCellValue('H' . $row, $inscription->getEleve()->getEcoleOrigine()); // École d'Origine
            $sheet->setCellValue('I' . $row, $inscription->getType()); // Type d'Inscription
            $sheet->setCellValue('J' . $row, $inscription->getRemiseInscription()); // Remise sur Inscription
            $sheet->setCellValue('K' . $row, $inscription->getRemiseScolarite()); // Remise sur Scolarité
            $sheet->setCellValue('L' . $row, ($inscription->getPromo() - 1 ).'-'.$inscription->getPromo()); // Promo
            $dateInscription = $inscription->getDateInscription();
            if ($dateInscription instanceof \DateTime) {
                $sheet->setCellValue('M' . $row, $dateInscription->format('d/m/Y'));
            } else {
                $sheet->setCellValue('M' . $row, 'N/A'); // Mettre "N/A" si ce n'est pas une date valide
            }

            $sheet->setCellValue('M' . $row, $inscription->getDateInscription() ? $inscription->getDateInscription()->format('d/m/Y') : NULL); // Date d'Inscription
            $sheet->setCellValue('N' . $row, $inscription->getStatut()); // Statut
            $sheet->setCellValue('O' . $row, $inscription->getEtatScol()); // État Scolarité
            $sheet->setCellValue('P' . $row, $inscription->getEtatPedagogie() ?: 'N/A'); // État Pédagogie
            $sheet->setCellValue('Q' . $row, $inscription->getSaisiePar()->getNomComplet()); // Saisie par
            $sheet->setCellValue('R' . $row, $inscription->getEtablissement()->getNom()); // Établissement
            $sheet->setCellValue('S' . $row, $inscription->getEleve()->getUsername()); // Username
            $sheet->setCellValue('T' . $row, $inscription->getEleve()->getEmail()); // Email
            $sheet->setCellValue('U' . $row, $inscription->getEleve()->getAdresse()); // Adresse
            $sheet->setCellValue('V' . $row, $inscription->getEleve()->getSexe()); // Sexe
            $sheet->setCellValue('W' . $row, $inscription->getEleve()->getLieuNaissance()); // Lieu de Naissance
            $sheet->setCellValue('X' . $row, $inscription->getEleve()->getNationalite()); // Nationalité

           
            $sheet->setCellValue('Y' . $row, $pere); // Père
            $sheet->setCellValue('Z' . $row, $mere); // Mère
            
            $row++;
        }

        // Créer un fichier Excel
        $writer = new Xlsx($spreadsheet);
        $dateTime = date('Y-m-d_H-i-s'); // Format : 2024-06-08_14-55-02
        $fileName = 'export_eleve_' . $dateTime . '.xlsx';

        // Créer une réponse HTTP avec le fichier Excel
        $response = new Response();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        // Écrire le contenu dans la réponse
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();
        $response->setContent($content);

        return $response;
    }


    #[Route('/paiement/{etablissement}', name: 'app_gandaal_administration_excel_paiement')]
    public function exportPaiement(Etablissement $etablissement, EntityManagerInterface $em, MouvementCaisseRepository $mouvementCaisseRepository): Response
    {

        $mouvements = $mouvementCaisseRepository->findBy(['etablissement' => $etablissement]); 
        
        // Créer un nouveau document Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Ajouter des données à l'Excel
        $sheet->setCellValue('A1', 'N°');
        $sheet->setCellValue('B1', 'type');
        $sheet->setCellValue('C1', 'reference');
        $sheet->setCellValue('D1', 'montant');
        $sheet->setCellValue('E1', 'caisse');
        $sheet->setCellValue('F1', 'devise');
        $sheet->setCellValue('G1', 'mode paie');
        $sheet->setCellValue('H1', 'numero paie');
        $sheet->setCellValue('I1', 'banque');
        $sheet->setCellValue('J1', 'taux');
        $sheet->setCellValue('K1', 'date opération');
        $sheet->setCellValue('L1', 'date de saisie');
        $sheet->setCellValue('M1', 'saisie par');
        $sheet->setCellValue('N1', 'promo');
        $sheet->setCellValue('O1', 'prénom/nom');
        $sheet->setCellValue('P1', 'matricule');
        $sheet->setCellValue('Q1', 'categorie');
        $sheet->setCellValue('R1', 'description');

        $row = 2;
        foreach ($mouvements as $key => $mouvement) {
            
            // Remplir les cellules avec les données des inscriptions
            $sheet->setCellValue('A' . $row, ($key + 1)); // Numéro
            $sheet->setCellValue('B' . $row, $mouvement->getTypeMouvement()); // Nom
            $sheet->setCellValue('C' . $row, $mouvement->getReference()); // Prénom
            $sheet->setCellValue('D' . $row, $mouvement->getMontant()); // Matricule
            $sheet->setCellValue('E' . $row, $mouvement->getCaisse()->getNom());           
            $sheet->setCellValue('F' . $row, $mouvement->getDevise()->getNom()); // Téléphone
            $sheet->setCellValue('G' . $row, $mouvement->getModePaie()->getNom()); // Classe
            $sheet->setCellValue('H' . $row, $mouvement->getNumeroPaie()); // École d'Origine
            $sheet->setCellValue('I' . $row, $mouvement->getBanquePaie()); // Type d'Inscription
            $sheet->setCellValue('J' . $row, $mouvement->getTaux()); // Remise sur Inscription
            $dateOperation = $mouvement->getDateOperation();
            if ($dateOperation instanceof \DateTime) {
                $sheet->setCellValue('K' . $row, $dateOperation->format('d/m/Y'));
            } else {
                $sheet->setCellValue('K' . $row, 'N/A'); // Mettre "N/A" si ce n'est pas une date valide
            }
            $dateSaisie = $mouvement->getDateSaisie();
            if ($dateSaisie instanceof \DateTime) {
                $sheet->setCellValue('L' . $row, $dateSaisie->format('d/m/Y'));
            } else {
                $sheet->setCellValue('L' . $row, 'N/A'); // Mettre "N/A" si ce n'est pas une date valide
            }
            $sheet->setCellValue('M' . $row, $mouvement->getSaisiePar()->getNomComplet()); // Saisie par
            $sheet->setCellValue('N' . $row, ($mouvement->getPromo() - 1 ).'-'.$mouvement->getPromo()); 
            if ($mouvement->getTypeMouvement() == 'scolarite' or $mouvement->getTypeMouvement() == 'inscription' or $mouvement->getTypeMouvement() == 'réinscription' or $mouvement->getTypeMouvement() == 'recette') {
                if ($mouvement->getInscription()) {
                    # code...
                    $sheet->setCellValue('O' . $row, $mouvement->getInscription()->getEleve()->getNomComplet()); 
                    $sheet->setCellValue('P' . $row, $mouvement->getInscription()->getEleve()->getMatricule()); 
                }else {
                    $sheet->setCellValue('O' . $row, 'N/A');
                    $sheet->setCellValue('P' . $row, 'N/A');
                }
            }else {
                $sheet->setCellValue('O' . $row, 'N/A');
                $sheet->setCellValue('P' . $row, 'N/A');
            }

            if ($mouvement->getTypeMouvement() == 'depense') {
                # code...
                $sheet->setCellValue('Q' . $row, $mouvement->getCategorieDepense()->getNom()); 
                $sheet->setCellValue('R' . $row, $mouvement->getDescription()); 
            }elseif ($mouvement->getTypeMouvement() == 'recette') {
                # code...
                $sheet->setCellValue('Q' . $row, $mouvement->getCategorieRecette()->getNom()); 
                $sheet->setCellValue('R' . $row, $mouvement->getDescription()); 
            }else {
                $sheet->setCellValue('Q' . $row, 'N/A');
                $sheet->setCellValue('R' . $row, 'N/A');
            }
            
            $row++;
        }

        // Créer un fichier Excel
        $writer = new Xlsx($spreadsheet);
        $dateTime = date('Y-m-d_H-i-s'); // Format : 2024-06-08_14-55-02
        $fileName = 'export_paiement_' . $dateTime . '.xlsx';

        // Créer une réponse HTTP avec le fichier Excel
        $response = new Response();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        // Écrire le contenu dans la réponse
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();
        $response->setContent($content);

        return $response;
    }
}
