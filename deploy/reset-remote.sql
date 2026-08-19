SET FOREIGN_KEY_CHECKS=0;
TRUNCATE activity_logs;
TRUNCATE announcements;
TRUNCATE awards;
TRUNCATE competition_participants;
TRUNCATE competitions;
TRUNCATE event_settings;
TRUNCATE match_participants;
TRUNCATE match_results;
TRUNCATE `matches`;
TRUNCATE participant_categories;
TRUNCATE participants;
TRUNCATE rankings;
TRUNCATE rounds;
TRUNCATE schedules;
TRUNCATE sessions;
TRUNCATE cache;
TRUNCATE cache_locks;
DELETE FROM events;
DELETE FROM users;
SET FOREIGN_KEY_CHECKS=1;

INSERT INTO users (name, email, password, email_verified_at, created_at, updated_at)
VALUES ('Admin', 'aksalaxsaitama@17an.dikantor', '$2y$10$SkPGOlkUguaxaE53uy8rz.0nmem7S1xW/KBIv3UxIqXrH86GvtiU.', NOW(), NOW(), NOW());

INSERT INTO events (name, slug, year, start_date, end_date, status, is_active, created_at, updated_at)
VALUES ('aksalaxsaitama', 'aksalaxsaitama', 2026, '2026-08-16', '2026-08-17', 'active', 1, NOW(), NOW());

INSERT INTO event_settings (event_id, theme_color, venue_default, created_at, updated_at)
VALUES (LAST_INSERT_ID(), '#D71920', '', NOW(), NOW());
