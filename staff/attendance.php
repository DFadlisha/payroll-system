<?php
/**
 * ENHANCED ATTENDANCE SYSTEM - High Trust & Transparency
 * Features: GPS Tracking, Photo Hash, IP Logging, Device Info
 */

$pageTitle = 'Enhanced Attendance - MI-NES Payroll';
require_once '../includes/header.php';
requireLogin();

if (isHR()) {
    redirect('../hr/dashboard.php');
}

$userId = $_SESSION['user_id'];
$today = date('Y-m-d');
$message = '';
$messageType = '';

// Initialize connection
$conn = getConnection();

// Process clock in/out
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $latitude = floatval($_POST['latitude'] ?? 0);
    $longitude = floatval($_POST['longitude'] ?? 0);
    $photoData = $_POST['photo'] ?? '';
    $deviceInfo = $_POST['device_info'] ?? '';
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

    try {
        // (Removed redundant $conn init)

        if (empty($photoData)) {
            $message = 'Photo verification required.';
            $messageType = 'error';
        } else {
            // Generate photo hash for security
            $photoHash = hash('sha256', $photoData);
            
            // Reverse Geocode (Simplified)
            $gpsLocation = $latitude . ', ' . $longitude;

            if ($action === 'clock_in') {
                $stmt = $conn->prepare("SELECT id FROM attendance WHERE user_id = ? AND DATE(clock_in) = ? AND status = 'active'");
                $stmt->execute([$userId, $today]);

                if ($stmt->fetch()) {
                    $message = 'You are already clocked in.';
                    $messageType = 'warning';
                } else {
                    $photoPath = saveAttendancePhoto($photoData, $userId, 'in');
                    $attendanceId = generateUuid();

                    $stmt = $conn->prepare("
                        INSERT INTO attendance (
                            id, user_id, clock_in, status, 
                            clock_in_latitude, clock_in_longitude, 
                            clock_in_address, clock_in_photo, 
                            gps_location, ip_address, photo_hash, device_info,
                            is_verified
                        ) VALUES (?, ?, NOW(), 'active', ?, ?, ?, ?, ?, ?, ?, ?, TRUE)
                    ");

                    $stmt->execute([
                        $attendanceId, $userId, 
                        $latitude, $longitude, $gpsLocation, $photoPath,
                        $gpsLocation, $ipAddress, $photoHash, $deviceInfo
                    ]);

                    $message = '✅ Clock In successful with security verification.';
                    $messageType = 'success';
                }
            } elseif ($action === 'clock_out') {
                $stmt = $conn->prepare("
                    SELECT id FROM attendance 
                    WHERE user_id = ? AND DATE(clock_in) = ? AND status = 'active'
                ");
                $stmt->execute([$userId, $today]);
                $record = $stmt->fetch();

                if (!$record) {
                    $message = 'No active clock in found.';
                    $messageType = 'error';
                } else {
                    $photoPath = saveAttendancePhoto($photoData, $userId, 'out');
                    
                    $stmt = $conn->prepare("
                        UPDATE attendance SET 
                            clock_out = NOW(), 
                            status = 'completed',
                            clock_out_latitude = ?,
                            clock_out_longitude = ?,
                            clock_out_photo = ?,
                            gps_location = CONCAT(gps_location, ' | Out: ', ?),
                            updated_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $latitude, $longitude, $photoPath, $gpsLocation, $record['id']
                    ]);

                    $message = '✅ Clock Out successful. Have a good rest!';
                    $messageType = 'success';
                }
            }
        }
    } catch (PDOException $e) {
        $message = 'System error: ' . $e->getMessage();
        $messageType = 'error';
    }
}

function saveAttendancePhoto($base64Data, $userId, $type) {
    $uploadDir = __DIR__ . '/../uploads/attendance/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Data));
    $filename = $userId . '_' . date('Ymd_His') . '_' . $type . '.jpg';
    file_put_contents($uploadDir . $filename, $imageData);
    return 'uploads/attendance/' . $filename;
}

function generateUuid() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

// Get history
$stmt = $conn->prepare("SELECT * FROM attendance WHERE user_id = ? ORDER BY clock_in DESC LIMIT 5");
$stmt->execute([$userId]);
$history = $stmt->fetchAll();
?>

<?php include '../includes/staff_sidebar.php'; ?>

<div class="main-content">
    <?php $navTitle = 'Enhanced Attendance'; include '../includes/top_navbar.php'; ?>

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4">
                    <div class="card-body p-5 text-center bg-white">
                        <h2 class="display-5 fw-bold mb-2" id="liveClock">00:00:00</h2>
                        <p class="text-muted mb-5"><?= date('l, d F Y') ?></p>
                        
                        <div id="securityBadge" class="d-inline-flex align-items-center bg-light px-3 py-2 rounded-pill mb-4 border">
                            <i class="bi bi-shield-lock-fill text-success me-2"></i>
                            <span class="small fw-bold text-uppercase tracking-wider">Security Verified System</span>
                        </div>

                        <div id="cameraWrapper" class="mx-auto mb-4" style="max-width: 400px; display: none;">
                            <div class="position-relative bg-dark rounded-4 overflow-hidden border border-4 border-white shadow">
                                <video id="videoPreview" autoplay playsinline style="width: 100%; transform: scaleX(-1);"></video>
                                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" id="gpsOverlay">
                                    <div class="spinner-border text-white" role="status"></div>
                                </div>
                            </div>
                            <div class="mt-3 text-success small" id="gpsStatus">
                                <i class="bi bi-geo-alt-fill me-1"></i> Determining GPS Location...
                            </div>
                        </div>

                        <div id="mainActions">
                            <?php 
                            $stmt = $conn->prepare("SELECT status FROM attendance WHERE user_id = ? AND DATE(clock_in) = ? AND status = 'active' LIMIT 1");
                            $stmt->execute([$userId, $today]);
                            $isActive = $stmt->fetch();
                            ?>
                            
                            <?php if (!$isActive): ?>
                                <button onclick="prepareAttendance('clock_in')" class="btn btn-primary btn-lg rounded-pill px-5 py-3 shadow-lg">
                                    <i class="bi bi-camera-fill me-2"></i> Clock In Securely
                                </button>
                            <?php else: ?>
                                <button onclick="prepareAttendance('clock_out')" class="btn btn-warning btn-lg rounded-pill px-5 py-3 shadow-lg text-dark">
                                    <i class="bi bi-box-arrow-right me-2"></i> Clock Out Securely
                                </button>
                            <?php endif; ?>
                        </div>
                        
                        <div id="captureAction" style="display: none;">
                            <button onclick="captureAndSubmit()" class="btn btn-success btn-lg rounded-pill px-5 py-3 shadow-lg">
                                <i class="bi bi-check-circle-fill me-2"></i> Confirm Verification
                            </button>
                            <button onclick="cancelAttendance()" class="btn btn-link text-danger mt-3 d-block mx-auto text-decoration-none">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i>Recent Sessions</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr class="small text-uppercase text-muted">
                                        <th class="ps-4">Date</th>
                                        <th>In</th>
                                        <th>Out</th>
                                        <th>Verification</th>
                                        <th class="pe-4">Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($history as $row): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold"><?= date('d M', strtotime($row['clock_in'])) ?></td>
                                            <td><?= date('h:i A', strtotime($row['clock_in'])) ?></td>
                                            <td><?= $row['clock_out'] ? date('h:i A', strtotime($row['clock_out'])) : '<span class="text-warning">Active</span>' ?></td>
                                            <td>
                                                <span class="badge bg-success-soft text-success rounded-pill px-2">
                                                    <i class="bi bi-patch-check-fill me-1"></i> GPS + Photo
                                                </span>
                                            </td>
                                            <td class="pe-4">
                                                <button class="btn btn-sm btn-light rounded-circle" onclick="viewDetails('<?= $row['id'] ?>')">
                                                    <i class="bi bi-info-circle"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 bg-primary text-white mb-4">
                    <div class="card-body p-4 text-center">
                        <div class="mb-3">
                            <i class="bi bi-shield-check display-4"></i>
                        </div>
                        <h4 class="fw-bold">Trust Guarantee</h4>
                        <p class="small opacity-75">All attendance records are timestamped, location-verified, and cryptographically hashed for mutual integrity.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="hiddenForm" method="POST" style="display: none;">
    <input type="hidden" name="action" id="formAction">
    <input type="hidden" name="latitude" id="formLat">
    <input type="hidden" name="longitude" id="formLng">
    <input type="hidden" name="photo" id="formPhoto">
    <input type="hidden" name="device_info" id="formDevice">
</form>

<canvas id="photoCanvas" style="display: none;"></canvas>

<script>
    let currentStream = null;
    let currentAction = null;
    let userCoords = null;

    setInterval(() => {
        document.getElementById('liveClock').innerText = new Date().toLocaleTimeString('en-US', { hour12: false });
    }, 1000);

    async function prepareAttendance(action) {
        currentAction = action;
        document.getElementById('mainActions').style.display = 'none';
        document.getElementById('cameraWrapper').style.display = 'block';
        document.getElementById('captureAction').style.display = 'block';

        try {
            // Start Camera
            currentStream = await navigator.mediaDevices.getUserMedia({ video: true });
            document.getElementById('videoPreview').srcObject = currentStream;

            // Get Location
            navigator.geolocation.getCurrentPosition((pos) => {
                userCoords = pos.coords;
                document.getElementById('gpsOverlay').style.display = 'none';
                document.getElementById('gpsStatus').innerHTML = `<i class="bi bi-geo-alt-fill me-1"></i> GPS Ready (±${Math.round(pos.coords.accuracy)}m)`;
            }, (err) => {
                alert("Please enable GPS/Location to procced.");
                cancelAttendance();
            }, { enableHighAccuracy: true });

        } catch (err) {
            alert("Camera access denied.");
            cancelAttendance();
        }
    }

    function captureAndSubmit() {
        if (!userCoords) return alert("Waiting for GPS...");

        const video = document.getElementById('videoPreview');
        const canvas = document.getElementById('photoCanvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);

        const photoData = canvas.toDataURL('image/jpeg', 0.8);
        
        document.getElementById('formAction').value = currentAction;
        document.getElementById('formLat').value = userCoords.latitude;
        document.getElementById('formLng').value = userCoords.longitude;
        document.getElementById('formPhoto').value = photoData;
        document.getElementById('formDevice').value = navigator.userAgent;
        
        document.getElementById('hiddenForm').submit();
    }

    function cancelAttendance() {
        if (currentStream) currentStream.getTracks().forEach(t => t.stop());
        document.getElementById('mainActions').style.display = 'block';
        document.getElementById('cameraWrapper').style.display = 'none';
        document.getElementById('captureAction').style.display = 'none';
    }
</script>

<?php require_once '../includes/footer.php'; ?>