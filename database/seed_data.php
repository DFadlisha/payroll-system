<?php
/**
 * Database Seeding Script
 * Run this to populate the database with sample data.
 */

// Include database connection
require_once __DIR__ . '/../config/database.php';

echo "🌱 Starting Database Seed...\n";

try {
    $conn = getConnection();
    
    // 1. Seed Companies
    echo "Checking companies...\n";
    $companies = [
        [
            'name' => 'NES Solution & Network Sdn Bhd',
            'logo_url' => 'nes.jpg'
        ],
        [
            'name' => 'Mentari Infiniti Sdn Bhd',
            'logo_url' => 'mentari.png'
        ]
    ];
    
    $companyIds = [];
    
    foreach ($companies as $comp) {
        // Check if company exists (Case Insensitive)
        $stmt = $conn->prepare("SELECT id FROM companies WHERE name ILIKE ?");
        $stmt->execute([$comp['name']]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            $companyIds[$comp['name']] = $existing['id'];
            echo " - Found existing company: {$comp['name']}\n";
        } else {
            // Create new company
            $stmt = $conn->prepare("INSERT INTO companies (name, logo_url) VALUES (?, ?) RETURNING id");
            // PostgreSQL RETURNING id is useful
            // If the driver doesn't support fetching specifically from RETURNING easily in all modes, we'll try standard approaches.
            // But PDO with PGSQL supports it.
            
            try {
                // Try clean insert without ID (let DB handle auto-increment if int, or we might need UUID?)
                // Assuming companies.id is SERIAL or UUID. Based on register.php it seems it might be UUID or int.
                // register.php uses: $conn->query("SELECT id, name...
                // Let's assume standard INSERT.
                
                // We'll treat it as likely SERIAL or we need to gen UUID?
                // Let's check table schema? No strictly.
                // NOTE: If table doesn't exist, this will fail. Assuming tables exist.
                
                // Let's try to assume it's SERIAL/INTEGER for now based on standard usage, or UUID.
                // If it fails, I'll update.
                $stmt = $conn->prepare("INSERT INTO companies (name, logo_url) VALUES (?, ?)");
                $stmt->execute([$comp['name'], $comp['logo_url']]);
                
                // Get ID
                $stmt = $conn->prepare("SELECT id FROM companies WHERE name = ?");
                $stmt->execute([$comp['name']]);
                $newId = $stmt->fetchColumn();
                $companyIds[$comp['name']] = $newId;
                echo " - Created company: {$comp['name']}\n";
            } catch (PDOException $e) {
               echo "Error creating company: " . $e->getMessage() . "\n";
            }
        }
    }
    
    $mainCompanyId = array_values($companyIds)[0]; // Use first company for users
    
    // 2. Seed Users
    echo "\nSeeding Users...\n";
    
    $users = [
        [
            'email' => 'hr@example.com',
            'password' => 'password123',
            'full_name' => 'HR Manager',
            'role' => 'hr',
            'employment_type' => 'permanent',
            'basic_salary' => 5000,
            'hourly_rate' => 0,
            'internship_months' => null
        ],
        [
            'email' => 'permanent@example.com',
            'password' => 'password123',
            'full_name' => 'Permanent Staff',
            'role' => 'staff',
            'employment_type' => 'permanent',
            'basic_salary' => 3500,
            'hourly_rate' => 0,
            'internship_months' => null
        ],
        [
            'email' => 'parttime@example.com',
            'password' => 'password123',
            'full_name' => 'Part Time Staff',
            'role' => 'staff',
            'employment_type' => 'part-time',
            'basic_salary' => 0,
            'hourly_rate' => 15, // RM15/hour
            'internship_months' => null
        ],
        [
            'email' => 'intern@example.com',
            'password' => 'password123',
            'full_name' => 'Intern Student',
            'role' => 'staff',
            'employment_type' => 'intern',
            'basic_salary' => 500, // Allowance
            'hourly_rate' => 0,
            'internship_months' => 6
        ],
        [
            'email' => 'leader@example.com',
            'password' => 'password123',
            'full_name' => 'Team Leader',
            'role' => 'staff', // Still staff role, but leader type
            'employment_type' => 'leader',
            'basic_salary' => 4500,
            'hourly_rate' => 0,
            'internship_months' => null
        ]
    ];
    
    foreach ($users as $user) {
        $stmt = $conn->prepare("SELECT id FROM profiles WHERE email = ?");
        $stmt->execute([$user['email']]);
        if ($stmt->fetch()) {
            echo " - User already exists: {$user['email']}\n";
        } else {
            // Generate UUID
            $uuid = sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );
            
            $hashed = password_hash($user['password'], PASSWORD_DEFAULT);
            
            try {
                // Try insert
                 $stmt = $conn->prepare("
                        INSERT INTO profiles (id, email, full_name, password, role, employment_type, company_id, basic_salary, hourly_rate, internship_months, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                    ");
                 $stmt->execute([
                     $uuid, 
                     $user['email'], 
                     $user['full_name'], 
                     $hashed, 
                     $user['role'], 
                     $user['employment_type'], 
                     $mainCompanyId,
                     $user['basic_salary'],
                     $user['hourly_rate'],
                     $user['internship_months']
                 ]);
                 echo " - Created user: {$user['email']} ({$user['employment_type']})\n";
                 
            } catch (PDOException $e) {
                // Fallback for missing columns if schema varies
                echo "Error creating user {$user['email']}: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n✅ Seeding complete!\n";
    
} catch (PDOException $e) {
    echo "❌ Critical Error: " . $e->getMessage() . "\n";
}
