ALTER TABLE hospitals
ADD COLUMN admin_id INT NULL
AFTER hospital_id;
ALTER TABLE hospitals
ADD CONSTRAINT fk_hospital_admin FOREIGN KEY (admin_id) REFERENCES users(user_id) ON DELETE
SET NULL;