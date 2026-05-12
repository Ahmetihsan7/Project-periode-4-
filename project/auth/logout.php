<?php
// =============================================
// Logout — sessie vernietigen en redirect
// =============================================
require_once __DIR__ . '/../includes/functions.php';

$_SESSION['flash'] = ['type' => 'info', 'bericht' => 'Je bent uitgelogd. Tot ziens!'];
session_destroy();
header('Location: /index.php');
exit;
