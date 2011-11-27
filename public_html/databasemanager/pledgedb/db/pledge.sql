-- phpMyAdmin SQL Dump
-- version 3.4.7.1
-- http://www.phpmyadmin.net
--
-- Host: gdrights.org.customers.tigertech.net
-- Generation Time: Nov 27, 2011 at 01:35 PM
-- Server version: 5.1.49
-- PHP Version: 5.2.6-1+lenny13aaa+tigertech2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `pledges`
--

-- --------------------------------------------------------

--
-- Table structure for table `pledge`
--

CREATE TABLE IF NOT EXISTS `pledge` (
  `public` tinyint(1) NOT NULL DEFAULT '1',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `iso3` char(3) COLLATE utf8_unicode_ci NOT NULL,
  `conditional` tinyint(1) NOT NULL,
  `quantity` enum('absolute','intensity') COLLATE utf8_unicode_ci NOT NULL,
  `reduction_percent` int(11) NOT NULL,
  `rel_to` enum('below','of') COLLATE utf8_unicode_ci NOT NULL,
  `year_or_bau` enum('year','bau') COLLATE utf8_unicode_ci NOT NULL,
  `rel_to_year` int(11) NOT NULL,
  `by_year` int(11) NOT NULL,
  `source` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `details` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=79 ;

--
-- Dumping data for table `pledge`
--

INSERT INTO `pledge` (`public`, `id`, `iso3`, `conditional`, `quantity`, `reduction_percent`, `rel_to`, `year_or_bau`, `rel_to_year`, `by_year`, `source`, `details`) VALUES
(0, 2, 'AUS', 1, 'absolute', 25, 'below', 'year', 2000, 2020, 'Climate Interactive Climate Scoreboard Sept 2, 2011', ''),
(0, 29, 'BRA', 1, 'absolute', 36, 'below', 'bau', 0, 2020, 'Climate Interactive Climate Scoreboard Sept 2, 2011', ''),
(1, 38, 'CHK', 0, 'intensity', 45, 'below', 'year', 2005, 2020, 'Climate Interactive Climate Scoreboard Sept 2, 2011', 'increase forest coverage by 40 million hectares by 2020'),
(0, 27, 'BLR', 0, 'absolute', 5, 'below', 'year', 1990, 2020, 'Climate Interactive Climate Scoreboard Sept 2, 2011', ''),
(0, 30, 'BRA', 0, 'absolute', 39, 'below', 'bau', 0, 2020, 'Climate Interactive Climate Scoreboard Sept 2, 2011', ''),
(0, 39, 'CAN', 1, 'absolute', 17, 'below', 'year', 2005, 2020, 'Climate Interactive Climate Scoreboard Sept 2, 2011', ''),
(0, 40, 'CRI', 0, 'absolute', 100, 'below', 'bau', 0, 2021, 'Climate Interactive Climate Scoreboard Sept 2, 2011', ''),
(0, 41, 'HRV', 0, 'absolute', 5, 'below', 'year', 1990, 2020, 'Climate Interactive Climate Scoreboard Sept 2, 2011', ''),
(0, 42, 'ISL', 1, 'absolute', 30, 'below', 'year', 1990, 2020, 'Climate Interactive Climate Scoreboard Sept 2, 2011', ''),
(0, 43, 'IND', 0, 'intensity', 20, 'below', 'year', 2005, 2020, 'Climate Interactive Climate Scoreboard Sept 2, 2011', ''),
(0, 44, 'IND', 1, 'intensity', 25, 'below', 'year', 2005, 2020, 'Climate Interactive Climate Scoreboard Sept 2, 2011', '20% of electricity from renewable energy by 2020'),
(0, 45, 'IDN', 0, 'absolute', 26, 'below', 'bau', 0, 2020, 'Climate Interactive Climate Scoreboard Sept 2, 2011', ''),
(0, 46, 'IDN', 1, 'absolute', 41, 'below', 'bau', 0, 2020, 'Climate Interactive Climate Scoreboard Sept 2, 2011', ' Change forest to net sink by 2030'),
(0, 47, 'ISR', 0, 'absolute', 20, 'below', 'bau', 0, 2020, 'Climate Interactive Climate Scoreboard Sept 2, 2011', ''),
(0, 49, 'JPN', 1, 'absolute', 25, 'below', 'year', 1990, 2020, 'Climate Interactive Climate Scoreboard Sept 2, 2011', ''),
(0, 50, 'KAZ', 0, 'absolute', 15, 'below', 'year', 1992, 2020, 'Climate Interactive Climate Scoreboard Sept 2, 2011', ''),
(0, 51, 'JPN', 1, 'absolute', 25, 'below', 'year', 1990, 2020, 'Climate Interactive Climate Scoreboard Sept 2, 2011', ''),
(0, 52, 'LIE', 0, 'absolute', 20, 'below', 'year', 1990, 2020, 'Climate Interactive Climate Scoreboard Sept 2, 2011', ''),
(0, 53, 'LIE', 1, 'absolute', 30, 'below', 'year', 1990, 2020, 'Climate Interactive Climate Scoreboard Sept 2, 2011', ''),
(0, 54, 'MDV', 0, 'absolute', 100, 'below', 'bau', 0, 2020, 'Climate Interactive Climate Scoreboard Sept 2, 2011', ''),
(0, 55, 'MHL', 0, 'absolute', 40, 'below', 'year', 2009, 2020, 'Climate Interactive Climate Scoreboard Sept 2, 2011', ''),
(0, 56, 'MEX', 1, 'absolute', 30, 'below', 'bau', 0, 2020, 'Climate Interactive Climate Scoreboard Sept 2, 2011', ''),
(0, 57, 'MDA', 0, 'absolute', 25, 'below', 'year', 1990, 2020, 'Climate Interactive Climate Scoreboard Sept 2, 2011', ''),
(0, 58, 'MCO', 0, 'absolute', 30, 'below', 'year', 1990, 2020, 'Climate Interactive Climate Scoreboard Sept 2, 2011', ''),
(0, 59, 'NZL', 1, 'absolute', 20, 'below', 'year', 1990, 2020, 'Climate Interactive Climate Scoreboard Sept 2, 2011', ''),
(0, 60, 'NOR', 0, 'absolute', 30, 'below', 'year', 1990, 2020, 'Climate Interactive Climate Scoreboard Sept 2, 2011', '6% from land use, land-use change and forestry (LULUCF) improvements'),
(0, 61, 'NOR', 1, 'absolute', 40, 'below', 'year', 1990, 2020, 'Climate Interactive Climate Scoreboard Sept 2, 2011', ''),
(0, 62, 'PNG', 0, 'absolute', 50, 'below', 'bau', 0, 2030, 'Climate Interactive Climate Scoreboard Sept 2, 2011', ''),
(1, 63, 'RUS', 0, 'absolute', 15, 'below', 'year', 1990, 2020, 'Climate Interactive Climate Scoreboard Sept 2, 2011', ''),
(1, 64, 'RUS', 1, 'absolute', 25, 'below', 'year', 1990, 2020, 'Climate Interactive Climate Scoreboard Sept 2, 2011', ''),
(0, 65, 'SGP', 0, 'absolute', 7, 'below', 'bau', 0, 2020, 'Climate Interactive Climate Scoreboard Sept 2, 2011', ''),
(0, 66, 'SGP', 1, 'absolute', 16, 'below', 'bau', 0, 2020, 'Climate Interactive Climate Scoreboard Sept 2, 2011', ''),
(0, 67, 'ZAF', 1, 'absolute', 34, 'below', 'bau', 0, 2020, 'Climate Interactive Climate Scoreboard Sept 2, 2011', 'Emissions peak in 2025, stabilize for 10 years and decline'),
(0, 68, 'KOR', 0, 'absolute', 30, 'below', 'bau', 0, 2020, 'Climate Interactive Climate Scoreboard Sept 2, 2011', ''),
(0, 69, 'CHE', 0, 'absolute', 20, 'below', 'year', 1990, 2020, 'Climate Interactive Climate Scoreboard Sept 2, 2011', ''),
(0, 70, 'CHE', 1, 'absolute', 30, 'below', 'year', 1990, 2020, 'Climate Interactive Climate Scoreboard Sept 2, 2011', ''),
(0, 71, 'UKR', 1, 'absolute', 20, 'below', 'year', 1990, 2020, 'Climate Interactive Climate Scoreboard Sept 2, 2011', ''),
(0, 72, 'GBR', 1, 'absolute', 50, 'below', 'year', 1990, 2025, 'Climate Interactive Climate Scoreboard Sept 2, 2011', ''),
(1, 73, 'USA', 1, 'absolute', 17, 'below', 'year', 2005, 2020, 'Climate Interactive Climate Scoreboard Sept 2, 2011', '');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
