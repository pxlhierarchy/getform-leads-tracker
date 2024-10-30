<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Redirect to login if not logged in
    header('Location: login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leads-Tracker v.1</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }
        h1 {
            text-align: center;
        }
        .submissions-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-top: 20px;
        }
        @media (max-width: 768px) {
            .submissions-container {
                grid-template-columns: 1fr; /* One column on mobile devices */
            }
        }
        .submission-card {
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }
        .submission-details {
            flex-grow: 1;
        }
        .action-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
        }
        input[type="text"] {
            width: 70%; /* Make the notes input field take up 70% */
            padding: 8px;
            margin-right: 10px; /* Add space between input and button */
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            white-space: nowrap;
        }
        button:hover {
            background-color: #45a049;
        }
        .archive-link {
            display: block;
            text-align: center;
            margin: 20px 0;
        }
        .archive-link a {
            background-color: #008CBA;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }
        .archive-link a:hover {
            background-color: #007B9E;
        }
    </style>
</head>
<body>
<div class="logout-container">
    <a href="logout.php">Logout</a>
</div>
<h1>Leads Tracker</h1>

<!-- Button/link to navigate to the Archived page -->
<div class="archive-link">
    <a href="archive.html">Go to Archived Submissions</a>
</div>

<!-- Container for submissions, formatted in rows of 4, responsive to 1 per row on mobile -->
<div id="submissions" class="submissions-container"></div>

<script>
    async function fetchSubmissions() {
        try {
            const response = await fetch('submissions.json?timestamp=' + new Date().getTime());
            const submissions = await response.json();

            // Check if submissions data is an array
            if (!Array.isArray(submissions)) {
                throw new Error('Invalid data format: Submissions should be an array.');
            }

            // Sort submissions by submissionDate, assuming ISO 8601 format (e.g., 'YYYY-MM-DDTHH:mm:ss')
            submissions.sort((a, b) => new Date(b.submissionDate) - new Date(a.submissionDate));

            displaySubmissions(submissions);
        } catch (error) {
            console.error('Error fetching submissions:', error);
        }
    }

    function displaySubmissions(submissions) {
        const submissionsContainer = document.getElementById('submissions');
        submissionsContainer.innerHTML = ''; // Clear previous submissions

        submissions.forEach((submission) => {
            if (!submission.name || !submission.email || !submission.message || !submission.phone || !submission.subject || !submission.submissionDate) {
                console.warn('Skipping submission due to missing fields:', submission);
                return;
            }

            const submissionDiv = document.createElement('div');
            submissionDiv.className = 'submission-card'; // Style class for aesthetic
            submissionDiv.id = `submission-${submission.id}`; // Set ID for removal

            // Properly escape and sanitize the message to handle line breaks and quotes
            const safeMessage = submission.message
                .replace(/\\/g, '\\\\') // Escape backslashes
                .replace(/'/g, "\\'") // Escape single quotes
                .replace(/\r?\n/g, '\\n'); // Handle newlines

            // Add submission details
            submissionDiv.innerHTML = `
                <div class="submission-details">
                    <p><strong>Name:</strong> ${submission.name}</p>
                    <p><strong>Email:</strong> ${submission.email}</p>
                    <p><strong>Phone:</strong> ${submission.phone}</p>
                    <p><strong>Device:</strong> ${submission.subject}</p>
                    <p><strong>Message:</strong> ${safeMessage}</p>
                    <p><strong>Submission Date:</strong> ${submission.submissionDate}</p>
                </div>
                <div class="action-container">
                    <input type="text" id="notes-${submission.id}" placeholder="Add notes..." />
                    <button onclick="archiveSubmission(
                        ${submission.id}, 
                        '${submission.name.replace(/'/g, "\\'")}', 
                        '${submission.email.replace(/'/g, "\\'")}', 
                        '${submission.phone.replace(/'/g, "\\'")}', 
                        '${submission.subject.replace(/'/g, "\\'")}', 
                        '${safeMessage}', 
                        '${submission.submissionDate.replace(/'/g, "\\'")}'
                    )">Contacted</button>
                </div>
            `;

            // Prepend new submissions to show the latest ones at the top
            submissionsContainer.prepend(submissionDiv);
        });
    }

    async function archiveSubmission(id, name, email, phone, subject, message, submissionDate) {
        const notes = document.getElementById(`notes-${id}`).value; // Get notes input value

        // Create an archived submission object
        const archivedSubmission = {
            id,
            name,
            email,
            phone,
            subject,
            message,
            submissionDate,
            notes, // Include notes here
        };

        try {
            // Fetch existing archived submissions
            const archiveResponse = await fetch('archived.json');
            const archivedSubmissions = await archiveResponse.json();

            // Add the new archived submission to the archived list
            archivedSubmissions.push(archivedSubmission);

            // Save updated archived submissions back to the server
            await fetch('save_archived_submissions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(archivedSubmissions), // Save updated archived list
            });

            // Fetch current submissions
            const submissionsResponse = await fetch('submissions.json');
            const submissions = await submissionsResponse.json();

            // Filter out the archived submission from the main submissions list
            const updatedSubmissions = submissions.filter(submission => submission.id !== id);

            // Save updated submissions back to the server
            await fetch('save_submissions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(updatedSubmissions), // Save the updated submissions (without the archived one)
            });

            // Remove the archived submission from the display
            document.getElementById(`submission-${id}`).remove();

            alert('Submission archived successfully!');
        } catch (error) {
            console.error('Error archiving submission:', error);
        }
    }

    // Fetch submissions when the page loads
    fetchSubmissions();

    // Poll for new submissions every 2 minutes (120000 ms)
    setInterval(fetchSubmissions, 120000);
</script>
</body>
</html>
