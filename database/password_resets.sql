-- Password Reset Tokens Table
-- Used for forgot password functionality

CREATE TABLE IF NOT EXISTS password_resets (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_password_resets_user
        FOREIGN KEY (user_id)
        REFERENCES profiles(id)
        ON DELETE CASCADE
);

-- Index for faster token lookup
CREATE INDEX IF NOT EXISTS idx_password_resets_token ON password_resets(token);
CREATE INDEX IF NOT EXISTS idx_password_resets_email ON password_resets(email);
CREATE INDEX IF NOT EXISTS idx_password_resets_expires_at ON password_resets(expires_at);

-- Clean up expired tokens (should be run periodically)
-- DELETE FROM password_resets WHERE expires_at < NOW() OR used = TRUE;

COMMENT ON TABLE password_resets IS 'Stores password reset tokens for forgot password functionality';
COMMENT ON COLUMN password_resets.token IS 'Unique random token sent to user email';
COMMENT ON COLUMN password_resets.expires_at IS 'Token expiry time (typically 1 hour from creation)';
COMMENT ON COLUMN password_resets.used IS 'Whether token has been used to reset password';
