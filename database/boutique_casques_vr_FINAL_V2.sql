-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Hôte : db
-- Généré le : lun. 15 déc. 2025 à 12:52
-- Version du serveur : 8.0.43
-- Version de PHP : 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `boutique_casques_vr`
--
CREATE DATABASE IF NOT EXISTS `boutique_casques_vr` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `boutique_casques_vr`;

-- --------------------------------------------------------

--
-- Structure de la table `articles_panier`
--

CREATE TABLE `articles_panier` (
  `id_article_panier` int NOT NULL,
  `id_panier` int NOT NULL,
  `id_casque` int NOT NULL,
  `quantite` int NOT NULL DEFAULT '1',
  `prix_unitaire` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `articles_panier`
--

INSERT INTO `articles_panier` (`id_article_panier`, `id_panier`, `id_casque`, `quantite`, `prix_unitaire`) VALUES
(9, 2, 1, 1, 1799.99),
(10, 2, 5, 1, 3499.00),
(26, 1, 1, 1, 1799.99);

-- --------------------------------------------------------

--
-- Structure de la table `casques`
--

CREATE TABLE `casques` (
  `id_casque` int NOT NULL,
  `id_marque` int NOT NULL,
  `id_createur` int NOT NULL,
  `nom_casque` varchar(100) NOT NULL,
  `prix` decimal(10,2) NOT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `description` text,
  `image_fichier` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `casques`
--

INSERT INTO `casques` (`id_casque`, `id_marque`, `id_createur`, `nom_casque`, `prix`, `stock`, `description`, `image_fichier`) VALUES
(1, 2, 1, 'Bigscreen Beyond', 1799.99, 5, 'Casque VR ultra-léger conçu pour le PC, lancé en 2023.', 'bigscreen-beyond.jpg'),
(2, 3, 2, 'Pimax 5K Super', 1199.99, 8, 'Casque VR grand champ de vision, adapté aux jeux exigeants.', 'pimax-5k-super.jpg'),
(3, 4, 3, 'Oculus Rift', 399.99, 10, 'Casque VR historique lancé en 2016, pensé pour le PC gaming.', 'oculus-rift.jpg'),
(4, 5, 4, 'PSVR', 499.99, 6, 'Casque VR pour console PlayStation, compatible PS4.', 'psvr.jpg'),
(5, 1, 1, 'Apple Vision Pro', 3499.00, 3, 'Casque de spatial computing haut de gamme, lancé en 2024.', 'apple-vision-pro.jpg');

-- --------------------------------------------------------

--
-- Structure de la table `commandes`
--

CREATE TABLE `commandes` (
  `id_commande` int NOT NULL,
  `id_utilisateur` int NOT NULL,
  `id_panier` int NOT NULL,
  `montant_total` decimal(10,2) NOT NULL,
  `date_commande` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `commandes`
--

INSERT INTO `commandes` (`id_commande`, `id_utilisateur`, `id_panier`, `montant_total`, `date_commande`) VALUES
(1, 1, 1, 3898.99, '2025-12-10 18:20:34'),
(2, 2, 2, 4199.97, '2025-12-10 18:20:34'),
(3, 1, 1, 4952.74, '2025-12-12 18:08:11'),
(4, 2, 2, 4838.91, '2025-12-12 18:14:05'),
(5, 1, 1, 4952.74, '2025-12-12 18:37:00'),
(6, 3, 3, 1849.57, '2025-12-12 18:43:47'),
(7, 1, 1, 4952.74, '2025-12-12 18:52:59'),
(8, 4, 4, 3229.26, '2025-12-13 09:11:17');

-- --------------------------------------------------------

--
-- Structure de la table `marques`
--

CREATE TABLE `marques` (
  `id_marque` int NOT NULL,
  `nom_marque` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `marques`
--

INSERT INTO `marques` (`id_marque`, `nom_marque`) VALUES
(1, 'Apple'),
(2, 'Bigscreen'),
(3, 'Pimax'),
(4, 'Meta / Oculus'),
(5, 'Sony PlayStation');

-- --------------------------------------------------------

--
-- Structure de la table `paniers`
--

CREATE TABLE `paniers` (
  `id_panier` int NOT NULL,
  `id_utilisateur` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `paniers`
--

INSERT INTO `paniers` (`id_panier`, `id_utilisateur`) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4);

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id_utilisateur` int NOT NULL,
  `nom_utilisateur` varchar(50) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `courriel` varchar(100) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `pays` varchar(50) DEFAULT NULL,
  `adresse` varchar(100) DEFAULT NULL,
  `argent` decimal(10,2) DEFAULT '0.00',
  `ville` varchar(50) DEFAULT NULL,
  `telephone` varchar(30) DEFAULT NULL,
  `solde` decimal(10,2) NOT NULL DEFAULT '10000.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id_utilisateur`, `nom_utilisateur`, `nom`, `prenom`, `courriel`, `mot_de_passe`, `pays`, `adresse`, `argent`, `ville`, `telephone`, `solde`) VALUES
(1, 'Abdoulaye', '', '', 'abdoulaye@example.com', 'abc@123', NULL, NULL, 0.00, NULL, NULL, 5047.26),
(2, 'William', '', '', 'william@example.com', 'abc@123', NULL, NULL, 0.00, NULL, NULL, 5161.09),
(3, 'Sedrick', '', '', 'sedrick@example.com', 'abc@123', NULL, NULL, 0.00, NULL, NULL, 8150.43),
(4, 'Alexandre', '', '', 'alexandre@example.com', 'abc@123', NULL, NULL, 0.00, NULL, NULL, 6770.74);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `articles_panier`
--
ALTER TABLE `articles_panier`
  ADD PRIMARY KEY (`id_article_panier`),
  ADD KEY `fk_articles_panier_paniers` (`id_panier`),
  ADD KEY `fk_articles_panier_casques` (`id_casque`);

--
-- Index pour la table `casques`
--
ALTER TABLE `casques`
  ADD PRIMARY KEY (`id_casque`),
  ADD KEY `fk_casques_marques` (`id_marque`),
  ADD KEY `fk_casques_utilisateurs` (`id_createur`);

--
-- Index pour la table `commandes`
--
ALTER TABLE `commandes`
  ADD PRIMARY KEY (`id_commande`),
  ADD KEY `fk_commandes_utilisateur` (`id_utilisateur`),
  ADD KEY `fk_commandes_panier` (`id_panier`);

--
-- Index pour la table `marques`
--
ALTER TABLE `marques`
  ADD PRIMARY KEY (`id_marque`);

--
-- Index pour la table `paniers`
--
ALTER TABLE `paniers`
  ADD PRIMARY KEY (`id_panier`),
  ADD KEY `fk_paniers_utilisateurs` (`id_utilisateur`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id_utilisateur`),
  ADD UNIQUE KEY `courriel` (`courriel`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `articles_panier`
--
ALTER TABLE `articles_panier`
  MODIFY `id_article_panier` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT pour la table `casques`
--
ALTER TABLE `casques`
  MODIFY `id_casque` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `commandes`
--
ALTER TABLE `commandes`
  MODIFY `id_commande` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `marques`
--
ALTER TABLE `marques`
  MODIFY `id_marque` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `paniers`
--
ALTER TABLE `paniers`
  MODIFY `id_panier` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id_utilisateur` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `articles_panier`
--
ALTER TABLE `articles_panier`
  ADD CONSTRAINT `fk_articles_panier_casques` FOREIGN KEY (`id_casque`) REFERENCES `casques` (`id_casque`),
  ADD CONSTRAINT `fk_articles_panier_paniers` FOREIGN KEY (`id_panier`) REFERENCES `paniers` (`id_panier`) ON DELETE CASCADE;

--
-- Contraintes pour la table `casques`
--
ALTER TABLE `casques`
  ADD CONSTRAINT `fk_casques_marques` FOREIGN KEY (`id_marque`) REFERENCES `marques` (`id_marque`),
  ADD CONSTRAINT `fk_casques_utilisateurs` FOREIGN KEY (`id_createur`) REFERENCES `utilisateurs` (`id_utilisateur`);

--
-- Contraintes pour la table `commandes`
--
ALTER TABLE `commandes`
  ADD CONSTRAINT `fk_commandes_panier` FOREIGN KEY (`id_panier`) REFERENCES `paniers` (`id_panier`),
  ADD CONSTRAINT `fk_commandes_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id_utilisateur`);

--
-- Contraintes pour la table `paniers`
--
ALTER TABLE `paniers`
  ADD CONSTRAINT `fk_paniers_utilisateurs` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id_utilisateur`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
