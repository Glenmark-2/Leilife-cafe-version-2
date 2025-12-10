<?php
// Ensure $page is set (default to dashboard if not)
$activePage = $page ?? 'dashboard';
?>

<nav class="bottom-nav">
    <a href="?page=dashboard" class="nav-item <?php echo $activePage === 'dashboard' ? 'active' : ''; ?>">
        <i class="ph <?php echo $activePage === 'dashboard' ? 'ph-house-fill' : 'ph-house'; ?>"></i>
        <span>Home</span>
    </a>

    <a href="?page=available" class="nav-item <?php echo $activePage === 'available' ? 'active' : ''; ?>">
        <i class="ph <?php echo $activePage === 'available' ? 'ph-package-fill' : 'ph-package'; ?>"></i>
        <span>Available</span>
    </a>

    <a href="?page=deliveries" class="nav-item <?php echo $activePage === 'deliveries' ? 'active' : ''; ?>">
        <i class="ph <?php echo $activePage === 'deliveries' ? 'ph-moped-fill' : 'ph-moped'; ?>"></i>
        <span>My Deliveries</span>
    </a>

    <a href="?page=profile" class="nav-item <?php echo $activePage === 'profile' ? 'active' : ''; ?>">
        <i class="ph <?php echo $activePage === 'profile' ? 'ph-user-fill' : 'ph-user'; ?>"></i>
        <span>Profile</span>
    </a>
</nav>

<script>
    // Simple script to handle 'active' state purely visually if needed, though PHP handles it on load.
    // Also exposes the current page.
    window.currentDriverPage = "<?php echo htmlspecialchars($activePage); ?>";
</script>
</body>
</html>
