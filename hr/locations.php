<?php
/**
 * ============================================
 * HR LOCATIONS MANAGEMENT PAGE
 * ============================================
 * Manage work locations (factories, sorting centers).
 * ============================================
 */

require_once '../includes/header.php';
requireHR();
$pageTitle = 'Locations - MI-NES Payroll System';

$companyId = $_SESSION['company_id'];
$message = '';
$messageType = '';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getConnection();

    if (isset($_POST['add_location'])) {
        $name = sanitize($_POST['name']);
        $address = sanitize($_POST['address']);

        try {
            $stmt = $conn->prepare("INSERT INTO work_locations (company_id, name, address) VALUES (?, ?, ?)");
            $stmt->execute([$companyId, $name, $address]);
            $message = 'Location added successfully.';
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = 'Error adding location.';
            $messageType = 'error';
        }
    }

    if (isset($_POST['delete_location'])) {
        $id = $_POST['location_id'];
        try {
            $stmt = $conn->prepare("UPDATE work_locations SET is_active = FALSE WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'Location deleted successfully.';
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = 'Error deleting location.';
            $messageType = 'error';
        }
    }
}

// Fetch Locations
try {
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT * FROM work_locations WHERE company_id = ? AND is_active = TRUE ORDER BY created_at DESC");
    $stmt->execute([$companyId]);
    $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $locations = [];
}
?>

<?php include '../includes/hr_sidebar.php'; ?>

<div class="main-content">
    <?php
    $navTitle = 'Locations';
    include '../includes/top_navbar.php';
    ?>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType === 'error' ? 'danger' : $messageType ?> alert-dismissible fade show">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Work Locations</h2>
            <p class="text-muted">Manage factories and sorting centers.</p>
        </div>
        <button type="button" class="btn btn-primary rounded-pill" data-bs-toggle="modal"
            data-bs-target="#addLocationModal">
            <i class="bi bi-plus-circle me-2"></i>Add Location
        </button>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <?php if (empty($locations)): ?>
                <div class="text-center py-5">
                    <p class="text-muted">No locations found.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Name</th>
                                <th>Address</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($locations as $loc): ?>
                                <tr>
                                    <td class="ps-4 fw-bold"><?= htmlspecialchars($loc['name']) ?></td>
                                    <td><?= htmlspecialchars($loc['address']) ?></td>
                                    <td class="text-end pe-4">
                                        <form method="POST" class="d-inline"
                                            onsubmit="return confirm('Delete this location?');">
                                            <input type="hidden" name="location_id" value="<?= $loc['id'] ?>">
                                            <button type="submit" name="delete_location"
                                                class="btn btn-sm btn-outline-danger rounded-circle">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Location Modal -->
<div class="modal fade" id="addLocationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Add New Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Location Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Factory A" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="3" placeholder="Full address"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_location" class="btn btn-primary rounded-pill">Save
                        Location</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>