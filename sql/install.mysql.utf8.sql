CREATE TABLE IF NOT EXISTS `contacttodb` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NULL COLLATE 'utf8mb4_unicode_ci',
	`email` VARCHAR(255) NULL COLLATE 'utf8mb4_unicode_ci',
	`subject` VARCHAR(2048) NULL COLLATE 'utf8mb4_unicode_ci',
	`message` TEXT NULL COLLATE 'utf8mb4_unicode_ci',
	`fields` TEXT NULL COLLATE 'utf8mb4_unicode_ci',
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`) USING BTREE,
	FULLTEXT INDEX `subject` (`subject`),
	FULLTEXT INDEX `fields` (`fields`)
)
COMMENT='Datastore from plugin contacttodb'
COLLATE='utf8mb4_unicode_ci'
ENGINE=MyISAM AUTO_INCREMENT=1
;