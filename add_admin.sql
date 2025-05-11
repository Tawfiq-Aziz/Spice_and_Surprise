-- Add admin user type to User table if not exists
ALTER TABLE `User` 
ADD COLUMN `user_type` ENUM('user', 'vendor', 'admin') DEFAULT 'user';

-- Create admin user (password: admin123)
INSERT INTO `User` (`username`, `password`, `email`, `user_type`) VALUES
('admin', '$2y$10$8K1p/a0dR1xqM8K1p/a0dR1xqM8K1p/a0dR1xqM8K1p/a0dR1xqM', 'admin@spiceandsurprise.com', 'admin'); 