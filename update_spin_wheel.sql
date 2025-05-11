-- Update spin_challenge table structure
ALTER TABLE `spin_challenge` 
ADD COLUMN `time_limit` int(11) DEFAULT 60,
ADD COLUMN `points_reward` int(11) DEFAULT 50;

-- Update spin_challenge_option table structure
ALTER TABLE `spin_challenge_option` 
ADD COLUMN `time_limit` int(11) DEFAULT 60,
ADD COLUMN `points_reward` int(11) DEFAULT 50;

-- First, insert a record into spin_challenge table
INSERT INTO `spin_challenge` (`challenge_id`, `max_tries`) VALUES
(1, 3);

-- Then insert sample spin wheel tasks
INSERT INTO `spin_challenge_option` (`sp_id`, `option_text`) VALUES
(1, 'Try 3 different types of jhalmuri in 1 hour'),
(1, 'Visit 2 different street food vendors in 45 minutes'),
(1, 'Try 4 different types of chaat in 1 hour'),
(1, 'Complete a food bingo card in 30 minutes'),
(1, 'Try 5 different types of street food in 1.5 hours'),
(1, 'Visit 3 different food stalls in 1 hour'),
(1, 'Try 2 different types of biryani in 45 minutes'),
(1, 'Complete a dessert challenge in 30 minutes');

-- Add foreign key constraints
ALTER TABLE `spin_challenge_option`
ADD CONSTRAINT `fk_spin_challenge` 
FOREIGN KEY (`sp_id`) REFERENCES `spin_challenge` (`challenge_id`) 
ON DELETE CASCADE;

-- Add index for better performance
CREATE INDEX `idx_spin_challenge_id` ON `spin_challenge_option` (`sp_id`); 