<?php

namespace App\Controller\Gandaal\Administration\Comptabilite;

use App\Entity\Etablissement;
use App\Entity\PaiementSalairePersonnel;
use App\Repository\ConfigCaisseRepository;
use App\Repository\ConfigDeviseRepository;
use App\Repository\ConfigModePaiementRepository;
use App\Repository\CursusRepository;
use App\Repository\FraisInscriptionRepository;
use App\Repository\FraisScolariteRepository;
use App\Repository\InscriptionRepository;
use App\Repository\UserRepository;
use App\Repository\MouvementCaisseRepository;
use App\Repository\MouvementCollaborateurRepository;
use App\Repository\PaiementEleveRepository;
use App\Repository\PaiementSalairePersonnelRepository;
use App\Repository\PersonnelActifRepository;
use App\Repository\SalaireRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/gandaal/administration/comptabilite/admin/bilan')]
class BilanController extends AbstractController
{
    #[Route('/{etablissement}', name: 'app_gandaal_administration_comptabilite_bilan')]
    public function index(Etablissement $etablissement, Request $request, SessionInterface $session, MouvementCaisseRepository $mouvementRep, ConfigDeviseRepository $deviseRep, ConfigCaisseRepository $caisseRep, PaiementEleveRepository $paiementEleveRep, ConfigModePaiementRepository $modePaieRep): Response
    {
        $firstOp = $mouvementRep->findOneBy(['promo' => $session->get('promo')], ['dateOperation' => 'ASC']);
        $date1 = $request->get("date1") ? $request->get("date1") : ($firstOp ? $firstOp->getDateOperation()->format('Y-m-d') : $request->get("date1"));
        $date2 = $request->get("date2") ? $request->get("date2") : date("Y-m-d");

        $search_devise = $request->get("search_devise") ? $deviseRep->find($request->get("search_devise")) : $deviseRep->find(1);
        $search_caisse = $request->get("search_caisse") ? $caisseRep->find($request->get("search_caisse")) : $caisseRep->findOneBy([]);

        $caisses = $caisseRep->findBy(['etablissement' => $etablissement]);
        $devises = $deviseRep->findAll();
        $modesPaiement = $modePaieRep->findAll();

        $solde_caisses = $mouvementRep->soldeCaisseParPeriodeParLieu($date1, $date2, $session->get('promo'), $etablissement, $devises, $caisses);
        $caisses_lieu = [];
        foreach ($solde_caisses as $solde) {
            foreach ($caisses as $caisse) {
                if ($solde['id_caisse'] == $caisse->getId()) {
                    $caisses_lieu[$caisse->getNom()][] = $solde;
                } 
            }
        }

        $solde_caisses_devises = $mouvementRep->soldeCaisseParDeviseParLieu($date1, $date2, $session->get('promo'), $etablissement, $devises);

        if ($request->get("search_caisse")) {
            $solde_types = $mouvementRep->soldeCaisseParPeriodeParTypeParLieuParDeviseParCaisse($date1, $date2, $session->get('promo'), $etablissement, $search_devise, $search_caisse);
        } else {
            $solde_types = $mouvementRep->soldeCaisseParPeriodeParTypeParLieuParDevise($date1, $date2, $session->get('promo'), $etablissement, $search_devise);
        }

        // Organiser les données par mode de paiement
        $solde_types_par_mode = [];
        foreach ($solde_types as $solde_type) {
            $modePaie = $solde_type['mouvement']->getModePaie()->getNom();
            $typeMouvement = $solde_type['mouvement']->getTypeMouvement();

            if (!isset($solde_types_par_mode[$typeMouvement])) {
                $solde_types_par_mode[$typeMouvement] = [];
            }

            if (!isset($solde_types_par_mode[$typeMouvement][$modePaie])) {
                $solde_types_par_mode[$typeMouvement][$modePaie] = [
                    'mouvement' => $solde_type['mouvement'],
                    'solde' => 0,
                    'nbre' => 0
                ];
            }

            $solde_types_par_mode[$typeMouvement][$modePaie]['solde'] += $solde_type['solde'];
            $solde_types_par_mode[$typeMouvement][$modePaie]['nbre'] += $solde_type['nbre'];
        }

        // Calculer les totaux pour chaque type de mouvement
        $totals = [];
        foreach ($solde_types_par_mode as $typeMouvement => $modes) {
            $totals[$typeMouvement] = [
                'nbre' => array_sum(array_column($modes, 'nbre')),
                'solde' => array_sum(array_column($modes, 'solde'))
            ];
        }

        // // Trier les types de mouvement par solde (positifs en haut, négatifs en bas)
        // foreach ($solde_types_par_mode as $typeMouvement => &$modes) {
        //     usort($modes, function($a, $b) {
        //         return $b['solde'] <=> $a['solde'];
        //     });
        // }

        // dd($solde_types_par_mode);

        return $this->render('gandaal/administration/comptabilite/bilan/index.html.twig', [
            'solde_caisses' => $caisses_lieu,
            'solde_caisses_devises' => $solde_caisses_devises,
            'solde_types' => $solde_types_par_mode,
            'totals' => $totals,
            'etablissement' => $etablissement,
            'liste_caisse' => $caisses,
            'search' => "",
            'devises' => $devises,
            'modesPaiement' => $modesPaiement,
            'date1' => $date1,
            'date2' => $date2,
            'search_devise' => $search_devise,
            'search_caisse' => $search_caisse,
            'promo' => $session->get('promo'),
        ]);
    }


    #[Route('/mouvement/caisse/{etablissement}', name: 'app_gandaal_administration_comptabilite_bilan_mouvement_caisse', methods: ['GET'])]
    public function mouvementCaisse(MouvementCaisseRepository $mouvementCaisseRep, ConfigDeviseRepository $deviseRep, Request $request, ConfigCaisseRepository $caisseRep, SessionInterface $session, Etablissement $etablissement): Response
    {
        if ($request->get("search_devise")){
            $search_devise = $deviseRep->find($request->get("search_devise"));
        }else{
            $search_devise = $deviseRep->find(1);
        }

        if ($request->get("search_caisse")){
            $search_caisse = $caisseRep->find($request->get("search_caisse"));
        }else{
            $search_caisse = $caisseRep->findOneBy([]);
        }

        if ($request->get("date1")){
            $date1 = $request->get("date1");
            $date2 = $request->get("date2");

        }else{
            $date1 = date("Y-01-01");
            $date2 = date("Y-m-d");
        }

        $pageEncours = $request->get('pageEncours', 1);
        
        $operations = $mouvementCaisseRep->listeOperationcaisseParEtablissementParCaisseParDeviseParPeriode($etablissement, $search_caisse, $search_devise, $session->get('promo'), $date1, $date2, $pageEncours, 50);

        $solde_generale = $mouvementCaisseRep->findSoldeCaisseByPromo($search_caisse , $search_devise, $session->get('promo'));
        $solde_selection = $mouvementCaisseRep->soldeCaisseParDeviseParPeriode($search_caisse, $search_devise,  $session->get('promo'), $date1, $date2);

        return $this->render('gandaal/administration/comptabilite/bilan/mouvement_caisse.html.twig', [
            'etablissement' => $etablissement,
            'liste_caisse' => $caisseRep->findBy(['etablissement' => $etablissement]),
            'date1' => $date1,
            'date2' => $date2,
            'search_devise' => $search_devise,
            'search_caisse' => $search_caisse,
            'devises' => $deviseRep->findAll(),
            'operations' => $operations,
            'solde_general' => $solde_generale,
            'solde_selection' => $solde_selection
        ]);
    }

    #[Route('/bilan/annuel/{etablissement}', name: 'app_gandaal_administration_comptabilite_bilan_annuel', methods: ['GET'])]
    public function bilanAnnuel(MouvementCaisseRepository $mouvementCaisseRep, SalaireRepository $salaireRep, PersonnelActifRepository $personnelActifRep, MouvementCollaborateurRepository $mouvementCollabRep, PaiementSalairePersonnelRepository $paiementSalaireRep, PaiementEleveRepository $paiementEleveRep, InscriptionRepository $inscriptionRep, FraisInscriptionRepository $fraisInsRep, FraisScolariteRepository $fraisScolRep, ConfigDeviseRepository $deviseRep, Request $request, ConfigCaisseRepository $caisseRep, SessionInterface $session, CursusRepository $cursusRep, Etablissement $etablissement): Response
    {
        $parcours = $cursusRep->findBy(['etablissement' => $etablissement]);
        $dataPaiements = [];

        foreach ($parcours as $cursus) {
            $totalPaiement = $paiementEleveRep->cumulPaiementsParCursus($cursus, $session->get('promo'), null, null, 'inscription');

            $nbreInscrits = $inscriptionRep->nombreInscriptionParTypeParCursus('inscription', $cursus, $session->get('promo'));

            $cumul_reste_inscription_cursus = 0;
            $reste_inscription_cursus = [];

            $inscriptions = $inscriptionRep->listeDesElevesInscritsParCursusParType('inscription', $cursus, $session->get('promo'), 'inactif');
            foreach ($inscriptions as $inscription) {
                $remiseIns = $inscription->getRemiseInscription() ?? 0;
                $classe = $inscription->getClasse();

                $frais = $fraisScolRep->findBy(['formation' => $classe->getFormation(), 'promo' => $session->get('promo')]);

                $fraisIns = $fraisInsRep->findOneBy(['cursus' => $cursus, 'description' => $inscription->getType(), 'promo' => $session->get('promo')]);

                $fraisInscription = $fraisIns->getMontant() * (1 - ($remiseIns / 100));

                $resteInscription = $fraisInscription - $paiementEleveRep->paiementEleveParType($inscription, null, null, 'inscription');

                $cumul_reste_inscription_cursus += $resteInscription;
            }

            $dataPaiements[] = [
                'cursus' => $cursus,
                'nbre' => $nbreInscrits,
                'totalPaiement' => $totalPaiement,
                'resteApayer' => $cumul_reste_inscription_cursus,
                'tauxRecouvrement' => $totalPaiement ? ($totalPaiement/($totalPaiement + $cumul_reste_inscription_cursus))*100 : 0,

            ];
        }

        $dataPaiementsReinscrits = [];
        

        foreach ($parcours as $cursus) {
            $totalPaiement = $paiementEleveRep->cumulPaiementsParCursus($cursus, $session->get('promo'), null, null, 'réinscription');
            $nbre = $inscriptionRep->nombreInscriptionParTypeParCursus('réinscription', $cursus, $session->get('promo'));

            $reste_inscription_cursus = [];

            $inscriptions = $inscriptionRep->listeDesElevesInscritsParCursusParType('réinscription', $cursus, $session->get('promo'));
            $cumul_reste_inscription_cursus = 0;
            foreach ($inscriptions as $inscription) {
                $remiseIns = $inscription->getRemiseInscription() ?? 0;
                $classe = $inscription->getClasse();

                $frais = $fraisScolRep->findBy(['formation' => $classe->getFormation(), 'promo' => $session->get('promo')]);
                $fraisIns = $fraisInsRep->findOneBy(['cursus' => $cursus, 'description' => $inscription->getType(), 'promo' => $session->get('promo')]);

                $fraisInscription = $fraisIns->getMontant() * (1 - ($remiseIns / 100));

                $resteInscription = $fraisInscription - $paiementEleveRep->paiementEleveParType($inscription, null, null, 'réinscription');

                $cumul_reste_inscription_cursus += $resteInscription;
            }
            // dd($cumul_reste_inscription_cursus);

            $reste_inscription_cursus[] = [
                'cursus' => $cursus,
                'resteInscription' => $cumul_reste_inscription_cursus,
            ];

            $dataPaiementsReinscrits[] = [
                'cursus' => $cursus,
                'nbre' => $nbre,
                'totalPaiement' => $totalPaiement,
                'resteApayer' => $cumul_reste_inscription_cursus,
                'tauxRecouvrement' => $totalPaiement ? ($totalPaiement/($totalPaiement + $cumul_reste_inscription_cursus))*100 : 0,

            ];
        }

        $dataPaiementsScolarite = [];
        


        foreach ($parcours as $cursus) {
            $totalPaiement = $paiementEleveRep->cumulPaiementsParCursus($cursus, $session->get('promo'), null, null, 'scolarite');

            $nbre = $paiementEleveRep->nombrePaiementScolariteParCursus($cursus, $session->get('promo'));

            $inscriptions = $inscriptionRep->listeDesElevesInscritsParCursus($cursus, $session->get('promo'), 'inactif');
            $cumul_reste_scolarite = 0;
            foreach ($inscriptions as $inscription) {
                $remiseScolarite = $inscription->getRemiseScolarite() ?? 0;
                $classe = $inscription->getClasse();

                $frais = $fraisScolRep->findBy(['formation' => $classe->getFormation(), 'promo' => $session->get('promo')]);

                // $totalScolarite = $fraisScolRep->montantTotalFraisScolariteParFormation($classe->getFormation(), $session->get('promo'));

                // $totalScolarite *= (1 - ($remiseScolarite / 100)); 

                $resteScolarite = $paiementEleveRep->resteScolariteEleve($inscription, $session->get('promo'), $frais, $remiseScolarite / 100);

                // dump($resteScolarite);

                $cumul_reste_scolarite += array_sum($resteScolarite);
            }
            // dd('aa');
            $dataPaiementsScolarite[] = [
                'cursus' => $cursus,
                'nbre' => $nbre,
                'totalPaiement' => $totalPaiement,
                'resteApayer' => $cumul_reste_scolarite,
                'tauxRecouvrement' => $totalPaiement ? ($totalPaiement/($totalPaiement + $cumul_reste_scolarite))*100 : 0,
            ];
        }

        // Initialiser un tableau pour les totaux par cursus
        $dataPaiementsTotal = [];

        // Cumul des données pour chaque catégorie
        foreach ([$dataPaiements, $dataPaiementsReinscrits, $dataPaiementsScolarite] as $index => $dataArray) {
            foreach ($dataArray as $data) {
                $cursus = $data['cursus'];

                // Initialiser les totaux pour ce cursus si ce n'est pas déjà fait
                if (!isset($dataPaiementsTotal[$cursus->getNom()])) {
                    $dataPaiementsTotal[$cursus->getNom()] = [
                        'nbre' => 0,
                        'totalPaiement' => 0,
                        'resteApayer' => 0,
                        'cursus' => $cursus,
                    ];
                }

                // Accumuler les valeurs pour ce cursus
                if ($index !== 2) { // Vérifier si ce n'est pas $dataPaiementsScolarite (index 2)
                    $dataPaiementsTotal[$cursus->getNom()]['nbre'] += $data['nbre'];
                }
                
                // Accumuler les autres valeurs pour tous les tableaux
                $dataPaiementsTotal[$cursus->getNom()]['totalPaiement'] += $data['totalPaiement'];
                $dataPaiementsTotal[$cursus->getNom()]['resteApayer'] += $data['resteApayer'];
            }
        }

        // Calculer le taux de recouvrement pour chaque cursus

        foreach ($dataPaiementsTotal as $cursus => $totaux) {
            $totalPaiement = $totaux['totalPaiement'];
            $cumulResteApayer = $totaux['resteApayer'];

            $dataPaiementsTotal[$cursus]['tauxRecouvrement'] = $totalPaiement 
                ? ($totalPaiement / ($totalPaiement + $cumulResteApayer)) * 100 
                : 0;

            // Ajouter le cursus à chaque entrée
            $dataPaiementsTotal[$cursus]['cursus'] = $totaux['cursus'];
        }

        // Réindexer le tableau si nécessaire (pour une utilisation ultérieure)
        $dataPaiementsTotal = array_values($dataPaiementsTotal);

        $typeMouvement = ['activite', 'recette', 'depense'];

        $paiementsParTypes = $mouvementCaisseRep->sommePaiementParType($typeMouvement, $session->get('promo'));

        $typeMouvement = ['salaire'];
        $typePersonnel= ['enseignant', 'personnel-enseignant'];

        $paiementSalaires = $paiementSalaireRep->listePaiementParEtablissementParPromo($etablissement, $session->get('promo'));

        // Initialisation d'un tableau pour stocker les paiements par type de personnel
        $groupedPaiementsSalaires = [];

        // Boucle sur les paiements pour les regrouper par type
        foreach ($paiementSalaires as $paiement) {
            $typePersonnel = $paiement->getPersonnelActif()->getType(); // Récupération du type du personnel
            
            // Si le type est "enseignant" ou "personnel-enseignant", on les regroupe sous "enseignant"
            if ($typePersonnel === 'personnel' || $typePersonnel === 'personnel-enseignant') {
                $typePersonnel = 'personnel'; // Regroupe sous un seul groupe "enseignant"
            }

            // Si le groupe pour ce type n'existe pas encore, on l'initialise
            if (!isset($groupedPaiementsSalaires[$typePersonnel])) {
                $groupedPaiementsSalaires[$typePersonnel] = [
                    'nbre' => 0, // Initialisation du compteur de paiements
                    'totalPaiement' => 0.0, // Initialisation du montant total
                    'prev' => 0.0, // Valeur initiale pour la comparaison (peut-être un montant de référence)
                ];
            }

            // Incrémentation du nombre de paiements pour ce type
            $groupedPaiementsSalaires[$typePersonnel]['nbre']++;
            
            // Ajout du montant du paiement au total
            $montantPaiement = (float)$paiement->getMontant();
            $groupedPaiementsSalaires[$typePersonnel]['totalPaiement'] += $montantPaiement;
            if ($typePersonnel == 'personnel') {
                $personnels = $personnelActifRep->listeDesPersonnelsActifParTypeParEtablissementParPromo('personnel', 'personnel-enseignant', $etablissement, $session->get('promo'));
                $cumulSalairePersonnel = 0;
                foreach ($personnels as  $value) {
                    $salaire = $salaireRep->salairePersonnelParPromo($value->getPersonnel(), $session->get('promo'));
                    $cumulSalairePersonnel += $salaire;
                }
                $groupedPaiementsSalaires[$typePersonnel]['prev'] = 12 * $cumulSalairePersonnel;

            }else{
                $promo = $session->get('promo') - 1;
                $periode = date($promo."-11-30");
                $types = ['enseignant'];
                $cumulSalaireEnseignant = $paiementSalaireRep->rectificatifCumulPaiementParTypeParPeriodeParEtablissementParPromo($types, $periode, $etablissement, $session->get('promo'));
                $groupedPaiementsSalaires[$typePersonnel]['prev'] = 10.5 * $cumulSalaireEnseignant;
                
            }

            

            // Calcul du taux de recouvrement
            $prev = $groupedPaiementsSalaires[$typePersonnel]['prev']; // Utilisation du montant "prev" pour ce type
            $tauxRecouvrement = $prev ? ($groupedPaiementsSalaires[$typePersonnel]['totalPaiement'] / $prev) * 100 : 0;

            // Mise à jour des informations dans le tableau
            $groupedPaiementsSalaires[$typePersonnel]['prev'] = $prev; // Si vous avez une logique pour mettre à jour 'prev'
            $groupedPaiementsSalaires[$typePersonnel]['tauxRecouvrement'] = $tauxRecouvrement;
        }

        $soldeGeneraux = $mouvementCollabRep->soldeGeneralParEtablissementGroupeParDevise($etablissement);
        $soldeDettes = $mouvementCollabRep->soldeDettesParEtablissementGroupeParDevise($etablissement);
        $soldeCreances = $mouvementCollabRep->soldeCreancesParEtablissementGroupeParDevise($etablissement);

        // gestion des abandons

        $inscriptions = $inscriptionRep->findBy(['statut' => 'inactif', 'promo' => $session->get('promo')]);
        $cumul_paiements_abandons = 0; // Initialisation du cumul des paiements

        foreach ($inscriptions as $inscription) {
            $paiements = $paiementEleveRep->cumulPaiementEleve($inscription, $session->get('promo'));
            
            // Assure-toi que $paiements retourne bien une valeur numérique avant de l'ajouter au cumul
            if (is_numeric($paiements)) {
                $cumul_paiements_abandons += $paiements;
            }
        }


    
        $abandons = $inscriptionRep->findBy(['statut' => 'inactif', 'promo' => $session->get('promo'), 'etablissement' => $etablissement]);
        // Tableau pour regrouper par classe
        $groupedByClass = [];

        foreach ($abandons as $inscription) {
            $classeNom = $inscription->getClasse()->getNom();
        
            // Créer le groupe de la classe s'il n'existe pas
            if (!isset($groupedByClass[$classeNom])) {
                $groupedByClass[$classeNom] = [
                    'inscriptions' => [],
                ];
            }
        
            // Récupérer le paiement pour cette inscription
            $paiements = $paiementEleveRep->cumulPaiementEleve($inscription, $session->get('promo'));
        
            // Ajouter l'inscription avec son paiement
            $groupedByClass[$classeNom]['inscriptions'][] = [
                'inscription' => $inscription,
                'paiement' => $paiements,
            ];
        }
        uksort($groupedByClass, 'strnatcmp');
        
        return $this->render('gandaal/administration/comptabilite/bilan/bilan_annuel_v1.html.twig', [
            'etablissement' => $etablissement,
            'cursus' => $parcours, 
            'dataPaiements' => $dataPaiements, 
            'dataPaiementsReinscrits' => $dataPaiementsReinscrits, 
            'dataPaiementsScolarite' => $dataPaiementsScolarite, 
            'dataPaiementsTotal' => $dataPaiementsTotal, 
            'paiementsParTypes' => $paiementsParTypes,
            'paiementSalaires' => $groupedPaiementsSalaires,
            'soldeGeneraux' => $soldeGeneraux,
            'soldeDettes' => $soldeDettes,
            'soldeCreances' => $soldeCreances,
            'cumulPaiementAbandons' => $cumul_paiements_abandons,
            'groupedByClass' => $groupedByClass,
            'promo' => $session->get('promo'),
        ]);
    }



}
