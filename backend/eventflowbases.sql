CREATE DATABASE IF NOT EXISTS eventflow
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
 
USE eventflow;
 
-- ── USERS ──
CREATE TABLE IF NOT EXISTS users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(50)  NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('admin','user') DEFAULT 'user',
  full_name     VARCHAR(100),
  created_at    DATETIME DEFAULT CURRENT_TIMESTAMP
);
 
-- ── EVENTS ──
CREATE TABLE IF NOT EXISTS events (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  title       VARCHAR(200) NOT NULL,
  category    ENUM('academic','personal','profesional','social','alt') NOT NULL,
  priority    ENUM('inalta','medie','scazuta') NOT NULL,
  event_date  DATE NOT NULL,
  event_time  TIME,
  location    VARCHAR(200),
  status      ENUM('planificat','confirmat','anulat','finalizat') DEFAULT 'planificat',
  description TEXT,
  is_favorite TINYINT(1) DEFAULT 0,
  created_by  INT,
  created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);
 
-- ── WIKI ARTICLES ──
CREATE TABLE IF NOT EXISTS wiki_articles (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  title        VARCHAR(200) NOT NULL,
  content      TEXT,
  category     VARCHAR(100),
  icon         VARCHAR(10) DEFAULT '📄',
  is_published TINYINT(1) DEFAULT 1,
  created_by   INT,
  created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at   DATETIME ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);
 
-- ── SESSION LOG ──
CREATE TABLE IF NOT EXISTS session_log (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT,
  action     VARCHAR(100),
  ip_address VARCHAR(50),
  logged_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);