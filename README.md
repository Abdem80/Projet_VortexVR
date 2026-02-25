[![Review Assignment Due Date](https://classroom.github.com/assets/deadline-readme-button-22041afd0340ce965d47ae6ef1cefeee28c7c493a6346c4f15d667ab976d596c.svg)](https://classroom.github.com/a/1UbTNVsq)
[![Open in Visual Studio Code](https://classroom.github.com/assets/open-in-vscode-2e0aaae1b6195c2367325f4f02e2d04e9abb55f0b24a779b69b11b9e10269abc.svg)](https://classroom.github.com/online_ide?assignment_repo_id=21844862&assignment_repo_type=AssignmentRepo)

---

<div align="center">

# 🥽 Vortex VR

### Boutique en ligne de casques de réalité virtuelle

*Projet intégrateur — DEC en Techniques de l'informatique, Conception et programmation*  
*Cégep de Sherbrooke — Session 5*

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?style=for-the-badge&logo=docker&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)

</div>

---

## 📋 Table des matières

1. [Description du projet](#-description-du-projet)
2. [Objectifs pédagogiques](#-objectifs-pédagogiques)
3. [Fonctionnalités](#-fonctionnalités)
4. [Technologies utilisées](#-technologies-utilisées)
5. [Architecture générale](#-architecture-générale)
6. [Installation et démarrage](#-installation-et-démarrage)
7. [Sécurité et bonnes pratiques](#-sécurité-et-bonnes-pratiques)
8. [Compétences démontrées](#-compétences-démontrées)
9. [Équipe de développement](#-équipe-de-développement)
10. [Perspectives d'amélioration](#-perspectives-damélioration)

---

## 🎯 Description du projet

**Vortex VR** est une application web e-commerce développée en **PHP natif**, permettant la gestion complète d'une boutique de casques de réalité virtuelle. Le projet couvre l'intégralité du cycle d'une expérience d'achat en ligne : de l'inscription de l'utilisateur jusqu'à la finalisation du paiement, en passant par la consultation du catalogue, la personnalisation de produits et la gestion de compte.

Le projet a été conçu sans framework afin d'approfondir la maîtrise des concepts fondamentaux du développement web côté serveur : gestion de sessions, requêtes SQL préparées, architecture MVC inspirée, et sécurité applicative.

---

## 🎓 Objectifs pédagogiques

Ce projet vise à démontrer la maîtrise des compétences suivantes dans le cadre du programme de **Techniques de l'informatique** :

- Concevoir et développer une application web dynamique complète avec PHP
- Modéliser et interagir avec une base de données relationnelle via PDO
- Implémenter une gestion sécurisée des utilisateurs (authentification, sessions)
- Appliquer les principes de séparation des responsabilités (logique, présentation, données)
- Utiliser Docker pour normaliser et isoler l'environnement de développement
- Travailler en équipe avec un contrôle de version Git

---

## ✅ Fonctionnalités

### Gestion des utilisateurs
- 📝 **Inscription** — Création de compte avec validation des champs et hachage sécurisé du mot de passe
- 🔐 **Connexion / Déconnexion** — Authentification par session PHP sécurisée
- 👤 **Gestion du profil** — Modification des informations personnelles du compte

### Catalogue et produits
- 🛍️ **Catalogue produits** — Consultation de l'ensemble des casques VR disponibles
- 🎨 **Création personnalisée** — Outil de configuration de casques sur mesure selon les préférences de l'utilisateur

### Panier et paiement
- 🛒 **Gestion du panier** — Ajout, modification de quantité et suppression de produits
- 💳 **Processus de paiement** — Validation de la commande et traitement du paiement
- 💰 **Portefeuille** — Système de solde utilisateur intégré

---

## 🛠️ Technologies utilisées

| Catégorie | Technologie | Usage |
|---|---|---|
| **Backend** | PHP 8 | Logique applicative, traitement des formulaires, gestion des sessions |
| **Base de données** | MySQL 8 | Stockage des utilisateurs, produits, commandes et préférences |
| **Accès données** | PDO (PHP Data Objects) | Requêtes préparées, abstraction de la base de données |
| **Frontend** | HTML5 / CSS3 | Structure et mise en page de l'interface utilisateur |
| **Interactivité** | JavaScript (ES6) | Validation côté client, interactions dynamiques |
| **Environnement** | Docker / Docker Compose | Isolation et reproductibilité de l'environnement de développement |
| **Versionnement** | Git / GitHub | Suivi des modifications et collaboration en équipe |

---

## 🏗️ Architecture générale

Le projet suit une **architecture modulaire sans framework**, inspirée du patron MVC, avec une séparation claire entre les couches de présentation, de traitement et d'accès aux données.

```
Project VortexVR/
│
├── 📄 index.php              # Page d'accueil
├── 📄 login.php              # Connexion utilisateur
├── 📄 register.php           # Inscription utilisateur
├── 📄 catalogue.php          # Catalogue des produits
├── 📄 panier.php             # Gestion du panier
├── 📄 checkout.php           # Finalisation de la commande
├── 📄 wallet.php             # Portefeuille utilisateur
├── 📄 compte.php             # Gestion du profil utilisateur
├── 📄 creation_casque.php    # Configurateur de casque personnalisé
├── 📄 traitement.php         # Traitement des actions (logique métier)
├── 📄 updateClient.php       # Mise à jour des informations client
│
├── 📁 classe/                # Classes PHP (modèles de données)
│   └── *.php                 # Ex : Client.php, Produit.php, Panier.php...
│
├── 📁 inc/                   # Fichiers inclus (en-tête, pied de page, connexion DB)
│   └── *.php                 # Ex : header.php, footer.php, connexion.php...
│
├── 📁 css/                   # Feuilles de style CSS
├── 📁 js/                    # Scripts JavaScript
├── 📁 images/                # Ressources visuelles
├── 📁 database/              # Scripts SQL (création, peuplement)
│   └── *.sql
│
├── 📄 config.example.php     # Template de configuration (à copier)
└── 📄 config.local.php       # Configuration locale (non versionnée)
```

### Flux de navigation

```
[Visiteur] → Inscription / Connexion → [Utilisateur authentifié]
                                              │
                          ┌───────────────────┼──────────────────┐
                          ▼                   ▼                  ▼
                     Catalogue          Création casque      Mon compte
                          │
                     Ajout panier
                          │
                    Validation panier
                          │
                       Paiement ──── Portefeuille
```

---

## 🚀 Installation et démarrage

### Prérequis

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) installé et en cours d'exécution
- [Git](https://git-scm.com/) pour cloner le dépôt

### Étapes d'installation

**1. Cloner le dépôt**

```bash
git clone https://github.com/<votre-organisation>/<nom-du-repo>.git
cd <nom-du-repo>
```

**2. Configurer l'environnement**

Copier le fichier de configuration exemple et le personnaliser :

```bash
cp Web/public_html/Project\ VoxTR/config.example.php Web/public_html/Project\ VoxTR/config.local.php
```

Modifier `config.local.php` avec vos paramètres de base de données :

```php
define('DB_HOST', 'db');       // Nom du service Docker
define('DB_NAME', 'vortexvr');
define('DB_USER', 'root');
define('DB_PASS', 'votre_mot_de_passe');
```

**3. Démarrer les conteneurs Docker**

```bash
docker compose up -d
```

**4. Initialiser la base de données**

Exécuter les scripts SQL via l'interface phpMyAdmin ou directement dans le conteneur MySQL :

```bash
docker exec -i <nom_conteneur_mysql> mysql -u root -p vortexvr < Web/public_html/Project\ VoxTR/database/schema.sql
```

**5. Accéder à l'application**

| Service | URL |
|---|---|
| Application web | http://localhost:8080 |
| phpMyAdmin | http://localhost:8081 |

> **Note :** Les ports peuvent varier selon la configuration de votre `docker-compose.yml`.

---

## 🔒 Sécurité et bonnes pratiques

La sécurité a été une priorité tout au long du développement :

| Mesure | Description |
|---|---|
| **Hachage des mots de passe** | Utilisation de `password_hash()` (bcrypt) et vérification via `password_verify()` |
| **Requêtes préparées PDO** | Protection systématique contre les injections SQL |
| **Protection XSS** | Échappement des sorties avec `htmlspecialchars()` |
| **Gestion de session sécurisée** | Régénération de l'identifiant de session à la connexion (`session_regenerate_id()`) |
| **Contrôle d'accès** | Vérification de l'état de connexion sur toutes les pages protégées |
| **Séparation de la configuration** | `config.local.php` exclu du versionnement Git (`.gitignore`) |
| **Validation des entrées** | Validation côté serveur de tous les formulaires |

---

## 💡 Compétences démontrées

Ce projet met en valeur un ensemble de compétences techniques et méthodologiques :

### Développement backend
- ✔️ Développement en **PHP 8** sans framework — compréhension approfondie du langage
- ✔️ **Architecture orientée objet** — définition et utilisation de classes PHP
- ✔️ **Accès aux données avec PDO** — requêtes préparées, gestion des erreurs
- ✔️ **Gestion des sessions PHP** — authentification et maintien de l'état utilisateur
- ✔️ **Traitement de formulaires** — validation, assainissement et persistance des données

### Développement frontend
- ✔️ **HTML5 sémantique** — structure accessible et bien organisée
- ✔️ **CSS3** — mise en page responsive et stylisation cohérente
- ✔️ **JavaScript** — interactions dynamiques et validation côté client

### Base de données
- ✔️ **Modélisation relationnelle MySQL** — conception du schéma, clés étrangères
- ✔️ **Requêtes SQL avancées** — jointures, agrégations, transactions

### DevOps et collaboration
- ✔️ **Docker / Docker Compose** — conteneurisation et reproductibilité de l'environnement
- ✔️ **Git / GitHub** — contrôle de version et travail collaboratif en équipe

### Qualité et sécurité
- ✔️ **Sécurité applicative** — prévention XSS, injections SQL, gestion sécurisée des mots de passe
- ✔️ **Code commenté et maintenable** — documentation PHPDoc, lisibilité du code

---

## 👥 Équipe de développement

Projet réalisé en équipe dans le cadre du cours de **Développement web avancé** :

| Membre | Rôle principal |
|---|---|
| **Abdoulaye** | Développement backend, gestion de la base de données |
| **Sedrick** | Développement frontend, intégration CSS |
| **William** | Logique métier, gestion du panier et des commandes |
| **Alexandre** | Sécurité, architecture et configuration Docker |

---

## 🔭 Perspectives d'amélioration

Bien que fonctionnel, ce projet pourrait être enrichi dans le cadre d'une évolution future :

- 🔍 **Moteur de recherche et filtres** — Filtrage par prix, marque ou catégorie dans le catalogue
- 📱 **Design responsive amélioré** — Optimisation complète pour les appareils mobiles
- 🌐 **API REST** — Exposition des fonctionnalités sous forme d'API pour une application front-end découplée (React, Vue.js)
- 🔑 **Double authentification (2FA)** — Renforcement de la sécurité des comptes utilisateurs
- 📊 **Tableau de bord administrateur** — Interface de gestion des produits, commandes et utilisateurs
- 🧪 **Tests automatisés** — Mise en place de tests unitaires et fonctionnels (PHPUnit)
- 🚀 **Déploiement en production** — Configuration CI/CD et déploiement sur un hébergeur cloud

---

<div align="center">

*Projet académique — Cégep de Sherbrooke | DEC Techniques de l'informatique*  
*© 2025 — Équipe Vortex VR*

</div>
