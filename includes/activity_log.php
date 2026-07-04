<?php

require_once __DIR__.'/../models/ActivityLogModel.php';

function writeLog(
    $conn,
    $action,
    $module,
    $description
) {
    if (!isset($_SESSION['user'])) {
        return;
    }

    $log = new ActivityLogModel($conn);

    $log->addLog(
        $_SESSION['user']['user_id'],
        $action,
        $module,
        $description
    );
}
