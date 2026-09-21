CREATE TABLE topic_references (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    topic_id BIGINT UNSIGNED NOT NULL,
    referenced_topic_id BIGINT UNSIGNED NOT NULL,
    label VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_topic_references_topic FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE,
    CONSTRAINT fk_topic_references_target FOREIGN KEY (referenced_topic_id) REFERENCES topics(id) ON DELETE CASCADE,
    CONSTRAINT chk_topic_references_distinct CHECK (topic_id <> referenced_topic_id),
    UNIQUE KEY uq_topic_reference (topic_id, referenced_topic_id),
    INDEX idx_topic_references_topic (topic_id),
    INDEX idx_topic_references_target (referenced_topic_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
