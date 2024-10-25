<?php
// Get the raw POST data
$data = file_get_contents('php://input');

// Save to submissions.json
file_put_contents('submissions.json', $data);

// Return success message
echo json_encode(['success' => true]);
?>
