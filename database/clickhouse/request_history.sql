CREATE TABLE IF NOT EXISTS request_history (
    session_id UInt64,
    route String,
    method String,
    payload Nullable (String),
    created_at DateTime DEFAULT now()
) ENGINE = MergeTree ()
ORDER BY (created_at, session_id);