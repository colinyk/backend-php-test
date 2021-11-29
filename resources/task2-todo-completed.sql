-- TASK 2: allow to flag todo as completed

ALTER TABLE todos ADD completed TINYINT DEFAULT 0 COMMENT 'TASK 2: 1=flag todo as completed';

