-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 03-04-2025 a las 23:58:35
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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `collection`
--

CREATE TABLE `collection` (
  `idCollection` int(6) NOT NULL,
  `collectionOwner` varchar(50) NOT NULL,
  `collCardList` varchar(255) NOT NULL COMMENT 'dirección donde va a estar el archivo json en el servidor',
  `collectionValue` decimal(6,2) NOT NULL,
  `cardAmount` int(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `deck`
--

CREATE TABLE `deck` (
  `idDeck` int(7) NOT NULL,
  `deckName` varchar(50) NOT NULL,
  `deckCardList` varchar(255) NOT NULL COMMENT 'dirección donde se guardará el archivo json en el servidor',
  `deckOwner` varchar(50) NOT NULL,
  `type` varchar(100) NOT NULL,
  `deckValue` decimal(6,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `profilePic` varchar(255) DEFAULT NULL COMMENT 'dirección del fichero en el servidor'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `collection`
--
ALTER TABLE `collection`
  ADD PRIMARY KEY (`idCollection`),
  ADD UNIQUE KEY `collOwner` (`collectionOwner`),
  ADD UNIQUE KEY `collCardList` (`collCardList`);

--
-- Indices de la tabla `deck`
--
ALTER TABLE `deck`
  ADD PRIMARY KEY (`idDeck`),
  ADD UNIQUE KEY `deckCardList` (`deckCardList`),
  ADD KEY `FK_DDO_UUN` (`deckOwner`);

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
  ADD CONSTRAINT `FK_CCO_UUN` FOREIGN KEY (`collectionOwner`) REFERENCES `user` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `deck`
--
ALTER TABLE `deck`
  ADD CONSTRAINT `FK_DDO_UUN` FOREIGN KEY (`deckOwner`) REFERENCES `user` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `tokenauth`
--
ALTER TABLE `tokenauth`
  ADD CONSTRAINT `FK_TAidU_UidU` FOREIGN KEY (`idUser`) REFERENCES `user` (`idUser`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
