    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Mobile sidebar toggle
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('show');
        }
        
        // Show loading spinner
        function showLoading() {
            document.getElementById('loading').classList.add('show');
        }
        
        // Hide loading spinner
        function hideLoading() {
            document.getElementById('loading').classList.remove('show');
        }
        
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                var alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
        
        // Confirm delete
        function confirmDelete(message) {
            return confirm(message || 'Adakah anda pasti untuk memadam?');
        }
        
        // Format input as currency
        function formatCurrency(input) {
            let value = input.value.replace(/[^0-9.]/g, '');
            if (value) {
                value = parseFloat(value).toFixed(2);
                input.value = value;
            }
        }
        
        // Form validation highlight
        (function() {
            'use strict';
            var forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>
</html>
