-- phpMyAdmin SQL Dump
-- version 4.4.10
-- http://www.phpmyadmin.net
--
-- Host: climateequityreference.org.customers.tigertech.net
-- Generation Time: Aug 17, 2015 at 06:40 AM
-- Server version: 5.5.44-0+deb7u1-log
-- PHP Version: 5.5.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `pledges_cerp`
--
CREATE DATABASE IF NOT EXISTS `pledges_cerp` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `pledges_cerp`;

-- --------------------------------------------------------

--
-- Table structure for table `carbon_price`
--
-- Creation: Jun 17, 2015 at 03:38 AM
-- Last update: Jun 17, 2015 at 03:38 AM
--

DROP TABLE IF EXISTS `carbon_price`;
CREATE TABLE IF NOT EXISTS `carbon_price` (
  `year` int(11) NOT NULL,
  `c_price_USD_per_tCO2e` float NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Truncate table before insert `carbon_price`
--

TRUNCATE TABLE `carbon_price`;
--
-- Dumping data for table `carbon_price`
--

INSERT INTO `carbon_price` (`year`, `c_price_USD_per_tCO2e`) VALUES
(2011, 30),
(2012, 30),
(2013, 30),
(2014, 30),
(2015, 30),
(2016, 30),
(2017, 30),
(2018, 30),
(2019, 30),
(2020, 30),
(2021, 30),
(2022, 30),
(2023, 30),
(2024, 30),
(2025, 30),
(2026, 30),
(2027, 30),
(2028, 30),
(2029, 30),
(2030, 30);

-- --------------------------------------------------------

--
-- Table structure for table `country`
--
-- Creation: Jun 17, 2015 at 03:38 AM
-- Last update: Jun 17, 2015 at 03:38 AM
--

DROP TABLE IF EXISTS `country`;
CREATE TABLE IF NOT EXISTS `country` (
  `iso3` char(3) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(35) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Truncate table before insert `country`
--

TRUNCATE TABLE `country`;
--
-- Dumping data for table `country`
--

INSERT INTO `country` (`iso3`, `name`) VALUES
('ALB', 'Albania'),
('DZA', 'Algeria'),
('AGO', 'Angola'),
('ATG', 'Antigua and Barbuda'),
('ARG', 'Argentina'),
('ARM', 'Armenia'),
('AUS', 'Australia'),
('AUT', 'Austria'),
('AZE', 'Azerbaijan'),
('BHS', 'Bahamas, The'),
('BHR', 'Bahrain'),
('BGD', 'Bangladesh'),
('BRB', 'Barbados'),
('BLR', 'Belarus'),
('BEL', 'Belgium'),
('BLZ', 'Belize'),
('BEN', 'Benin'),
('BTN', 'Bhutan'),
('BOL', 'Bolivia'),
('BIH', 'Bosnia and Herzegovina'),
('BWA', 'Botswana'),
('BRA', 'Brazil'),
('BRN', 'Brunei'),
('BGR', 'Bulgaria'),
('BFA', 'Burkina Faso'),
('BDI', 'Burundi'),
('KHM', 'Cambodia'),
('CMR', 'Cameroon'),
('CAN', 'Canada'),
('CPV', 'Cape Verde'),
('CAF', 'Central African Republic'),
('TCD', 'Chad'),
('CHL', 'Chile'),
('CHK', 'China'),
('COL', 'Colombia'),
('COM', 'Comoros'),
('COD', 'Congo, Democratic Republic of the'),
('COG', 'Congo, Republic of the'),
('COK', 'Cook Islands'),
('CRI', 'Costa Rica'),
('CIV', 'Cote d''Ivoire'),
('HRV', 'Croatia'),
('CUB', 'Cuba'),
('CYP', 'Cyprus'),
('CZE', 'Czech Republic'),
('DNK', 'Denmark'),
('DJI', 'Djibouti'),
('DMA', 'Dominica'),
('DOM', 'Dominican Republic'),
('ECU', 'Ecuador'),
('EGY', 'Egypt'),
('SLV', 'El Salvador'),
('GNQ', 'Equatorial Guinea'),
('ERI', 'Eritrea'),
('EST', 'Estonia'),
('ETH', 'Ethiopia'),
('FJI', 'Fiji'),
('FIN', 'Finland'),
('FRA', 'France'),
('GAB', 'Gabon'),
('GMB', 'Gambia, The'),
('GEO', 'Georgia'),
('DEU', 'Germany'),
('GHA', 'Ghana'),
('GRC', 'Greece'),
('GRD', 'Grenada'),
('GTM', 'Guatemala'),
('GIN', 'Guinea'),
('GNB', 'Guinea-Bissau'),
('GUY', 'Guyana'),
('HTI', 'Haiti'),
('HND', 'Honduras'),
('HUN', 'Hungary'),
('ISL', 'Iceland'),
('IND', 'India'),
('IDN', 'Indonesia'),
('IRN', 'Iran'),
('IRQ', 'Iraq'),
('IRL', 'Ireland'),
('ISR', 'Israel'),
('ITA', 'Italy'),
('JAM', 'Jamaica'),
('JPN', 'Japan'),
('JOR', 'Jordan'),
('KAZ', 'Kazakhstan'),
('KEN', 'Kenya'),
('KIR', 'Kiribati'),
('PRK', 'Korea, Dem. Rep.'),
('KOR', 'Korea, Rep.'),
('KWT', 'Kuwait'),
('KGZ', 'Kyrgyzstan'),
('LAO', 'Laos'),
('LVA', 'Latvia'),
('LBN', 'Lebanon'),
('LSO', 'Lesotho'),
('LBR', 'Liberia'),
('LBY', 'Libya'),
('LIE', 'Liechtenstein'),
('LTU', 'Lithuania'),
('LUX', 'Luxembourg'),
('MKD', 'Macedonia'),
('MDG', 'Madagascar'),
('MWI', 'Malawi'),
('MYS', 'Malaysia'),
('MDV', 'Maldives'),
('MLI', 'Mali'),
('MLT', 'Malta'),
('MHL', 'Marshall Islands'),
('MRT', 'Mauritania'),
('MUS', 'Mauritius'),
('MEX', 'Mexico'),
('FSM', 'Micronesia, Federated States of'),
('MDA', 'Moldova'),
('MCO', 'Monaco'),
('MNG', 'Mongolia'),
('MNE', 'Montenegro'),
('MAR', 'Morocco'),
('MOZ', 'Mozambique'),
('MMR', 'Myanmar'),
('NAM', 'Namibia'),
('NRU', 'Nauru'),
('NPL', 'Nepal'),
('NLD', 'Netherlands'),
('NZL', 'New Zealand'),
('NIC', 'Nicaragua'),
('NER', 'Niger'),
('NGA', 'Nigeria'),
('NIU', 'Niue'),
('NOR', 'Norway'),
('OMN', 'Oman'),
('PAK', 'Pakistan'),
('PLW', 'Palau'),
('PAN', 'Panama'),
('PNG', 'Papua New Guinea'),
('PRY', 'Paraguay'),
('PER', 'Peru'),
('PHL', 'Philippines'),
('POL', 'Poland'),
('PRT', 'Portugal'),
('QAT', 'Qatar'),
('ROU', 'Romania'),
('RUS', 'Russia'),
('RWA', 'Rwanda'),
('KNA', 'Saint Kitts and Nevis'),
('LCA', 'Saint Lucia'),
('VCT', 'Saint Vincent and the Grenadines'),
('WSM', 'Samoa'),
('SMR', 'San Marino'),
('STP', 'Sao Tome and Principe'),
('SAU', 'Saudi Arabia'),
('SEN', 'Senegal'),
('SRB', 'Serbia'),
('SYC', 'Seychelles'),
('SLE', 'Sierra Leone'),
('SGP', 'Singapore'),
('SVK', 'Slovakia'),
('SVN', 'Slovenia'),
('SLB', 'Solomon Islands'),
('SOM', 'Somalia'),
('ZAF', 'South Africa'),
('ESP', 'Spain'),
('LKA', 'Sri Lanka'),
('SDN', 'Sudan'),
('SUR', 'Suriname'),
('SWZ', 'Swaziland'),
('SWE', 'Sweden'),
('CHE', 'Switzerland'),
('SYR', 'Syria'),
('TWN', 'Taiwan'),
('TJK', 'Tajikistan'),
('TZA', 'Tanzania'),
('THA', 'Thailand'),
('TLS', 'Timor-Leste'),
('TGO', 'Togo'),
('TON', 'Tonga'),
('TTO', 'Trinidad and Tobago'),
('TUN', 'Tunisia'),
('TUR', 'Turkey'),
('TKM', 'Turkmenistan'),
('TUV', 'Tuvalu'),
('UGA', 'Uganda'),
('UKR', 'Ukraine'),
('ARE', 'United Arab Emirates'),
('GBR', 'United Kingdom'),
('USA', 'United States'),
('URY', 'Uruguay'),
('UZB', 'Uzbekistan'),
('VUT', 'Vanuatu'),
('VEN', 'Venezuela'),
('VNM', 'Vietnam'),
('PSE', 'West Bank and Gaza'),
('YEM', 'Yemen'),
('ZMB', 'Zambia'),
('AFG', 'Afghanistan'),
('ZWE', 'Zimbabwe');

-- --------------------------------------------------------

--
-- Table structure for table `intl_pledge`
--
-- Creation: Jun 17, 2015 at 03:38 AM
-- Last update: Jun 17, 2015 at 03:38 AM
--

DROP TABLE IF EXISTS `intl_pledge`;
CREATE TABLE IF NOT EXISTS `intl_pledge` (
  `id` int(11) NOT NULL,
  `iso3` char(3) COLLATE utf8_unicode_ci NOT NULL,
  `pledge_mln_USD` float NOT NULL,
  `source` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=MyISAM AUTO_INCREMENT=82 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Truncate table before insert `intl_pledge`
--

TRUNCATE TABLE `intl_pledge`;
--
-- Dumping data for table `intl_pledge`
--

INSERT INTO `intl_pledge` (`id`, `iso3`, `pledge_mln_USD`, `source`) VALUES
(71, 'KOR', 4.76, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011'),
(70, 'PRT', 5.68, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011'),
(69, 'PAK', 3.94, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011'),
(68, 'NOR', 2104.37, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011'),
(67, 'NGA', 2, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011'),
(66, 'NZL', 9.75, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011'),
(65, 'NLD', 174.5, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011'),
(64, 'MCO', 0.01, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011'),
(63, 'MEX', 5.14, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011'),
(62, 'LUX', 5.52, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011'),
(61, 'PRK', 3, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011'),
(60, 'JPN', 16683.4, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011'),
(59, 'ITA', 92.37, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011'),
(58, 'IRL', 39.81, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011'),
(57, 'IND', 6.45, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011'),
(56, 'HUN', 1.34, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011'),
(55, 'GRC', 4.74, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011'),
(54, 'DEU', 2042.97, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011'),
(53, 'FRA', 457.29, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011'),
(52, 'FIN', 70.49, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011'),
(51, 'DNK', 131.97, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011'),
(50, 'CZE', 6.4, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011'),
(49, 'CYP', 0.8, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011'),
(48, 'CHK', 8.24, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011'),
(47, 'CAN', 275.44, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011'),
(46, 'BRA', 3.9, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011'),
(45, 'BEL', 86.07, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011'),
(44, 'AUT', 31.19, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011'),
(43, 'AUS', 437.46, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011'),
(72, 'ROU', 0.21, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011'),
(73, 'RUS', 3.5, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011'),
(74, 'SVN', 4.22, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011'),
(75, 'ZAF', 4.14, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011'),
(76, 'ESP', 331.46, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011'),
(77, 'SWE', 201.03, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011'),
(78, 'CHE', 108.61, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011'),
(79, 'TUR', 3.94, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011'),
(80, 'GBR', 5467.62, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011'),
(81, 'USA', 2355.44, '<a href="http://www.climatefundsupdate.org/projects" target="_blank">Climate Funds Update</a>, accessed 29 Nov 2011');

-- --------------------------------------------------------

--
-- Table structure for table `pledge`
--
-- Creation: Aug 10, 2015 at 07:03 PM
-- Last update: Aug 13, 2015 at 07:16 PM
--

DROP TABLE IF EXISTS `pledge`;
CREATE TABLE IF NOT EXISTS `pledge` (
  `public` tinyint(1) NOT NULL DEFAULT '1',
  `id` int(11) NOT NULL,
  `iso3` char(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `region` char(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `conditional` tinyint(1) NOT NULL,
  `quantity` enum('absolute','intensity','target_Mt') COLLATE utf8_unicode_ci NOT NULL,
  `reduction_percent` decimal(6,2) DEFAULT NULL,
  `rel_to` enum('below','of') COLLATE utf8_unicode_ci DEFAULT NULL,
  `year_or_bau` enum('year','bau') COLLATE utf8_unicode_ci DEFAULT NULL,
  `rel_to_year` int(11) DEFAULT NULL,
  `target_Mt` decimal(8,3) DEFAULT NULL,
  `target_Mt_CO2` decimal(8,3) DEFAULT NULL,
  `target_Mt_nonCO2` decimal(8,3) DEFAULT NULL,
  `target_Mt_LULUCF` decimal(8,3) DEFAULT NULL,
  `by_year` int(11) NOT NULL,
  `include_nonco2` tinyint(1) NOT NULL DEFAULT '1',
  `include_lulucf` tinyint(1) NOT NULL DEFAULT '0',
  `info_link` text COLLATE utf8_unicode_ci,
  `source` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `caveat` varchar(4096) COLLATE utf8_unicode_ci NOT NULL,
  `details` varchar(1024) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=264 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Truncate table before insert `pledge`
--

TRUNCATE TABLE `pledge`;
--
-- Dumping data for table `pledge`
--

INSERT INTO `pledge` (`public`, `id`, `iso3`, `region`, `conditional`, `quantity`, `reduction_percent`, `rel_to`, `year_or_bau`, `rel_to_year`, `target_Mt`, `target_Mt_CO2`, `target_Mt_nonCO2`, `target_Mt_LULUCF`, `by_year`, `include_nonco2`, `include_lulucf`, `info_link`, `source`, `caveat`, `details`) VALUES
(1, 29, 'BRA', NULL, 1, 'absolute', 39.00, 'below', 'bau', 0, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, NULL, '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a> 2 Sep 2011', '', ''),
(1, 38, 'CHK', NULL, 0, 'intensity', 45.00, 'below', 'year', 2005, NULL, 0.000, 0.000, 0.000, 2020, 0, 0, '', '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a> April 19, 2013', 'The high end of China''s 40% to 45% pledge. Xie Zhenhua, China''s top climate envoy, announced a lower 2025 target at the Major Emitter''s Forum in April of 2012. However, he reiterated China''s stronger formal pledge on November 26th 2012, as the Doha COP was beginning.\n\n{"unconditional":"yes", "pledge_qualifier":"high end of range"}', 'increase forest coverage by 40 million hectares by 2020<br /><span style="font-family: Verdana, ''Lucida Grande'', Lucida, Helvetica, Arial, sans-serif; font-size: 12px;">Increase non-fossil energy sources to 15% of primary energy consumption by 2020&nbsp;</span><br style="font-family: Verdana, ''Lucida Grande'', Lucida, Helvetica, Arial, sans-serif; font-size: 12px;" /><span class="emphasis" style="font-style: italic; color: green; font-family: Verdana, ''Lucida Grande'', Lucida, Helvetica, Arial, sans-serif; font-size: 12px;">Cap on energy consumption in 2015 at 4 billion tons of coal equivalent (tce)&nbsp;<br />Emissions Peak in 2030 and fall to 2005 levels by 2050</span>'),
(1, 27, 'BLR', NULL, 0, 'absolute', 5.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a> 2 Sep 2011', 'Belarus''s scores are low because its'' pledges are low. &nbsp;So low that in the 2020 pledge year its'' emissions are still far above (rather than below) its'' BAU emissions. &nbsp;This is of course because its'' pledges are anchored to its 1990 emissions, rather than to its responsibility and capacity.', ''),
(1, 30, 'BRA', NULL, 0, 'absolute', 36.00, 'below', 'bau', 0, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, NULL, '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a> 2 Sep 2011', '', ''),
(1, 39, 'CAN', NULL, 0, 'absolute', 17.00, 'below', 'year', 2005, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a> 2 Sep 2011', '', ''),
(1, 41, 'HRV', NULL, 0, 'absolute', 5.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, NULL, '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a> 2 Sep 2011', '', ''),
(1, 42, 'ISL', NULL, 1, 'absolute', 30.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, NULL, '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a> 2 Sep 2011', '', ''),
(1, 43, 'IND', NULL, 0, 'intensity', 20.00, 'below', 'year', 2005, 0.000, 0.000, 0.000, 0.000, 2020, 0, 0, '', '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a> 2 Sep 2011', '', ''),
(1, 44, 'IND', NULL, 1, 'intensity', 25.00, 'below', 'year', 2005, 0.000, 0.000, 0.000, 0.000, 2020, 0, 0, '', '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a> 2 Sep 2011', '', '20% of electricity from renewable energy by 2020'),
(1, 45, 'IDN', NULL, 0, 'absolute', 26.00, 'below', 'bau', 0, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, NULL, '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a> 2 Sep 2011', '', ''),
(1, 46, 'IDN', NULL, 1, 'absolute', 41.00, 'below', 'bau', 0, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, NULL, '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a> 2 Sep 2011', '', ' Change forest to net sink by 2030'),
(1, 47, 'ISR', NULL, 0, 'absolute', 20.00, 'below', 'bau', 0, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, NULL, '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a> 2 Sep 2011', '', ''),
(1, 50, 'KAZ', NULL, 0, 'absolute', 15.00, 'below', 'year', 1992, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, NULL, '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a> 2 Sep 2011', '', ''),
(1, 52, 'LIE', NULL, 0, 'absolute', 20.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a> 2 Sep 2011', 'Liechtenstein''s score is very low because its'' pledges are very small. &nbsp;It is a very wealthy country. &nbsp;Its'' pledges are very low given the size of its capacity.', ''),
(1, 53, 'LIE', NULL, 1, 'absolute', 30.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a> 2 Sep 2011', 'Liechtenstein''s score is very low because its'' pledges are very small. &nbsp;It is a very wealthy country. &nbsp;Its'' pledges are very low given the size of its capacity.', ''),
(1, 54, 'MDV', NULL, 0, 'absolute', 100.00, 'below', 'bau', NULL, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a> 2 Sep 2011', '', 'Status of this pledge is unclear, as it was made before the coup'),
(1, 55, 'MHL', NULL, 1, 'absolute', 40.00, 'below', 'year', 2009, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a> 2 Sep 2011', '', ''),
(1, 56, 'MEX', NULL, 1, 'intensity', 30.00, 'below', 'bau', NULL, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a> 2 Sep 2011', '', ''),
(1, 57, 'MDA', NULL, 0, 'absolute', 25.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a> 2 Sep 2011', 'Moldovia''s scores are low because its'' pledge is low. &nbsp;So low that in the 2020 pledge year its'' emissions are still far above (rather than below) its BAU emissions. &nbsp;This is of course because its'' pledge is anchored to its'' 1990 emissions, rather than to its'' responsibility and capacity.', ''),
(1, 58, 'MCO', NULL, 0, 'absolute', 30.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a> 2 Sep 2011', 'Monaco''s score is very low because its'' pledge is very small. &nbsp;It is a very wealthy country, and its'' pledge is very low given the size of its capacity.', ''),
(1, 59, 'NZL', NULL, 1, 'absolute', 20.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a> 2 June 2012', '', ''),
(1, 60, 'NOR', NULL, 0, 'absolute', 30.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a> 19 April 2013', '<span style="font-family: ''Lucida Grande'', ''Lucida Sans Unicode'', Verdana, Arial, Helvetica, sans-serif; font-size: 11.818181991577148px; line-height: 17.27272605895996px;">Norway is a wealthy country that makes significant contributions to international mitigation.&nbsp; These contributions support both forest protection and the development / deployment of renewable energy.&nbsp; Norwegian &nbsp;experts have estimated the number of tons mitigated by these contributions but we have not included them when calculating Norway&rsquo;s score.&nbsp; Before this can be done, support for international mitigation must be reported for all countries, and accounted for in a coherent and comparable manner.&nbsp;</span>', '6% from land use, land-use change and forestry (LULUCF) improvements'),
(1, 61, 'NOR', NULL, 1, 'absolute', 40.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a> 19 April 2013', '<p class="MsoNormal">Norway is a wealthy country that makes significant contributions to international mitigation.&nbsp; These contributions support both forest protection and the development / deployment of renewable energy.&nbsp; Norwegian &nbsp;experts have estimated the number of tons mitigated by these contributions but we have not included them when calculating Norway&rsquo;s score.&nbsp; Before this can be done, support for international mitigation must be reported for all countries, and accounted for in a coherent and comparable manner.&nbsp;&nbsp;</p>', '6% from land use, land-use change and forestry (LULUCF) improvements'),
(1, 62, 'PNG', NULL, 1, 'absolute', 100.00, 'below', 'bau', NULL, 0.000, 0.000, 0.000, 0.000, 2050, 1, 0, '', '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a> April 19, 2013', '', ''),
(1, 63, 'RUS', NULL, 0, 'absolute', 15.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, NULL, '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a> 2 Sep 2011', '', ''),
(1, 64, 'RUS', NULL, 1, 'absolute', 25.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, NULL, '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a> 2 Sep 2011', '', ''),
(1, 65, 'SGP', NULL, 0, 'absolute', 7.00, 'below', 'bau', 0, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, NULL, '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a> 2 Sep 2011', '', ''),
(1, 66, 'SGP', NULL, 1, 'absolute', 16.00, 'below', 'bau', 0, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, NULL, '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a> 2 Sep 2011', '', ''),
(1, 67, 'ZAF', NULL, 1, 'absolute', 34.00, 'below', 'bau', NULL, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a> 2 Sep 2011', '', '<span style="color: #008000; font-family: Verdana, ''Lucida Grande'', Lucida, Helvetica, Arial, sans-serif; font-size: 12px; font-style: italic;">Emissions peak in 2025, stabilize for 10 years and decline</span>'),
(1, 68, 'KOR', NULL, 0, 'intensity', 30.00, 'below', 'bau', NULL, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a> 2 Sep 2011', '', ''),
(1, 69, 'CHE', NULL, 0, 'absolute', 20.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, NULL, '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a> 2 Sep 2011', '', ''),
(1, 70, 'CHE', NULL, 1, 'absolute', 30.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, NULL, '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a> 2 Sep 2011', '', ''),
(1, 71, 'UKR', NULL, 1, 'absolute', 20.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a> 2 Sep 2011', 'Ukraine''s scores are low because its'' pledges are low. &nbsp;So low that in the 2020 pledge year its'' emissions are still far above (rather than below) its'' BAU emissions. &nbsp;This is because its'' pledges are anchored to its 1990 emissions, rather than to its'' responsibility and capacity.', ''),
(1, 72, 'GBR', NULL, 0, 'absolute', 35.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', 'https://www.gov.uk/government/policies/reducing-the-uk-s-greenhouse-gas-emissions-by-80-by-2050/supporting-pages/carbon-budgets', '', 'UK policy (22 January 2013) is to reduce emissions to 1,950 MtCO2e over 2023 to 2027 period, to an level that averages 50% of 1990 emissions. &nbsp;It''s 2018 to 2022 goal is 2,544 MtCO2e, to a level that averages 65% of 1990 emissions.'),
(1, 73, 'USA', NULL, 0, 'absolute', 17.00, 'below', 'year', 2005, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a> 2 Sep 2011', 'This may not actually be a conditional pledge. &nbsp;It depends on who you ask.', ''),
(1, 102, 'CRI', NULL, 1, 'absolute', 100.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 1, '', '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a> 2 Sep 2011', '', ''),
(1, 100, 'AUS', NULL, 0, 'absolute', 5.00, 'below', 'year', 2000, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a href="http://climateactiontracker.org/countries/australia.html">Climate Action Tracker Nov 20, 2013</a>', '"Australia pledged an unconditional target of a 5% emission reduction below 2005 levels by 2020. The currently implemented policies of Australia would be sufficient to meet its unconditional pledge, if continued. However, the Abbott Government, elected in September 2013 has confirmed its intent to repeal the Clean Energy Legislation. At its first sitting the in mid-November 2013 the House of Representatives voted for repeal. The Government does not yet have the majority in the Senate for repeal, but may as early as July 2014, after which time it will need to be negotiated with minor parties to repeal. This repeal would dismantle most of the present policy framework including the current fixed carbon prices and the cap-and-trade system put in place in 2011. The Government insists that it will call a fresh general election should the Senate not support repeal."', 'Pledges also applied to LULUCF'),
(1, 98, NULL, 'eu28', 0, 'absolute', 20.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a> 2 Sep 2011', '', ''),
(1, 99, NULL, 'eu28', 1, 'absolute', 30.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, NULL, '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a> 2 Sep 2011', '', ''),
(1, 108, 'ATG', NULL, 0, 'absolute', 25.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a>&nbsp;April 19, 2013', '', ''),
(1, 111, 'BLR', NULL, 1, 'absolute', 10.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a>&nbsp;19 June 2012', 'Belarus''s scores are low because its'' pledges are low. &nbsp;So low that in the 2020 pledge year its'' emissions are still far above (rather than below) its'' BAU emissions. &nbsp;This is of course because its'' pledges are anchored to its 1990 emissions, rather than to its responsibility and capacity.', ''),
(1, 113, NULL, 'eu28', 0, 'absolute', 40.00, 'below', 'year', 1990, NULL, 0.000, 0.000, 0.000, 2030, 1, 0, '', 'http://climateactiontracker.org/countries/eu.html', '', ''),
(1, 115, 'ISL', NULL, 0, 'absolute', 50.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2050, 1, 0, '', '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a>&nbsp;19 June 2012', '', 'Substantial share from LULUCF'),
(1, 116, 'ISL', NULL, 1, 'absolute', 75.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2050, 1, 0, '', '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a>&nbsp;19 June 2012', '', ''),
(1, 224, 'NOR', NULL, 0, 'absolute', 40.00, 'below', 'year', 1990, NULL, 0.000, 0.000, 0.000, 2030, 1, 0, '', 'http://climateactiontracker.org/countries/norway.html', '', ''),
(1, 225, 'MEX', NULL, 1, 'target_Mt', NULL, NULL, NULL, NULL, 622.720, 0.000, 0.000, 0.000, 2030, 1, 1, '', 'CAT INDC Database; http://climateactiontracker.org/countries/mexico.html<br /><br />Also: <a href="http://www4.unfccc.int/submissions/INDC/Published%20Documents/Mexico/1/MEXICO%20INDC%2003.30.2015.pdf">official INDC submission</a> 30 March 2015', 'BAU (2030) in INDC submission (GHG only, no Black Carbon) is 973 Mt CO2e); 2030 conditional target is 36% below BAU in 2030; this gives an emissions target of 622.72 Mt CO2e\n\n{"description_override":"reduce total emissions by 36% compared to Mexican INDC baseline", "help_label":"<br><b>Important information on pledge and baseline calibration</b>", "help_title":"INDC Information", "help_text":"<p>This pledge evaluation does not include Mexico&#39;s planned reductions of black carbon.<p><p>Mexico has provided an official baseline in its INDC submission. This baseline projection differs from the <a href=glossary.php#gloss_bau target=_self>no-policies baseline</a> used by the Climate Equity Reference Calculator. We have compensated for this difference. The target emissions in 2030 are unchanged.</p>"}', 'IMPORTANT: Mexico''s INDC submission contains a substantial amount of reductions in Black Carbon, for which they provide a GWP (900) and convert to CO2e. We ignore that part completely. Further, they specify a peak year (2026), which is not consistent with the calculator output.'),
(1, 226, 'MEX', NULL, 0, 'target_Mt', NULL, NULL, NULL, NULL, 758.940, NULL, NULL, NULL, 2030, 1, 1, '', 'CAT INDC Database;&nbsp;http://climateactiontracker.org/countries/mexico.html<br /><br />O<a href="http://www4.unfccc.int/submissions/INDC/Published%20Documents/Mexico/1/MEXICO%20INDC%2003.30.2015.pdf">fficial INDC submission</a>&nbsp;30 March 2015', 'BAU (2030) in INDC submission (GHG only, no Black Carbon) is 973 Mt CO2e); 2030 non-conditional target is 22% below BAU in 2030; This gives an emissions target of 758.94 Mt CO2e;\n\n{"description_override":"reduce total emissions by 22% compared to Mexican INDC baseline", "help_label":"<br><b>Important information on pledge and baseline calibration</b>", "help_title":"INDC Information", "help_text":"<p>This pledge evaluation does not include Mexico&#39;s planned reductions of black carbon<p><p>Mexico has provided an official baseline in its INDC submission. This baseline projection differs from the <a href=glossary.php#gloss_bau target=_self>no-policies baseline</a> used by the Climate Equity Reference Calculator. We have compensated for this difference. The target emissions in 2030 are unchanged.</p>"}', 'IMPORTANT: Mexico''s INDC submission contains substantial reductions in Black Carbon, for which they provide a GWP (900) and which they convert to CO2e. We ignore this completely. Further, they specify a peak year (2026), which is not consistent with the calculator output.'),
(1, 131, 'UKR', NULL, 0, 'absolute', 50.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2050, 1, 0, '', '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a>&nbsp;19 June 2012', 'Ukraine''s scores are low because its'' pledges are low. &nbsp;So low that in the 2020 pledge year its'' emissions are still far above (rather than below) its'' BAU emissions. &nbsp;This is because its'' pledges are anchored to its 1990 emissions, rather than to its responsibility and capacity.', ''),
(1, 119, 'MEX', NULL, 1, 'absolute', 50.00, 'below', 'year', 2000, 0.000, 0.000, 0.000, 0.000, 2050, 1, 0, '', '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a>&nbsp;19 June 2012; also reiteated in <a href="http://www4.unfccc.int/submissions/INDC/Published%20Documents/Mexico/1/MEXICO%20INDC%2003.30.2015.pdf">Mexico''s INDC Submission</a> 30 Mar 2015.', '', ''),
(1, 120, 'NZL', NULL, 0, 'absolute', 50.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2050, 1, 0, '', '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a>&nbsp;19 June 2012', '', ''),
(1, 121, 'NOR', NULL, 1, 'absolute', 100.00, 'below', 'bau', NULL, 0.000, 0.000, 0.000, 0.000, 2050, 1, 0, '', '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a>&nbsp;19 June 2012', '<p class="MsoNormal">Norway is a wealthy country that makes significant contributions to international mitigation.&nbsp; These contributions support both forest protection and the development / deployment of renewable energy.&nbsp; Norwegian &nbsp;experts have estimated the number of tons mitigated by these contributions but, nevertheless, we have not included them when calculating Norway&rsquo;s score.&nbsp; Before this can be done, support for international mitigation must be reported for all countries, and accounted for in a coherent and comparable manner.&nbsp;&nbsp;</p>', ''),
(1, 123, 'RUS', NULL, 0, 'absolute', 50.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2050, 1, 0, '', '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a>&nbsp;19 June 2012', '', ''),
(1, 125, 'ZAF', NULL, 1, 'absolute', 42.00, 'below', 'bau', NULL, 0.000, 0.000, 0.000, 0.000, 2025, 1, 0, '', '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a>&nbsp;19 June 2012', '', '<span style="color: #008000; font-family: Verdana, ''Lucida Grande'', Lucida, Helvetica, Arial, sans-serif; font-size: 12px; font-style: italic;">Emissions peak in 2025, stabilize for 10 years and decline</span>'),
(1, 133, 'CHK', NULL, 0, 'intensity', 40.00, 'below', 'year', 2005, NULL, 0.000, 0.000, 0.000, 2020, 0, 0, '', '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a>&nbsp;April 19, 2013', 'This is the low end of China''s 40% to 45% pledge.\n\n{"unconditional":"yes", "pledge_qualifier":"low end of range"}', 'increase forest coverage by 40 million hectares by 2020'),
(1, 139, 'DOM', NULL, 0, 'absolute', 25.00, 'below', 'year', 2010, 0.000, 0.000, 0.000, 0.000, 2030, 1, 0, '', 'Pledge made in Doha at the end of 2012', '', ''),
(1, 183, 'FIN', NULL, 0, 'absolute', 15.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 166, 'ROU', NULL, 0, 'absolute', 41.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 154, 'BGR', NULL, 1, 'absolute', 41.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 140, 'CHL', NULL, 1, 'absolute', 20.00, 'below', 'bau', NULL, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a style="color: #444444; text-decoration: none; border-bottom-width: 1px; border-bottom-style: solid; font-family: ''Lucida Grande'', ''Lucida Sans Unicode'', Verdana, Arial, Helvetica, sans-serif; font-size: 11.818181991577148px; line-height: 17.27272605895996px;" href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a><span style="font-family: ''Lucida Grande'', ''Lucida Sans Unicode'', Verdana, Arial, Helvetica, sans-serif; font-size: 11.818181991577148px; line-height: 17.27272605895996px; background-color: #ffffee;">&nbsp;</span><span style="font-family: ''Lucida Grande'', ''Lucida Sans Unicode'', Verdana, Arial, Helvetica, sans-serif; font-size: 12px; background-color: #ffffee; line-height: 17.600000381469727px;">April 19, 2013</span>', '', ''),
(1, 220, 'MYS', NULL, 1, 'intensity', 40.00, 'below', 'year', 2005, 0.000, 0.000, 0.000, 0.000, 2020, 0, 0, '', '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a> April 19, 2013', 'In Copenhagen Malaysia offered a voluntary contribution to reduce by up to 40 percent in terms of emissions intensity of GDP (gross domestic product) by 2020 from 2005 levels.', ''),
(1, 144, 'TWN', NULL, 0, 'absolute', 30.00, 'below', 'bau', NULL, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '', '', '<span style="color: #666666; font-family: Verdana, ''Lucida Grande'', Lucida, Helvetica, Arial, sans-serif; font-size: 12px;">Emissions from all sources should drop to 257 Mt by 2020</span>'),
(1, 146, 'GBR', NULL, 0, 'absolute', 50.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2025, 1, 0, '', '<div>https://www.gov.uk/government/policies/reducing-the-uk-s-greenhouse-gas-emissions-by-80-by-2050/supporting-pages/carbon-budgets</div>', '', 'UK policy (22 January 2013) is to reduce emissions to 1,950 MtCO2e over 2023 to 2027 period, to an level that averages 50% of 1990 emissions. &nbsp;It''s 2018 to 2022 goal is 2,544 MtCO2e, to a level that averages 65% of 1990 emissions.'),
(1, 148, 'VNM', NULL, 1, 'intensity', 8.00, 'below', 'year', 2010, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a href="http://climateinteractive.org/scoreboard" target="_blank">Climate Interactive Climate Scoreboard</a>&nbsp;<span style="font-family: ''Lucida Grande'', ''Lucida Sans Unicode'', Verdana, Arial, Helvetica, sans-serif; font-size: 12px; line-height: 17.600000381469727px; background-color: #ffffee;">April 19, 2013</span>', '', ''),
(1, 156, 'DEU', NULL, 1, 'absolute', 46.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 155, 'DEU', NULL, 0, 'absolute', 34.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 157, 'LVA', NULL, 0, 'absolute', 55.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 158, 'LVA', NULL, 1, 'absolute', 55.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 160, 'EST', NULL, 0, 'absolute', 54.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 162, 'EST', NULL, 1, 'absolute', 54.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 163, 'LTU', NULL, 0, 'absolute', 53.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 164, 'LTU', NULL, 1, 'absolute', 53.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 165, 'BGR', NULL, 0, 'absolute', 42.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 167, 'ROU', NULL, 1, 'absolute', 37.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 168, 'GBR', NULL, 1, 'absolute', 44.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', 'This pledge is not based on the UK''s domestic climate legislation, which does not specify an pledge increase in the case of the EU raising its ambition to 30%. &nbsp;This figure is from our own analysis of the EU 25''s 30% pledge - which we take to be conditional - and its disaggregation.', ''),
(1, 169, 'SWE', NULL, 0, 'absolute', 32.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 170, 'SWE', NULL, 1, 'absolute', 52.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 171, 'CZE', NULL, 0, 'absolute', 31.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 172, 'CZE', NULL, 1, 'absolute', 32.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 173, 'SVK', NULL, 0, 'absolute', 29.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 174, 'SVK', NULL, 1, 'absolute', 32.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 175, 'FRA', NULL, 0, 'absolute', 23.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 176, 'FRA', NULL, 1, 'absolute', 35.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 177, 'DNK', NULL, 0, 'absolute', 23.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 178, 'DNK', NULL, 1, 'absolute', 31.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 179, 'HUN', NULL, 0, 'absolute', 23.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 180, 'HUN', NULL, 1, 'absolute', 25.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 181, 'BEL', NULL, 0, 'absolute', 17.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 182, 'BEL', NULL, 1, 'absolute', 29.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 195, 'AUT', NULL, 0, 'absolute', 108.00, 'of', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a style="color: #444444; text-decoration: none; border-bottom-width: 1px; border-bottom-style: solid; font-family: ''Lucida Grande'', ''Lucida Sans Unicode'', Verdana, Arial, Helvetica, sans-serif; font-size: 11.818181991577148px; line-height: 17.27272605895996px;" title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 185, 'FIN', NULL, 1, 'absolute', 25.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 186, 'POL', NULL, 0, 'absolute', 14.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 187, 'POL', NULL, 1, 'absolute', 18.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 188, 'NLD', NULL, 0, 'absolute', 12.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 189, 'NLD', NULL, 1, 'absolute', 24.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 191, 'MLT', NULL, 0, 'absolute', 2.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 192, 'MLT', NULL, 1, 'absolute', 2.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 193, 'ITA', NULL, 0, 'absolute', 2.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 194, 'ITA', NULL, 1, 'absolute', 15.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 200, 'AUT', NULL, 1, 'absolute', 10.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a style="color: #444444; text-decoration: none; border-bottom-width: 1px; border-bottom-style: solid; font-family: ''Lucida Grande'', ''Lucida Sans Unicode'', Verdana, Arial, Helvetica, sans-serif; font-size: 11.818181991577148px; line-height: 17.27272605895996px;" title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 202, 'CYP', NULL, 0, 'absolute', 200.00, 'of', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a style="color: #444444; text-decoration: none; border-bottom-width: 1px; border-bottom-style: solid; font-family: ''Lucida Grande'', ''Lucida Sans Unicode'', Verdana, Arial, Helvetica, sans-serif; font-size: 11.818181991577148px; line-height: 17.27272605895996px;" title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 204, 'CYP', NULL, 1, 'absolute', 177.00, 'of', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a style="color: #444444; text-decoration: none; border-bottom-width: 1px; border-bottom-style: solid; font-family: ''Lucida Grande'', ''Lucida Sans Unicode'', Verdana, Arial, Helvetica, sans-serif; font-size: 11.818181991577148px; line-height: 17.27272605895996px;" title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 205, 'GRC', NULL, 0, 'absolute', 112.00, 'of', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a style="color: #444444; text-decoration: none; border-bottom-width: 1px; border-bottom-style: solid; font-family: ''Lucida Grande'', ''Lucida Sans Unicode'', Verdana, Arial, Helvetica, sans-serif; font-size: 11.818181991577148px; line-height: 17.27272605895996px;" title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 206, 'GRC', NULL, 0, 'absolute', 1.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a style="color: #444444; text-decoration: none; border-bottom-width: 1px; border-bottom-style: solid; font-family: ''Lucida Grande'', ''Lucida Sans Unicode'', Verdana, Arial, Helvetica, sans-serif; font-size: 11.818181991577148px; line-height: 17.27272605895996px;" title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 208, 'ESP', NULL, 0, 'absolute', 146.00, 'of', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a style="color: #444444; text-decoration: none; border-bottom-width: 1px; border-bottom-style: solid; font-family: ''Lucida Grande'', ''Lucida Sans Unicode'', Verdana, Arial, Helvetica, sans-serif; font-size: 11.818181991577148px; line-height: 17.27272605895996px;" title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 209, 'ESP', NULL, 1, 'absolute', 125.00, 'of', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '', '', ''),
(1, 210, 'IRL', NULL, 0, 'absolute', 119.00, 'of', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a style="color: #444444; text-decoration: none; border-bottom-width: 1px; border-bottom-style: solid; font-family: ''Lucida Grande'', ''Lucida Sans Unicode'', Verdana, Arial, Helvetica, sans-serif; font-size: 11.818181991577148px; line-height: 17.27272605895996px;" title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 211, 'IRL', NULL, 1, 'absolute', 102.00, 'of', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a style="color: #444444; text-decoration: none; border-bottom-width: 1px; border-bottom-style: solid; font-family: ''Lucida Grande'', ''Lucida Sans Unicode'', Verdana, Arial, Helvetica, sans-serif; font-size: 11.818181991577148px; line-height: 17.27272605895996px;" title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 212, 'LUX', NULL, 0, 'absolute', 106.00, 'of', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a style="color: #444444; text-decoration: none; border-bottom-width: 1px; border-bottom-style: solid; font-family: ''Lucida Grande'', ''Lucida Sans Unicode'', Verdana, Arial, Helvetica, sans-serif; font-size: 11.818181991577148px; line-height: 17.27272605895996px;" title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 213, 'LUX', NULL, 1, 'absolute', 10.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a style="color: #444444; text-decoration: none; border-bottom-width: 1px; border-bottom-style: solid; font-family: ''Lucida Grande'', ''Lucida Sans Unicode'', Verdana, Arial, Helvetica, sans-serif; font-size: 11.818181991577148px; line-height: 17.27272605895996px;" title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 214, 'PRT', NULL, 0, 'absolute', 114.00, 'of', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a style="color: #444444; text-decoration: none; border-bottom-width: 1px; border-bottom-style: solid; font-family: ''Lucida Grande'', ''Lucida Sans Unicode'', Verdana, Arial, Helvetica, sans-serif; font-size: 11.818181991577148px; line-height: 17.27272605895996px;" title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 215, 'PRT', NULL, 1, 'absolute', 107.00, 'of', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a style="color: #444444; text-decoration: none; border-bottom-width: 1px; border-bottom-style: solid; font-family: ''Lucida Grande'', ''Lucida Sans Unicode'', Verdana, Arial, Helvetica, sans-serif; font-size: 11.818181991577148px; line-height: 17.27272605895996px;" title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 216, 'SVN', NULL, 0, 'absolute', 129.00, 'of', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a style="color: #444444; text-decoration: none; border-bottom-width: 1px; border-bottom-style: solid; font-family: ''Lucida Grande'', ''Lucida Sans Unicode'', Verdana, Arial, Helvetica, sans-serif; font-size: 11.818181991577148px; line-height: 17.27272605895996px;" title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 217, 'SVN', NULL, 1, 'absolute', 116.00, 'of', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a style="color: #444444; text-decoration: none; border-bottom-width: 1px; border-bottom-style: solid; font-family: ''Lucida Grande'', ''Lucida Sans Unicode'', Verdana, Arial, Helvetica, sans-serif; font-size: 11.818181991577148px; line-height: 17.27272605895996px;" title="GDRs EU pledge aggregation " href="http://gdrights.org/scorecard-info/eu-pledge-disaggregation/">http://gdrights.org/scorecard-info/eu-pledge-disaggregation/</a>', '', ''),
(1, 218, 'NZL', NULL, 0, 'absolute', 5.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', 'http://beehive.govt.nz/release/new-zealand-commits-2020-climate-change-target.', '', ''),
(1, 219, 'JPN', NULL, 0, 'absolute', 3.80, 'below', 'year', 2005, 0.000, 0.000, 0.000, 0.000, 2020, 1, 0, '', '<a href="http://climateactiontracker.org/countries/japan.html">Climate Action Tracker Nov 20 21013</a>', '"Japan revised its 2020 pledge on 15 November 2013 and now aims to reduce emissions by 3.8% compared with fiscal year 2005 levels by 2020.&nbsp; The new 2020 pledge is equivalent to an increase of 3.1% above 1990 levels and represents a strong decrease in ambition."', ''),
(1, 221, 'USA', NULL, 0, 'absolute', 26.00, 'below', 'year', 2005, NULL, 0.000, 0.000, 0.000, 2025, 1, 1, '', 'http://www.whitehouse.gov/the-press-office/2014/11/11/fact-sheet-us-china-joint-announcement-climate-change-and-clean-energy-c', '', ''),
(1, 222, 'USA', NULL, 1, 'absolute', 28.00, 'below', 'year', 2005, NULL, 0.000, 0.000, 0.000, 2025, 1, 1, '', 'http://www.whitehouse.gov/the-press-office/2014/11/11/fact-sheet-us-china-joint-announcement-climate-change-and-clean-energy-c', '{"unconditional":"yes"}', ''),
(1, 223, 'CHE', NULL, 0, 'absolute', 50.00, 'below', 'year', 1990, NULL, 0.000, 0.000, 0.000, 2030, 1, 0, '', 'http://climateactiontracker.org/countries/switzerland.html', 'No Finance', ''),
(1, 228, 'RUS', NULL, 0, 'absolute', 25.00, 'below', 'year', 1990, NULL, 0.000, 0.000, 0.000, 2030, 1, 1, '', 'http://climateactiontracker.org/countries/russianfederation.html', '', ''),
(1, 229, 'RUS', NULL, 0, 'absolute', 30.00, 'below', 'year', 1990, NULL, 0.000, 0.000, 0.000, 2030, 1, 1, '', 'http://climateactiontracker.org/countries/russianfederation.html', '', ''),
(1, 230, 'CAN', NULL, 0, 'absolute', 30.00, 'below', 'year', 2005, 0.000, 0.000, 0.000, 0.000, 2030, 1, 1, '', 'CAIT (http://cait.wri.org/indc/#/profile/Canada) <a href="http://www4.unfccc.int/submissions/INDC/Published%20Documents/Canada/1/INDC%20-%20Canada%20-%20English.pdf">official INDC submission</a>', '', ''),
(1, 236, 'MAR', NULL, 1, 'target_Mt', NULL, NULL, NULL, NULL, 148.000, 0.000, 0.000, 0.000, 2030, 1, 1, '', 'http://climateactiontracker.org/countries/morocco.html', '{"description_override":"reduce total emissions by 32% compared to Moroccan INDC baseline", "help_label":"<br><b>Important information on pledge, baseline calibration</b>", "help_title":"INDC Information", "help_text":"<p>&ldquo;Meeting this target will require an overall investment in the order of USD 45 billion, of which USD 35 billion is conditional upon international support through new climate finance mechanisms, such as the Green Climate Fund&rdquo;.</p><p>Morocco has provided an official baseline in its INDC submission. This baseline projection differs from the <a href=glossary.php#gloss_bau target=_s  elf>no-policies baseline</a> used by the Climate Equity Reference Calculator. We have compensated for this difference. The target emissions in 2030 are unchanged. </p>"}', '"Meeting this target will require an overall investment in the order of USD 45 billion, of which USD 35 billion is conditional upon international support through new climate finance mechanisms, such as the Green Climate Fund."'),
(1, 234, 'LIE', NULL, 0, 'intensity', 40.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2030, 1, 0, '', 'CAIT', '', ''),
(1, 235, 'ETH', NULL, 1, 'target_Mt', NULL, NULL, NULL, NULL, 145.000, 90.000, 95.000, -40.000, 2030, 1, 1, '', '', 'BAU (2030) in INDC submission (all GHG, and *including* land use emissions) is 400 Mt CO2e; 2030 unconditional target is 64% below BAU in 2030; this gives an emissions target of 144 Mt CO2e\n\n{"description_override":"reduce total emissions by 64% compared to Ethiopian INDC baseline", "help_label":"<br><b>Important information on pledge and baseline calibration</b>", "help_title":"INDC Information", "help_text":"<p>Ethiopia&#39;s INDC can only be interpreted in a manner that includes land use emissions.<p><p>Ethiopia has provided an official baseline in its INDC submission. This baseline projection differs from the <a href=glossary.php#gloss_bau target=_self>no-policies baseline</a> used by the Climate Equity Reference Calculator. We have compensated for this difference. The target emissions in 2030 are unchanged. </p>"}', '');
INSERT INTO `pledge` (`public`, `id`, `iso3`, `region`, `conditional`, `quantity`, `reduction_percent`, `rel_to`, `year_or_bau`, `rel_to_year`, `target_Mt`, `target_Mt_CO2`, `target_Mt_nonCO2`, `target_Mt_LULUCF`, `by_year`, `include_nonco2`, `include_lulucf`, `info_link`, `source`, `caveat`, `details`) VALUES
(1, 237, 'MAR', NULL, 0, 'target_Mt', NULL, NULL, NULL, NULL, 117.000, 0.000, 0.000, 0.000, 2030, 1, 1, '', 'http://climateactiontracker.org/countries/morocco.html', '{"description_override":"reduce total emissions by 13% compared to Moroccan INDC baseline", "help_label":"<br><b>Important information on baseline calibration</b>", "help_title":"INDC Baseline Calibration", "help_text":"<p>Morocco has provided an official baseline in its INDC submission. This baseline projection differs from the <a href=glossary.php#gloss_bau target=_self>no-policies baseline</a> used by the Climate Equity Reference Calculator. We have compensated for this difference. The target emissions in 2030 are unchanged.</p>"}', 'Conditional: 32% by 2030 compared to "business as usual" projected emissions. This commitment is contingent upon gaining access to new sources of finance and enhanced support, compared to that received over the past years, within the context of a new legally-binding agreement under the auspices of the UNFCCC. This target translates into a cumulative reduction of 401 Mt CO2eq over the period 2020&shy;-2030. Meeting this target will require an overall investment in the order of USD 45 billion, of which USD 35 billion is conditional upon international support through new climate finance mechanisms, such as the Green Climate Fund.'),
(1, 238, 'CHK', NULL, 0, 'intensity', 60.00, 'below', 'year', 2005, NULL, 0.000, 0.000, 0.000, 2030, 1, 0, '', 'China 2015 INDC', 'This is the low end of China''s 60% to 65% pledge.\n\n{"help_label":"<br><b>Important INDC information</b>", "help_title":"INDC information", "help_text":"Pledge does not include land use, but China also intends to increase the forest stock volume by around 4.5 billion cubic meters on the 2005 level.", "unconditional":"yes", "pledge_qualifier":"low end of range"}', '&bull;&nbsp;To achieve the peaking of carbon dioxide emissions around 2030 and making best efforts to peak early; <br />&bull; To lower carbon dioxide emissions per unit of GDP by 60% to 65% from the 2005 level; <br />&bull; To increase the share of non-fossil fuels in primary energy consumption to around 20%; and <br />&bull; To increase the forest stock volume by around 4.5 billion cubic meters on the 2005 level'),
(1, 242, 'JPN', NULL, 0, 'absolute', 25.40, 'below', 'year', 2005, NULL, 0.000, 0.000, 0.000, 2030, 1, 0, '', '', '', '<span id="docs-internal-guid-49a9c674-b78c-e9bd-ae60-33433a88c3a6"><span style="font-size: 13.3333333333333px; font-family: Arial; vertical-align: baseline; white-space: pre-wrap; background-color: transparent;">Reduction of 26% by 2030 compared to 2013 (25.4% reduction compared to 2005) (approximately 1.042 billion t-CO2 eq. as 2030 emissions)</span></span>'),
(1, 241, 'CHK', NULL, 1, 'intensity', 65.00, 'below', 'year', 2005, NULL, 0.000, 0.000, 0.000, 2030, 1, 0, '', 'China 2015 INDC', 'This is the high end of China''s 60% to 65% pledge.\n\n{"help_label":"<br><b>Important INDC information</b>", "help_title":"INDC Information", "help_text":"Pledge does not include land use, but China also intends to increase the forest stock volume by around 4.5 billion cubic meters on the 2005 level.", "unconditional":"yes", "pledge_qualifier":"high end of range"}', '&bull;&nbsp;To achieve the peaking of carbon dioxide emissions around 2030 and making best efforts to peak early;&nbsp;<br />&bull; To lower carbon dioxide emissions per unit of GDP by 60% to 65% from the 2005 level;&nbsp;<br />&bull; To increase the share of non-fossil fuels in primary energy consumption to around 20%; and&nbsp;<br />&bull; To increase the forest stock volume by around 4.5 billion cubic meters on the 2005 level'),
(1, 243, 'NZL', NULL, 0, 'absolute', 30.00, 'below', 'year', 2005, 0.000, 0.000, 0.000, 0.000, 2030, 1, 0, '', '', '', 'From INDC:&nbsp;<span id="docs-internal-guid-49a9c674-b78f-dcea-2c76-5cd41de753ed"><span style="font-size: 13.3333333333333px; font-family: Arial; vertical-align: baseline; white-space: pre-wrap; background-color: transparent;">This corresponds to a reduction of 11% from 1990 levels.</span></span>'),
(1, 244, 'SGP', NULL, 0, 'intensity', 36.00, 'below', 'year', 2005, 0.000, 0.000, 0.000, 0.000, 2030, 1, 0, '', '', '', 'Intends to reduce its Emissions Intensity by 36% from 2005 levels by 2030, and stabilise its emissions with the aim of peaking around 2030.<br /><br />Emissions Intensity in 2005: Singapore&rsquo;s greenhouse gas (GHG) emissions per S$GDP (at 2010 prices) in 2005 is 0.176 kgCO2e/S$. Projected Emissions Intensity in 2030: Singapore&rsquo;s GHG emissions per S$GDP (at 2010 prices) in 2030 is projected to be 0.113 kgCO2e/S$.'),
(1, 245, 'ISL', NULL, 0, 'absolute', 40.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2030, 1, 0, '', '', '', ''),
(1, 246, 'SRB', NULL, 0, 'absolute', 10.00, 'below', 'year', 1990, 0.000, 0.000, 0.000, 0.000, 2030, 1, 0, '', '', '', ''),
(1, 247, 'MHL', NULL, 0, 'target_Mt', NULL, NULL, NULL, NULL, 0.125, 0.000, 0.000, 0.000, 2025, 1, 0, '', '', '(1) BAU (2010) in INDC submission is 0.185 Mt CO2e; (2) 2030 non-conditional target is 32% below 2010 BAU in 2030; (3) (1) and (2) gives an emissions target of 0.125 CO2e\n\n{"help_label":"<br><b>Important information on baseline calibration</b>", "help_title":"INDC Baseline Calibration", "help_text":"<p>Marshall Islands has provided an official baseline in its INDC submission. This baseline projection differs from the <a href=glossary.php#gloss_bau target=_self>no-policies baseline</a> used by the Climate Equity Reference Calculator. We have compensated for this difference. The target emissions in 2025 are unchanged.</p>"}', ''),
(1, 248, 'MHL', NULL, 0, 'target_Mt', NULL, NULL, NULL, NULL, 0.110, 0.000, 0.000, 0.000, 2030, 1, 0, '', '', '(1) BAU (2010) in INDC submission is 0.185 Mt CO2e); (2) 2030 non-conditional target is 45% below 2010 BAU in 2030; (3) (1) and (2) gives an emissions target of 0.10175 CO2e; (4) absolute 2010 emissions for MHL are 0.11 Mt CO2e.\n\n{"help_label":"<br>Important information on baseline calibration", "help_title":"INDC Baseline Calibration", "help_text":"<p>Marshall Islands has provided an official baseline in its INDC submission. This baseline projection differs from the no-policies baseline used by the Climate Equity Reference Calculator. We have compensated for this difference. The target emissions in 2030 are unchanged.</p>"}', 'Higher pledge is noted in INDC as being "indicative." &nbsp;We have interpreted this as meaning Conditional.'),
(1, 261, 'AUS', NULL, 0, 'absolute', 26.00, 'below', 'year', 2005, NULL, NULL, NULL, NULL, 2030, 1, 1, '', 'http://cait.wri.org/indc/#/profile/Australia', '', ''),
(1, 262, 'AUS', NULL, 0, 'absolute', 28.00, 'below', 'year', 2005, NULL, NULL, NULL, NULL, 2030, 1, 0, '', 'http://cait.wri.org/indc/#/profile/Australia', '', ''),
(1, 251, 'KOR', NULL, 0, 'target_Mt', NULL, NULL, NULL, NULL, 536.000, 0.000, 0.000, 0.000, 2030, 1, 0, '', '', '(1) BAU (2030) in INDC submission is 850.6 Mt CO2e); (2) 2030 non-conditional target is 37% below BAU in 2030; (3) this means the 2030 INDC specifies a target of 535.878 Mt.\n\n{"help_label":"<br><b>Important information on baseline calibration</b>", "help_title":"INDC Baseline Calibration", "help_text":"<p>Korea has provided an official baseline in its INDC submission. This baseline projection differs from the <a href=glossary.php#gloss_bau target=_self>no-policies baseline</a> used by the Climate Equity Reference Calculator. We have compensated for this difference. The target emissions in 2030 are unchanged.</p>"}', '<span id="docs-internal-guid-49a9c674-b7d0-fc5b-b4ff-7cd54f135129"><span style="font-size: 13.3333333333333px; font-family: Arial; vertical-align: baseline; white-space: pre-wrap; background-color: transparent;">Pledge is to reduce emissions by 37% from the business-as-usual (BAU, 850.6 MtCO2eq) level by 2030 across all economic sectors.(National BAU: 782.5 MtCO2eq by 2020; 809.7 MtCO2eq by 2025; 850.6 MtCO2eq by 2030). </span></span>'),
(1, 250, 'GAB', NULL, 1, 'absolute', 50.00, 'below', 'year', 2000, NULL, 0.000, 0.000, 0.000, 2025, 1, 1, '', '', 'This INDC includes ag and forestry, but excludes deforestation. This is complicated, but we are interpreting this INDC as including forestry.', ''),
(1, 252, 'KEN', NULL, 1, 'target_Mt', NULL, NULL, NULL, NULL, 100.000, 0.000, 0.000, 0.000, 2030, 1, 1, '', '', '(1) BAU (2030) in INDC submission is 143 Mt CO2e; (2) 2030 conditional target is 30% in the BAU is 2030; (3) from (1) and (2) we get a 2030 emissions target of 100 Mt CO2e\n\n{"description_override":"reduce total emissions by 30% compared to Kenyan INDC baseline", "help_label":"<br><b>Important information on baseline calibration</b>", "help_title":"INDC Baseline Calibration", "help_text":"Kenya has provided an official baseline in its INDC submission. This baseline projection differs from the no-policies baseline used by the Climate Equity Reference Calculator. We have compensated for this difference. The target emissions in 2030 are unchanged."}', ''),
(0, 257, 'AFG', NULL, 0, 'target_Mt', NULL, NULL, NULL, NULL, 40.000, 20.000, 12.500, 7.500, 2030, 1, 0, '', '', '{"description_override":"reduce oil use by 80%"}', 'THIS IS A NONSENSICAL TARGET FOR TESTING ONLY - NOT PUBLIC'),
(1, 259, 'bla', NULL, 0, 'absolute', NULL, 'below', 'year', 1990, NULL, NULL, NULL, NULL, 2030, 1, 0, '', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `region`
--
-- Creation: Jun 17, 2015 at 03:38 AM
-- Last update: Jun 17, 2015 at 03:38 AM
--

DROP TABLE IF EXISTS `region`;
CREATE TABLE IF NOT EXISTS `region` (
  `region_code` char(32) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Truncate table before insert `region`
--

TRUNCATE TABLE `region`;
--
-- Dumping data for table `region`
--

INSERT INTO `region` (`region_code`, `name`) VALUES
('eu28', 'EU 28'),
('high_income', 'High Income'),
('upper_mid_income', 'Upper Middle Income'),
('lower_mid_income', 'Lower Middle Income'),
('low_income', 'Low Income'),
('annex_1', 'Annex 1'),
('annex_2', 'Annex 2'),
('non_annex_1', 'Non-Annex 1'),
('eit', 'EITs'),
('ldc', 'LDCs'),
('eu15', 'EU 15'),
('eu12', 'EU 12+'),
('OECD_NA', 'OECD North America'),
('OECD', 'OECD'),
('OECD_Europe', 'OECD Europe'),
('OECD_Pacific', 'OECD Pacific'),
('EE_Eurasia', 'Eastern Europe and Eurasia'),
('Asia', 'Non-OECD Asia'),
('Africa', 'Africa'),
('Middle_East', 'Middle East'),
('Latin_America', 'Latin America'),
('Non-OECD', 'Non-OECD'),
('world', 'World'),
('eu13', 'EU 13'),
('ASEAN', 'ASEAN'),
('CPA', 'Centrally Planned Asia'),
('SSA', 'Sub Saharan Africa'),
('NAMER', 'North America'),
('WEU', 'Western Europe'),
('MAF', 'Middle East and Africa'),
('PAS', 'Other Pacific Asia'),
('OECD90', 'OECD 1990 members'),
('MEA', 'Middle East and North Africa'),
('JPAUNZ', 'JPAUNZ');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `carbon_price`
--
ALTER TABLE `carbon_price`
  ADD PRIMARY KEY (`year`);

--
-- Indexes for table `country`
--
ALTER TABLE `country`
  ADD PRIMARY KEY (`iso3`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `intl_pledge`
--
ALTER TABLE `intl_pledge`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pledge`
--
ALTER TABLE `pledge`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `region`
--
ALTER TABLE `region`
  ADD PRIMARY KEY (`region_code`),
  ADD UNIQUE KEY `name` (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `intl_pledge`
--
ALTER TABLE `intl_pledge`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=82;
--
-- AUTO_INCREMENT for table `pledge`
--
ALTER TABLE `pledge`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=264;
