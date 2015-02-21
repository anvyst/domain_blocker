DROP TABLE IF EXISTS `mod_domains_blocked`;
CREATE TABLE `mod_domains_blocked` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ipaddress` varchar(100) DEFAULT NULL,
  `domain` varchar(200) DEFAULT NULL,
  `attempts` int(11) DEFAULT '0',
  `description` mediumtext,
  `created` datetime DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_domain` (`domain`),
  KEY `idx_ipaddress` (`ipaddress`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `mod_domain_blocker`;
CREATE TABLE `mod_domain_blocker` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `pattern` varchar(200) NOT NULL,
  `pattern_type` enum('regexp','string') NOT NULL,
  `activated` tinyint(4) DEFAULT '1',
  `created` datetime DEFAULT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pattern` (`pattern`),
  KEY `idx_activated` (`activated`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
