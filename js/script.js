/*Mise en place des fonctionnalite java script pour la page index ou acceuil*/ 

if(document.URL.includes("index.php")){

  document.getElementById('avant').addEventListener('click', avant);
  document.getElementById('apres').addEventListener('click', apres);

  let num = 0;

  let casques = document.querySelectorAll('.acc-produit');
  let totalCasque = casques.length;

  for(let i = 0; i < totalCasque; i++){
    if(i !== 0){
      casques[i].style.display = 'none';
    }
  }


  function apres(){
    casques[num].style.display = 'none';
    num = (num + 1) % totalCasque;
    casques[num].style.display = 'block';
  }

  function avant(){
    casques[num].style.display = 'none';
    num = (num - 1 + totalCasque) % totalCasque;
    casques[num].style.display = 'block';
  }
}



/*Mise en place des fonctionnalité Java Script pour la page panier*/

if(document.URL.includes("panier.php")){
document.getElementById('commandePanier').addEventListener('click', sendCheckout)
document.querySelectorAll(".supprimerArticle").forEach(bouton => {bouton.addEventListener("click", validerSupprimer)});

function sendCheckout(){
  let checkboxes = document.querySelectorAll('.produit-checkbox:checked');

  if(checkboxes.length === 0) {
    alert('Il vous faut au minimum un produit de Sélectionner!');
    return;
  }

  var form = document.createElement('form');
  form.method = 'POST';
  form.action = 'checkout.php';

  checkboxes.forEach(checkbox => {
    var input = document.createElement('input');
    input.type ='hidden';
    input.name = 'articleSelectionner[]';
    input.value = checkbox.value;
    form.appendChild(input);
  })
  document.body.appendChild(form);
  let validation = confirm("Voulez-vous vraiment poursuivre avec les produits selectionner");
  if(validation){
      form.submit();
  }
}

function validerSupprimer(event){

  let validation = confirm("Voulez-vous vraiment enlever cet article de votre panier?");
  if(!validation){
    event.preventDefault();
  }
}
}





/*Mise en place des Fonctionnalité Java Script 
Pour la page Checkout*/

if (document.URL.includes("checkout.php")) {

    let buttons = document.querySelectorAll(".qty-btn");

    for (let i = 0; i < buttons.length; i++) {
        buttons[i].addEventListener("click", gererQuantity);
    }

    function gererQuantity(e) {
        let btn = e.target;  
        let card = btn.closest(".checkout-product-card");  
        let qteEl = card.querySelector(".product-quantity");  
        let qte = lireQteDepuisTexte(qteEl.textContent); 

        if (btn.textContent === "+") {
            qte++;
        } 
        if (btn.textContent === "-" && qte > 1) {
            qte--;
        }

        ecrireQte(qteEl, qte);
        recalculerRecap();
        sauverQtesParProduit();
    }

    document.querySelector(".checkout-action").addEventListener("submit", validerPanier);

    function validerPanier(e) {
        recalculerRecap();
        sauverQtesParProduit();

        let valider = confirm("Voulez-vous confirmer la validation de votre panier ?");

        if (!valider) {
            e.preventDefault(); 
        }
    }

    function round2(n) {
        return Math.round(n * 100) / 100;
    }

    function lireMontant(txt) {
        return parseFloat(txt.replace("$", "").replace(/\s/g, "").replace(",", ".")) || 0;
    }

    function ecrireMontant(n) {
        return n.toLocaleString("fr-CA", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function lireQteDepuisTexte(txt) {
        let match = txt.match(/(\d+)\s*$/);
        return match ? parseInt(match[1], 10) : 1;
    }

    function ecrireQte(el, qte) {
        el.textContent = "Quantité dans le panier : " + qte;
    }

    let rows = document.querySelectorAll(".checkout-box .Pannel-div");
    let nbEl = rows[0].querySelector(".Pannel-value");
    let montantEl = rows[1].querySelector(".Pannel-value");
    let livraisonEl = rows[2].querySelector(".Pannel-value");
    let tpsEl = rows[3].querySelector(".Pannel-value");
    let tvqEl = rows[4].querySelector(".Pannel-value");
    let totalEl = rows[5].querySelector(".Pannel-value");

    function recalculerRecap() {
        let cards = document.querySelectorAll(".checkout-product-card");

        let nbArticles = 0;
        let montantArticles = 0;

        for (let i = 0; i < cards.length; i++) {
            let qteEl = cards[i].querySelector(".product-quantity");
            let prixEl = cards[i].querySelector(".product-price");

            let qte = lireQteDepuisTexte(qteEl.textContent);
            let prix = lireMontant(prixEl.textContent);

            nbArticles += qte;
            montantArticles += qte * prix;
        }

        montantArticles = round2(montantArticles);

        let livraison = lireMontant(livraisonEl.textContent);
        let tps = round2(montantArticles * 0.05);
        let tvq = round2(montantArticles * 0.09975);
        let totalFinal = round2(montantArticles + livraison + tps + tvq);

        nbEl.textContent = nbArticles;
        montantEl.textContent = ecrireMontant(montantArticles) + " $";
        tpsEl.textContent = ecrireMontant(tps) + " $";
        tvqEl.textContent = ecrireMontant(tvq) + " $";
        totalEl.textContent = ecrireMontant(totalFinal) + " $";

        setCookie("checkout_qte", nbArticles, 1);
        setCookie("checkout_total", totalFinal.toFixed(2), 1);
    }

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

    restaurerQtesParProduit();
    recalculerRecap();
    sauverQtesParProduit();
}

/*Mise en place des Fonctionnalité Java Script 
Pour la page Wallet*/

if (document.URL.includes("wallet.php")) {
    let qteCookie = getCookie("checkout_qte");
    let totalCookie = getCookie("checkout_total");

    let rows = document.querySelectorAll(".wallet-row");

    let qteSpan = rows[1].querySelector("span:last-child");

    let totalSpan = document.querySelector(".wallet-total span:last-child");

    if (qteCookie !== "") {
        qteSpan.textContent = qteCookie;
    }

    if (totalCookie !== "") {
        let total = parseFloat(totalCookie).toFixed(2);
        totalSpan.textContent = total.replace(".", ",") + " $";
    }

    let formAchat = document.querySelector(".wallet-actions form");

    formAchat.addEventListener("submit", confirmerAchat); 
    
    function confirmerAchat (e) {
        let ok = confirm("Confirmer la validation de l'achat ?");
        if (!ok) {
            e.preventDefault();
            return;
        }
    };
}


 function setCookie(cname, cvalue, exdays) {
    const d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    let expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
  }

  function getCookie(cname) {
    let name = cname + "=";
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(';');
    for (let i = 0; i < ca.length; i++) {
      let c = ca[i];
      while (c.charAt(0) == ' ') {
        c = c.substring(1);
      }
      if (c.indexOf(name) == 0) {
        return c.substring(name.length, c.length);
      }
    }
    return "";
  }

//Gatcha

var code = "";
if (window.location.pathname.endsWith("compte.php")) {
function generateCaptcha() {
    code = "";
    for (var i = 0; i < 5; i++) {
        code = code + (Math.floor(Math.random() * 9) + 1);
    }
    document.getElementById("txtCaptcha").value = code;
    document.getElementById("CaptchaDiv").innerHTML = code;
}

function checkform(theform) {
    var user = document.getElementById("CaptchaInput").value;

    if (user == "") {
        alert("Met le captcha !");
        return false;
    }

    if (user != code) {
        alert("Faux captcha !");
        document.getElementById("CaptchaInput").value = "";
        generateCaptcha();
        return false;
    }

    document.getElementById("protectedContent").style.display = "block";
    document.getElementById("CaptchaInput").style.display = "none";
    document.getElementById("txtCaptcha").style.display = "none";
    document.getElementById("CaptchaDiv").style.display = "none";

    return false;
}

window.onload = function() {
    generateCaptcha();
};
}