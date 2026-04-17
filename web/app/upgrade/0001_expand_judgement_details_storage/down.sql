ALTER TABLE submissions
  MODIFY result BLOB NOT NULL;

ALTER TABLE custom_test_submissions
  MODIFY result BLOB NOT NULL;

ALTER TABLE hacks
  MODIFY details BLOB NOT NULL;
