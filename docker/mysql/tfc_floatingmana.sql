-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         8.0.42 - MySQL Community Server - GPL
-- SO del servidor:              Linux
-- HeidiSQL Versión:             12.10.0.7000
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para tfc_floatingmana
-- DROP DATABASE IF EXISTS `tfc_floatingmana`
CREATE DATABASE IF NOT EXISTS `tfc_floatingmana` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `tfc_floatingmana`;

-- Volcando estructura para tabla tfc_floatingmana.card
CREATE TABLE IF NOT EXISTS `card` (
  `idCard` int NOT NULL AUTO_INCREMENT,
  `cardName` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `idScryfall` varchar(64) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`idCard`),
  UNIQUE KEY `cardName` (`cardName`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla tfc_floatingmana.card: ~1 rows (aproximadamente)
INSERT INTO `card` (`idCard`, `cardName`, `idScryfall`) VALUES
	(1, 'counter spell', '5d93b770-dc46-46ad-aefe-282dac8cc246');

-- Volcando estructura para tabla tfc_floatingmana.collection
CREATE TABLE IF NOT EXISTS `collection` (
  `idCollection` int NOT NULL AUTO_INCREMENT,
  `idUser` int NOT NULL,
  `idCard` int NOT NULL,
  `purchasePrice` decimal(6,2) NOT NULL,
  `isFoil` int NOT NULL,
  `state` int NOT NULL,
  PRIMARY KEY (`idCollection`),
  UNIQUE KEY `collOwner` (`idUser`),
  KEY `FK_CidC_CidC` (`idCard`),
  KEY `FK_CS_SidS` (`state`),
  CONSTRAINT `FK_CidC_CidC` FOREIGN KEY (`idCard`) REFERENCES `card` (`idCard`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CidU_UIdU` FOREIGN KEY (`idUser`) REFERENCES `user` (`idUser`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CS_SidS` FOREIGN KEY (`state`) REFERENCES `state` (`idState`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla tfc_floatingmana.collection: ~0 rows (aproximadamente)

-- Volcando estructura para tabla tfc_floatingmana.deck
CREATE TABLE IF NOT EXISTS `deck` (
  `idDeck` int NOT NULL AUTO_INCREMENT,
  `deckName` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `idUser` int NOT NULL,
  `format` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `coverImg` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '""',
  PRIMARY KEY (`idDeck`),
  KEY `FK_DDO_UUN` (`idUser`),
  CONSTRAINT `FK_DidU_UidU` FOREIGN KEY (`idUser`) REFERENCES `user` (`idUser`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla tfc_floatingmana.deck: ~1 rows (aproximadamente)
INSERT INTO `deck` (`idDeck`, `deckName`, `idUser`, `format`, `coverImg`) VALUES
	(1, 'hola', 1, 'agresivo', '""');

-- Volcando estructura para tabla tfc_floatingmana.deckcard
CREATE TABLE IF NOT EXISTS `deckcard` (
  `idDeckCard` int NOT NULL AUTO_INCREMENT,
  `idDeck` int NOT NULL,
  `idCard` int NOT NULL,
  `cardQuantity` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`idDeckCard`),
  KEY `FK_DCidD_DidD` (`idDeck`),
  KEY `FK_DCidC_CidC` (`idCard`),
  CONSTRAINT `FK_DCidC_CidC` FOREIGN KEY (`idCard`) REFERENCES `card` (`idCard`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_DCidD_DidD` FOREIGN KEY (`idDeck`) REFERENCES `deck` (`idDeck`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='tabla resultado de la relación (N:M) de Deck y Card';

-- Volcando datos para la tabla tfc_floatingmana.deckcard: ~1 rows (aproximadamente)
INSERT INTO `deckcard` (`idDeckCard`, `idDeck`, `idCard`, `cardQuantity`) VALUES
	(1, 1, 1, 1);

-- Volcando estructura para tabla tfc_floatingmana.state
CREATE TABLE IF NOT EXISTS `state` (
  `idState` int NOT NULL,
  `stateName` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`idState`),
  UNIQUE KEY `stateName` (`stateName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla tfc_floatingmana.state: ~6 rows (aproximadamente)
INSERT INTO `state` (`idState`, `stateName`) VALUES
	(3, 'excellent'),
	(4, 'good'),
	(1, 'mint'),
	(2, 'near mint'),
	(5, 'played'),
	(6, 'poor');

-- Volcando estructura para tabla tfc_floatingmana.tokenauth
CREATE TABLE IF NOT EXISTS `tokenauth` (
  `idUser` int NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `expirationDate` date NOT NULL,
  PRIMARY KEY (`token`),
  UNIQUE KEY `idUser` (`idUser`),
  CONSTRAINT `FK_TAidU_UidU` FOREIGN KEY (`idUser`) REFERENCES `user` (`idUser`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla tfc_floatingmana.tokenauth: ~0 rows (aproximadamente)

-- Volcando estructura para tabla tfc_floatingmana.user
CREATE TABLE IF NOT EXISTS `user` (
  `idUser` int NOT NULL AUTO_INCREMENT,
  `username` varchar(25) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `isAuth` int NOT NULL DEFAULT '0' COMMENT 'se añade a la bd antes de autenticarse por lo que por defecto está a falso (0) hasta que se autentique y pase a verdadero (1)',
  `profilePic` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'dirección del fichero jpg/png en el servidor',
  PRIMARY KEY (`idUser`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `profilePic` (`profilePic`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla tfc_floatingmana.user: ~1 rows (aproximadamente)
INSERT INTO `user` (`idUser`, `username`, `email`, `password`, `isAuth`, `profilePic`) VALUES
	(1, 'alexmm', 'alexmm@gmail.com', '$2y$13$Fch38As4hedtIp62PBoTOuFod3L7yvKACInkuj28w4ELFEva8Fbca', 1, NULL);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
