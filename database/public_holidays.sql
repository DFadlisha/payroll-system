-- ============================================
-- PUBLIC HOLIDAYS TABLE
-- ============================================
-- For tracking Malaysian public holidays
-- Required for overtime rate calculations (3x rate on public holidays)
-- ============================================

-- Drop existing table if you want to recreate
-- DROP TABLE IF EXISTS public_holidays CASCADE;

CREATE TABLE IF NOT EXISTS public_holidays (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    holiday_date DATE NOT NULL UNIQUE,
    holiday_name VARCHAR(255) NOT NULL,
    holiday_type VARCHAR(50) DEFAULT 'national', -- national, state-specific
    state_code VARCHAR(10), -- NULL for national, state code for state-specific (e.g., 'JHR', 'KL')
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Index for faster date lookups
CREATE INDEX IF NOT EXISTS idx_public_holidays_date ON public_holidays(holiday_date);
CREATE INDEX IF NOT EXISTS idx_public_holidays_active ON public_holidays(is_active);
CREATE INDEX IF NOT EXISTS idx_public_holidays_state ON public_holidays(state_code);

-- Clear existing data (optional - comment out if you want to keep existing data)
TRUNCATE TABLE public_holidays;

-- ============================================
-- MALAYSIAN PUBLIC HOLIDAYS 2025
-- ============================================
INSERT INTO public_holidays (holiday_date, holiday_name, holiday_type, state_code) VALUES
-- National Holidays 2025
('2025-01-01', 'New Year''s Day / Tahun Baru', 'national', NULL),
('2025-01-25', 'Thaipusam', 'national', NULL),
('2025-01-29', 'Chinese New Year / Tahun Baru Cina', 'national', NULL),
('2025-01-30', 'Chinese New Year (Day 2) / Tahun Baru Cina (Hari Kedua)', 'national', NULL),
('2025-02-01', 'Federal Territory Day / Hari Wilayah Persekutuan', 'national', NULL),
('2025-03-18', 'Nuzul Al-Quran', 'national', NULL),
('2025-03-31', 'Hari Raya Aidilfitri', 'national', NULL),
('2025-04-01', 'Hari Raya Aidilfitri (Day 2) / Hari Raya Aidilfitri (Hari Kedua)', 'national', NULL),
('2025-04-18', 'Good Friday / Jumaat Agung', 'national', NULL),
('2025-05-01', 'Labour Day / Hari Pekerja', 'national', NULL),
('2025-05-12', 'Wesak Day / Hari Wesak', 'national', NULL),
('2025-06-02', 'Agong''s Birthday / Hari Keputeraan Yang di-Pertuan Agong', 'national', NULL),
('2025-06-07', 'Hari Raya Aidiladha / Hari Raya Haji', 'national', NULL),
('2025-06-27', 'Awal Muharram / Maal Hijrah', 'national', NULL),
('2025-08-31', 'National Day / Hari Merdeka', 'national', NULL),
('2025-09-05', 'Prophet Muhammad''s Birthday / Maulidur Rasul', 'national', NULL),
('2025-09-16', 'Malaysia Day / Hari Malaysia', 'national', NULL),
('2025-10-20', 'Deepavali / Diwali', 'national', NULL),
('2025-12-25', 'Christmas Day / Hari Krismas', 'national', NULL);

-- ============================================
-- MALAYSIAN PUBLIC HOLIDAYS 2026 (Preview)
-- ============================================
INSERT INTO public_holidays (holiday_date, holiday_name, holiday_type, state_code) VALUES
('2026-01-01', 'New Year''s Day / Tahun Baru', 'national', NULL),
('2026-01-14', 'Thaipusam', 'national', NULL),
('2026-01-17', 'Chinese New Year / Tahun Baru Cina', 'national', NULL),
('2026-01-18', 'Chinese New Year (Day 2) / Tahun Baru Cina (Hari Kedua)', 'national', NULL),
('2026-02-01', 'Federal Territory Day / Hari Wilayah Persekutuan', 'national', NULL),
('2026-03-08', 'Nuzul Al-Quran', 'national', NULL),
('2026-03-20', 'Hari Raya Aidilfitri (Estimated)', 'national', NULL),
('2026-03-21', 'Hari Raya Aidilfitri (Day 2 - Estimated)', 'national', NULL),
('2026-04-03', 'Good Friday / Jumaat Agung', 'national', NULL),
('2026-05-01', 'Labour Day / Hari Pekerja', 'national', NULL),
('2026-05-31', 'Wesak Day / Hari Wesak', 'national', NULL),
('2026-05-27', 'Hari Raya Aidiladha (Estimated)', 'national', NULL),
('2026-06-08', 'Agong''s Birthday / Hari Keputeraan Yang di-Pertuan Agong', 'national', NULL),
('2026-06-17', 'Awal Muharram / Maal Hijrah (Estimated)', 'national', NULL),
('2026-08-25', 'Prophet Muhammad''s Birthday / Maulidur Rasul (Estimated)', 'national', NULL),
('2026-08-31', 'National Day / Hari Merdeka', 'national', NULL),
('2026-09-16', 'Malaysia Day / Hari Malaysia', 'national', NULL),
('2026-11-08', 'Deepavali / Diwali (Estimated)', 'national', NULL),
('2026-12-25', 'Christmas Day / Hari Krismas', 'national', NULL);

-- ============================================
-- STATE-SPECIFIC HOLIDAYS (Examples)
-- ============================================
-- Uncomment and add state-specific holidays as needed
-- INSERT INTO public_holidays (holiday_date, holiday_name, holiday_type, state_code) VALUES
-- -- Johor
-- ('2025-03-23', 'Birthday of Sultan of Johor', 'state-specific', 'JHR'),
-- -- Selangor
-- ('2025-12-11', 'Birthday of Sultan of Selangor', 'state-specific', 'SEL'),
-- -- Penang
-- ('2025-07-12', 'Governor of Penang''s Birthday', 'state-specific', 'PNG'),
-- -- Sabah
-- ('2025-05-30', 'Harvest Festival / Pesta Kaamatan', 'state-specific', 'SBH'),
-- ('2025-05-31', 'Harvest Festival (Day 2)', 'state-specific', 'SBH'),
-- -- Sarawak
-- ('2025-06-01', 'Gawai Dayak', 'state-specific', 'SWK'),
-- ('2025-06-02', 'Gawai Dayak (Day 2)', 'state-specific', 'SWK');

-- ============================================
-- NOTES
-- ============================================
COMMENT ON TABLE public_holidays IS 'Malaysian public holidays for overtime calculations (3x rate) and leave management';
COMMENT ON COLUMN public_holidays.holiday_type IS 'national = nationwide, state-specific = certain states only';
COMMENT ON COLUMN public_holidays.state_code IS 'State codes: JHR=Johor, KL=KL, SEL=Selangor, PNG=Penang, SBH=Sabah, SWK=Sarawak, etc.';
COMMENT ON COLUMN public_holidays.is_active IS 'Set to FALSE to disable without deleting';

-- ============================================
-- MAINTENANCE QUERIES
-- ============================================
-- View all active holidays
-- SELECT holiday_date, holiday_name FROM public_holidays WHERE is_active = TRUE ORDER BY holiday_date;

-- Check if specific date is a public holiday
-- SELECT * FROM public_holidays WHERE holiday_date = '2025-08-31' AND is_active = TRUE;

-- Disable a holiday
-- UPDATE public_holidays SET is_active = FALSE WHERE holiday_date = '2025-01-01';

-- Delete old holidays (before 2025)
-- DELETE FROM public_holidays WHERE holiday_date < '2025-01-01';

