CREATE TABLE IF NOT EXISTS settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(128) NOT NULL UNIQUE,
  `value` TEXT NOT NULL
);

INSERT INTO settings(`key`,`value`) VALUES
('media_allowed_mime','image/jpeg,image/png,image/gif,image/webp,application/pdf')
ON CONFLICT DO NOTHING;

INSERT INTO settings(`key`,`value`) VALUES
('media_max_upload_mb','10')
ON CONFLICT DO NOTHING;
