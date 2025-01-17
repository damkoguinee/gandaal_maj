<?php
namespace App\Twig;

use App\Entity\User;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

class Extension extends AbstractExtension{
    private $parametres;
    public function __construct(ParameterBagInterface $parametres)
    {
        $this->parametres=$parametres;
    }
    public function extrait(string $texte = null, int $longeueur){
        return strlen($texte) > $longeueur ? substr($texte, 0, $longeueur). "[...]" : $texte;
    }

    public function estNumerique ($variable): bool
    {
        return is_numeric($variable);
    }

    public function roles(User $user): string
    {
        $text ="";
        foreach ($user->getRoles() as $role) {
            $text.= $text ? ", " :"";
            switch ($role){
                case 'ROLE_ADMIN':
                    $text .= "Administrateur";
                    break;
                
                case 'ROLE_ENSEIGNANT':
                    $text .= "Enseignant";
                    break;
                
                case 'ROLE_PERSONNEL':
                    $text .= "Personnel";
                    break;
                case 'ROLE_ELEVE':
                    $text .= "Eleve";
                    break;
                
                case 'ROLE_STOCK':
                    $text .= "Stock";
                    break;

                case 'ROLE_GESTIONNAIRE':
                    $text .= "Gestionnaire";
                    break;
                case 'ROLE_RESPONSABLE':
                    $text .= "Responsable";
                    break;
                case 'ROLE_ACTIONNAIRE':
                    $text .= "Actionnaire";
                    break;
                case 'ROLE_DEVELOPPEUR':
                    $text .= "Dévéloppeur";
                    break;
                case 'ROLE_SUPPRESSION':
                    $text .= "Suppression";
                    break;

                case 'ROLE_MODIFICATION':
                    $text .= "Modification";
                    break;
                default:
                    $text .="";
                    break;
            }
        }
        return $text;
    }

    public function baliseImg($imageName, $classes = "", $alt ="")
    {
        // dd($this->parametres->get("chemin_images"));
        $balise ="<img src='".$this->parametres->get("chemin_images")."$imageName' class='$classes' alt='$alt'>";
        return $balise;
    }

    public function upperAccentsFilter($value)
    {
        // Convertir les caractères accentués en majuscules
        $value = mb_strtoupper($value, 'UTF-8');
        $value = str_replace('é', 'É', $value); // Transformer 'é' en 'É' si nécessaire
        
        return $value;
    }

    public function strtoupperFilter($value)
    {
        $value = mb_strtoupper($value, 'UTF-8');
        // $value = str_replace('é', 'É', $value); 
        return $value;
    }

    public function strtolowerFilter($value)
    {
        return strtolower($value);
    }

    public function ucwordsFilter($value)
    {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }
    public function ucfirstFilter($value)
    {
        return ucfirst($value);
    }

    public function exposantClasseFilter(string $text): string
    {
        // Remplacer les suffixes "ère" et "ème" par leur version en exposant
        $text = str_replace('ère', '<sup>ère</sup>', $text);
        $text = str_replace('ème', '<sup>ème</sup>', $text);
        $text = str_replace('Ère', '<sup>ère</sup>', $text);
        $text = str_replace('Ème', '<sup>ème</sup>', $text);
        $text = str_replace('ÈRE', '<sup>ère</sup>', $text);
        $text = str_replace('ÈME', '<sup>ème</sup>', $text);

        $text = str_replace('ere', '<sup>ère</sup>', $text);
        $text = str_replace('eme', '<sup>ème</sup>', $text);
        $text = str_replace('Ere', '<sup>ère</sup>', $text);
        $text = str_replace('Eme', '<sup>ème</sup>', $text);
        $text = str_replace('ERE', '<sup>ère</sup>', $text);
        $text = str_replace('EME', '<sup>ème</sup>', $text);
        
        return $text;
    }

    public function calculateAgeFilter(\DateTime $birthDate)
    {
        $currentDate = new \DateTime();
        $ageInterval = $currentDate->diff($birthDate);

        $ageData = [
            'age' => $ageInterval->y,
            'isBaby' => $ageInterval->m < 12,
            'ageInMonths' => $ageInterval->m,
            'ageInWeeks' => $ageInterval->m * 4 + $ageInterval->d / 7, // Convertit les jours en semaines
        ];

        return $ageData;
    }

    /**
     * Pour ajouter une fonction à Twig, on utilise la méthode getFunctions 
     * Pour ajouter un filtre à Twig, on utilise la méthode getFilters 
     * Pour ajouter un test à Twig, on utilise la méthode getTest 
     */

     public function getFunctions()
     {
        return [
            new TwigFunction("extrait", [$this, "extrait"])
        ];
     }

    /* */
    public function getFilters()
    {
        return [
            new TwigFilter("extrait", [$this, "extrait"]),
            new TwigFilter("autorisations", [$this, "roles"]),
            new TwigFilter("img", [$this, "baliseImg"]),
            new TwigFilter('upper_accents', [$this, 'upperAccentsFilter']),
            new TwigFilter("strtoupper", [$this, "strtoupperFilter"]),
            new TwigFilter("ucwords", [$this, "ucwordsFilter"]),
            new TwigFilter("ucfirst", [$this, "ucfirstFilter"]),
            new TwigFilter("strtolower", [$this, "strtolowerFilter"]),
            new TwigFilter("calculateAge", [$this, "calculateAgeFilter"]),
            new TwigFilter("exposantClasse", [$this, "exposantClasseFilter"]),

            
        ];
    }

    public function getTests()
    {
        return [
            new TwigTest("numeric", [$this, "estNumerique"])
        ];
    }
}