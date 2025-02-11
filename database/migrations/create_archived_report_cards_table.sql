CREATE TABLE archived_report_cards (
    archive_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pupil_id BIGINT UNSIGNED NOT NULL,
    academic_year INT NOT NULL,
    term INT NOT NULL,
    term_average DECIMAL(5,2) NOT NULL,
    rank INT NOT NULL,
    class_size INT NOT NULL,
    class_average DECIMAL(5,2) NOT NULL,
    pdf_content MEDIUMBLOB NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pupil_id) REFERENCES pupils(pupil_id),
    INDEX idx_pupil_term (pupil_id, academic_year, term)
); 