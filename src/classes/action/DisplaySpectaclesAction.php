<?php
namespace iutnc\nrv\action;

use iutnc\nrv\repository\NrvRepository;
use iutnc\nrv\festival\Spectacle;
use iutnc\nrv\festival\Soiree;
use iutnc\nrv\render\SpectacleRenderer;
use iutnc\nrv\render\SoireeRenderer;
use iutnc\nrv\auth\Authz;


class DisplaySpectaclesAction extends Action {

    // public pour display action
    public static function createSpec($sp, $repo,$choixRendu): string {
        $stylenom = $repo->getStyleNom($sp['idStyle']);
        $images = $repo->getImagesBySpectacleId($sp['idSpectacle']);
        $artistes = $repo->getArtisteBySpectacleId($sp['idSpectacle']);
        $s = new Spectacle($sp['nomSpectacle'], $sp['horaireDebut'], $sp['horaireFin'], $stylenom, $sp['description'], $artistes, $images, $sp['lienAudio'], $sp['statut']);
        $renderer = new SpectacleRenderer($s);
        return $renderer->render($choixRendu);
    }

    private static function getSelectOptions(array $options, string $nom, string $val, string $valchoisis): string {
        $html = "<label for='$nom'>Choisir un $nom :</label>";
        $html .= "<select name='$nom' id='$nom' onchange='this.form.submit()'>";
        $html .= "<option value=''>Sélectionner</option>";
        foreach ($options as $option) {
            $valeur = $option[$val];
            $valeuroption = $valeur;
            
            if ($nom === 'lieu') { // pour lieu on utilise l'id au lieu du nom 
                $valeuroption = $option['idLieu'];
            }
            $html .= "<option value='{$valeuroption}'" . ($valchoisis == $valeuroption ? ' selected' : '') . ">{$valeur}</option>";
        }
        $html .= "</select>";
        return $html;
    }
    

    private static function getOptions($repo): string {
        $trichoix = filter_var($_GET['trie'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
        $html = "<label for='trie'>Trier par :</label>";
        $html .= "<form method='GET' action='' id='filterForm'>";
        $html .= "<select name='trie' id='trie' onchange='this.form.submit()'>";
        $html .= "<option value=''>Pas de filtre</option>";
        $html .= "<option value='style'" . ($trichoix === 'style' ? ' selected' : '') . ">Style</option>";
        $html .= "<option value='date'" . ($trichoix === 'date' ? ' selected' : '') . ">Date</option>";
        $html .= "<option value='lieu'" . ($trichoix === 'lieu' ? ' selected' : '') . ">Lieu</option>";
        $html .= "<option value='preferences'" . ($trichoix === 'preferences' ? ' selected' : '') . ">Préférences</option>";

        $html .= "</select>";

        if ($trichoix === 'style') {
            $styles = $repo->getAllStyles();
            $stylechoix = filter_var($_GET['style'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $html .= self::getSelectOptions($styles, 'style', 'nomStyle', $stylechoix);
        } elseif ($trichoix === 'date') {
            $dates = $repo->getAllDates();
            $datechoix = filter_var($_GET['date'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $html .= self::getSelectOptions($dates, 'date', 'dateSoiree', $datechoix);
        } elseif ($trichoix === 'lieu') {
            $lieux = $repo->getAllLieux();
            $lieuchoix = filter_var($_GET['lieu'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
            $html .= self::getSelectOptions($lieux, 'lieu', 'lieuAdresse', $lieuchoix);
        }
        
        $html .= "</form>";
        return $html;
    }

    private function renderFilteredSpectacles($repo, array $spectacles, string $trie, ?string $choix): string {
        $html = '';
        foreach ($spectacles as $sp) {
            $valide = true;
            switch ($trie) {
                case 'style':
                    if ($choix !== null) {
                        $valide = strtolower($choix) === strtolower($repo->getStyleNom($sp['idSpectacle']));
                    }
                    break;
                case 'date':
                    if ($choix !== null) {
                        $liste_spec_date = $repo->getAllSpecAtDate($choix);
                        $valide = in_array($sp['idSpectacle'], $liste_spec_date);
                    }
                    break;
                case 'lieu':
                    if ($choix !== null) {
                        $liste_spec_lieu = $repo->getAllSpecAtLieu($choix);
                        $valide = in_array($sp['idSpectacle'], $liste_spec_lieu);
                    }
                    break;
                case 'preferences':
                    if (!empty($_COOKIE['preferences'])) {
                        $preferences = explode(',', $_COOKIE['preferences']);
                        $valide = in_array($sp['idSpectacle'], $preferences);
                    }
                    else{
                        $valide = false;
                    }
                    break;

                default:
                    $valide = true;
                    break;
            }
            
            if ($valide) {
                $html .= self::createSpec($sp, $repo,2) . "<li><a href='?action=programme&id={$sp['idSpectacle']}'>Plus d'info</a></li>";
            }
            
        }
        return $html;
    }

    protected function get(): string {
        $repo = NrvRepository::getInstance();
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        $id = ($id === false) ? null : $id;
        $trie = filter_var($_GET['trie'] ?? null, FILTER_SANITIZE_SPECIAL_CHARS);
        $choix = null;
        if ($trie && isset($_GET[$trie])) {
            $choix = filter_var($_GET[$trie], FILTER_SANITIZE_SPECIAL_CHARS);
            $choix = ($choix === '') ? null : $choix;
        }
        $spectacles = $repo->getAllSpectacles();


        if (!$id) {
            $html = "<h2>Spectacles Disponibles</h2>";
            $html .= "<form method='GET' action=''>";
            $html .= "<input type='hidden' name='action' value='programme'>";
            $html .= self::getOptions($repo);
            $html .= "</form><ul>";

            $html .= $this->renderFilteredSpectacles($repo, $spectacles, $trie, $choix);
            return $html;

        } else {

            $html = <<<HTML
            <script>
            document.addEventListener("DOMContentLoaded", () => {
                const preferences = getPreferences();
                const spectacleId = document.getElementById('pref').dataset.id;
                if (preferences.includes(spectacleId)) {
                    document.getElementById('pref').textContent = "Retirer des préférences";
                } else {
                    document.getElementById('pref').textContent = "Ajouter aux préférences";
                }
            });
            </script>
            HTML;
            
            $messagebut = "Ajouter aux préférences";
            if (isset($_COOKIE['preferences']) && $_COOKIE['preferences'] !== "") {
                $liste_pref = explode(",", $_COOKIE['preferences']);
                if (in_array($id, $liste_pref)){
                    $messagebut = "Retirer des préférences";
                }
            }
            $html .= "<button id='pref' data-id='{$id}' onclick='switchPrefs({$id})'>{$messagebut}</button>";
            $user = Authz::checkRole(50); 
            if (!is_string($user)) {
                $html .= "<button><a href='?action=modify-spectacle&id={$id}'\">Modifier ce spectacle</a></button>";
            }

            $html .= "<h2>Infos : </h2><ul>";
            // $sp = $spectacles[$id - 1]; // GROSSE ERREUR L'ELEMENT 1 N' A PAS FORCEMENT L'ID 1 ERREUR LOGIQUEE FAAUT FIX
            foreach ($spectacles as $spectacle) {
                if ($spectacle['idSpectacle'] == $id) {
                    $sp = $spectacle;
                    break;
                }
            }
            
            $html .= self::createSpec($sp, $repo,1);


            $soirees = $repo->getAllSoireeForSpec($sp['idSpectacle']);
            $html .= "<h1>Dispo dans les soirées suivantes : </h1>";
            foreach ($soirees as $soiree) {
                $lieuNom = $repo->getLieuNom($soiree['idLieu']);
                $s = new Soiree($soiree['nomSoiree'], $soiree['dateSoiree'], $lieuNom, $soiree['thematique'], $soiree['horaire'], floatval($soiree['tarif']));
                $renderer = new SoireeRenderer($s);
                $html .= $renderer->render(1);
                $html .= $this->getNavigationLinks($repo->getStyleNom($sp['idSpectacle']), $soiree['idLieu'], $soiree['dateSoiree'],$soiree['idSoiree']);
            }

            // $html .= $this->getNavigationLinks($stylenom, "dd", "dd");
            return $html;
        }
    }

    private function getNavigationLinks(string $style, string $lieu, string $date,string $idSoiree): string {
        $styleLink = "?action=programme&trie=style&style=" . urlencode($style);
        $lieuLink = "?action=programme&trie=lieu&lieu=" . urlencode($lieu);
        $dateLink = "?action=programme&trie=date&date=" . urlencode($date);
        $soireeLink = "?action=list-soirees&id=" . urlencode($idSoiree);

        return "<div class='navigation-links'>
                    <a href='$lieuLink'>Voir les spectacles au même lieu</a> |
                    <a href='$styleLink'>Voir les spectacles du même style</a> |
                    <a href='$dateLink'>Voir les spectacles à la même date</a> |
                    <a href='$soireeLink'>Plus d'infos sur cette soirée</a>
                </div>";
    }
}
