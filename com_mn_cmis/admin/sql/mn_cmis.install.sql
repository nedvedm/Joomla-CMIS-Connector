CREATE TABLE IF NOT EXISTS `#__mn_cmis_stat` (
  `id` varchar(255) CHARACTER SET utf8 COLLATE utf8_czech_ci NOT NULL,
  `noderef` varchar(70) CHARACTER SET utf8 COLLATE utf8_czech_ci NOT NULL,
  `type` varchar(15) CHARACTER SET utf8 COLLATE utf8_czech_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_czech_ci NOT NULL,
  `sef_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;