# Leads Tracker Application

A simple web application designed for tracking leads, built with PHP, HTML, JavaScript, and JSON for persistence. This application allows you to track new submissions, add notes, archive leads, and manage access through a login page.

## Features

- **Lead Submissions**: Displays new leads fetched from Getform API.
- **Archiving Functionality**: Archive contacted leads and add custom notes.
- **Login Authentication**: Simple login system to restrict access.
- **Mobile Responsive**: Works on both desktop and mobile devices.

## Installation and Setup

### Prerequisites

- A web server with PHP support (e.g., Apache, Nginx).
- Getform account and API token.
- Git installed for version control.

### Steps to Setup

1. **Clone the Repository**
   ```bash
   git clone https://github.com/pxlhierarchy/getform-leads-tracker
   ```
2. **Upload to Server**
   - Place the files in your server's public HTML directory.

3. **Update Getform URL**
   - Replace the placeholder API URL in `submissions.php` with your Getform API link.
   ```php
   $api_url = 'https://api.getform.io/v1/forms/your-form-id?token=your-api-token';
   ```

4. **Set Up Login Credentials**
   - Update the username and password in `authenticate.php` to your preferred credentials.
   ```php
   $valid_username = 'your_username';
   $valid_password = 'your_password';
   ```

5. **Set Permissions**
   - Ensure that the `submissions.json` and `archived.json` files have write permissions.
   ```bash
   chmod 664 submissions.json
   chmod 664 archived.json
   ```
   - Make sure the web server user owns these files.
   ```bash
   chown www-data:www-data submissions.json
   chown www-data:www-data archived.json
   ```

6. **Configure Cron Job**
   - Set up a cron job to run `submissions.php` periodically to fetch new leads.
   ```
   */2 * * * * /usr/bin/php /path/to/your/submissions.php
   ```

### File Structure

- **index.php**: Main page displaying all leads.
- **submissions.php**: Fetches leads from Getform and updates `submissions.json`.
- **authenticate.php**: Handles login authentication.
- **logout.php**: Logs out the user.
- **save_submissions.php**: Saves updated submissions after archiving.
- **save_archived_submissions.php**: Saves leads to `archived.json` after archiving.
- **submissions.json**: Stores current leads.
- **archived.json**: Stores archived leads.

## Usage

### Logging In

1. Navigate to the login page.
2. Enter the username and password you set up in `authenticate.php`.

### Viewing Leads

- The main page (`index.php`) displays all unarchived leads.
- Leads are sorted by submission date, with the latest appearing at the top.

### Archiving Leads

1. Add notes to a lead in the input field next to it.
2. Click the "Contacted" button to archive the lead.
3. Archived leads will be moved to `archive.html`.

### Viewing Archived Leads

- Click the "Go to Archived Submissions" button to navigate to the archived leads page.

## Troubleshooting

### Permissions Issues

- If you encounter permission denied errors when writing to `submissions.json` or `archived.json`, make sure the web server has write access to these files.
- Use `chown` and `chmod` commands as mentioned in the setup section.

### Cron Job Not Running

- Verify that the cron job is set for the correct user.
- Check the cron log (`/var/log/syslog`) to ensure it is running without errors.

### Common Errors

- **Cannot Read Property 'replace' of Undefined**: Ensure all fields from the JSON response are properly sanitized before usage.
- **Permission Denied**: Double-check file permissions for `submissions.json` and `archived.json`.

## License

This project is licensed under the MIT License.

