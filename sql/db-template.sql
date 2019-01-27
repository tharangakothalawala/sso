--
-- Table to persist the third party connections
--
CREATE TABLE `thirdparty_connections` (
    `id` INT(10) NOT NULL AUTO_INCREMENT,
    `app_user_id` VARCHAR(255) NOT NULL COMMENT 'unique user id belongs to the application logic',
    `vendor_name` ENUM('facebook', 'instagram', 'twitter', 'google', 'slack', 'linkedin', 'yahoo') NOT NULL,
    `vendor_email` VARCHAR(255) NOT NULL COMMENT 'email address associated with the third party account',
    `vendor_access_token` TEXT,
    `vendor_data` TEXT COMMENT 'any other data related to the vendor user in JSON format',
    `created_at` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
    `updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `appuser_thirdparty_email` (`app_user_id`, `vendor_name`, `vendor_email`),
    KEY `thirdparty_email` (`vendor_name`, `vendor_email`),
    KEY `email` (`vendor_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 COMMENT='this stores the third party vendor tokens associating them to the app users';
