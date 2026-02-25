<?php
/**
 * inc/autoloader.php
 *
 * Enregistre un chargeur automatique de classes (PSR-like) pour la boutique VortexVR.
 *
 * Au lieu de devoir écrire un require_once par classe dans chaque page, il
 * suffit d'inclure ce fichier une seule fois (via header.php). PHP appellera
 * automatiquement loadClass() la première fois qu'une classe non encore
 * chargée est instanciée.
 *
 * Convention : chaque classe ClassName doit se trouver dans
 * le fichier classe/ClassName.php (sensible à la casse).
 *
 * @project VortexVR – Boutique de casques VR
 */

declare(strict_types=1);

/**
 * Charge automatiquement le fichier d'une classe depuis le dossier classe/.
 *
 * Cette fonction est enregistrée auprès de spl_autoload_register() et est
 * appelée par PHP chaque fois qu'une classe inconnue est utilisée.
 *
 * @param string $className Nom de la classe à charger (ex. "ClientManager").
 * @return void
 */
function loadClass(string $className): void
{
    // Construction du chemin absolu vers le fichier de la classe.
    // __DIR__ retourne le dossier du fichier courant (inc/), on remonte d'un niveau.
    $fichier = __DIR__ . '/../classe/' . $className . '.php';

    // On vérifie l'existence du fichier avant d'inclure
    // pour éviter un fatal error si la classe n'est pas dans classe/.
    if (file_exists($fichier)) {
        require_once $fichier;
    }
}

// Enregistrement de la fonction comme autoloader PHP.
// À partir de ce point, toute instanciation d'une classe inconnue
// déclenchera automatiquement loadClass().
spl_autoload_register('loadClass');