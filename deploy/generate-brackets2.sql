-- Billiard (comp_id=1): 10 peserta, 16-slot bracket, 4 rounds
INSERT INTO rounds (competition_id, round_number, name, created_at, updated_at) VALUES
(1, 1, 'Babak 1', NOW(), NOW()),
(1, 2, 'Perempat Final', NOW(), NOW()),
(1, 3, 'Semi Final', NOW(), NOW()),
(1, 4, 'Final', NOW(), NOW());

SET @r1_1 = (SELECT id FROM rounds WHERE competition_id=1 AND round_number=1);
SET @r1_2 = (SELECT id FROM rounds WHERE competition_id=1 AND round_number=2);
SET @r1_3 = (SELECT id FROM rounds WHERE competition_id=1 AND round_number=3);
SET @r1_4 = (SELECT id FROM rounds WHERE competition_id=1 AND round_number=4);

INSERT INTO `matches` (competition_id, round_id, match_number, status, created_at, updated_at) VALUES
(1, @r1_1, 1, 'finished', NOW(), NOW()),
(1, @r1_1, 2, 'scheduled', NOW(), NOW()),
(1, @r1_1, 3, 'finished', NOW(), NOW()),
(1, @r1_1, 4, 'finished', NOW(), NOW()),
(1, @r1_1, 5, 'finished', NOW(), NOW()),
(1, @r1_1, 6, 'finished', NOW(), NOW()),
(1, @r1_1, 7, 'scheduled', NOW(), NOW()),
(1, @r1_1, 8, 'finished', NOW(), NOW());

SET @m1 = (SELECT id FROM `matches` WHERE competition_id=1 AND round_id=@r1_1 AND match_number=1);
SET @m2 = (SELECT id FROM `matches` WHERE competition_id=1 AND round_id=@r1_1 AND match_number=2);
SET @m3 = (SELECT id FROM `matches` WHERE competition_id=1 AND round_id=@r1_1 AND match_number=3);
SET @m4 = (SELECT id FROM `matches` WHERE competition_id=1 AND round_id=@r1_1 AND match_number=4);
SET @m5 = (SELECT id FROM `matches` WHERE competition_id=1 AND round_id=@r1_1 AND match_number=5);
SET @m6 = (SELECT id FROM `matches` WHERE competition_id=1 AND round_id=@r1_1 AND match_number=6);
SET @m7 = (SELECT id FROM `matches` WHERE competition_id=1 AND round_id=@r1_1 AND match_number=7);
SET @m8 = (SELECT id FROM `matches` WHERE competition_id=1 AND round_id=@r1_1 AND match_number=8);

INSERT INTO match_participants (match_id, participant_id, is_winner, created_at, updated_at) VALUES (@m1, 1, 1, NOW(), NOW());
INSERT INTO match_participants (match_id, participant_id, created_at, updated_at) VALUES (@m2, 8, NOW(), NOW()), (@m2, 9, NOW(), NOW());
INSERT INTO match_participants (match_id, participant_id, is_winner, created_at, updated_at) VALUES (@m3, 5, 1, NOW(), NOW());
INSERT INTO match_participants (match_id, participant_id, is_winner, created_at, updated_at) VALUES (@m4, 4, 1, NOW(), NOW());
INSERT INTO match_participants (match_id, participant_id, is_winner, created_at, updated_at) VALUES (@m5, 3, 1, NOW(), NOW());
INSERT INTO match_participants (match_id, participant_id, is_winner, created_at, updated_at) VALUES (@m6, 6, 1, NOW(), NOW());
INSERT INTO match_participants (match_id, participant_id, created_at, updated_at) VALUES (@m7, 7, NOW(), NOW()), (@m7, 10, NOW(), NOW());
INSERT INTO match_participants (match_id, participant_id, is_winner, created_at, updated_at) VALUES (@m8, 2, 1, NOW(), NOW());

-- QF
INSERT INTO `matches` (competition_id, round_id, match_number, status, created_at, updated_at) VALUES
(1, @r1_2, 1, 'scheduled', NOW(), NOW()),
(1, @r1_2, 2, 'scheduled', NOW(), NOW()),
(1, @r1_2, 3, 'scheduled', NOW(), NOW()),
(1, @r1_2, 4, 'scheduled', NOW(), NOW());

SET @qf1 = (SELECT id FROM `matches` WHERE competition_id=1 AND round_id=@r1_2 AND match_number=1);
SET @qf2 = (SELECT id FROM `matches` WHERE competition_id=1 AND round_id=@r1_2 AND match_number=2);
SET @qf3 = (SELECT id FROM `matches` WHERE competition_id=1 AND round_id=@r1_2 AND match_number=3);
SET @qf4 = (SELECT id FROM `matches` WHERE competition_id=1 AND round_id=@r1_2 AND match_number=4);

INSERT INTO match_participants (match_id, participant_id, created_at, updated_at) VALUES (@qf1, 1, NOW(), NOW());
INSERT INTO match_participants (match_id, participant_id, created_at, updated_at) VALUES (@qf2, 5, NOW(), NOW()), (@qf2, 4, NOW(), NOW());
INSERT INTO match_participants (match_id, participant_id, created_at, updated_at) VALUES (@qf3, 3, NOW(), NOW()), (@qf3, 6, NOW(), NOW());
INSERT INTO match_participants (match_id, participant_id, created_at, updated_at) VALUES (@qf4, 2, NOW(), NOW());

-- SF
INSERT INTO `matches` (competition_id, round_id, match_number, status, created_at, updated_at) VALUES
(1, @r1_3, 1, 'scheduled', NOW(), NOW()),
(1, @r1_3, 2, 'scheduled', NOW(), NOW());

-- Final
INSERT INTO `matches` (competition_id, round_id, match_number, status, created_at, updated_at) VALUES
(1, @r1_4, 1, 'scheduled', NOW(), NOW());

-- ============================================================
-- Batminton (comp_id=2): same structure
-- ============================================================
INSERT INTO rounds (competition_id, round_number, name, created_at, updated_at) VALUES
(2, 1, 'Babak 1', NOW(), NOW()),
(2, 2, 'Perempat Final', NOW(), NOW()),
(2, 3, 'Semi Final', NOW(), NOW()),
(2, 4, 'Final', NOW(), NOW());

SET @b1_1 = (SELECT id FROM rounds WHERE competition_id=2 AND round_number=1);
SET @b1_2 = (SELECT id FROM rounds WHERE competition_id=2 AND round_number=2);
SET @b1_3 = (SELECT id FROM rounds WHERE competition_id=2 AND round_number=3);
SET @b1_4 = (SELECT id FROM rounds WHERE competition_id=2 AND round_number=4);

INSERT INTO `matches` (competition_id, round_id, match_number, status, created_at, updated_at) VALUES
(2, @b1_1, 1, 'finished', NOW(), NOW()),
(2, @b1_1, 2, 'scheduled', NOW(), NOW()),
(2, @b1_1, 3, 'finished', NOW(), NOW()),
(2, @b1_1, 4, 'finished', NOW(), NOW()),
(2, @b1_1, 5, 'finished', NOW(), NOW()),
(2, @b1_1, 6, 'finished', NOW(), NOW()),
(2, @b1_1, 7, 'scheduled', NOW(), NOW()),
(2, @b1_1, 8, 'finished', NOW(), NOW());

SET @bm1 = (SELECT id FROM `matches` WHERE competition_id=2 AND round_id=@b1_1 AND match_number=1);
SET @bm2 = (SELECT id FROM `matches` WHERE competition_id=2 AND round_id=@b1_1 AND match_number=2);
SET @bm3 = (SELECT id FROM `matches` WHERE competition_id=2 AND round_id=@b1_1 AND match_number=3);
SET @bm4 = (SELECT id FROM `matches` WHERE competition_id=2 AND round_id=@b1_1 AND match_number=4);
SET @bm5 = (SELECT id FROM `matches` WHERE competition_id=2 AND round_id=@b1_1 AND match_number=5);
SET @bm6 = (SELECT id FROM `matches` WHERE competition_id=2 AND round_id=@b1_1 AND match_number=6);
SET @bm7 = (SELECT id FROM `matches` WHERE competition_id=2 AND round_id=@b1_1 AND match_number=7);
SET @bm8 = (SELECT id FROM `matches` WHERE competition_id=2 AND round_id=@b1_1 AND match_number=8);

INSERT INTO match_participants (match_id, participant_id, is_winner, created_at, updated_at) VALUES (@bm1, 1, 1, NOW(), NOW());
INSERT INTO match_participants (match_id, participant_id, created_at, updated_at) VALUES (@bm2, 8, NOW(), NOW()), (@bm2, 9, NOW(), NOW());
INSERT INTO match_participants (match_id, participant_id, is_winner, created_at, updated_at) VALUES (@bm3, 5, 1, NOW(), NOW());
INSERT INTO match_participants (match_id, participant_id, is_winner, created_at, updated_at) VALUES (@bm4, 4, 1, NOW(), NOW());
INSERT INTO match_participants (match_id, participant_id, is_winner, created_at, updated_at) VALUES (@bm5, 3, 1, NOW(), NOW());
INSERT INTO match_participants (match_id, participant_id, is_winner, created_at, updated_at) VALUES (@bm6, 6, 1, NOW(), NOW());
INSERT INTO match_participants (match_id, participant_id, created_at, updated_at) VALUES (@bm7, 7, NOW(), NOW()), (@bm7, 10, NOW(), NOW());
INSERT INTO match_participants (match_id, participant_id, is_winner, created_at, updated_at) VALUES (@bm8, 2, 1, NOW(), NOW());

INSERT INTO `matches` (competition_id, round_id, match_number, status, created_at, updated_at) VALUES
(2, @b1_2, 1, 'scheduled', NOW(), NOW()),
(2, @b1_2, 2, 'scheduled', NOW(), NOW()),
(2, @b1_2, 3, 'scheduled', NOW(), NOW()),
(2, @b1_2, 4, 'scheduled', NOW(), NOW());

SET @bqf1 = (SELECT id FROM `matches` WHERE competition_id=2 AND round_id=@b1_2 AND match_number=1);
SET @bqf2 = (SELECT id FROM `matches` WHERE competition_id=2 AND round_id=@b1_2 AND match_number=2);
SET @bqf3 = (SELECT id FROM `matches` WHERE competition_id=2 AND round_id=@b1_2 AND match_number=3);
SET @bqf4 = (SELECT id FROM `matches` WHERE competition_id=2 AND round_id=@b1_2 AND match_number=4);

INSERT INTO match_participants (match_id, participant_id, created_at, updated_at) VALUES (@bqf1, 1, NOW(), NOW());
INSERT INTO match_participants (match_id, participant_id, created_at, updated_at) VALUES (@bqf2, 5, NOW(), NOW()), (@bqf2, 4, NOW(), NOW());
INSERT INTO match_participants (match_id, participant_id, created_at, updated_at) VALUES (@bqf3, 3, NOW(), NOW()), (@bqf3, 6, NOW(), NOW());
INSERT INTO match_participants (match_id, participant_id, created_at, updated_at) VALUES (@bqf4, 2, NOW(), NOW());

INSERT INTO `matches` (competition_id, round_id, match_number, status, created_at, updated_at) VALUES
(2, @b1_3, 1, 'scheduled', NOW(), NOW()),
(2, @b1_3, 2, 'scheduled', NOW(), NOW());

INSERT INTO `matches` (competition_id, round_id, match_number, status, created_at, updated_at) VALUES
(2, @b1_4, 1, 'scheduled', NOW(), NOW());
