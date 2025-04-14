-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 14-04-2025 a las 19:18:19
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `tfc_floatingmana`
--

DROP DATABASE IF EXISTS tfc_floatingmana;
CREATE DATABASE tfc_floatingmana;
USE tfc_floatingmana;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `card`
--

CREATE TABLE `card` (
  `idCard` int(4) NOT NULL,
  `cardName` varchar(255) NOT NULL,
  `idScryfall` int(7) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `collection`
--

CREATE TABLE `collection` (
  `idCollection` int(6) NOT NULL,
  `idUser` varchar(50) NOT NULL,
  `idCard` int(4) NOT NULL,
  `purchasePrice` decimal(6,2) NOT NULL,
  `isFoil` int(1) NOT NULL,
  `state` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `deck`
--

CREATE TABLE `deck` (
  `idDeck` int(7) NOT NULL,
  `deckName` varchar(50) NOT NULL,
  `idUser` varchar(50) NOT NULL,
  `type` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `deckcard`
--

CREATE TABLE `deckcard` (
  `idDeckCard` int(7) NOT NULL,
  `idDeck` int(7) NOT NULL,
  `idCard` int(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='tabla resultado de la relación (N:M) de Deck y Card';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `state`
--

CREATE TABLE `state` (
  `idState` int(1) NOT NULL,
  `stateName` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `state`
--

INSERT INTO `state` (`idState`, `stateName`) VALUES
(3, 'excellent'),
(4, 'good'),
(1, 'mint'),
(2, 'near mint'),
(5, 'played'),
(6, 'poor');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tokenauth`
--

CREATE TABLE `tokenauth` (
  `idUser` int(6) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expirationDate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user`
--

CREATE TABLE `user` (
  `idUser` int(6) NOT NULL,
  `username` varchar(25) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `isAuth` int(1) NOT NULL DEFAULT 0 COMMENT 'se añade a la bd antes de autenticarse por lo que por defecto está a falso (0) hasta que se autentique y pase a verdadero (1)',
  `profilePic` varchar(255) DEFAULT NULL COMMENT 'dirección del fichero jpg/png en el servidor'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `card`
--
ALTER TABLE `card`
  ADD PRIMARY KEY (`idCard`),
  ADD UNIQUE KEY `cardName` (`cardName`);

--
-- Indices de la tabla `collection`
--
ALTER TABLE `collection`
  ADD PRIMARY KEY (`idCollection`),
  ADD UNIQUE KEY `collOwner` (`idUser`),
  ADD KEY `FK_CidC_CidC` (`idCard`),
  ADD KEY `FK_CS_SidS` (`state`);

--
-- Indices de la tabla `deck`
--
ALTER TABLE `deck`
  ADD PRIMARY KEY (`idDeck`),
  ADD KEY `FK_DDO_UUN` (`idUser`);

--
-- Indices de la tabla `deckcard`
--
ALTER TABLE `deckcard`
  ADD PRIMARY KEY (`idDeckCard`),
  ADD KEY `FK_DCidD_DidD` (`idDeck`),
  ADD KEY `FK_DCidC_CidC` (`idCard`);

--
-- Indices de la tabla `state`
--
ALTER TABLE `state`
  ADD PRIMARY KEY (`idState`),
  ADD UNIQUE KEY `stateName` (`stateName`);

--
-- Indices de la tabla `tokenauth`
--
ALTER TABLE `tokenauth`
  ADD PRIMARY KEY (`token`),
  ADD UNIQUE KEY `idUser` (`idUser`);

--
-- Indices de la tabla `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`idUser`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `profilePic` (`profilePic`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `card`
--
ALTER TABLE `card`
  MODIFY `idCard` int(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `collection`
--
ALTER TABLE `collection`
  MODIFY `idCollection` int(6) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `deck`
--
ALTER TABLE `deck`
  MODIFY `idDeck` int(7) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `deckcard`
--
ALTER TABLE `deckcard`
  MODIFY `idDeckCard` int(7) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `user`
--
ALTER TABLE `user`
  MODIFY `idUser` int(6) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `collection`
--
ALTER TABLE `collection`
  ADD CONSTRAINT `FK_CS_SidS` FOREIGN KEY (`state`) REFERENCES `state` (`idState`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_CidC_CidC` FOREIGN KEY (`idCard`) REFERENCES `card` (`idCard`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_CidU_UUn` FOREIGN KEY (`idUser`) REFERENCES `user` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `deck`
--
ALTER TABLE `deck`
  ADD CONSTRAINT `FK_DidU_UUn` FOREIGN KEY (`idUser`) REFERENCES `user` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `deckcard`
--
ALTER TABLE `deckcard`
  ADD CONSTRAINT `FK_DCidC_CidC` FOREIGN KEY (`idCard`) REFERENCES `card` (`idCard`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_DCidD_DidD` FOREIGN KEY (`idDeck`) REFERENCES `deck` (`idDeck`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `tokenauth`
--
ALTER TABLE `tokenauth`
  ADD CONSTRAINT `FK_TAidU_UidU` FOREIGN KEY (`idUser`) REFERENCES `user` (`idUser`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
