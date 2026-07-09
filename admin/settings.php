<?php
require_once '../includes/auth.php';
require_role('admin');

// Admin settings reuse the shared settings page after role access is confirmed.
require_once '../includes/settings_page.php';
