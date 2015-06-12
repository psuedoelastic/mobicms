--
-- Структура таблицы `album__cat`
--
DROP TABLE IF EXISTS `album__cat`;
CREATE TABLE `album__cat` (
  `id`          INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
  `user_id`     INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `sort`        INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `name`        VARCHAR(100)        NOT NULL DEFAULT '',
  `description` TEXT                NOT NULL,
  `password`    VARCHAR(20)         NOT NULL DEFAULT '',
  `access`      TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `access` (`access`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

--
-- Структура таблицы `album__comments`
--
DROP TABLE IF EXISTS `album__comments`;
CREATE TABLE `album__comments` (
  `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sub_id`     INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `time`       INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `user_id`    INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `text`       TEXT             NOT NULL,
  `reply`      TEXT             NOT NULL,
  `attributes` TEXT             NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sub_id` (`sub_id`),
  KEY `user_id` (`user_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

--
-- Структура таблицы `album__downlosystem__advt` Разобраться, че за хуйня???
--
DROP TABLE IF EXISTS `album__downlosystem__advt`;
CREATE TABLE `album__downlosystem__advt` (
  `user_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `file_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `time`    INT(10) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`, `file_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

--
-- Структура таблицы `album__files`
--
DROP TABLE IF EXISTS `album__files`;
CREATE TABLE `album__files` (
  `id`                 INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
  `user_id`            INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `album_id`           INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `description`        TEXT                NOT NULL,
  `img_name`           VARCHAR(100)        NOT NULL DEFAULT '',
  `tmb_name`           VARCHAR(100)        NOT NULL DEFAULT '',
  `time`               INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `comments`           TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
  `comm_count`         INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `access`             TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `vote_plus`          INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `vote_minus`         INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `views`              INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `downlosystem__advt` INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `unread_comments`    TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `album_id` (`album_id`),
  KEY `access` (`access`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

--
-- Структура таблицы `album__views`
--
DROP TABLE IF EXISTS `album__views`;
CREATE TABLE `album__views` (
  `user_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `file_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `time`    INT(10) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`, `file_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

--
-- Структура таблицы `album__votes`
--
DROP TABLE IF EXISTS `album__votes`;
CREATE TABLE `album__votes` (
  `id`      INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `file_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `vote`    TINYINT(2)       NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `file_id` (`file_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;