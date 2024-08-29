# 🛠️ WordPress Post Maintenance Plugin #

The WordPress Post Maintenance Plugin is designed to help you manage and maintain your WordPress posts efficiently. With features like scheduled post scans, automatic cleanup of outdated content, and email notifications, this plugin ensures that your site remains optimized and up-to-date. It's perfect for site administrators who want to automate routine maintenance tasks.

Features
Automated Post Scanning: Schedule post scans to identify and handle outdated or underperforming content.
Customizable Post Maintenance: Set specific thresholds for post age, engagement, and more to tailor the maintenance process to your needs.
Email Notifications: Receive email alerts when certain maintenance actions are performed.
Dashboard Notifications: Stay informed with real-time notifications in the WordPress dashboard.
Easy Integration: Works seamlessly with the WordPress REST API for advanced customization.
Installation
From the WordPress Admin Dashboard
Download the Plugin:

Download the plugin's .zip file from the GitHub repository.
Upload the Plugin:

Navigate to Plugins > Add New in your WordPress dashboard.
Click Upload Plugin and choose the downloaded .zip file.
Click Install Now and then Activate the plugin.
Manually via FTP
Download the Plugin:

Download the plugin's .zip file from the GitHub repository.
Extract the Plugin:

Extract the .zip file on your local machine.
Upload to WordPress:

Use an FTP client to upload the extracted plugin folder to your WordPress installation under the wp-content/plugins/ directory.
Activate the Plugin:

Go to the Plugins page in your WordPress admin dashboard and activate the plugin.
Usage
1. Configure Post Maintenance
After activating the plugin, navigate to the Post Maintenance settings page in your WordPress admin dashboard.
Configure your desired post maintenance settings, including post types, age thresholds, engagement thresholds, and email notifications.
2. Manually Run Maintenance Tasks
You can manually trigger post maintenance tasks by navigating to the Post Maintenance settings page and clicking the Run Maintenance button.
3. Setting Up a Cron Job
To automate the maintenance process, you can set up a cron job to trigger the maintenance task at regular intervals.

Cron Job Command
Add the following cron job to your server to run the post maintenance every day at midnight:

bash
Copy code
0 0 * * * wget -q -O - "http://yourwebsite.com/wp-json/ltg/v1/maintenance/scan-posts" >/dev/null 2>&1
Replace http://yourwebsite.com with your actual website URL.

Running Tests
Prerequisites
Ensure that PHPUnit is installed and properly configured in your development environment.

Running Unit Tests
The plugin includes unit tests to ensure that all features work as expected. You can run these tests using the following command:

bash
Copy code
./vendor/bin/phpunit --filter test_scan_posts_updates_post_meta
This command runs the specific test for verifying that the post maintenance feature updates the ltg_test_last_scan post meta correctly. You can run other tests by specifying the appropriate filter.

Running All Tests
To run all the tests in the plugin:

bash
Copy code
./vendor/bin/phpunit
Troubleshooting Tests
If you encounter issues with tests, try running them individually to isolate problems, as described above. Make sure your test environment closely mirrors your production environment.

Contributing
We welcome contributions! Please feel free to submit issues, fork the repository, and send pull requests.

Support
For any questions or support, please open an issue on GitHub or contact us at labannjoroge8292@gmail.com.

