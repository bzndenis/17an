-- Fix both to knockout
UPDATE competitions SET system='knockout' WHERE id IN (1, 2);

-- Clear Billiard bracket (was generated as point system)
SET FOREIGN_KEY_CHECKS=0;
DELETE mp FROM match_participants mp INNER JOIN `matches` m ON mp.match_id=m.id WHERE m.competition_id=1;
DELETE FROM `matches` WHERE competition_id=1;
DELETE FROM rounds WHERE competition_id=1;
DELETE FROM rankings WHERE competition_id=1;
SET FOREIGN_KEY_CHECKS=1;

-- Rebuild Billiard knockout bracket (same as before)
INSERT INTO rounds (competition_id, round_number, name, created_at, updated_at) VALUES
(1, 1, 'Babak 1', NOW(), NOW()),
(1, 2, 'Perempat Final', NOW(), NOW()),
(1, 3, 'Semi Final', NOW(), NOW()),
(1, 4, 'Final', NOW(), NOW());

SET @r1 = (SELECT id FROM rounds WHERE competition_id=1 AND round_number=1);
SET @r2 = (SELECT id FROM rounds WHERE competition_id=1 AND round_number=2);
SET @r3 = (SELECT id FROM rounds WHERE competition_id=1 AND round_number=3);
SET @r4 = (SELECT id FROM rounds WHERE competition_id=1 AND round_number=4);

INSERT INTO `matches` (competition_id, round_id, match_number, status, bracket_position, created_at, updated_at) VALUES
(1, @r1, 1, 'scheduled', 2, NOW(), NOW()),
(1, @r1, 2, 'scheduled', 7, NOW(), NOW());

INSERT INTO `matches` (competition_id, round_id, match_number, status, bracket_position, created_at, updated_at) VALUES
(1, @r2, 3, 'scheduled', 1, NOW(), NOW()),
(1, @r2, 4, 'scheduled', 2, NOW(), NOW()),
(1, @r2, 5, 'scheduled', 3, NOW(), NOW()),
(1, @r2, 6, 'scheduled', 4, NOW(), NOW());

INSERT INTO `matches` (competition_id, round_id, match_number, status, bracket_position, created_at, updated_at) VALUES
(1, @r3, 7, 'scheduled', 1, NOW(), NOW()),
(1, @r3, 8, 'scheduled', 2, NOW(), NOW());

INSERT INTO `matches` (competition_id, round_id, match_number, status, bracket_position, created_at, updated_at) VALUES
(1, @r4, 9, 'scheduled', 1, NOW(), NOW());

SET @m1 = (SELECT id FROM `matches` WHERE competition_id=1 AND match_number=1);
SET @m2 = (SELECT id FROM `matches` WHERE competition_id=1 AND match_number=2);
SET @qf1 = (SELECT id FROM `matches` WHERE competition_id=1 AND match_number=3);
SET @qf2 = (SELECT id FROM `matches` WHERE competition_id=1 AND match_number=4);
SET @qf3 = (SELECT id FROM `matches` WHERE competition_id=1 AND match_number=5);
SET @qf4 = (SELECT id FROM `matches` WHERE competition_id=1 AND match_number=6);
SET @sf1 = (SELECT id FROM `matches` WHERE competition_id=1 AND match_number=7);
SET @sf2 = (SELECT id FROM `matches` WHERE competition_id=1 AND match_number=8);
SET @fin = (SELECT id FROM `matches` WHERE competition_id=1 AND match_number=9);

UPDATE `matches` SET next_match_id=@qf1 WHERE id=@m1;
UPDATE `matches` SET next_match_id=@qf4 WHERE id=@m2;
UPDATE `matches` SET next_match_id=@sf1 WHERE id=@qf1;
UPDATE `matches` SET next_match_id=@sf1 WHERE id=@qf2;
UPDATE `matches` SET next_match_id=@sf2 WHERE id=@qf3;
UPDATE `matches` SET next_match_id=@sf2 WHERE id=@qf4;
UPDATE `matches` SET next_match_id=@fin WHERE id=@sf1;
UPDATE `matches` SET next_match_id=@fin WHERE id=@sf2;

INSERT INTO match_participants (match_id, participant_id, created_at, updated_at) VALUES
(@m1, 8, NOW(), NOW()), (@m1, 9, NOW(), NOW());
INSERT INTO match_participants (match_id, participant_id, created_at, updated_at) VALUES
(@m2, 7, NOW(), NOW()), (@m2, 10, NOW(), NOW());
INSERT INTO match_participants (match_id, participant_id, created_at, updated_at) VALUES (@qf1, 1, NOW(), NOW());
INSERT INTO match_participants (match_id, participant_id, created_at, updated_at) VALUES (@qf2, 5, NOW(), NOW()), (@qf2, 4, NOW(), NOW());
INSERT INTO match_participants (match_id, participant_id, created_at, updated_at) VALUES (@qf3, 3, NOW(), NOW()), (@qf3, 6, NOW(), NOW());
INSERT INTO match_participants (match_id, participant_id, created_at, updated_at) VALUES (@qf4, 2, NOW(), NOW());
