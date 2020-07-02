-- Misty3 Framework SQL

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `m_user_role`;
DROP TABLE IF EXISTS `m_user_acl`;
DROP TABLE IF EXISTS `m_user`;

CREATE TABLE `m_user_role` (`role_id` binary(16) NOT NULL, `role_name` varchar(255) NOT NULL, PRIMARY KEY (`role_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE `m_user` (`user_id` binary(16) NOT NULL, `user_login` varchar(255) NOT NULL, `user_password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL, `user_session_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_as_cs DEFAULT NULL, `role_id` binary(16) DEFAULT NULL, `create_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP, `update_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, `delete_date` datetime DEFAULT NULL, PRIMARY KEY (`user_id`), KEY `role_id` (`role_id`), KEY `user_session_id_delete_date` (`user_session_id`,`delete_date`), KEY `user_login_delete_date` (`user_login`,`delete_date`), CONSTRAINT `m_user_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `m_user_role` (`role_id`) ON DELETE SET NULL ON UPDATE RESTRICT) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE `m_user_acl` (`acl_id` binary(16) NOT NULL, `acl_module` varchar(255) NOT NULL, `acl_action` varchar(255) DEFAULT NULL, `acl_type` enum('ALLOW','DENY') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'ALLOW', `role_id` binary(16) DEFAULT NULL, `user_id` binary(16) DEFAULT NULL, KEY `user_id` (`user_id`), KEY `role_id` (`role_id`), CONSTRAINT `m_user_acl_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `m_user` (`user_id`) ON DELETE SET NULL, CONSTRAINT `m_user_acl_ibfk_3` FOREIGN KEY (`role_id`) REFERENCES `m_user_role` (`role_id`) ON DELETE SET NULL ON UPDATE RESTRICT) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

SET @m_user_role__uuid = UUID();
INSERT INTO `m_user_role` (`role_id`, `role_name`) VALUES (UUID_TO_BIN(@m_user_role__uuid, 0), 'Administrator');
INSERT INTO `m_user_acl` (`acl_id`, `acl_module`, `acl_action`, `acl_type`, `role_id`, `user_id`) VALUES (UUID_TO_BIN(UUID(), 0), 'main', NULL, 'ALLOW', UUID_TO_BIN(@m_user_role__uuid, 0), NULL);

SET @m_user__uuid = UUID();
INSERT INTO `m_user` (`user_id`, `user_login`, `user_password`, `user_session_id`, `role_id`, `create_date`) VALUES (UUID_TO_BIN(@m_user__uuid, 0), 'admin', '$2y$10$qhzTfZSHuM5WImS4.GfIUeho1/QuwREylM3DC9VKpxLK2WLZaxmpW', NULL, UUID_TO_BIN(@m_user_role__uuid, 0), NOW());
