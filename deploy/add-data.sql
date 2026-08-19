-- Update event date
UPDATE events SET start_date='2026-08-21', end_date='2026-08-21', name='aksalaxsaitama' WHERE slug='aksalaxsaitama';

-- Get event id
SET @eid = (SELECT id FROM events WHERE slug='aksalaxsaitama' LIMIT 1);

-- Category
INSERT INTO participant_categories (event_id, name, created_at, updated_at)
VALUES (@eid, 'Umum', NOW(), NOW());
SET @cid = LAST_INSERT_ID();

-- Peserta
INSERT INTO participants (event_id, category_id, name, number, gender, status, created_at, updated_at) VALUES
(@eid, @cid, 'Harrid', 1, 'male', 'active', NOW(), NOW()),
(@eid, @cid, 'Sandi', 2, 'male', 'active', NOW(), NOW()),
(@eid, @cid, 'Denis', 3, 'male', 'active', NOW(), NOW()),
(@eid, @cid, 'Dimas', 4, 'male', 'active', NOW(), NOW()),
(@eid, @cid, 'Rafi', 5, 'male', 'active', NOW(), NOW()),
(@eid, @cid, 'Fahmi', 6, 'male', 'active', NOW(), NOW()),
(@eid, @cid, 'Alivia', 7, 'female', 'active', NOW(), NOW()),
(@eid, @cid, 'Marsya', 8, 'female', 'active', NOW(), NOW()),
(@eid, @cid, 'Maman', 9, 'male', 'active', NOW(), NOW()),
(@eid, @cid, 'Bayu', 10, 'male', 'active', NOW(), NOW());

-- Lomba Billiard (knockout, 10 peserta)
INSERT INTO competitions (event_id, name, slug, system, category, status, start_at, duration_minutes, created_at, updated_at)
VALUES (@eid, 'Billiard', 'billiard', 'knockout', 'Olahraga', 'ongoing', '2026-08-21 08:00:00', 120, NOW(), NOW());
SET @comp1 = LAST_INSERT_ID();

-- Lomba Batminton (knockout, 10 peserta)
INSERT INTO competitions (event_id, name, slug, system, category, status, start_at, duration_minutes, created_at, updated_at)
VALUES (@eid, 'Batminton', 'batminton', 'knockout', 'Olahraga', 'ongoing', '2026-08-21 10:00:00', 120, NOW(), NOW());
SET @comp2 = LAST_INSERT_ID();

-- Daftarkan semua peserta ke kedua lomba
INSERT INTO competition_participants (competition_id, participant_id, seed)
SELECT @comp1, id, ROW_NUMBER() OVER (ORDER BY id) FROM participants WHERE event_id = @eid;

INSERT INTO competition_participants (competition_id, participant_id, seed)
SELECT @comp2, id, ROW_NUMBER() OVER (ORDER BY id) FROM participants WHERE event_id = @eid;
