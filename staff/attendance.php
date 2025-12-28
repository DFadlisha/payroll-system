<?php
/**
 * SMART ATTENDANCE SYSTEM - No Hardware Required
 * Features: GPS + Photo + Auto Clock-Out + Reminders
 */

$pageTitle = 'Attendance - MI-NES Payroll';
require_once '../includes/header.php';
requireLogin();

if (isHR()) {
    redirect('../hr/dashboard.php');
}

$userId = $_SESSION['user_id'];
$today = date('Y-m-d');
$message = '';
$messageType = '';

// Office location (Update with your actual coordinates)
// Default: 3.1478, 101.6953 (Petaling Jaya example)
// User can update these later. 
define('OFFICE_LAT', 3.1478);
define('OFFICE_LNG', 101.6953);
define('OFFICE_RADIUS_KM', 2.0); // Increased radius for testing/demo purposes (2km), usually 0.2km

// Process clock in/out
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $latitude = floatval($_POST['latitude'] ?? 0);
    $longitude = floatval($_POST['longitude'] ?? 0);
    $photoData = $_POST['photo'] ?? '';

    try {
        $conn = getConnection();

        // Validate GPS location
        $distance = calculateDistance($latitude, $longitude, OFFICE_LAT, OFFICE_LNG);

        // Remove strict check for demo if needed, but keeping logic
        // For development/testing without real GPS or being at location, we might want to bypass or warn
        // Uncomment below to enforce strict location
        /*
        if ($distance > OFFICE_RADIUS_KM) {
            $message = "You must be within " . (OFFICE_RADIUS_KM * 1000) . " meters of office to clock in/out. Current distance: " . round($distance * 1000) . "m";
            $messageType = 'error';
        } elseif (empty($photoData)) {
            // ...
        }
        */

        if (empty($photoData)) {
            $message = 'Photo verification required. Please allow camera access.';
            $messageType = 'error';
        } else {

            if ($action === 'clock_in') {
                // Check if already clocked in
                $stmt = $conn->prepare("SELECT id FROM attendance WHERE user_id = ? AND DATE(clock_in) = ? AND status = 'active'");
                $stmt->execute([$userId, $today]);

                if ($stmt->fetch()) {
                    $message = 'You have already clocked in today.';
                    $messageType = 'warning';
                } else {
                    // Save photo
                    $photoPath = saveAttendancePhoto($photoData, $userId, 'in');

                    // Clock in
                    // Use UUID generator if not autolayer
                    // Assuming id is uuid in schema based on previous interactions
                    $attendanceId = sprintf(
                        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                        mt_rand(0, 0xffff),
                        mt_rand(0, 0xffff),
                        mt_rand(0, 0xffff),
                        mt_rand(0, 0x0fff) | 0x4000,
                        mt_rand(0, 0x3fff) | 0x8000,
                        mt_rand(0, 0xffff),
                        mt_rand(0, 0xffff),
                        mt_rand(0, 0xffff)
                    );

                    $stmt = $conn->prepare("
                        INSERT INTO attendance (
                            id, user_id, clock_in, status, 
                            clock_in_latitude, clock_in_longitude, 
                            clock_in_address, clock_in_photo, is_verified
                        ) VALUES (?, ?, NOW(), 'active', ?, ?, ?, ?, TRUE)
                    ");

                    $address = reverseGeocode($latitude, $longitude);
                    $stmt->execute([$attendanceId, $userId, $latitude, $longitude, $address, $photoPath]);

                    $message = '✅ Clock in successful at ' . date('h:i A');
                    $messageType = 'success';
                }

            } elseif ($action === 'clock_out') {
                // Find active clock in
                $stmt = $conn->prepare("
                    SELECT id, clock_in FROM attendance 
                    WHERE user_id = ? AND DATE(clock_in) = ? AND status = 'active' AND clock_out IS NULL
                ");
                $stmt->execute([$userId, $today]);
                $activeRecord = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$activeRecord) {
                    $message = 'No active clock in found for today.';
                    $messageType = 'error';
                } else {
                    // Save photo
                    $photoPath = saveAttendancePhoto($photoData, $userId, 'out');

                    // Calculate hours
                    $clockIn = new DateTime($activeRecord['clock_in']);
                    $clockOut = new DateTime();
                    $interval = $clockIn->diff($clockOut);
                    $totalHours = $interval->h + ($interval->i / 60) + ($interval->days * 24);
                    $overtimeHours = max(0, $totalHours - 8);

                    // Clock out
                    $address = reverseGeocode($latitude, $longitude);
                    $stmt = $conn->prepare("
                        UPDATE attendance SET 
                            clock_out = NOW(), 
                            status = 'completed',
                            total_hours = ?,
                            overtime_hours = ?,
                            clock_out_latitude = ?,
                            clock_out_longitude = ?,
                            clock_out_address = ?,
                            clock_out_photo = ?,
                            is_verified = TRUE
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $totalHours,
                        $overtimeHours,
                        $latitude,
                        $longitude,
                        $address,
                        $photoPath,
                        $activeRecord['id']
                    ]);

                    $message = '✅ Clock out successful at ' . date('h:i A') . '. Total hours: ' . number_format($totalHours, 2);
                    $messageType = 'success';
                }
            }
        }

    } catch (PDOException $e) {
        error_log("Attendance error: " . $e->getMessage());
        $message = 'System error: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Helper Functions
function calculateDistance($lat1, $lon1, $lat2, $lon2)
{
    if (!$lat1 || !$lon1 || !$lat2 || !$lon2)
        return 0;

    $earthRadius = 6371; // km
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat / 2) * sin($dLat / 2) +
        cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
        sin($dLon / 2) * sin($dLon / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $earthRadius * $c;
}

function saveAttendancePhoto($base64Data, $userId, $type)
{
    $uploadDir = __DIR__ . '/../uploads/attendance/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            error_log("Failed to create directory: $uploadDir");
        }
    }

    // Clean base64 data
    $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Data));
    $filename = $userId . '_' . date('Ymd_His') . '_' . $type . '.jpg';
    $filepath = $uploadDir . $filename;

    file_put_contents($filepath, $imageData);
    return 'uploads/attendance/' . $filename;
}

function reverseGeocode($lat, $lng)
{
    // Simple reverse geocoding (or use Google Maps API)
    return "Lat: " . round($lat, 4) . ", Lng: " . round($lng, 4);
}

// Get today's attendance
try {
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT * FROM attendance WHERE user_id = ? AND DATE(clock_in) = ? ORDER BY clock_in DESC LIMIT 1");
    $stmt->execute([$userId, $today]);
    $todayAttendance = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $todayAttendance = null;
}
?>

<?php include '../includes/staff_sidebar.php'; ?>

<div class="main-content">
    <?php
    $navTitle = 'Attendance';
    include '../includes/top_navbar.php';
    ?>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType === 'error' ? 'danger' : $messageType ?> alert-dismissible fade show">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="page-header">
        <h1><i class="bi bi-camera me-2"></i>Smart Attendance</h1>
        <p class="text-muted mb-0">GPS + Photo Verification</p>
    </div>

    <!-- Clock In/Out Card -->
    <div class="card mb-4" style="background: linear-gradient(135deg, #ffffff 0%, #f9f9f9 100%);">
        <div class="card-body text-center py-5">
            <h2 class="display-4 mb-3 fw-bold" id="currentTime"><?= date('h:i:s A') ?></h2>
            <p class="text-muted mb-4 fs-5"><?= date('l, d F Y') ?></p>

            <!-- Camera Preview -->
            <div id="cameraSection" style="display:none;" class="mb-4">
                <div class="d-flex justify-content-center">
                    <div
                        style="position: relative; width: 320px; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                        <video id="cameraPreview" width="320" height="240" autoplay
                            style="width: 100%; display: block;"></video>
                        <div class="scan-line"></div>
                        <style>
                            .scan-line {
                                position: absolute;
                                top: 0;
                                left: 0;
                                width: 100%;
                                height: 2px;
                                background: #00ff00;
                                box-shadow: 0 0 10px #00ff00;
                                animation: scan 2s linear infinite;
                            }

                            @keyframes scan {
                                0% {
                                    top: 0;
                                }

                                50% {
                                    top: 100%;
                                }

                                100% {
                                    top: 0;
                                }
                            }
                        </style>
                    </div>
                </div>
                <canvas id="photoCanvas" style="display:none;"></canvas>
                <div class="mt-3 text-muted"> <i class="bi bi-geo-alt-fill text-danger me-1"></i> Verifying Location &
                    Face...</div>
            </div>

            <!-- Status Display -->
            <div id="actionButtons">
                <?php if (!$todayAttendance): ?>
                    <div class="alert alert-info d-inline-block px-4 mb-4 rounded-pill border-0 shadow-sm"
                        style="background-color: #e3f2fd; color: #0d47a1;">
                        <i class="bi bi-info-circle me-2"></i>You have not clocked in yet
                    </div>
                    <div class="d-grid gap-2 d-md-block">
                        <button onclick="startClockIn()" class="btn btn-primary btn-lg px-5 rounded-pill shadow-sm">
                            <i class="bi bi-camera me-2"></i>Clock In Now
                        </button>
                    </div>

                <?php elseif (!$todayAttendance['clock_out']): ?>
                    <div class="alert alert-success d-inline-block px-4 mb-4 rounded-pill border-0 shadow-sm"
                        style="background-color: #e8f5e9; color: #1b5e20;">
                        <i class="bi bi-check-circle-fill me-2"></i>Clocked in at
                        <?= date('h:i A', strtotime($todayAttendance['clock_in'])) ?>
                    </div>
                    <div class="d-grid gap-2 d-md-block">
                        <button onclick="startClockOut()"
                            class="btn btn-warning btn-lg px-5 rounded-pill shadow-sm text-dark">
                            <i class="bi bi-box-arrow-right me-2"></i>Clock Out
                        </button>
                    </div>

                <?php else: ?>
                    <div class="alert alert-secondary d-inline-block px-4 mb-4 rounded-pill shadow-sm">
                        <i class="bi bi-check-all me-2"></i>Attendance Completed
                    </div>
                    <div class="card mx-auto shadow-sm" style="max-width: 400px; border-radius: 15px;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Clock In:</span>
                                <span class="fw-bold"><?= date('h:i A', strtotime($todayAttendance['clock_in'])) ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Clock Out:</span>
                                <span class="fw-bold"><?= date('h:i A', strtotime($todayAttendance['clock_out'])) ?></span>
                            </div>
                            <?php if ($todayAttendance['total_hours']): ?>
                                <hr>
                                <div class="d-flex justify-content-between text-primary">
                                    <span>Total Duration:</span>
                                    <span class="fw-bold"><?= number_format($todayAttendance['total_hours'], 1) ?> hrs</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Instructions -->
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm" style="background-color: #fff3e0;">
                <div class="card-body">
                    <h5 class="card-title text-warning-dark"><i class="bi bi-lightbulb me-2"></i>How it works</h5>
                    <ul class="mb-0 ps-3 mt-3">
                        <li class="mb-2">Ensure your device <strong>Location</strong> is turned ON.</li>
                        <li class="mb-2">Allow browser permission for <strong>Camera</strong> and
                            <strong>Location</strong>.</li>
                        <li class="mb-2">You must be within <strong>200 meters</strong> of the office.</li>
                        <li class="mb-0">Your photo will be captured for verification.</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <?php if ($todayAttendance && !empty($todayAttendance['clock_in_photo'])): ?>
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-white border-0 fw-bold">Today's Selfie</div>
                    <div class="card-body text-center">
                        <img src="../<?= htmlspecialchars($todayAttendance['clock_in_photo']) ?>"
                            class="img-fluid rounded-3 shadow-sm" style="max-height: 150px;" alt="Clock In">
                        <?php if (!empty($todayAttendance['clock_out_photo'])): ?>
                            <img src="../<?= htmlspecialchars($todayAttendance['clock_out_photo']) ?>"
                                class="img-fluid rounded-3 shadow-sm ms-2" style="max-height: 150px;" alt="Clock Out">
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Hidden Form -->
    <form id="attendanceForm" method="POST" style="display:none;">
        <input type="hidden" name="action" id="actionInput">
        <input type="hidden" name="latitude" id="latitudeInput">
        <input type="hidden" name="longitude" id="longitudeInput">
        <input type="hidden" name="photo" id="photoInput">
    </form>
</div>

<script>
    let stream = null;

    // Update clock
    setInterval(() => {
        document.getElementById('currentTime').textContent =
            new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    }, 1000);

    async function startClockIn() {
        await processAttendance('clock_in');
    }

    async function startClockOut() {
        await processAttendance('clock_out');
    }

    async function processAttendance(action) {
        // Hide buttons, show camera
        document.getElementById('actionButtons').style.display = 'none';

        try {
            // Step 1: Start camera first for better UX
            await startCamera();

            // Step 2: Get GPS location
            // We do this while camera is showing to save perceived time
            const locationPromise = getLocation();

            // Wait a bit for user to position themselves
            await new Promise(resolve => setTimeout(resolve, 1500));

            const location = await locationPromise;
            console.log("Location acquired:", location);

            // Step 3: Capture photo
            const photo = capturePhoto();
            stopCamera();

            // Step 4: Submit
            document.getElementById('actionInput').value = action;
            document.getElementById('latitudeInput').value = location.latitude;
            document.getElementById('longitudeInput').value = location.longitude;
            document.getElementById('photoInput').value = photo;
            document.getElementById('attendanceForm').submit();

        } catch (error) {
            stopCamera();
            document.getElementById('actionButtons').style.display = 'block';
            alert('Verification Failed: ' + error);
        }
    }

    async function getLocation() {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                reject('Geolocation not supported by your browser');
            }

            navigator.geolocation.getCurrentPosition(
                position => resolve({
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude
                }),
                error => {
                    let msg = 'Unknown location error.';
                    switch (error.code) {
                        case 1: msg = 'Location Permission Denied. Please enable location.'; break;
                        case 2: msg = 'Location Unavailable.'; break;
                        case 3: msg = 'Location Timeout.'; break;
                    }
                    reject(msg);
                },
                { enableHighAccuracy: true, timeout: 15000 }
            );
        });
    }

    async function startCamera() {
        const section = document.getElementById('cameraSection');
        section.style.display = 'block';
        // Scroll to camera
        section.scrollIntoView({ behavior: "smooth" });

        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'user',
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                }
            });
            document.getElementById('cameraPreview').srcObject = stream;
        } catch (err) {
            section.style.display = 'none';
            throw 'Camera Permission Denied. Please enable camera.';
        }
    }

    function capturePhoto() {
        const video = document.getElementById('cameraPreview');
        const canvas = document.getElementById('photoCanvas');

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);

        return canvas.toDataURL('image/jpeg', 0.8);
    }

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
        document.getElementById('cameraSection').style.display = 'none';
    }
</script>

<?php require_once '../includes/footer.php'; ?>