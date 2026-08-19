-- Clear bracket data
SET FOREIGN_KEY_CHECKS=0;
TRUNCATE match_participants;
TRUNCATE match_results;
TRUNCATE `matches`;
TRUNCATE rounds;
SET FOREIGN_KEY_CHECKS=1;

-- Fix competition systems
UPDATE competitions SET system='knockout' WHERE id IN (1, 2);

-- ============================================================
-- Billiard (comp_id=1): 10 peserta, knockout
-- Standard seeding 16-bracket: Seed8 vs Seed9, Seed7 vs Seed10 (rest bye)
-- Peserta: 1=Harrid,2=Sandi,3=Denis,4=Dimas,5=Rafi,6=Fahmi,7=Alivia,8=Marsya,9=Maman,10=Bayu
-- ============================================================

INSERT INTO rounds (competition_id, round_number, name, created_at, updated_at) VALUES
(1, 1, 'Babak 1', NOW(), NOW()),
(1, 2, 'Perempat Final', NOW(), NOW()),
(1, 3, 'Semi Final', NOW(), NOW()),
(1, 4, 'Final', NOW(), NOW());

SET @r1 = (SELECT id FROM rounds WHERE competition_id=1 AND round_number=1);
SET @r2 = (SELECT id FROM rounds WHERE competition_id=1 AND round_number=2);
SET @r3 = (SELECT id FROM rounds WHERE competition_id=1 AND round_number=3);
SET @r4 = (SELECT id FROM rounds WHERE competition_id=1 AND round_number=4);

-- R1: 2 actual matches
INSERT INTO `matches` (competition_id, round_id, match_number, status, bracket_position, created_at, updated_at) VALUES
(1, @r1, 1, 'scheduled', 2, NOW(), NOW()),
(1, @r1, 2, 'scheduled', 7, NOW(), NOW());

-- R2: 4 QF
INSERT INTO `matches` (competition_id, round_id, match_number, status, bracket_position, created_at, updated_at) VALUES
(1, @r2, 3, 'scheduled', 1, NOW(), NOW()),
(1, @r2, 4, 'scheduled', 2, NOW(), NOW()),
(1, @r2, 5, 'scheduled', 3, NOW(), NOW()),
(1, @r2, 6, 'scheduled', 4, NOW(), NOW());

-- R3: 2 SF
INSERT INTO `matches` (competition_id, round_id, match_number, status, bracket_position, created_at, updated_at) VALUES
(1, @r3, 7, 'scheduled', 1, NOW(), NOW()),
(1, @r3, 8, 'scheduled', 2, NOW(), NOW());

-- R4: Final
INSERT INTO `matches` (competition_id, round_id, match_number, status, bracket_position, created_at, updated_at) VALUES
(1, @r4, 9, 'scheduled', 1, NOW(), NOW());

-- Get match IDs
SET @m1 = (SELECT id FROM `matches` WHERE competition_id=1 AND match_number=1);
SET @m2 = (SELECT id FROM `matches` WHERE competition_id=1 AND match_number=2);
SET @qf1 = (SELECT id FROM `matches` WHERE competition_id=1 AND match_number=3);
SET @qf2 = (SELECT id FROM `matches` WHERE competition_id=1 AND match_number=4);
SET @qf3 = (SELECT id FROM `matches` WHERE competition_id=1 AND match_number=5);
SET @qf4 = (SELECT id FROM `matches` WHERE competition_id=1 AND match_number=6);
SET @sf1 = (SELECT id FROM `matches` WHERE competition_id=1 AND match_number=7);
SET @sf2 = (SELECT id FROM `matches` WHERE competition_id=1 AND match_number=8);
SET @fin = (SELECT id FROM `matches` WHERE competition_id=1 AND match_number=9);

-- next_match_id links
UPDATE `matches` SET next_match_id=@qf1 WHERE id=@m1;
UPDATE `matches` SET next_match_id=@qf4 WHERE id=@m2;
UPDATE `matches` SET next_match_id=@sf1 WHERE id=@qf1;
UPDATE `matches` SET next_match_id=@sf1 WHERE id=@qf2;
UPDATE `matches` SET next_match_id=@sf2 WHERE id=@qf3;
UPDATE `matches` SET next_match_id=@sf2 WHERE id=@qf4;
UPDATE `matches` SET next_match_id=@fin WHERE id=@sf1;
UPDATE `matches` SET next_match_id=@fin WHERE id=@sf2;

-- R1 participants (Marsya=8 vs Maman=9, Alivia=7 vs Bayu=10)
INSERT INTO match_participants (match_id, participant_id, created_at, updated_at) VALUES
(@m1, 8, NOW(), NOW()), (@m1, 9, NOW(), NOW()),
(@m2, 7, NOW(), NOW()), (@m2, 10, NOW(), NOW());

-- QF bye participants
-- QF1: Harrid(1) waits for winner of M1
INSERT INTO match_participants (match_id, participant_id, created_at, updated_at) VALUES (@qf1, 1, NOW(), NOW());
-- QF2: Rafi(5) vs Dimas(4)
INSERT INTO match_participants (match_id, participant_id, created_at, updated_at) VALUES (@qf2, 5, NOW(), NOW()), (@qf2, 4, NOW(), NOW());
-- QF3: Denis(3) vs Fahmi(6)
INSERT INTO match_participants (match_id, participant_id, created_at, updated_at) VALUES (@qf3, 3, NOW(), NOW()), (@qf3, 6, NOW(), NOW());
-- QF4: Sandi(2) waits for winner of M2
INSERT INTO match_participants (match_id, participant_id, created_at, updated_at) VALUES (@qf4, 2, NOW(), NOW());

-- ============================================================
-- Batminton (comp_id=2): same bracket structure
-- ============================================================

INSERT INTO rounds (competition_id, round_number, name, created_at, updated_at) VALUES
(2, 1, 'Babak 1', NOW(), NOW()),
(2, 2, 'Perempat Final', NOW(), NOW()),
(2, 3, 'Semi Final', NOW(), NOW()),
(2, 4, 'Final', NOW(), NOW());

SET @br1 = (SELECT id FROM rounds WHERE competition_id=2 AND round_number=1);
SET @br2 = (SELECT id FROM rounds WHERE competition_id=2 AND round_number=2);
SET @br3 = (SELECT id FROM rounds WHERE competition_id=2 AND round_number=3);
SET @br4 = (SELECT id FROM rounds WHERE competition_id=2 AND round_number=4);

INSERT INTO `matches` (competition_id, round_id, match_number, status, bracket_position, created_at, updated_at) VALUES
(2, @br1, 1, 'scheduled', 2, NOW(), NOW()),
(2, @br1, 2, 'scheduled', 7, NOW(), NOW());

INSERT INTO `matches` (competition_id, round_id, match_number, status, bracket_position, created_at, updated_at) VALUES
(2, @br2, 3, 'scheduled', 1, NOW(), NOW()),
(2, @br2, 4, 'scheduled', 2, NOW(), NOW()),
(2, @br2, 5, 'scheduled', 3, NOW(), NOW()),
(2, @br2, 6, 'scheduled', 4, NOW(), NOW());

INSERT INTO `matches` (competition_id, round_id, match_number, status, bracket_position, created_at, updated_at) VALUES
(2, @br3, 7, 'scheduled', 1, NOW(), NOW()),
(2, @br3, 8, 'scheduled', 2, NOW(), NOW());

INSERT INTO `matches` (competition_id, round_id, match_number, status, bracket_position, created_at, updated_at) VALUES
(2, @br4, 9, 'scheduled', 1, NOW(), NOW());

SET @bm1 = (SELECT id FROM `matches` WHERE competition_id=2 AND match_number=1);
SET @bm2 = (SELECT id FROM `matches` WHERE competition_id=2 AND match_number=2);
SET @bqf1 = (SELECT id FROM `matches` WHERE competition_id=2 AND match_number=3);
SET @bqf2 = (SELECT id FROM `matches` WHERE competition_id=2 AND match_number=4);
SET @bqf3 = (SELECT id FROM `matches` WHERE competition_id=2 AND match_number=5);
SET @bqf4 = (SELECT id FROM `matches` WHERE competition_id=2 AND match_number=6);
SET @bsf1 = (SELECT id FROM `matches` WHERE competition_id=2 AND match_number=7);
SET @bsf2 = (SELECT id FROM `matches` WHERE competition_id=2 AND match_number=8);
SET @bfin = (SELECT id FROM `matches` WHERE competition_id=2 AND match_number=9);

UPDATE `matches` SET next_match_id=@bqf1 WHERE id=@bm1;
UPDATE `matches` SET next_match_id=@bqf4 WHERE id=@bm2;
UPDATE `matches` SET next_match_id=@bsf1 WHERE id=@bqf1;
UPDATE `matches` SET next_match_id=@bsf1 WHERE id=@bqf2;
UPDATE `matches` SET next_match_id=@bsf2 WHERE id=@bqf3;
UPDATE `matches` SET next_match_id=@bsf2 WHERE id=@bqf4;
UPDATE `matches` SET next_match_id=@bfin WHERE id=@bsf1;
UPDATE `matches` SET next_match_id=@bfin WHERE id=@bsf2;

INSERT INTO match_participants (match_id, participant_id, created_at, updated_at) VALUES
(@bm1, 8, NOW(), NOW()), (@bm1, 9, NOW(), NOW()),
(@bm2, 7, NOW(), NOW()), (@bm2, 10, NOW(), NOW());

INSERT INTO match_participants (match_id, participant_id, created_at, updated_at) VALUES (@bqf1, 1, NOW(), NOW());
INSERT INTO match_participants (match_id, participant_id, created_at, updated_at) VALUES (@bqf2, 5, NOW(), NOW()), (@bqf2, 4, NOW(), NOW());
INSERT INTO match_participants (match_id, participant_id, created_at, updated_at) VALUES (@bqf3, 3, NOW(), NOW()), (@bqf3, 6, NOW(), NOW());
INSERT INTO match_participants (match_id, participant_id, created_at, updated_at) VALUES (@bqf4, 2, NOW(), NOW());
