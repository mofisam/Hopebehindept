</main> <!-- Close main content column -->
        </div> <!-- Close row -->
    </div> <!-- Close container-fluid -->

    <!-- Footer -->
    <footer class="footer mt-auto py-3 bg-light border-top">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6 text-muted">
                    &copy; <?= date('Y') ?> Hopebehindebt. All rights reserved.
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="text-muted me-3">v1.0.0</span>
                    <a href="#" class="text-muted me-3"><i class="bi bi-life-preserver"></i> Help</a>
                    <a href="#" class="text-muted"><i class="bi bi-shield-lock"></i> Privacy</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Required Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- DataTables (for tables) -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Summernote (for rich text editors) -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    
    <!-- SweetAlert2 (for alerts) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom Admin JS -->
    <script src="../assets/js/admin.js"></script>
    
    <?php if (isset($customJS)): ?>
        <!-- Page-specific JS -->
        <script src="<?= $customJS ?>"></script>
    <?php endif; ?>
    
    <!-- Initialize DataTables -->
    <script>
        $(document).ready(function() {
            $('table').DataTable({
                responsive: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search...",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    infoFiltered: "(filtered from _MAX_ total entries)",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                }
            });
        });
    </script>
</body>
</html>