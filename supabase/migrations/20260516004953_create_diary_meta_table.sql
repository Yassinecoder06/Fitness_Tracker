CREATE TABLE diary_notes (
    id bigserial PRIMARY KEY,
    user_id bigint NOT NULL,
    date date NOT NULL,
    note text NOT NULL,
    created_at timestamp DEFAULT now(),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE water_intake (
    id bigserial PRIMARY KEY,
    user_id bigint NOT NULL,
    date date NOT NULL,

    glasses int NOT NULL DEFAULT 0,
    UNIQUE (user_id, date),

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);


-- diary_notes data
INSERT INTO diary_notes (user_id, date, note) VALUES
(1, '2026-05-20', 'Good workout today, focused on chest and triceps.'),
(1, '2026-05-21', 'Felt tired in the morning but completed cardio session.'),
(2, '2026-05-20', 'Started a new diet plan with higher protein intake.'),
(2, '2026-05-22', 'Rest day, stayed hydrated and stretched well.'),
(3, '2026-05-23', 'Leg day was intense, need more recovery time.');

-- water_intake data
INSERT INTO water_intake (user_id, date, glasses) VALUES
(1, '2026-05-20', 8),
(1, '2026-05-21', 6),
(1, '2026-05-22', 7),

(2, '2026-05-20', 5),
(2, '2026-05-21', 9),
(2, '2026-05-22', 8),

(3, '2026-05-21', 4),
(3, '2026-05-22', 6),
(3, '2026-05-23', 10);