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
