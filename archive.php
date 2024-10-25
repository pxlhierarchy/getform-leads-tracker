<?php
// archive.php

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;
$notes = $data['notes'] ?? '';

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'No ID provided']);
    exit;
}

// File paths
$submissionsFile = 'submissions.json';
$archivedFile = 'archived.json';

// Read current submissions from submissions.json
$submissions = file_exists($submissionsFile) ? json_decode(file_get_contents($submissionsFile), true) : [];

// Archive submission
$archivedSubmissions = [];
$updatedSubmissions = [];

foreach ($submissions as $submission) {
    if ($submission['id'] == $id) {
        // Add the submission to archived submissions
        $submission['notes'] = $notes; // Add notes to the archived submission
        $archivedSubmissions[] = $submission;
    } else {
        // Keep the submission in the updated list
        $updatedSubmissions[] = $submission;
    }
}

// Save updated submissions back to submissions.json
file_put_contents($submissionsFile, json_encode($updatedSubmissions, JSON_PRETTY_PRINT));

// Save archived submissions to archived.json
if (!file_exists($archivedFile)) {
    file_put_contents($archivedFile, json_encode([], JSON_PRETTY_PRINT)); // Create the file if it doesn't exist
}
$existingArchives = json_decode(file_get_contents($archivedFile), true);
$existingArchives = array_merge($existingArchives, $archivedSubmissions);
file_put_contents($archivedFile, json_encode($existingArchives, JSON_PRETTY_PRINT));

// Return success response
echo json_encode(['success' => true]);
