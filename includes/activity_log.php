<?php

require_once __DIR__.'/../models/ActivityLogModel.php';

function writeLog(
    $conn,
    $action,
    $module,
    $description
) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $userId = null;

    /* CHECK ADMIN */
    if (isset($_SESSION['admin'])) {
        $userId = $_SESSION['admin']['user_id'];
    }

    /* CHECK CUSTOMER */
    elseif (isset($_SESSION['customer'])) {
        $userId = $_SESSION['customer']['user_id'];
    }

    /* NOT LOGIN */
    if (!$userId) {
        return;
    }

    $log = new ActivityLogModel($conn);

    $log->addLog(
        $userId,
        $action,
        $module,
        $description
    );
}
