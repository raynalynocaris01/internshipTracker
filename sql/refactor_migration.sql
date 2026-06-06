-- Add missing indexes for performance
ALTER TABLE attendance ADD INDEX idx_student_date (student_id, date);
ALTER TABLE attendance ADD INDEX idx_qr_session (qr_session_id);
ALTER TABLE qr_sessions ADD INDEX idx_token (qr_token);
ALTER TABLE qr_sessions ADD INDEX idx_section_date (section_id, date, session_type);

-- Add foreign key constraints
ALTER TABLE sections 
MODIFY COLUMN subject_id INT NOT NULL,
ADD CONSTRAINT fk_sections_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE;

ALTER TABLE instructor_subjects
ADD CONSTRAINT fk_ins_subject_instructor FOREIGN KEY (instructor_id) REFERENCES users(id) ON DELETE CASCADE,
ADD CONSTRAINT fk_ins_subject_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE;

ALTER TABLE instructor_sections
ADD CONSTRAINT fk_ins_section_instructor FOREIGN KEY (instructor_id) REFERENCES users(id) ON DELETE CASCADE,
ADD CONSTRAINT fk_ins_section_section FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE CASCADE;

-- Add timestamp columns for auditing
ALTER TABLE attendance ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE attendance ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Add status tracking for QR sessions
ALTER TABLE qr_sessions 
ADD COLUMN created_by INT,
ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Remove or comment out pending_attendance table if not used
-- DROP TABLE IF EXISTS pending_attendance;