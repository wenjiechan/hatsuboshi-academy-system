<footer class="app-footer">
    <span>&copy; 2026 Hatsuboshi Gakuen</span>
    <span>
        Logged in as
        <?= htmlspecialchars($_SESSION['user_name'] ?? 'Guest', ENT_QUOTES, 'UTF-8') ?>
        /
        <?= htmlspecialchars(ucfirst($_SESSION['role'] ?? 'guest'), ENT_QUOTES, 'UTF-8') ?>
    </span>
</footer>

</body>
</html>