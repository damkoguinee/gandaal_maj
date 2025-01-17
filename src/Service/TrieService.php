<?php

namespace App\Service;

use App\Entity\Inscription;

class TrieService
{
    /**
     * Trie une collection d'inscriptions par prénom, puis nom, puis matricule.
     *
     * @param Inscription[] $inscriptions
     * @return Inscription[]
     */
    public function trieInscriptions(array $inscriptions): array
    {
        usort($inscriptions, function (Inscription $a, Inscription $b) {
            $prenomCompare = strcmp($a->getEleve()->getPrenom(), $b->getEleve()->getPrenom());
            if ($prenomCompare !== 0) {
                return $prenomCompare;
            }

            $nomCompare = strcmp($a->getEleve()->getNom(), $b->getEleve()->getNom());
            if ($nomCompare !== 0) {
                return $nomCompare;
            }

            return strcmp($a->getEleve()->getMatricule(), $b->getEleve()->getMatricule());
        });

        return $inscriptions;
    }
}