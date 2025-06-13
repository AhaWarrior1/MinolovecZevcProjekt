/*
Created: 6/11/2025
Modified: 6/12/2025
Model: MinesweeperModel
Database: MySQL 8.0
*/

-- Create tables section -------------------------------------------------

-- Table reports

CREATE TABLE `reports`
(
  `id_reports` Int NOT NULL AUTO_INCREMENT,
  `reason` Text NOT NULL,
  `time_reported` Timestamp NOT NULL,
  `processed` Int(1) NOT NULL,
  `id_comments` Int NOT NULL,
  `id_users` Int NOT NULL,
  PRIMARY KEY (`id_reports`)
)
;

CREATE INDEX `IX_Relationship7` ON `reports` (`id_comments`)
;

CREATE INDEX `IX_Relationship9` ON `reports` (`id_users`)
;

-- Table statistics

CREATE TABLE `statistics`
(
  `id_statistics` Int NOT NULL AUTO_INCREMENT,
  `win_count` Int NOT NULL,
  `game_count` Int NOT NULL,
  `best_time` Float NOT NULL,
  `average_time` Float NOT NULL,
  `id_users` Int,
  PRIMARY KEY (`id_statistics`)
)
;

CREATE INDEX `IX_Relationship1` ON `statistics` (`id_users`)
;

-- Table results

CREATE TABLE `results`
(
  `id_results` Int NOT NULL AUTO_INCREMENT,
  `time` Float NOT NULL,
  `playing_date` Timestamp NOT NULL,
  `id_users` Int NOT NULL,
  `id_configurations` Int,
  PRIMARY KEY (`id_results`)
)
;

CREATE INDEX `IX_Relationship2` ON `results` (`id_users`)
;

CREATE INDEX `IX_Relationship3` ON `results` (`id_configurations`)
;

-- Table users

CREATE TABLE `users`
(
  `id_users` Int NOT NULL AUTO_INCREMENT,
  `username` Varchar(64) NOT NULL,
  `password` Varchar(70) NOT NULL,
  `email` Varchar(50) NOT NULL,
  `registration_date` Timestamp NOT NULL,
  `banned` Int(1) NOT NULL,
  `admin` Int(1) NOT NULL,
  PRIMARY KEY (`id_users`)
)
;

-- Table users_achievements

CREATE TABLE `users_achievements`
(
  `id_users_achievements` Int NOT NULL AUTO_INCREMENT,
  `date_achieved` Timestamp NOT NULL,
  `id_users` Int NOT NULL,
  `id_achievements` Int NOT NULL,
  PRIMARY KEY (`id_users_achievements`)
)
;

CREATE INDEX `IX_Relationship4` ON `users_achievements` (`id_users`)
;

CREATE INDEX `IX_Relationship5` ON `users_achievements` (`id_achievements`)
;

-- Table achievements

CREATE TABLE `achievements`
(
  `id_achievements` Int NOT NULL AUTO_INCREMENT,
  `name` Varchar(30) NOT NULL,
  `description` Varchar(50),
  PRIMARY KEY (`id_achievements`)
)
;

-- Table configurations

CREATE TABLE `configurations`
(
  `id_configurations` Int NOT NULL AUTO_INCREMENT,
  `width` Int NOT NULL,
  `height` Int NOT NULL,
  `mine_count` Int NOT NULL,
  PRIMARY KEY (`id_configurations`)
)
;

-- Table comments

CREATE TABLE `comments`
(
  `id_comments` Int NOT NULL AUTO_INCREMENT,
  `content` Text NOT NULL,
  `post_time` Timestamp NULL,
  `id_users` Int NOT NULL,
  `id_results` Int NOT NULL,
  PRIMARY KEY (`id_comments`)
)
;

CREATE INDEX `IX_Relationship6` ON `comments` (`id_users`)
;

CREATE INDEX `IX_Relationship8` ON `comments` (`id_results`)
;

-- Create foreign keys (relationships) section -------------------------------------------------

ALTER TABLE `statistics` ADD CONSTRAINT `Relationship1` FOREIGN KEY (`id_users`) REFERENCES `users` (`id_users`) ON DELETE RESTRICT ON UPDATE RESTRICT
;

ALTER TABLE `results` ADD CONSTRAINT `Relationship2` FOREIGN KEY (`id_users`) REFERENCES `users` (`id_users`) ON DELETE RESTRICT ON UPDATE RESTRICT
;

ALTER TABLE `results` ADD CONSTRAINT `Relationship3` FOREIGN KEY (`id_configurations`) REFERENCES `configurations` (`id_configurations`) ON DELETE RESTRICT ON UPDATE RESTRICT
;

ALTER TABLE `users_achievements` ADD CONSTRAINT `Relationship4` FOREIGN KEY (`id_users`) REFERENCES `users` (`id_users`) ON DELETE RESTRICT ON UPDATE RESTRICT
;

ALTER TABLE `users_achievements` ADD CONSTRAINT `Relationship5` FOREIGN KEY (`id_achievements`) REFERENCES `achievements` (`id_achievements`) ON DELETE RESTRICT ON UPDATE RESTRICT
;

ALTER TABLE `comments` ADD CONSTRAINT `Relationship6` FOREIGN KEY (`id_users`) REFERENCES `users` (`id_users`) ON DELETE RESTRICT ON UPDATE RESTRICT
;

ALTER TABLE `reports` ADD CONSTRAINT `Relationship7` FOREIGN KEY (`id_comments`) REFERENCES `comments` (`id_comments`) ON DELETE RESTRICT ON UPDATE RESTRICT
;

ALTER TABLE `comments` ADD CONSTRAINT `Relationship8` FOREIGN KEY (`id_results`) REFERENCES `results` (`id_results`) ON DELETE RESTRICT ON UPDATE RESTRICT
;

ALTER TABLE `reports` ADD CONSTRAINT `Relationship9` FOREIGN KEY (`id_users`) REFERENCES `users` (`id_users`) ON DELETE RESTRICT ON UPDATE RESTRICT
;


