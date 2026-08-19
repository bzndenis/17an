-- Billiard (comp_id=1)
INSERT INTO rounds (competition_id, round_number, name, created_at, updated_at) VALUES
(1, 1, 'Babak 1', NOW(), NOW()),
(1, 2, 'Perempat Final', NOW(), NOW()),
(1, 3, 'Semi Final', NOW(), NOW()),
(1, 4, 'Final', NOW(), NOW());

SET @r1 = (SELECT id FROM rounds WHERE competition_id=1 AND round_number=1);
SET @r2 = (SELECT id FROM rounds WHERE competition_id=1 AND round_number=2);
SET @r3 = (SELECT id FROM rounds WHERE competition_id=1 AND round_number=3);
SET @r4 = (SELECT id FROM rounds WHERE competition_id=1 AND round_number=4);

-- R1: matches 1-8
INSERT INTO `matches` (competition_id, round_id, match_number, status, created_at, updated_at) VALUES
(1, @r1, 1, 'finished', NOW(), NOW()),
(1, @r1, 2, 'scheduled', NOW(), NOW()),
(1, @r1, 3, 'finished', NOW(), NOW()),
(1, @r1, 4, 'finished', NOW(), NOW()),
(1, @r1, 5, 'finished', NOW(), NOW()),
(1, @r1, 6, 'finished', NOW(), NOW()),
(1, @r1, 7, 'scheduled', NOW(), NOW()),
(1, @r1, 8, 'finished', NOW(), NOW());

-- R2: matches 9-12
INSERT INTO `matches` (competition_id, round_id, match_number, status, created_at, updated_at) VALUES
(1, @r2, 9, 'scheduled', NOW(), NOW()),
(1, @r2, 10, 'scheduled', NOW(), NOW()),
(1, @r2, 11, 'scheduled', NOW(), NOW()),
(1, @r2, 12, 'scheduled', NOW(), NOW());

-- R3: matches 13-14
INSERT INTO `matches` (competition_id, round_id, match_number, status, created_at, updated_at) VALUES
(1, @r3, 13, 'scheduled', NOW(), NOW()),
(1, @r3, 14, 'scheduled', NOW(), NOW());

-- R4: match 15
INSERT INTO `matches` (competition_id, round_id, match_number, status, created_at, updated_at) VALUES
(1, @r4, 15, 'scheduled', NOW(), NOW());

-- Match participants R1
SET @m1 = (SELECT id FROM `matches` WHERE competition_id=1 AND match_number=1);
SET @m2 = (SELECT id FROM `matches` WHERE competition_id=1 AND match_number=2);
SET @m3 = (SELECT id FROM `matches` WHERE competition_id=1 AND match_number=3);
SET @m4 = (SELECT id FROM `matches` WHERE competition_id=1 AND match_number=4);
SET @m5 = (SELECT id FROM `matches` WHERE competition_id=1 AND match_number=5);
SET @m6 = (SELECT id FROM `matches` WHERE competition_id=1 AND match_number=6);
SET @m7 = (SELECT id FROM `matches` WHERE competition_id=1 AND match_number=7);
SET @m8 = (SELECT id FROM `matches` WHERE competition_id=1 AND match_number=8);
SET @m9 = (SELECT id FROM `matches` WHERE competition_id=1 AND match_number=9);
SET @m10 = (SELECT id FROM `matches` WHERE competition_id=1 AND match_number=10);
SET @m11 = (SELECT id FROM `matches` WHERE competition_id=1 AND match_number=11);
SET @m12 = (SELECT id FROM `matches` WHERE competition_id=1 AND match_number=12);

INSERT INTO match_participants (match_id, participant_id, is_winner, created_at, updated_at) VALUES
(@m1, 1, 1, NOW(), NOW()),
(@m2, 8, 0, NOW(), NOW()), (@m2, 9, 0, NOW(), NOW()),
(@m3, 5, 1, NOW(), NOW()),
(@m4, 4, 1, NOW(), NOW()),
(@m5, 3, 1, NOW(), NOW()),
(@m6, 6, 1, NOW(), NOW()),
(@m7, 7, 0, NOW(), NOW()), (@m7, 10, 0, NOW(), NOW()),
(@m8, 2, 1, NOW(), NOW());

-- QF pre-fill bye winners
INSERT INTO match_participants (match_id, participant_id, created_at, updated_at) VALUES
(@m9, 1, NOW(), NOW()),
(@m10, 5, NOW(), NOW()), (@m10, 4, NOW(), NOW()),
(@m11, 3, NOW(), NOW()), (@m11, 6, NOW(), NOW()),
(@m12, 2, NOW(), NOW());

-- ============================================================
-- Batminton (comp_id=2)
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

INSERT INTO `matches` (competition_id, round_id, match_number, status, created_at, updated_at) VALUES
(2, @br1, 1, 'finished', NOW(), NOW()),
(2, @br1, 2, 'scheduled', NOW(), NOW()),
(2, @br1, 3, 'finished', NOW(), NOW()),
(2, @br1, 4, 'finished', NOW(), NOW()),
(2, @br1, 5, 'finished', NOW(), NOW()),
(2, @br1, 6, 'finished', NOW(), NOW()),
(2, @br1, 7, 'scheduled', NOW(), NOW()),
(2, @br1, 8, 'finished', NOW(), NOW());

INSERT INTO `matches` (competition_id, round_id, match_number, status, created_at, updated_at) VALUES
(2, @br2, 9, 'scheduled', NOW(), NOW()),
(2, @br2, 10, 'scheduled', NOW(), NOW()),
(2, @br2, 11, 'scheduled', NOW(), NOW()),
(2, @br2, 12, 'scheduled', NOW(), NOW());

INSERT INTO `matches` (competition_id, round_id, match_number, status, created_at, updated_at) VALUES
(2, @br3, 13, 'scheduled', NOW(), NOW()),
(2, @br3, 14, 'scheduled', NOW(), NOW());

INSERT INTO `matches` (competition_id, round_id, match_number, status, created_at, updated_at) VALUES
(2, @br4, 15, 'scheduled', NOW(), NOW());

SET @bm1 = (SELECT id FROM `matches` WHERE competition_id=2 AND match_number=1);
SET @bm2 = (SELECT id FROM `matches` WHERE competition_id=2 AND match_number=2);
SET @bm3 = (SELECT id FROM `matches` WHERE competition_id=2 AND match_number=3);
SET @bm4 = (SELECT id FROM `matches` WHERE competition_id=2 AND match_number=4);
SET @bm5 = (SELECT id FROM `matches` WHERE competition_id=2 AND match_number=5);
SET @bm6 = (SELECT id FROM `matches` WHERE competition_id=2 AND match_number=6);
SET @bm7 = (SELECT id FROM `matches` WHERE competition_id=2 AND match_number=7);
SET @bm8 = (SELECT id FROM `matches` WHERE competition_id=2 AND match_number=8);
SET @bm9 = (SELECT id FROM `matches` WHERE competition_id=2 AND match_number=9);
SET @bm10 = (SELECT id FROM `matches` WHERE competition_id=2 AND match_number=10);
SET @bm11 = (SELECT id FROM `matches` WHERE competition_id=2 AND match_number=11);
SET @bm12 = (SELECT id FROM `matches` WHERE competition_id=2 AND match_number=12);

INSERT INTO match_participants (match_id, participant_id, is_winner, created_at, updated_at) VALUES
(@bm1, 1, 1, NOW(), NOW()),
(@bm2, 8, 0, NOW(), NOW()), (@bm2, 9, 0, NOW(), NOW()),
(@bm3, 5, 1, NOW(), NOW()),
(@bm4, 4, 1, NOW(), NOW()),
(@bm5, 3, 1, NOW(), NOW()),
(@bm6, 6, 1, NOW(), NOW()),
(@bm7, 7, 0, NOW(), NOW()), (@bm7, 10, 0, NOW(), NOW()),
(@bm8, 2, 1, NOW(), NOW());

INSERT INTO match_participants (match_id, participant_id, created_at, updated_at) VALUES
(@bm9, 1, NOW(), NOW()),
(@bm10, 5, NOW(), NOW()), (@bm10, 4, NOW(), NOW()),
(@bm11, 3, NOW(), NOW()), (@bm11, 6, NOW(), NOW()),
(@bm12, 2, NOW(), NOW());
