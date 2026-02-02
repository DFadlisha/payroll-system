<?php
/**
 * CLI Script to run holiday seeding
 * Usage: php run_seed.php [year]
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/seed_holidays.php';

$year = $argv[1] ?? date('Y');

echo "🔄 Seeding holidays for year $year...\n";

try {
    fetchAndSeedHolidays($year);
    echo "✅ Successfully seeded holidays for $year\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
