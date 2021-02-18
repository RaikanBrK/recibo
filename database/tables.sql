CREATE DATABASE IF NOT EXISTS `receipt` DEFAULT CHARACTER SET utf8;
use receipt;

CREATE TABLE IF NOT EXISTS `receipt`.`auth_social` (
	id INT NOT NULL AUTO_INCREMENT,
	social CHAR(30) NOT NULL,

	PRIMARY KEY(`id`)
) ENGINE = InnoDB;

REPLACE INTO `receipt`.`auth_social` (id, social) VALUES (1, 'Email'),
(2, 'Facebook'),
(3, 'Google');

CREATE TABLE IF NOT EXISTS `receipt`.`usuarios` (
	id INT NOT NULL AUTO_INCREMENT,
	nome VARCHAR(40) NOT NULL,
	email VARCHAR(100) NOT NULL UNIQUE,
	senha VARCHAR(50) NULL,

	authSocialId INT NULL DEFAULT 1,
	FOREIGN KEY(authSocialId) REFERENCES auth_social(id),

	PRIMARY KEY(`id`)
) ENGINE = InnoDB;