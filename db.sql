CREATE TABLE `products` (
`id` int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
`title` text NOT NULL DEFAULT '',
`technicalDetails` text NOT NULL DEFAULT '',
`picture` text NOT NULL DEFAULT '',
`url` text NOT NULL DEFAULT ''
);