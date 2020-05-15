CREATE DATABASE IF NOT EXISTS `xddp` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `xddp`;

CREATE TABLE `activity_enum` (
  `key_id` int(11) NOT NULL,
  `activity_id` int(11) NOT NULL,
  `enum_id` int(11) NOT NULL,
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `activity_list` (
  `id` int(11) NOT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '1',
  `user_id` int(11) NOT NULL DEFAULT '1',
  `year` smallint(6) NOT NULL DEFAULT '0',
  `month` tinyint(4) NOT NULL DEFAULT '0',
  `day` tinyint(4) NOT NULL DEFAULT '0',
  `time` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` varchar(400) COLLATE utf8mb4_unicode_ci NOT NULL,
  `img` varchar(3000) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '[""]',
  `extra_param` varchar(10000) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '{}',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `api_request_log` (
  `id` int(11) NOT NULL,
  `request_id` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` int(11) NOT NULL,
  `message` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL,
  `req_data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `res_data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `enum_list` (
  `id` int(11) NOT NULL,
  `type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '枚举父类型',
  `value` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '枚举值',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='枚举表';

INSERT INTO `enum_list` (`id`, `type`, `value`, `create_time`, `update_time`) VALUES
(1, 'place', '书房椅', '2020-03-01 13:05:45', '2020-04-21 22:28:19'),
(2, 'place', '书房地', '2020-03-01 13:05:59', '2020-04-21 22:28:19'),
(3, 'place', '座厕', '2020-03-01 13:05:37', '2020-04-21 22:28:19'),
(4, 'place', '洗澡', '2020-03-01 13:07:00', '2020-04-21 22:28:19'),
(5, 'place', '大床', '2020-03-01 13:06:06', '2020-04-21 22:28:19'),
(6, 'toy', '训练器', '2020-03-26 12:06:53', '2020-04-21 22:28:19'),
(7, 'toy', '飞机杯', '2020-03-26 12:06:53', '2020-04-21 22:28:19'),
(8, 'toy', '绿油', '2020-04-05 18:24:04', '2020-04-21 22:28:19'),
(9, 'toy', '红油', '2020-04-19 21:52:42', '2020-04-21 22:28:19'),
(10, 'shot', '2', '2020-04-22 08:54:41', '2020-04-22 08:54:41'),
(11, 'shot', '3', '2020-04-22 08:54:41', '2020-04-22 08:55:20'),
(12, 'shot', '4', '2020-04-22 08:54:41', '2020-04-22 08:55:20'),
(13, 'shot', '5', '2020-04-22 08:54:41', '2020-04-22 08:55:20'),
(14, 'shot', '6', '2020-04-22 08:54:41', '2020-04-22 08:55:20'),
(15, 'shot', '7', '2020-04-22 08:54:41', '2020-04-22 08:55:20'),
(16, 'toy', '套', '2020-05-06 20:10:27', '2020-05-06 20:10:27');

CREATE TABLE `enum_type` (
  `id` int(11) NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `extra_param` varchar(10000) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '{}',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `enum_type` (`id`, `type`, `name`, `extra_param`, `create_time`, `update_time`) VALUES
(1, 'place', '场所', '{\"multiple\":false}', '2020-04-22 08:53:15', '2020-04-22 11:17:34'),
(2, 'toy', '工具', '{\"multiple\":true}', '2020-04-22 08:53:39', '2020-04-22 11:17:34'),
(3, 'shot', '股数', '{\"multiple\":false}', '2020-04-22 08:53:39', '2020-04-22 11:17:34');

CREATE TABLE `see_file_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity_id` int(11) NOT NULL,
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `user_name` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nick_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `salt` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pin` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token_key` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` varchar(19) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


ALTER TABLE `activity_enum`
  ADD PRIMARY KEY (`key_id`),
  ADD KEY `xie_id` (`activity_id`,`enum_id`);

ALTER TABLE `activity_list`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Index 2` (`year`,`month`,`day`);

ALTER TABLE `api_request_log`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `request_id` (`request_id`);

ALTER TABLE `enum_list`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Index 2` (`type`);

ALTER TABLE `enum_type`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type` (`type`);

ALTER TABLE `see_file_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`,`activity_id`);

ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `activity_enum`
  MODIFY `key_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `activity_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `api_request_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `enum_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

ALTER TABLE `enum_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `see_file_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;
