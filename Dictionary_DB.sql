CREATE TABLE `users` (
  `id` integer PRIMARY KEY,
  `name` string,
  `email` string,
  `email_verified_at` timestamp,
  `password` string,
  `remember_token` string,
  `timestamps` timestamp
);

CREATE TABLE `words` (
  `id` interger,
  `user_id` interger,
  `word_name` string,
  `description` text,
  `timestamps` timestamp
);

CREATE TABLE `tags` (
  `id` interger,
  `user_id` interger,
  `tag_name` string,
  `timestamps` timestamp
);

CREATE TABLE `word_tag` (
  `word_id` interger,
  `tag_id` interger
);

ALTER TABLE `words` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `tags` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `word_tag` ADD FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`);

ALTER TABLE `word_tag` ADD FOREIGN KEY (`word_id`) REFERENCES `words` (`id`);
