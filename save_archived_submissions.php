<?php
// Get the raw POST data
$data = file_get_contents('php://input');

// Decode the existing archived submissions
$archived_submissions = [];
if (file_exists('archived.json')) {
    $archived_submissions = json_decode(file_get_contents('archived.json'), true);
}

// Decode new data and append it to the archived submissions
$new_data = json_decode($data, true);
$archived_submissions = array_merge($archived_submissions, $new_data);

// Save the updated archived submissions
file_put_contents('archived.json', json_encode($archived_submissions));

// Return success message
echo json_encode(['success' => true]);
?>
