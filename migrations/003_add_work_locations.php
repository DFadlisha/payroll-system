<?php
require_once __DIR__ . '/../config/database.php';

try {
    $conn = getConnection();

    // 1. Create work_locations table
    $sql = "
    CREATE TABLE IF NOT EXISTS work_locations (
        id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
        company_id UUID NOT NULL REFERENCES companies(id),
        name TEXT NOT NULL,
        address TEXT,
        latitude NUMERIC,
        longitude NUMERIC,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMPTZ DEFAULT NOW(),
        updated_at TIMESTAMPTZ DEFAULT NOW()
    );
    ";
    $conn->exec($sql);
    echo "Created work_locations table.\n";

    // 2. Add location_id column to attendance table if not exists
    $sql = "
    DO $$ 
    BEGIN 
        IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name = 'attendance' AND column_name = 'location_id') THEN 
            ALTER TABLE attendance ADD COLUMN location_id UUID REFERENCES work_locations(id); 
        END IF; 
    END $$;
    ";
    $conn->exec($sql);
    echo "Added location_id to attendance table.\n";

    echo "Migration completed successfully.";

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage());
}
