<?php
// Define absolute paths to the JSON files
$submissions_file = '/path/to/submissions.json';
$archived_file = '/path/to/archived.json';
$last_submission_file = '/path/to/last_submission.json';


// submissions.php - Fetch from the Getform API
$api_url = 'INSERT YOUR GETFORM API URL HERE';
$response = file_get_contents($api_url);
if ($response === false) {
    echo json_encode(['success' => false, 'message' => 'Error fetching submissions from Getform API.']);
    exit;
}

$data = json_decode($response, true);
if ($data === null) {
    echo json_encode(['success' => false, 'message' => 'Invalid response from Getform API.']);
    exit;
}

// Load the last processed submission ID if available
$last_processed_id = null;
if (file_exists($last_submission_file)) {
    $last_submission_data = json_decode(file_get_contents($last_submission_file), true);
    if ($last_submission_data !== null && isset($last_submission_data['last_id'])) {
        $last_processed_id = $last_submission_data['last_id'];
    }
}

// Load existing submissions to check for duplicates
$existing_submissions = [];
if (file_exists($submissions_file)) {
    $existing_submissions = json_decode(file_get_contents($submissions_file), true);
}

// Load archived submissions to ensure they are excluded
$archived_submissions = [];
if (file_exists($archived_file)) {
    $archived_submissions = json_decode(file_get_contents($archived_file), true);
    $archived_ids = array_column($archived_submissions, 'id'); // Collect archived submission IDs
} else {
    $archived_ids = [];
}

// Ensure proper structure of the response data
if (isset($data['success']) && $data['success'] === true && isset($data['data']['submissions'])) {
    $submissions = $data['data']['submissions'];

    // Filter to include only submissions newer than the last processed one
    $new_submissions = [];
    foreach ($submissions as $submission) {
        if ($last_processed_id === null || $submission['id'] > $last_processed_id) {
            // Exclude archived submissions and duplicates
            if (!in_array($submission['id'], $archived_ids)) {
                $is_duplicate = false;
                foreach ($existing_submissions as $existing) {
                    if (
                        $existing['email'] === $submission['email'] &&
                        $existing['name'] === $submission['name'] &&
                        $existing['message'] === $submission['message'] &&
                        $existing['phone'] === $submission['phone'] &&
                        $existing['subject'] === $submission['subject']
                    ) {
                        $is_duplicate = true;
                        break;
                    }
                }
                if (!$is_duplicate) {
                    $new_submissions[] = $submission;
                }
            }
        }
    }

    // If there are new submissions, merge them with existing submissions
    if (!empty($new_submissions)) {
        $merged_submissions = array_merge($existing_submissions, $new_submissions);

        // Save the updated submissions to a JSON file for persistence
        file_put_contents($submissions_file, json_encode($merged_submissions, JSON_PRETTY_PRINT));

        // Update the last processed submission ID
        $last_processed_id = end($new_submissions)['id'];
        file_put_contents($last_submission_file, json_encode(['last_id' => $last_processed_id]));

        echo json_encode(['success' => true, 'submissions' => $merged_submissions]);
    } else {
        // No new submissions to process
        echo json_encode(['success' => true, 'message' => 'No new submissions.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Unexpected data format.']);
}
?>
