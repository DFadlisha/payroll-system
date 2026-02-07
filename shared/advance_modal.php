<!-- Edit Advance Modal -->
<div class="modal fade" id="advanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-cash-coin text-warning me-2"></i>Edit Salary Advance
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="update_advance" value="1">
                <input type="hidden" name="payroll_id" id="advancePayrollId">
                <div class="modal-body">
                    <div class="alert alert-info py-2 small">
                        <i class="bi bi-info-circle me-1"></i>
                        Enter the salary advance amount to be deducted from this employee's payslip. Net pay will be automatically recalculated.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Employee</label>
                        <input type="text" class="form-control bg-light" id="advanceEmployeeName" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Advance Amount (RM)</label>
                        <input type="number" step="0.01" min="0" name="advance" id="advanceAmount" 
                            class="form-control" placeholder="0.00" required>
                        <small class="text-muted">This amount will be deducted from the employee's net pay.</small>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4">
                        <i class="bi bi-save me-2"></i>Update Advance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAdvanceModal(payrollId, employeeName, currentAdvance) {
    document.getElementById('advancePayrollId').value = payrollId;
    document.getElementById('advanceEmployeeName').value = employeeName;
    document.getElementById('advanceAmount').value = currentAdvance;
    
    var modal = new bootstrap.Modal(document.getElementById('advanceModal'));
    modal.show();
}
</script>
