-- ============================================
-- Seed2Greens - Admin 2FA Migration
-- Add 2FA columns to existing admin table
-- ============================================

ALTER TABLE admin ADD COLUMN totp_secret VARCHAR(255) DEFAULT NULL AFTER email;
ALTER TABLE admin ADD COLUMN totp_enabled TINYINT(1) DEFAULT 0 AFTER totp_secret;
ALTER TABLE admin ADD COLUMN backup_codes JSON DEFAULT NULL AFTER totp_enabled;
