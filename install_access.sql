-- Таблицы для групп доступа/прав/привязок

CREATE TABLE IF NOT EXISTS `groups` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(128) NOT NULL,
  `days_mask` INT NOT NULL DEFAULT 127, -- биты 0..6 (Пн..Вс)
  `time_from` CHAR(5) NULL,
  `time_to` CHAR(5) NULL,
  `disabled` TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `group_pages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `group_id` INT NOT NULL,
  `page` VARCHAR(255) NOT NULL,
  INDEX(`group_id`),
  CONSTRAINT `fk_group_pages_group` FOREIGN KEY (`group_id`) REFERENCES `groups`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Набор прав/возможностей
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(64) NOT NULL UNIQUE,
  `description` VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Права, назначенные группе
CREATE TABLE IF NOT EXISTS `group_permissions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `group_id` INT NOT NULL,
  `permission_id` INT NOT NULL,
  UNIQUE KEY `uq_group_perm` (`group_id`, `permission_id`),
  CONSTRAINT `fk_gp_group` FOREIGN KEY (`group_id`) REFERENCES `groups`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_gp_perm` FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Привязка групп к пользователям
CREATE TABLE IF NOT EXISTS `user_groups` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `group_id` INT NOT NULL,
  UNIQUE KEY `uq_user_group` (`user_id`,`group_id`),
  INDEX(`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Доступ к конкретным словарям
CREATE TABLE IF NOT EXISTS `group_dicts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `group_id` INT NOT NULL,
  `dict_id` INT NOT NULL,
  UNIQUE KEY `uq_group_dict` (`group_id`,`dict_id`),
  CONSTRAINT `fk_gd_group` FOREIGN KEY (`group_id`) REFERENCES `groups`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Базовый наполнитель прав
INSERT IGNORE INTO `permissions` (`code`, `description`) VALUES
 ('view_home', 'Просмотр главной'),
 ('view_dicts', 'Просмотр словарей'),
 ('view_words', 'Просмотр слов'),
 ('add_word', 'Добавление слова'),
 ('edit_word', 'Редактирование слова'),
 ('delete_word', 'Удаление слова'),
 ('import_words', 'Импорт слов'),
 ('export_words', 'Экспорт слов'),
 ('transfer_words_authors', 'Импорт/экспорт слов/авторов');


