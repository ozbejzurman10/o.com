CREATE DATABASE o_com;
USE o_com;

CREATE TABLE users (
  id int(11) NOT NULL AUTO_INCREMENT,
  username varchar(255) NOT NULL UNIQUE,
  display_name varchar(255) NOT NULL,
  email varchar(255) NOT NULL UNIQUE,
  password varchar(255) NOT NULL,
  profile_image varchar(255) DEFAULT 'default.png',
  bio text DEFAULT NULL,
  user_role enum('user','admin') NOT NULL DEFAULT 'user',
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id)
);

CREATE TABLE posts (
  id int(11) NOT NULL AUTO_INCREMENT,
  title varchar(255) NOT NULL,
  content text NOT NULL,
  user_id int(11) NOT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE follows (
  following_user_id int(11) NOT NULL,
  followed_user_id int(11) NOT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (following_user_id, followed_user_id),
  FOREIGN KEY (following_user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (followed_user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE likes (
  user_id int(11) NOT NULL,
  post_id int(11) NOT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (user_id, post_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
);

INSERT INTO users (id, username, display_name, email, password, profile_image, bio, user_role, created_at) VALUES
(11, 'test1', 'Uporabnik Ena', 'testmail@gmail.com', '$2y$10$FG5.3KPeFXtoeqckiGF1ouFUVpvFRKqNRSwWOt6wH5BAV7nkfWIaO', 'default.png', 'Nek opis uporabnika\r\nNova vrstica', 'user', '2026-03-27 10:51:57'),
(12, 'ozbej_admin', 'Ozbej Admin', 'testmai12e123l@gmail.com', '$2y$10$L3BoOQz6QM5pODiwoZEg4etXiHsPPj4wmUuAUXivJ8.K9WyTvsTK.', 'user_pfp69c7b5b0104da1.00664019.jpg', 'Admin @ o.com', 'admin', '2026-03-27 10:52:21'),
(13, 'test2', 'Uporabnik Dve', 'testmsegsegail@gmail.com', '$2y$10$mXcltxvWPfJwbB82/i0yMONEola7HxigtYc4WuZRgGonC0H0F04mW', 'default.png', '', 'user', '2026-03-28 11:00:33');

INSERT INTO posts (id, title, content, user_id, created_at) VALUES
(19, 'Test Objava', 'Vsebina objave\r\n\r\nNova vrstica', 13, '2026-03-28 12:00:00');

INSERT INTO follows (following_user_id, followed_user_id, created_at) VALUES
(11, 12, '2026-03-28 12:00:00'),
(12, 11, '2026-03-28 12:00:00');
