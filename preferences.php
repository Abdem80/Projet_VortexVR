<?php
/**
 * preferences.php
 *
 * Page de gestion des préférences utilisateur de la boutique VortexVR.
 *
 * Rôle dans le projet :
 *   Page réservée à la personnalisation de l'expérience utilisateur.
 *   Dans sa version actuelle, elle affiche un message de bienvenue et
 *   une introduction à l'univers VR. Elle est destinée à accueillir
 *   des fonctionnalités futures telles que la gestion des thèmes,
 *   les préférences de langue ou les filtres de catalogue par défaut.
 *
 * Structure :
 *   - Inclusion de header.php (session, CSS, nav).
 *   - Section HTML statique (en attente d'implémentation des préférences).
 *   - Inclusion de footer.php (fermeture des balises HTML).
 *
 * @project VortexVR – Boutique de casques VR
 */

// Chargement de l'en-tête HTML commun (session, autoloader, CSS, nav).
require_once "inc/header.php";
?>

<!-- =====================================================
     PAGE PRÉFÉRENCES : contenu en attente d'implémentation
     ===================================================== -->
<section>
    <div>
        <h2>Bienvenue sur la page de la gestion des préférences</h2>

        <!-- Description introductive de la boutique (texte statique provisoire) -->
        <p>
            Explorez l'univers de la réalité virtuelle, découvrez une sélection de casques
            performants et plongez dans une expérience immersive unique.
        </p>
    </div>
</section>

<?php
// Fermeture du <main>, affichage du pied de page HTML.
require_once "inc/footer.php";
?>
