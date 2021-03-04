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
	senha VARCHAR(150) NULL,
	img VARCHAR(300) NULL,

	authSocialId INT NULL DEFAULT 1,
	FOREIGN KEY(authSocialId) REFERENCES auth_social(id),

	PRIMARY KEY(`id`)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `receipt`.`codigos_password_status` (
	id INT NOT NULL AUTO_INCREMENT,
	status CHAR(30) NOT NULL,

	PRIMARY KEY(`id`)
) ENGINE = InnoDB;

REPLACE INTO `receipt`.`codigos_password_status` (id, status) VALUES (1, 'Pendente'),
(3, 'Expirado');

CREATE TABLE IF NOT EXISTS `receipt`.`codigos_password` (
	id INT NOT NULL AUTO_INCREMENT,
	ip VARCHAR(70) NOT NULL,
	codigo CHAR(6) NOT NULL UNIQUE,

	status_id INT NOT NULL DEFAULT 1,
	FOREIGN KEY(status_id) REFERENCES codigos_password_status(id),

	usuario_id INT NOT NULL,
	FOREIGN KEY(usuario_id) REFERENCES usuarios(id),

	data_create DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

	PRIMARY KEY(`id`)
) ENGINE = InnoDB;