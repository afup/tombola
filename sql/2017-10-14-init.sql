CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `nickname` varchar(255) NOT NULL,
  `avatar` varchar(255) NOT NULL,
  `date_game` date NOT NULL,
  `name` varchar(255) NOT NULL
) COMMENT='' ENGINE='InnoDB';

ALTER TABLE `users`
ADD UNIQUE `avatar_date_game` (`avatar`, `date_game`);

ALTER TABLE `users`
ADD `admin` tinyint(1) unsigned NOT NULL,
COMMENT='';
