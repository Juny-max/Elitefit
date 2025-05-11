-- Add OTP columns for forgot password to users table
ALTER TABLE users
ADD COLUMN password_otp_hash VARCHAR(255) DEFAULT NULL,
ADD COLUMN password_otp_expires DATETIME DEFAULT NULL,
ADD COLUMN password_otp_attempts INT DEFAULT 0;
