<?php
// Get the raw POST data
$data = file_get_contents('php://input');

// Decode the existing archived submissions
$archived_submissions = [];
if (file_exists('archived.json')) {
    $archived_submissions = json_decode(file_get_contents('archived.json'), true);
    if ($archived_submissions === null) {
        $archived_submissions = []; // Handle case where JSON is malformed or empty
    }
}

// Decode new data
$new_data = json_decode($data, true);
if ($new_data === null) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

// Append new data if it does not already exist
foreach ($new_data as $new_submission) {
    $exists = false;
    foreach ($archived_submissions as $existing_submission) {
        if ($existing_submission['id'] === $new_submission['id']) {
            $exists = true;
            break;
        }
    }
    if (!$exists) {
        $archived_submissions[] = $new_submission;
    }
}

// Save the updated archived submissions
file_put_contents('archived.json', json_encode($archived_submissions, JSON_PRETTY_PRINT));

// Return success message
echo json_encode(['success' => true]);
?>
