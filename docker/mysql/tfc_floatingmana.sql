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
DROP DATABASE IF EXISTS `tfc_floatingmana`;
CREATE DATABASE IF NOT EXISTS `tfc_floatingmana` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `tfc_floatingmana`;

-- Volcando estructura para tabla tfc_floatingmana.card
CREATE TABLE IF NOT EXISTS `card` (
  `idCard` int NOT NULL AUTO_INCREMENT,
  `cardName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `idScryfall` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`idCard`),
  UNIQUE KEY `Índice 2` (`idScryfall`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla tfc_floatingmana.card: ~100 rows (aproximadamente)
INSERT INTO `card` (`idCard`, `cardName`, `idScryfall`) VALUES
	(1, 'counter spell', '5d93b770-dc46-46ad-aefe-282dac8cc246'),
	(2, 'Innocent Traveler // Malicious Invader', '13a5e5fd-a67a-4c0e-97ae-923bdbc1be20'),
	(3, 'Mountain Valley', '9558892f-c4e8-4849-8c3b-384780cfb183'),
	(4, 'Kaya\'s Wrath', '5ed140c1-752b-4539-88f2-1fa354049b17'),
	(5, 'Terror // Terror', '60c92f1b-0c78-4809-9365-e1ffa515cb4b'),
	(6, 'Aftershock', 'd50f4196-0029-4e20-b685-e67df6d46e80'),
	(7, 'Dread Wight', '65d332e2-4b2d-4131-84f7-862cb138c477'),
	(8, 'Lonely Sandbar', 'b1c65080-fda9-4ccf-b11e-8936ea31e412'),
	(9, 'Icy Manipulator', 'e3dec5eb-c391-4152-860d-68b2f09b8459'),
	(10, 'Jetting Glasskite', '27e27d8f-3527-413d-a5d3-bced692e9034'),
	(11, 'Bounce Chamber', '756164bc-6d3a-4d0f-96b6-fd0ae9a5371e'),
	(12, 'Howling Fury', 'b7107b11-308e-4eb1-b16f-1fe92ecfe903'),
	(13, 'Bellowing Fiend', '2b0962d7-d797-4f07-bd73-9cd7a11ffad8'),
	(14, 'Forest', 'd65f7c03-647f-4e5a-98b1-1faa3d330e7b'),
	(15, 'Plains', '2b069f97-735a-4d85-8504-b5a863bd659b'),
	(16, 'The Weatherseed Treaty', 'e2a54461-1f75-4304-b6d7-635171419456'),
	(17, 'Rescue Retriever', '598def2c-003c-4aa4-ac7c-44ffd9639fdc'),
	(18, 'Golgari Findbroker', 'eb6104f6-127f-4ab8-94bb-d98783ecb377'),
	(19, 'Winding Wurm', 'ed75dc43-172c-4302-8807-23bfdd65baf4'),
	(20, 'Thunderbreak Regent', 'fbc60f6f-da15-4109-acf6-34ac9e4de9f9'),
	(21, 'Moon-Circuit Hacker', 'c6e466d1-943d-41e6-a47d-c9d951ca4262'),
	(22, 'Tovolar, Dire Overlord // Tovolar, the Midnight Scourge', '3d7b401f-79aa-4584-b42b-e81e15c7065b'),
	(23, 'Celestial Regulator', '25803f0b-4475-447e-abdf-dcd6a98dd654'),
	(24, 'Sanctify', '7c9aeb6e-678e-44ed-8143-c44250cdb196'),
	(25, 'Blue Sun\'s Twilight', 'ac7a6f3e-4e9f-4e50-ab60-17dc1b494fa5'),
	(26, 'Temple of the Dragon Queen', '91658f56-12c9-4173-94ad-dfd186b1dbae'),
	(27, 'Think Twice', '35471afe-e14c-48f4-b901-297111be9c23'),
	(28, 'Sporeweb Weaver', 'e6ab63c8-0adc-4d74-aee5-a58ce5c0dad8'),
	(29, 'The Gitrog Monster', 'ae42941c-3f01-4bf4-9528-7b3dc485320f'),
	(30, 'Elenda, the Dusk Rose', '783d4f17-ce50-41f8-a6e7-29eb2ee143f0'),
	(31, 'Hieroglyphic Illumination', 'c60a0e75-53bb-43e4-890f-0e1972a7e0b9'),
	(32, 'Chaoslace', '91f5ef08-0e54-49c2-9947-fdd5f843973d'),
	(33, 'Cliffhaven Sell-Sword', '7f334767-4353-4379-a934-fa67075db439'),
	(34, 'Plains', '7ee52536-8cfa-482b-874e-094c0a081894'),
	(35, 'Lumbering Worldwagon', '8bb97f9f-e7be-4794-a3a0-a0d73445c8fe'),
	(36, 'Triarch Praetorian', '88d5ae0e-7796-4c16-96a1-ef3bbf2d449e'),
	(37, 'Krosan Verge', '00a8776a-58f2-4a42-8919-2dd255f3f577'),
	(38, 'Stingscourger', '3102b0df-04c0-418c-a316-c8ea8660e9f7'),
	(39, 'Maestros Charm', '3b3e1bec-67d2-43fd-b799-b7c12b94d1f0'),
	(40, 'Gluttonous Guest', '18c07288-1c71-4e71-bdf5-910eb583a1d8'),
	(41, 'Ulvenwald Tracker', '1bf611cc-6a54-40bb-8fc3-b66d942254db'),
	(42, 'Insatiable Gorgers', 'bb56491b-bad7-44da-8aa5-91ffd875e76a'),
	(43, 'Jace, Unraveler of Secrets', '20d5521d-e9f1-49e0-aa13-8e6de794cb12'),
	(44, 'Valley Mightcaller', '31330554-1824-436b-aee5-931a7b652ddf'),
	(45, 'Firescreamer', '155a2213-bf6e-4a54-924b-e450b7d06f26'),
	(46, 'Vampire Envoy', '3ba8e7aa-9a87-410e-b846-5f5c910585cf'),
	(47, 'Damnation', '7fc1d7db-11a3-4ff9-8d27-1fe401053080'),
	(48, 'Faeburrow Elder', '1ca29912-88b1-413f-ad9d-63d7d1b1ca16'),
	(49, 'Crimson Mage', '52f544fa-170f-4da4-bd28-72f713a045ba'),
	(50, 'Majestic Genesis', 'eaadb067-4a77-4d6e-931d-447bd0c06594'),
	(51, 'Galvanic Iteration', '572e5519-86df-4fba-951a-3c0616d20838'),
	(52, 'Gaea\'s Will', '1ecd084d-20c6-43cf-b5d5-8ba3d692a50a'),
	(53, 'Reminisce', 'f49a8b0d-f130-4a16-a79c-607618cc40bd'),
	(54, 'Smelt-Ward Ignus', '4f24b2b6-b994-455f-b8e6-aa73e1be81b4'),
	(55, 'Island', 'a785f977-0635-4a20-ab8f-eab79659d16e'),
	(56, 'Satyr Wayfinder', 'a7313136-91a9-45fb-b5b4-07ba8f22326c'),
	(57, 'Necroduality', '400ca655-68ed-4714-96e9-55df00a325b3'),
	(58, 'Leeching Licid', '27bffefb-23c0-4d03-b716-b1a7eff39a05'),
	(59, 'Goblin Mutant', '8ae01cc1-e339-4b3e-a482-ef33bebbf04f'),
	(60, 'Swords to Plowshares', 'ea68cf78-e200-4169-89f4-cdee3aa13592'),
	(61, 'High Market', '3c99e02f-52e7-4a42-87dc-966034be79c7'),
	(62, 'Cemetery Illuminator', '5f619464-dc3b-4265-b4e4-2578034bf5bf'),
	(63, 'Beast Within', '601c59cf-f3df-4003-9ae9-613a1d4a620b'),
	(64, 'Circular Logic', '617152ea-bacd-42f3-928b-e0bcc686dd41'),
	(65, 'Fire Elemental', 'aa894222-04c9-4129-8d48-6ffdd329b1f0'),
	(66, 'Heroes of the Revel', 'dfdfb481-3446-42f4-a1c3-a88b69f2189a'),
	(67, 'Condescend', 'f70232b8-e0aa-4fc9-b76b-6766f69b9823'),
	(68, 'Sentry Bot', '11a2cac9-e3b7-4cdf-9d61-3970bef64a08'),
	(69, 'Wall of Swords', 'b8ae0268-e2de-45c5-8556-42455f66d646'),
	(70, 'Emmessi Tome', 'decb78dd-03d7-43a0-8ff5-1b97c6f515c9'),
	(71, 'Grizzly Bears', 'a0264440-2325-4143-87a7-2af5a6b2a2f9'),
	(72, 'Plains', 'ffc26409-4f6a-4740-adbf-83550e7ab262'),
	(73, 'Snow-Covered Swamp', 'e0ff0ab8-270a-4803-9e45-113366650cf7'),
	(74, 'Backwoods Survivalists', '4a3a65be-0ca1-437f-aafe-d96e8fe428ad'),
	(75, 'Forest', '562b16ae-8ae7-4b2d-9098-bf3ff1429f7b'),
	(76, 'Gix, Yawgmoth Praetor', '7ea211f0-322b-4a78-8853-61eeddccdb3c'),
	(77, 'Perilous Snare', 'd1edff87-8563-4cb5-ab9b-7696e920346a'),
	(78, 'Temple of the False God', '8f0778f4-2717-4e77-84cf-d01d359004ed'),
	(79, 'Altar\'s Reap', 'f3053af2-715c-4549-9003-bf4279029a95'),
	(80, 'Circle of Flame', 'a2de620b-1358-483e-8b59-53206b1d3936'),
	(81, 'Burning of Xinye', 'edb86d0d-e6cf-4988-a03e-26d52d78d929'),
	(82, 'Thopter Engineer', '77b83dbe-77de-4446-96a5-6b4c4b6e8a27'),
	(83, 'Basalt Monolith', '97d8dbc9-d9f3-4e8e-95d2-27917c152a29'),
	(84, 'Ancient Lumberknot', '24ed08db-a8ae-4579-9c54-0cc259e03892'),
	(85, 'Lizard Warrior', '053cd970-5b79-410b-8420-82d9a490b897'),
	(86, 'Shadowspear', '9adf5196-41e9-401c-8ccf-abfad0350f87'),
	(87, 'Mystic Forge', '74fd1f7f-4946-4f28-a759-3f73c0473536'),
	(88, 'Char', '53de6c9a-b7af-45bc-b0f7-41ebd5910cf4'),
	(89, 'Optimistic Scavenger', 'f67d6bd4-b03a-4d04-bc38-85b3ee39aa8a'),
	(90, 'Rootwater Hunter', 'cdf7ea34-2cde-4ec5-9b12-99b0002da986'),
	(91, 'Dimir Cluestone', '0d8ac24f-3309-453a-b2d6-6363df9a1ddd'),
	(92, 'Wooded Foothills', '3164be2f-28c9-4a33-bd70-d5089396538c'),
	(93, 'Samwise Gamgee', 'a1b6f13e-63d0-46bf-aa57-23c2dbdf62dd'),
	(94, 'Jill, Shiva\'s Dominant // Shiva, Warden of Ice', 'bbd46c0d-cd9d-4e48-b6bf-f619e141100c'),
	(95, 'Arcum\'s Weathervane', '9e142435-6930-4596-bc3b-60abde1229df'),
	(96, 'Fathom Seer', '20de275a-2e11-4452-8037-bc397dd53a8c'),
	(97, 'Forest', '3d09a0c7-3934-4bf3-a56a-5b670b168eee'),
	(98, 'Neutralizing Blast', 'e549a8fc-6001-43db-88b1-ce8ed42a3443'),
	(99, 'Killer Whale', '060a44f2-2251-4d90-925d-6c41da2d5ad7'),
	(100, 'Danitha, New Benalia\'s Light', '039e43f2-cf3b-4c60-ac55-d2aafb20eb34');

-- Volcando estructura para tabla tfc_floatingmana.collection
CREATE TABLE IF NOT EXISTS `collection` (
  `idCollection` int NOT NULL AUTO_INCREMENT,
  `idUser` int NOT NULL,
  `idCard` int NOT NULL,
  `purchasePrice` decimal(6,2) NOT NULL,
  `isFoil` int NOT NULL,
  `state` int NOT NULL,
  PRIMARY KEY (`idCollection`),
  KEY `FK_CidC_CidC` (`idCard`),
  KEY `FK_CS_SidS` (`state`),
  KEY `collOwner` (`idUser`) USING BTREE,
  CONSTRAINT `FK_CidC_CidC` FOREIGN KEY (`idCard`) REFERENCES `card` (`idCard`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CidU_UIdU` FOREIGN KEY (`idUser`) REFERENCES `user` (`idUser`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CS_SidS` FOREIGN KEY (`state`) REFERENCES `state` (`idState`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla tfc_floatingmana.collection: ~22 rows (aproximadamente)
INSERT INTO `collection` (`idCollection`, `idUser`, `idCard`, `purchasePrice`, `isFoil`, `state`) VALUES
	(1, 1, 5, 3.14, 0, 2),
	(2, 1, 66, 0.15, 1, 2),
	(4, 1, 79, 0.05, 0, 2),
	(5, 1, 84, 0.27, 0, 3),
	(6, 1, 63, 0.00, 0, 2),
	(7, 1, 88, 1.30, 0, 2),
	(8, 1, 57, 32.20, 1, 2),
	(9, 1, 11, 0.12, 0, 2),
	(10, 4, 1, 91.86, 1, 1),
	(11, 1, 2, 0.06, 0, 2),
	(12, 2, 3, 0.30, 0, 2),
	(13, 2, 4, 0.43, 0, 2),
	(14, 2, 6, 0.15, 0, 2),
	(15, 4, 7, 0.42, 0, 2),
	(16, 3, 8, 0.15, 0, 3),
	(17, 2, 9, 0.16, 0, 2),
	(18, 3, 10, 0.19, 1, 2),
	(19, 4, 12, 0.36, 0, 2),
	(20, 2, 13, 0.31, 0, 2),
	(21, 4, 14, 6.50, 1, 1),
	(22, 3, 15, 0.16, 0, 2),
	(23, 2, 16, 0.12, 0, 2);

-- Volcando estructura para tabla tfc_floatingmana.deck
CREATE TABLE IF NOT EXISTS `deck` (
  `idDeck` int NOT NULL AUTO_INCREMENT,
  `deckName` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `idUser` int NOT NULL,
  `format` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `coverImg` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '""',
  PRIMARY KEY (`idDeck`),
  KEY `FK_DDO_UUN` (`idUser`),
  CONSTRAINT `FK_DidU_UidU` FOREIGN KEY (`idUser`) REFERENCES `user` (`idUser`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla tfc_floatingmana.deck: ~5 rows (aproximadamente)
INSERT INTO `deck` (`idDeck`, `deckName`, `idUser`, `format`, `coverImg`) VALUES
	(1, 'patata', 1, 'commander', '""'),
	(2, 'cebolla', 1, 'modern', '""'),
	(3, 'naranja', 2, 'commander', '""'),
	(4, 'mazorca', 3, 'commander', '""'),
	(5, 'piñas', 4, 'commander', '""');

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
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='tabla resultado de la relación (N:M) de Deck y Card';

-- Volcando datos para la tabla tfc_floatingmana.deckcard: ~20 rows (aproximadamente)
INSERT INTO `deckcard` (`idDeckCard`, `idDeck`, `idCard`, `cardQuantity`) VALUES
	(1, 1, 88, 1),
	(2, 1, 84, 1),
	(3, 1, 5, 1),
	(4, 1, 66, 1),
	(5, 2, 1, 1),
	(6, 2, 11, 1),
	(7, 2, 2, 1),
	(8, 2, 3, 5),
	(9, 3, 4, 1),
	(10, 5, 6, 1),
	(11, 2, 7, 1),
	(12, 3, 8, 1),
	(13, 4, 9, 1),
	(14, 4, 10, 1),
	(15, 4, 11, 1),
	(16, 4, 12, 1),
	(17, 3, 13, 1),
	(18, 5, 14, 7),
	(19, 3, 15, 4),
	(20, 5, 16, 1);

-- Volcando estructura para tabla tfc_floatingmana.state
CREATE TABLE IF NOT EXISTS `state` (
  `idState` int NOT NULL,
  `stateName` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`idState`),
  UNIQUE KEY `stateName` (`stateName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla tfc_floatingmana.state: ~0 rows (aproximadamente)
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
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `expirationDate` datetime NOT NULL,
  PRIMARY KEY (`token`),
  UNIQUE KEY `idUser` (`idUser`),
  CONSTRAINT `FK_TAidU_UidU` FOREIGN KEY (`idUser`) REFERENCES `user` (`idUser`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla tfc_floatingmana.tokenauth: ~0 rows (aproximadamente)

-- Volcando estructura para tabla tfc_floatingmana.user
CREATE TABLE IF NOT EXISTS `user` (
  `idUser` int NOT NULL AUTO_INCREMENT,
  `username` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `isAuth` int NOT NULL DEFAULT '0' COMMENT 'se añade a la bd antes de autenticarse por lo que por defecto está a falso (0) hasta que se autentique y pase a verdadero (1)',
  `profilePic` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'dirección del fichero jpg/png en el servidor',
  PRIMARY KEY (`idUser`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `profilePic` (`profilePic`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla tfc_floatingmana.user: ~0 rows (aproximadamente)
INSERT INTO `user` (`idUser`, `username`, `email`, `password`, `isAuth`, `profilePic`) VALUES
	(1, 'alexmm', 'alexmm@gmail.com', '$2y$13$Fch38As4hedtIp62PBoTOuFod3L7yvKACInkuj28w4ELFEva8Fbca', 1, NULL),
	(2, 'ivanag', 'ivanag@gmail.com', '$2y$13$Fch38As4hedtIp62PBoTOuFod3L7yvKACInkuj28w4ELFEva8Fbca', 1, NULL),
	(3, 'danivs', 'danivs@gmail.com', '$2y$13$Fch38As4hedtIp62PBoTOuFod3L7yvKACInkuj28w4ELFEva8Fbca', 1, NULL),
	(4, 'ismapr', 'ismapr@gmail.com', '$2y$13$Fch38As4hedtIp62PBoTOuFod3L7yvKACInkuj28w4ELFEva8Fbca', 1, NULL);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
