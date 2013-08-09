CREATE TABLE `db_analyzer` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR (50),
  `group` VARCHAR (50),
  `subgroup` VARCHAR (50),
  `sql_statement` TEXT,
  `comments` TEXT,
  `hidden` TINYINT (1) DEFAULT 0,
  PRIMARY KEY (`id`)
);