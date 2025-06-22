<?php
/**
 * Get the current top-level domain (TLD) from the server's HTTP host.
 * Example: If the host is "example.com", this function will return "com".
 */
function getCurrentTld() {
    // Get the host from the current URL
    $host = $_SERVER['HTTP_HOST'];

    // Split the host by '.' to isolate the TLD
    $hostParts = explode('.', $host);
    $tld = end($hostParts); // Gets the last part

    return $tld;
}

// Helper to get current project based on host
function inetgap_get_current_project() {
    global $inetgap_projects;

    $host = $_SERVER['HTTP_HOST'] ?? '';
    $host = preg_replace('/^www\./', '', $host);    // remove www
    $host = preg_replace('/:\d+$/', '', $host);     // remove port

    return $inetgap_projects[$host] ?? null;
}
