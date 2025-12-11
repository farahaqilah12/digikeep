<?php
// footer.php
// Ensure session is started if not already (safeguard)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determine display name/role safely
$displayRole = 'Guest';
if (isset($_SESSION['role'])) {
    $displayRole = ucfirst($_SESSION['role']); // e.g., "Admin" or "User"
} elseif (isset($_SESSION['username'])) {
    $displayRole = htmlspecialchars($_SESSION['username']);
}
?>

    <footer class="footer mt-5 py-4 bg-light border-top">
        <div class="container-fluid">
            <div class="row align-items-center">
                
                <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                    <span class="text-muted small">
                        &copy; <?= date('Y') ?> <strong>Asset Management System</strong>. All rights reserved.
                    </span>
                </div>

                <div class="col-md-6 text-center text-md-end">
                    <span class="text-muted small me-3">Version 1.0</span>
                    <span class="text-muted small">
                        Logged in as: <span class="fw-bold text-primary"><?= $displayRole ?></span>
                    </span>
                </div>
                
            </div>
        </div>
    </footer>

  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>

</body>
</html>