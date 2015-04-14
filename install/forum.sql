--
-- Структура таблицы `forum__`
--
DROP TABLE IF EXISTS `forum__`;
CREATE TABLE `forum__` (
  `id`           INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
  `refid`        INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `type`         CHAR(1)             NOT NULL DEFAULT '',
  `time`         INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `user_id`      INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `from`         VARCHAR(25)         NOT NULL DEFAULT '',
  `realid`       INT(3)              NOT NULL DEFAULT '0',
  `ip`           INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `ip_via_proxy` INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `soft`         TEXT                NOT NULL,
  `text`         TEXT                NOT NULL,
  `close`        TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `close_who`    VARCHAR(50)         NOT NULL DEFAULT '',
  `vip`          TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `edit`         TEXT                NOT NULL,
  `tedit`        INT(11)             NOT NULL DEFAULT '0',
  `kedit`        INT(2)              NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `refid` (`refid`),
  KEY `type` (`type`),
  KEY `time` (`time`),
  KEY `close` (`close`),
  KEY `user_id` (`user_id`),
  FULLTEXT KEY `text` (`text`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

--
-- Структура таблицы `forum__curators`
--
DROP TABLE IF EXISTS `forum__curators`;
CREATE TABLE `forum__curators` (
  `id`                INT(10) UNSIGNED    NOT NULL,
  `user_id`           INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `topic_id`          INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `rename_topic`      TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `close_topic`       TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
  `change_first_post` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
  `delete_posts`      TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
  `edit_posts`        TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `create_pool`       TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
  `edit_pool`         TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `delete_pool`       TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
  `kick_users`        TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `curators` (`user_id`, `topic_id`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

--
-- Структура таблицы `forum__files`
--
DROP TABLE IF EXISTS `forum__files`;
CREATE TABLE `forum__files` (
  `id`       INT(10) UNSIGNED    NOT NULL AUTO_INCREMENT,
  `cat`      INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `subcat`   INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `topic`    INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `post`     INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `time`     INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `filename` TEXT                NOT NULL,
  `filetype` TINYINT(4) UNSIGNED NOT NULL DEFAULT '0',
  `dlcount`  INT(10) UNSIGNED    NOT NULL DEFAULT '0',
  `del`      TINYINT(1)          NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `cat` (`cat`),
  KEY `subcat` (`subcat`),
  KEY `topic` (`topic`),
  KEY `post` (`post`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

--
-- Структура таблицы `forum__rdm`
--
DROP TABLE IF EXISTS `forum__rdm`;
CREATE TABLE `forum__rdm` (
  `topic_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `user_id`  INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `time`     INT(10) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`topic_id`, `user_id`),
  KEY `time` (`time`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

--
-- Структура таблицы `forum__vote`
--
DROP TABLE IF EXISTS `forum__vote`;
CREATE TABLE `forum__vote` (
  `id`    INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type`  INT(2) UNSIGNED  NOT NULL DEFAULT '0',
  `time`  INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `topic` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `name`  VARCHAR(200)     NOT NULL DEFAULT '',
  `count` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `topic` (`topic`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

--
-- Структура таблицы `forum__vote_users`
--
DROP TABLE IF EXISTS `forum__vote_users`;
CREATE TABLE `forum__vote_users` (
  `id`    INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user`  INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `topic` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `vote`  INT(10) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `topic` (`topic`)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;
