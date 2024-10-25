<?php
// Define absolute paths to the JSON files
$submissions_file = '/path/to/submissions.json';
$archived_file = '/path/to/archived.json';

// submissions.php - Fetch from the Getform API
$api_url = 'insert your GETFORM API LINK URL WITH TOKEN HERE';
$response = file_get_contents($api_url);
if ($response === false) {
    echo json_encode(['success' => false, 'message' => 'Error fetching submissions from Getform API.']);
    exit;
}

$data = json_decode($response, true);

// Load archived submissions to ensure they are excluded
$archived_submissions = [];
if (file_exists($archived_file)) {
    $archived_submissions = json_decode(file_get_contents($archived_file), true);
    $archived_ids = array_column($archived_submissions, 'id'); // Collect archived submission IDs
} else {
    $archived_ids = [];
}

// Load existing submissions to check for duplicates
$existing_submissions = [];
if (file_exists($submissions_file)) {
    $existing_submissions = json_decode(file_get_contents($submissions_file), true);
}

// Ensure proper structure of the response data
if (isset($data['success']) && $data['success'] === true && isset($data['data']['submissions'])) {
    $submissions = $data['data']['submissions'];

    // Exclude archived submissions and filter out duplicates
    $filtered_submissions = array_filter($submissions, function($submission) use ($archived_ids, $existing_submissions) {
        // Exclude archived submissions
        if (in_array($submission['id'], $archived_ids)) {
            return false;
        }

        // Check for duplicates by email, name, message, phone, and subject
        foreach ($existing_submissions as $existing) {
            if (
                $existing['email'] === $submission['email'] &&
                $existing['name'] === $submission['name'] &&
                $existing['message'] === $submission['message'] &&
                $existing['phone'] === $submission['phone'] && // Check phone for duplicates
                $existing['subject'] === $submission['subject']
            ) {
                // Duplicate found, exclude this submission
                return false;
            }
        }
        return true;
    });

    // Merge new filtered submissions with existing submissions
    $merged_submissions = array_merge($existing_submissions, array_values($filtered_submissions));

    // Save the updated submissions to a JSON file for persistence
    file_put_contents($submissions_file, json_encode($merged_submissions, JSON_PRETTY_PRINT)); // Added PRETTY_PRINT for readability

    echo json_encode(['success' => true, 'submissions' => $merged_submissions]);
} else {
    echo json_encode(['success' => false, 'message' => 'Unexpected data format.']);
}
?>
