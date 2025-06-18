<?php
// Domain → project map
$inetgap_projects = [
    'valor.inetgap.sk' => 'valor',
    'klima.inetgap.sk' => 'mallay'
];

// Helper to get current project based on host
function inetgap_get_current_project() {
    global $inetgap_projects;

    $host = $_SERVER['HTTP_HOST'] ?? '';
    $host = preg_replace('/^www\./', '', $host);    // remove www
    $host = preg_replace('/:\d+$/', '', $host);     // remove port

    return $inetgap_projects[$host] ?? null;
}
