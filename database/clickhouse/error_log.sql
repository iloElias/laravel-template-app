CREATE TABLE IF NOT EXISTS error_log (
    url Nullable(String),
    error_message String,
    stack_trace Nullable(String),
    request_data Nullable(String),
    created_at DateTime DEFAULT now()
) ENGINE = MergeTree()
ORDER BY (created_at);
