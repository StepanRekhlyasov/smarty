CREATE DATABASE IF NOT EXISTS smarty;

USE smarty;

CREATE TABLE IF NOT EXISTS articles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    description TEXT NOT NULL,
    views_count INT UNSIGNED DEFAULT 0,
    image_url VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS articles_categories (
    article_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (article_id, category_id),
    FOREIGN KEY (article_id) REFERENCES articles(id),
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE articles_categories;
TRUNCATE TABLE articles;
TRUNCATE TABLE categories;
SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO articles (id, title, content, description) VALUES (1, 'Title of the Article', 'Content of the Article', 'Description of the Article');
INSERT INTO categories (id, title, description) VALUES (1, 'Title of the Category', 'Description of the Category');
INSERT INTO articles_categories (article_id, category_id) VALUES (1, 1);
