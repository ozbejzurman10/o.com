-- Tabela "follows"

CREATE TABLE follows (
  following_user_id int(11) NOT NULL,
  followed_user_id int(11) NOT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp()
)


INSERT INTO follows (following_user_id, followed_user_id, created_at) VALUES
(11, 12, '2026-03-28 12:00:00'),
(12, 11, '2026-03-28 12:00:00');

-- Tabela "likes"

CREATE TABLE likes (
  user_id int(11) NOT NULL,
  post_id int(11) NOT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp()
)

-- Tabela "posts"

CREATE TABLE posts (
  id int(11) NOT NULL,
  title varchar(255) NOT NULL,
  content text NOT NULL,
  user_id int(11) NOT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp()
)


INSERT INTO posts (id, title, content, user_id, created_at) VALUES
(19, 'Test Objava', 'Vsebina objave\r\n\r\nNova vrstica', 13, '2026-03-28 12:00:00');

-- Tabela "users"

CREATE TABLE users (
  id int(11) NOT NULL,
  username varchar(255) NOT NULL,
  display_name varchar(255) NOT NULL,
  email varchar(255) NOT NULL,
  password varchar(255) NOT NULL,
  profile_image varchar(255) DEFAULT 'default.png',
  bio text DEFAULT NULL,
  user_role enum('user','admin') NOT NULL DEFAULT 'user',
  created_at timestamp NOT NULL DEFAULT current_timestamp()
)

INSERT INTO users (id, username, display_name, email, password, profile_image, bio, user_role, created_at) VALUES
(11, 'test1', 'Uporabnik Ena', 'testmail@gmail.com', '$2y$10$FG5.3KPeFXtoeqckiGF1ouFUVpvFRKqNRSwWOt6wH5BAV7nkfWIaO', 'default.png', 'Nek opis uporabnika\r\nNova vrstica', 'user', '2026-03-27 10:51:57'),
(12, 'ozbej_admin', 'Ozbej Admin', 'testmai12e123l@gmail.com', '$2y$10$L3BoOQz6QM5pODiwoZEg4etXiHsPPj4wmUuAUXivJ8.K9WyTvsTK.', 'user_pfp69c7b5b0104da1.00664019.jpg', 'Admin @ o.com', 'admin', '2026-03-27 10:52:21'),
(13, 'test2', 'Uporabnik Dve', 'testmsegsegail@gmail.com', '$2y$10$mXcltxvWPfJwbB82/i0yMONEola7HxigtYc4WuZRgGonC0H0F04mW', 'default.png', '', 'user', '2026-03-28 11:00:33');


ALTER TABLE follows
  ADD PRIMARY KEY (following_user_id,followed_user_id),
  ADD KEY fk_followed_user (followed_user_id);


ALTER TABLE likes
  ADD PRIMARY KEY (user_id,post_id),
  ADD KEY fk_likes_post (post_id);


ALTER TABLE posts
  ADD PRIMARY KEY (id),
  ADD KEY fk_post_user (user_id);


ALTER TABLE users
  ADD PRIMARY KEY (id),
  ADD UNIQUE KEY username (username),
  ADD UNIQUE KEY email (email);


ALTER TABLE posts
  MODIFY id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;


ALTER TABLE users
  MODIFY id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;


ALTER TABLE follows
  ADD CONSTRAINT fk_followed_user FOREIGN KEY (followed_user_id) REFERENCES users (id) ON DELETE CASCADE,
  ADD CONSTRAINT fk_following_user FOREIGN KEY (following_user_id) REFERENCES users (id) ON DELETE CASCADE;


ALTER TABLE likes
  ADD CONSTRAINT fk_likes_post FOREIGN KEY (post_id) REFERENCES posts (id) ON DELETE CASCADE,
  ADD CONSTRAINT fk_likes_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE;

ALTER TABLE posts
  ADD CONSTRAINT fk_post_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE;
COMMIT;
