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