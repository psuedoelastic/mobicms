--
-- Структура таблицы `news`
--
DROP TABLE IF EXISTS `news`;
CREATE TABLE `news` (
  `id`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `time`        INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `author`      VARCHAR(50)      NOT NULL DEFAULT '',
  `author_id`   INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `title`       VARCHAR(200)     NOT NULL DEFAULT '',
  `text`        TEXT             NOT NULL,
  `comm_enable` TINYINT(1)       NOT NULL DEFAULT '0',
  `comm_count`  INT(10) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

--
-- Структура таблицы `system__sessions`
--
DROP TABLE IF EXISTS `system__sessions`;
CREATE TABLE `system__sessions` (
  `session_id`        VARCHAR(100)          NOT NULL DEFAULT '',
  `session_timestamp` INT(10) UNSIGNED     NOT NULL DEFAULT '0',
  `session_data`      TEXT                 NOT NULL,
  `user_id`           INT(10) UNSIGNED     NOT NULL DEFAULT '0',
  `ip`                INT(10) UNSIGNED     NOT NULL DEFAULT '0',
  `ip_via_proxy`      INT(10) UNSIGNED     NOT NULL DEFAULT '0',
  `user_agent`        VARCHAR(255)         NOT NULL DEFAULT '',
  `place`             VARCHAR(200)         NOT NULL DEFAULT '',
  `views`             INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `movings`           INT(10) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`session_id`),
  KEY `online` (`user_id`, `session_timestamp`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

--
-- Структура таблицы `user__`
--
DROP TABLE IF EXISTS `user__`;
CREATE TABLE `user__` (
  `id`             INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
  `nickname`       VARCHAR(50)         NOT NULL DEFAULT '',
  `change_time`    INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `password`       VARCHAR(255)        NOT NULL DEFAULT '',
  `token`          VARCHAR(100)        NOT NULL DEFAULT '',
  `login_try`      TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `email`          VARCHAR(50)         NOT NULL DEFAULT '',
  `rights`         TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `level`          TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
  `ban`            TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `sex`            ENUM('m', 'w')      NOT NULL DEFAULT 'm',
  `avatar`         VARCHAR(255)        NOT NULL DEFAULT '',
  `imname`         VARCHAR(100)         NOT NULL DEFAULT '',
  `birth`          DATE                NOT NULL DEFAULT '0000-00-00',
  `count_comments` INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `count_forum`    INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `join_date`      INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `last_visit`     INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `icq`            INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `skype`          VARCHAR(50)         NOT NULL DEFAULT '',
  `siteurl`        VARCHAR(100)        NOT NULL DEFAULT '',
  `about`          TEXT                NOT NULL,
  `live`           VARCHAR(100)        NOT NULL DEFAULT '',
  `tel`            VARCHAR(100)        NOT NULL DEFAULT '',
  `status`         VARCHAR(255)        NOT NULL DEFAULT '',
  `mailvis`        TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `lastpost`       INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `comm_count`     INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `comm_old`       INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `ip`             INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `ip_via_proxy`   INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `user_agent`     VARCHAR(255)        NOT NULL DEFAULT '',
  `reputation`     TEXT                NOT NULL,
  PRIMARY KEY (`id`),
  KEY `last_visit` (`last_visit`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

--
-- Структура таблицы `user__ip`
--
DROP TABLE IF EXISTS `user__ip`;
CREATE TABLE `user__ip` (
  `id`           BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`      INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `ip`           INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `ip_via_proxy` INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `user_agent`   VARCHAR(200)        NOT NULL DEFAULT '',
  `timestamp`    INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `ip` (`ip`),
  KEY `ip_via_proxy` (`ip_via_proxy`),
  KEY `timestamp` (`timestamp`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

--
-- Структура таблицы `user__reputation`
--
DROP TABLE IF EXISTS `user__reputation`;
CREATE TABLE `user__reputation` (
  `from`  INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `to`    INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `value` TINYINT(4)       NOT NULL DEFAULT '0',
  PRIMARY KEY (`from`, `to`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

--
-- Структура таблицы `user__settings`
--
DROP TABLE IF EXISTS `user__settings`;
CREATE TABLE `user__settings` (
  `user_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `key`     VARCHAR(32)      NOT NULL DEFAULT '',
  `value`   TEXT             NOT NULL,
  PRIMARY KEY (`user_id`, `key`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

--
-- Создаем суперпользователя
-- LOGIN:    admin
-- PASSWORD: admin
--
INSERT INTO `user__`
SET `nickname`    = 'admin',
  `password`      = '$2a$09$3dc6eee4535ff2912c44fO4djfEMWdsfFM9dw4NKsWCaeLIRyzB6u',
  `email`         = 'admin@test.com',
  `rights`        = 9,
  `level`         = 1,
  `sex`           = 'm',
  `about`         = '';