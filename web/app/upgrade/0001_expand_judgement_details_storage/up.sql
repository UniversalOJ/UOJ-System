ALTER TABLE submissions
  MODIFY result LONGBLOB NOT NULL;

ALTER TABLE custom_test_submissions
  MODIFY result LONGBLOB NOT NULL;

ALTER TABLE hacks
  MODIFY details LONGBLOB NOT NULL;
