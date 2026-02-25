/**
 * js/script.js – Scripts interactifs de la boutique VortexVR
 *
 * Rôle dans le projet :
 *   Fichier JavaScript unique chargé sur toutes les pages (via <script defer>
 *   dans header.php). Chaque bloc est conditionné par l'URL courante afin
 *   de n'activer que le code pertinent à la page en cours.
 *
 * Sections :
 *   1. index.php   – Carrousel de produits (boutons ◄ ►)
 *   2. panier.php  – Validation checkout, confirmation suppression article
 *   3. checkout.php– Contrôles de quantité (+/-), recalcul des totaux+taxes,
 *                    sauvegarde/restauration des quantités via cookies
 *   4. wallet.php  – Affichage des données depuis cookies, confirmation d'achat
 *   5. Utilitaires – setCookie() et getCookie() partagés entre pages
 *   6. compte.php  – Génération et validation du CAPTCHA numérique
 *
 * Utilisation des cookies :
 *   Les totaux et quantités calculés sur checkout.php sont persistés dans
 *   des cookies de session (expiration 1 jour) pour être relus sur wallet.php.
 *   Cela permet d'afficher des valeurs cohérentes même après rechargement.
 *
 * @project VortexVR – Boutique de casques VR
 */


/* =====================================================
   SECTION 1 : index.php — Carrousel des 3 casques vedettes
   Auteur : William
   ===================================================== */

/**
 * Carrousel page d'accueil.
 * Affiche un seul casque à la fois et permet de naviguer avec les boutons
 * "avant" (◄) et "apres" (►). La navigation est circulaire (boucle infinie).
 */
if (document.URL.includes("index.php")) {

    // Attachement des écouteurs sur les boutons de navigation.
    document.getElementById('avant').addEventListener('click', avant);
    document.getElementById('apres').addEventListener('click', apres);

    let num = 0; // Index du casque actuellement affiché (commence à 0).

    let casques = document.querySelectorAll('.acc-produit');
    let totalCasque = casques.length;

    // Masquer tous les casques sauf le premier au chargement.
    for (let i = 0; i < totalCasque; i++) {
        if (i !== 0) {
            casques[i].style.display = 'none';
        }
    }

    /** Affiche le casque suivant (avance dans le carrousel). */
    function apres() {
        casques[num].style.display = 'none';
        num = (num + 1) % totalCasque; // Modulo pour boucler au début
        casques[num].style.display = 'block';
    }

    /** Affiche le casque précédent (recule dans le carrousel). */
    function avant() {
        casques[num].style.display = 'none';
        num = (num - 1 + totalCasque) % totalCasque; // +totalCasque évite les valeurs négatives
        casques[num].style.display = 'block';
    }
}


/* =====================================================
   SECTION 2 : panier.php — Validation et suppression
   Auteur : William
   ===================================================== */

/**
 * Logique interactive du panier :
 * - sendCheckout()      : vérifie qu'au moins un article est coché avant de passer en commande.
 * - validerSupprimer()  : demande confirmation avant de supprimer un article.
 */
if (document.URL.includes("panier.php")) {

    // Écoute du clic sur "Passer à la commande".
    document.getElementById('commandePanier').addEventListener('click', sendCheckout);

    // Écoute du clic sur chaque bouton "Supprimer".
    document.querySelectorAll(".supprimerArticle").forEach(bouton => {
        bouton.addEventListener("click", validerSupprimer);
    });

    /**
     * Soumet le formulaire de checkout avec les articles cochés.
     * Affiche une alerte si aucune case n'est cochée.
     * Affiche une boîte de confirmation avant soumission.
     */
    function sendCheckout() {
        let checkboxes = document.querySelectorAll('.produit-checkbox:checked');

        if (checkboxes.length === 0) {
            alert('Il vous faut au minimum un produit de Sélectionner!');
            return;
        }

        // Création dynamique d'un formulaire POST vers checkout.php.
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = 'checkout.php';

        // Ajout de chaque article coché en champ caché.
        checkboxes.forEach(checkbox => {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'articleSelectionner[]';
            input.value = checkbox.value;
            form.appendChild(input);
        });

        document.body.appendChild(form);

        // Confirmation utilisateur avant soumission.
        let validation = confirm("Voulez-vous vraiment poursuivre avec les produits sélectionnés ?");
        if (validation) {
            form.submit();
        }
    }

    /**
     * Demande confirmation avant la suppression d'un article du panier.
     * Si l'utilisateur annule, empêche la soumission du formulaire.
     *
     * @param {Event} event Événement de clic sur le bouton "Supprimer".
     */
    function validerSupprimer(event) {
        let validation = confirm("Voulez-vous vraiment enlever cet article de votre panier ?");
        if (!validation) {
            event.preventDefault(); // Annule le submit du formulaire parent
        }
    }
}


/* =====================================================
   SECTION 3 : checkout.php — Contrôle des quantités et totaux
   Auteur : Abdoulaye
   ===================================================== */

/**
 * Sur la page checkout.php :
 * - Boutons +/- permettent d'ajuster les quantités visuellement (sans rechargement).
 * - Les totaux (montant, TPS, TVQ, total final) sont recalculés à la volée.
 * - Les quantités et le total sont sauvegardés dans des cookies pour wallet.php.
 * - Au chargement, les quantités précédemment sauvées sont restaurées.
 */
if (document.URL.includes("checkout.php")) {

    // Attache les écouteurs sur tous les boutons +/- de la grille de produits.
    let buttons = document.querySelectorAll(".qty-btn");
    for (let i = 0; i < buttons.length; i++) {
        buttons[i].addEventListener("click", gererQuantity);
    }

    /**
     * Gère le clic sur un bouton + ou - d'un article.
     * Met à jour l'affichage de la quantité et déclenche le recalcul du récapitulatif.
     *
     * @param {Event} e Événement de clic sur le bouton +/-.
     */
    function gererQuantity(e) {
        let btn = e.target;
        let card = btn.closest(".checkout-product-card");  // Carte produit parente
        let qteEl = card.querySelector(".product-quantity"); // Élément affichant la quantité
        let qte = lireQteDepuisTexte(qteEl.textContent);  // Lecture de la quantité actuelle

        if (btn.textContent === "+") { qte++; }
        if (btn.textContent === "-" && qte > 1) { qte--; } // Min 1 article

        ecrireQte(qteEl, qte);
        recalculerRecap();
        sauverQtesParProduit();
    }

    // Confirmation avant soumission du formulaire de commande.
    document.querySelector(".checkout-action").addEventListener("submit", validerPanier);

    /**
     * Demande confirmation avant validation du panier ; annule si l'utilisateur refuse.
     *
     * @param {Event} e Événement de soumission du formulaire.
     */
    function validerPanier(e) {
        recalculerRecap();
        sauverQtesParProduit();

        let valider = confirm("Voulez-vous confirmer la validation de votre panier ?");
        if (!valider) {
            e.preventDefault(); // Empêche l'envoi du formulaire
        }
    }

    /**
     * Arrondit un nombre à 2 décimales (évite les erreurs de flottants).
     *
     * @param {number} n Nombre à arrondir.
     * @returns {number}
     */
    function round2(n) { return Math.round(n * 100) / 100; }

    /**
     * Convertit une chaîne de montant affichée ("1 299,99 $") en nombre flottant.
     *
     * @param {string} txt Texte du montant affiché.
     * @returns {number}
     */
    function lireMontant(txt) {
        return parseFloat(txt.replace("$", "").replace(/\s/g, "").replace(",", ".")) || 0;
    }

    /**
     * Formate un nombre en chaîne monétaire locale (fr-CA : virgule, espace).
     *
     * @param {number} n Montant à formater.
     * @returns {string}
     */
    function ecrireMontant(n) {
        return n.toLocaleString("fr-CA", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    /**
     * Extrait la quantité depuis le texte "Quantité dans le panier : X".
     *
     * @param {string} txt Texte contenant la quantité.
     * @returns {number}
     */
    function lireQteDepuisTexte(txt) {
        let match = txt.match(/(\d+)\s*$/);
        return match ? parseInt(match[1], 10) : 1;
    }

    /**
     * Met à jour le texte de l'élément de quantité avec la nouvelle valeur.
     *
     * @param {HTMLElement} el  Élément DOM à mettre à jour.
     * @param {number}      qte Nouvelle quantité.
     */
    function ecrireQte(el, qte) {
        el.textContent = "Quantité dans le panier : " + qte;
    }

    // Références aux cellules du panneau récapitulatif (ordre important).
    let rows = document.querySelectorAll(".checkout-box .Pannel-div");
    let nbEl = rows[0].querySelector(".Pannel-value"); // Nombre d'articles
    let montantEl = rows[1].querySelector(".Pannel-value"); // Montant articles
    let livraisonEl = rows[2].querySelector(".Pannel-value"); // Livraison (fixe)
    let tpsEl = rows[3].querySelector(".Pannel-value"); // TPS calculée
    let tvqEl = rows[4].querySelector(".Pannel-value"); // TVQ calculée
    let totalEl = rows[5].querySelector(".Pannel-value"); // Total final

    /**
     * Recalcule et met à jour le panneau récapitulatif en fonction des quantités affichées.
     * Calcule : TPS (5%), TVQ (9,975%), total = articles + livraison + taxes.
     * Sauvegarde le total et la quantité dans des cookies pour wallet.php.
     */
    function recalculerRecap() {
        let cards = document.querySelectorAll(".checkout-product-card");
        let nbArticles = 0, montantArticles = 0;

        for (let i = 0; i < cards.length; i++) {
            let qteEl = cards[i].querySelector(".product-quantity");
            let prixEl = cards[i].querySelector(".product-price");
            let qte = lireQteDepuisTexte(qteEl.textContent);
            let prix = lireMontant(prixEl.textContent);

            nbArticles += qte;
            montantArticles += qte * prix;
        }

        montantArticles = round2(montantArticles);

        let livraison = lireMontant(livraisonEl.textContent); // Lu depuis la page (9,99$)
        let tps = round2(montantArticles * 0.05);       // 5%
        let tvq = round2(montantArticles * 0.09975);    // 9,975%
        let totalFinal = round2(montantArticles + livraison + tps + tvq);

        // Mise à jour du DOM du panneau récapitulatif.
        nbEl.textContent = nbArticles;
        montantEl.textContent = ecrireMontant(montantArticles) + " $";
        tpsEl.textContent = ecrireMontant(tps) + " $";
        tvqEl.textContent = ecrireMontant(tvq) + " $";
        totalEl.textContent = ecrireMontant(totalFinal) + " $";

        // Sauvegarde du total et des quantités dans des cookies (durée 1 jour).
        setCookie("checkout_qte", nbArticles, 1);
        setCookie("checkout_total", totalFinal.toFixed(2), 1);
    }

    /**
     * Sauvegarde les quantités par produit dans un cookie JSON.
     * Clé : nom du casque, valeur : quantité.
     */
    function sauverQtesParProduit() {
        let cards = document.querySelectorAll(".checkout-product-card");
        let map = {};

        cards.forEach(card => {
            let nom = card.querySelector(".product-name").textContent.trim();
            let qteEl = card.querySelector(".product-quantity");
            map[nom] = lireQteDepuisTexte(qteEl.textContent);
        });

        setCookie("checkout_qtes", JSON.stringify(map), 1);
    }

    /**
     * Restaure les quantités par produit depuis le cookie JSON.
     * Appelé au chargement de la page pour préserver les quantités
     * modifiées par l'utilisateur en cas de rechargement.
     */
    function restaurerQtesParProduit() {
        let raw = getCookie("checkout_qtes");
        let map = JSON.parse(raw);
        let cards = document.querySelectorAll(".checkout-product-card");

        cards.forEach(card => {
            let nom = card.querySelector(".product-name").textContent.trim();
            if (map[nom] != null) {
                let qteEl = card.querySelector(".product-quantity");
                ecrireQte(qteEl, parseInt(map[nom], 10));
            }
        });
    }

    // Initialisation au chargement : restaurer → recalculer → sauver.
    restaurerQtesParProduit();
    recalculerRecap();
    sauverQtesParProduit();
}


/* =====================================================
   SECTION 4 : wallet.php — Affichage des données cookie et confirmation
   Auteur : Abdoulaye
   ===================================================== */

/**
 * Sur wallet.php :
 * - Lit les cookies checkout_qte et checkout_total écrits par checkout.php.
 * - Met à jour les valeurs affichées dans le récapitulatif du wallet.
 * - Affiche une boîte de confirmation avant le débit du solde.
 */
if (document.URL.includes("wallet.php")) {

    let qteCookie = getCookie("checkout_qte");
    let totalCookie = getCookie("checkout_total");

    // Références aux spans à mettre à jour.
    let rows = document.querySelectorAll(".wallet-row");
    let qteSpan = rows[1].querySelector("span:last-child");
    let totalSpan = document.querySelector(".wallet-total span:last-child");

    // Affichage de la quantité depuis le cookie (si disponible).
    if (qteCookie !== "") {
        qteSpan.textContent = qteCookie;
    }

    // Affichage du total depuis le cookie (formaté en décimale française).
    if (totalCookie !== "") {
        let total = parseFloat(totalCookie).toFixed(2);
        totalSpan.textContent = total.replace(".", ",") + " $";
    }

    // Confirmation avant validation de l'achat.
    let formAchat = document.querySelector(".wallet-actions form");
    formAchat.addEventListener("submit", confirmerAchat);

    /**
     * Demande confirmation avant de soumettre le formulaire de paiement.
     *
     * @param {Event} e Événement de soumission du formulaire.
     */
    function confirmerAchat(e) {
        let ok = confirm("Confirmer la validation de l'achat ?");
        if (!ok) {
            e.preventDefault(); // Annule si l'utilisateur refuse
        }
    }
}


/* =====================================================
   SECTION 5 : Utilitaires — Gestion des cookies
   Partagé entre checkout.php et wallet.php
   ===================================================== */

/**
 * Crée ou met à jour un cookie dans le navigateur.
 *
 * @param {string} cname   Nom du cookie.
 * @param {*}      cvalue  Valeur à stocker.
 * @param {number} exdays  Durée de vie en jours.
 */
function setCookie(cname, cvalue, exdays) {
    const d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000)); // Calcul de l'expiration
    let expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

/**
 * Lit la valeur d'un cookie par son nom.
 *
 * @param {string} cname Nom du cookie à lire.
 * @returns {string} Valeur du cookie, ou "" s'il n'existe pas.
 */
function getCookie(cname) {
    let name = cname + "=";
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(';');

    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        // Suppression des espaces de tête.
        while (c.charAt(0) == ' ') { c = c.substring(1); }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return ""; // Cookie non trouvé
}


/* =====================================================
   SECTION 6 : compte.php — CAPTCHA numérique
   ===================================================== */

/**
 * Génère et valide un CAPTCHA numérique à 5 chiffres sur la page compte.php.
 *
 * Fonctionnement :
 *   1. generateCaptcha() génère un code à 5 chiffres aléatoires, l'affiche
 *      dans #CaptchaDiv et stocke la valeur dans l'input caché #txtCaptcha.
 *   2. checkform() compare la saisie de l'utilisateur avec le code stocké :
 *      - Succès : révèle #protectedContent (formulaire de profil).
 *      - Échec  : régénère un nouveau CAPTCHA.
 */
var code = ""; // Variable globale contenant le code CAPTCHA courant

if (window.location.pathname.endsWith("compte.php")) {

    /**
     * Génère un nouveau code CAPTCHA aléatoire à 5 chiffres (1–9, pas de zéro).
     * Met à jour l'affichage dans la page et la valeur de référence.
     */
    function generateCaptcha() {
        code = "";
        for (var i = 0; i < 5; i++) {
            code = code + (Math.floor(Math.random() * 9) + 1); // Chiffres 1 à 9
        }
        document.getElementById("txtCaptcha").value = code; // Valeur cachée de référence
        document.getElementById("CaptchaDiv").innerHTML = code; // Affichage visible
    }

    /**
     * Valide la saisie du CAPTCHA par l'utilisateur.
     *
     * Appelé via l'attribut onsubmit du formulaire dans compte.php.
     * Retourne toujours false pour empêcher la soumission réelle du formulaire
     * (la validation se fait uniquement côté client).
     *
     * @param {HTMLFormElement} theform Le formulaire CAPTCHA.
     * @returns {boolean} false (toujours, pour bloquer la soumission HTTP).
     */
    function checkform(theform) {
        var user = document.getElementById("CaptchaInput").value;

        if (user === "") {
            alert("Met le captcha !");
            return false;
        }

        if (user != code) {
            // CAPTCHA incorrect : effacer et régénérer.
            alert("Faux captcha !");
            document.getElementById("CaptchaInput").value = "";
            generateCaptcha();
            return false;
        }

        // CAPTCHA correct : révéler le formulaire de profil protégé.
        document.getElementById("protectedContent").style.display = "block";

        // Masquer les éléments du CAPTCHA (plus besoin de les afficher).
        document.getElementById("CaptchaInput").style.display = "none";
        document.getElementById("txtCaptcha").style.display = "none";
        document.getElementById("CaptchaDiv").style.display = "none";

        return false; // Toujours false pour ne pas soumettre le formulaire
    }

    // Génération automatique du CAPTCHA au chargement de la page.
    window.onload = function () {
        generateCaptcha();
    };
}