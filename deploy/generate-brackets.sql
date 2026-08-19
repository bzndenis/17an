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

-- Round 1: 8 matches (6 are byes, 2 actual matches for seeds 7-10 region)
-- Seeding standard: 1v16,8v9,5v12,4v13,3v14,6v11,7v10,2v15
-- With 10 players, seeds 11-16 are byes
-- Match 1: Seed1(Harrid) vs BYE -> Harrid advances
-- Match 2: Seed8(Marsya) vs Seed9(Maman)
-- Match 3: Seed5(Rafi) vs BYE -> Rafi advances
-- Match 4: Seed4(Dimas) vs BYE -> Dimas advances
-- Match 5: Seed3(Denis) vs BYE -> Denis advances
-- Match 6: Seed6(Fahmi) vs BYE -> Fahmi advances
-- Match 7: Seed7(Alivia) vs Seed10(Bayu)
-- Match 8: Seed2(Sandi) vs BYE -> Sandi advances

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

-- Match participants R1
-- M1: Harrid(1) BYE
INSERT INTO match_participants (match_id, participant_id, seed, is_winner, created_at, updated_at) VALUES (@m1, 1, 1, 1, NOW(), NOW());
-- M2: Marsya(8) vs Maman(9)
INSERT INTO match_participants (match_id, participant_id, seed, is_winner, created_at, updated_at) VALUES (@m2, 8, 8, NULL, NOW(), NOW()), (@m2, 9, 9, NULL, NOW(), NOW());
-- M3: Rafi(5) BYE
INSERT INTO match_participants (match_id, participant_id, seed, is_winner, created_at, updated_at) VALUES (@m3, 5, 5, 1, NOW(), NOW());
-- M4: Dimas(4) BYE
INSERT INTO match_participants (match_id, participant_id, seed, is_winner, created_at, updated_at) VALUES (@m4, 4, 4, 1, NOW(), NOW());
-- M5: Denis(3) BYE
INSERT INTO match_participants (match_id, participant_id, seed, is_winner, created_at, updated_at) VALUES (@m5, 3, 3, 1, NOW(), NOW());
-- M6: Fahmi(6) BYE
INSERT INTO match_participants (match_id, participant_id, seed, is_winner, created_at, updated_at) VALUES (@m6, 6, 6, 1, NOW(), NOW());
-- M7: Alivia(7) vs Bayu(10)
INSERT INTO match_participants (match_id, participant_id, seed, is_winner, created_at, updated_at) VALUES (@m7, 7, 7, NULL, NOW(), NOW()), (@m7, 10, 10, NULL, NOW(), NOW());
-- M8: Sandi(2) BYE
INSERT INTO match_participants (match_id, participant_id, seed, is_winner, created_at, updated_at) VALUES (@m8, 2, 2, 1, NOW(), NOW());

-- Round 2 (QF): 4 matches
INSERT INTO `matches` (competition_id, round_id, match_number, status, created_at, updated_at) VALUES
(1, @r1_2, 1, 'scheduled', NOW(), NOW()),
(1, @r1_2, 2, 'scheduled', NOW(), NOW()),
(1, @r1_2, 3, 'scheduled', NOW(), NOW()),
(1, @r1_2, 4, 'scheduled', NOW(), NOW());

-- Pre-fill QF with bye winners
SET @qf1 = (SELECT id FROM `matches` WHERE competition_id=1 AND round_id=@r1_2 AND match_number=1);
SET @qf2 = (SELECT id FROM `matches` WHERE competition_id=1 AND round_id=@r1_2 AND match_number=2);
SET @qf3 = (SELECT id FROM `matches` WHERE competition_id=1 AND round_id=@r1_2 AND match_number=3);
SET @qf4 = (SELECT id FROM `matches` WHERE competition_id=1 AND round_id=@r1_2 AND match_number=4);

-- QF1: Harrid vs winner(M2)
INSERT INTO match_participants (match_id, participant_id, seed, created_at, updated_at) VALUES (@qf1, 1, 1, NOW(), NOW());
-- QF2: Rafi vs Dimas
INSERT INTO match_participants (match_id, participant_id, seed, created_at, updated_at) VALUES (@qf2, 5, 5, NOW(), NOW()), (@qf2, 4, 4, NOW(), NOW());
-- QF3: Denis vs Fahmi
INSERT INTO match_participants (match_id, participant_id, seed, created_at, updated_at) VALUES (@qf3, 3, 3, NOW(), NOW()), (@qf3, 6, 6, NOW(), NOW());
-- QF4: winner(M7) vs Sandi
INSERT INTO match_participants (match_id, participant_id, seed, created_at, updated_at) VALUES (@qf4, 2, 2, NOW(), NOW());

-- Round 3 (SF): 2 matches
INSERT INTO `matches` (competition_id, round_id, match_number, status, created_at, updated_at) VALUES
(1, @r1_3, 1, 'scheduled', NOW(), NOW()),
(1, @r1_3, 2, 'scheduled', NOW(), NOW());

-- Round 4 (Final): 1 match
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

INSERT INTO match_participants (match_id, participant_id, seed, is_winner, created_at, updated_at) VALUES (@bm1, 1, 1, 1, NOW(), NOW());
INSERT INTO match_participants (match_id, participant_id, seed, is_winner, created_at, updated_at) VALUES (@bm2, 8, 8, NULL, NOW(), NOW()), (@bm2, 9, 9, NULL, NOW(), NOW());
INSERT INTO match_participants (match_id, participant_id, seed, is_winner, created_at, updated_at) VALUES (@bm3, 5, 5, 1, NOW(), NOW());
INSERT INTO match_participants (match_id, participant_id, seed, is_winner, created_at, updated_at) VALUES (@bm4, 4, 4, 1, NOW(), NOW());
INSERT INTO match_participants (match_id, participant_id, seed, is_winner, created_at, updated_at) VALUES (@bm5, 3, 3, 1, NOW(), NOW());
INSERT INTO match_participants (match_id, participant_id, seed, is_winner, created_at, updated_at) VALUES (@bm6, 6, 6, 1, NOW(), NOW());
INSERT INTO match_participants (match_id, participant_id, seed, is_winner, created_at, updated_at) VALUES (@bm7, 7, 7, NULL, NOW(), NOW()), (@bm7, 10, 10, NULL, NOW(), NOW());
INSERT INTO match_participants (match_id, participant_id, seed, is_winner, created_at, updated_at) VALUES (@bm8, 2, 2, 1, NOW(), NOW());

SET @bqf1 = (SELECT id FROM `matches` WHERE competition_id=2 AND round_id=@b1_2 AND match_number=1);
SET @bqf2 = (SELECT id FROM `matches` WHERE competition_id=2 AND round_id=@b1_2 AND match_number=2);
SET @bqf3 = (SELECT id FROM `matches` WHERE competition_id=2 AND round_id=@b1_2 AND match_number=3);
SET @bqf4 = (SELECT id FROM `matches` WHERE competition_id=2 AND round_id=@b1_2 AND match_number=4);

INSERT INTO `matches` (competition_id, round_id, match_number, status, created_at, updated_at) VALUES
(2, @b1_2, 1, 'scheduled', NOW(), NOW()),
(2, @b1_2, 2, 'scheduled', NOW(), NOW()),
(2, @b1_2, 3, 'scheduled', NOW(), NOW()),
(2, @b1_2, 4, 'scheduled', NOW(), NOW());

SET @bqf1 = (SELECT id FROM `matches` WHERE competition_id=2 AND round_id=@b1_2 AND match_number=1);
SET @bqf2 = (SELECT id FROM `matches` WHERE competition_id=2 AND round_id=@b1_2 AND match_number=2);
SET @bqf3 = (SELECT id FROM `matches` WHERE competition_id=2 AND round_id=@b1_2 AND match_number=3);
SET @bqf4 = (SELECT id FROM `matches` WHERE competition_id=2 AND round_id=@b1_2 AND match_number=4);

INSERT INTO match_participants (match_id, participant_id, seed, created_at, updated_at) VALUES (@bqf1, 1, 1, NOW(), NOW());
INSERT INTO match_participants (match_id, participant_id, seed, created_at, updated_at) VALUES (@bqf2, 5, 5, NOW(), NOW()), (@bqf2, 4, 4, NOW(), NOW());
INSERT INTO match_participants (match_id, participant_id, seed, created_at, updated_at) VALUES (@bqf3, 3, 3, NOW(), NOW()), (@bqf3, 6, 6, NOW(), NOW());
INSERT INTO match_participants (match_id, participant_id, seed, created_at, updated_at) VALUES (@bqf4, 2, 2, NOW(), NOW());

INSERT INTO `matches` (competition_id, round_id, match_number, status, created_at, updated_at) VALUES
(2, @b1_3, 1, 'scheduled', NOW(), NOW()),
(2, @b1_3, 2, 'scheduled', NOW(), NOW());

INSERT INTO `matches` (competition_id, round_id, match_number, status, created_at, updated_at) VALUES
(2, @b1_4, 1, 'scheduled', NOW(), NOW());
