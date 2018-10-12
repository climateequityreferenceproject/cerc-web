--
-- Database: `pledges`
--
CREATE DATABASE IF NOT EXISTS `pledges` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `pledges`;

-- --------------------------------------------------------

--
-- Table structure for table `carbon_price`
--

DROP TABLE IF EXISTS `carbon_price`;
CREATE TABLE IF NOT EXISTS `carbon_price` (
  `year` int(11) NOT NULL,
  `c_price_USD_per_tCO2e` float NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `country`
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

DROP TABLE IF EXISTS `intl_pledge`;
CREATE TABLE IF NOT EXISTS `intl_pledge` (
  `id` int(11) NOT NULL,
  `iso3` char(3) COLLATE utf8_unicode_ci NOT NULL,
  `pledge_mln_USD` float NOT NULL,
  `source` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=MyISAM AUTO_INCREMENT=82 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pledge`
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


-- --------------------------------------------------------

--
-- Table structure for table `region`
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
