# Post-Maintenance-Plugin
Efficiently manage and maintain your WordPress posts with the Post Maintenance Plugin. This plugin provides REST API endpoints to scan, update, and manage posts based on custom criteria such as post type, age, and engagement. Ensure your content is always fresh and relevant by automating routine maintenance tasks.

# Post Maintenance Plugin

The Post Maintenance Plugin for WordPress allows you to automate the management and maintenance of your posts. It provides REST API endpoints to scan posts, update metadata, send notifications, and perform other maintenance tasks based on custom criteria. Keep your content up-to-date and improve the overall quality of your website with this powerful tool.

## Features

- **Scan Posts**: Automatically scan posts by type, age, and engagement to apply maintenance rules.
- **Update Metadata**: Track the last scan date of each post with custom metadata.
- **Email Notifications**: Send notifications when certain criteria are met during a scan.
- **Dashboard Alerts**: Display maintenance notifications directly in the WordPress admin dashboard.

## Installation

Follow these steps to install the Post Maintenance Plugin on your WordPress site:

1. **Download the Plugin**: Clone this repository or download the ZIP file.

   ```bash
   git clone https://github.com/your-username/post-maintenance-plugin.git
Upload the Plugin:

Navigate to your WordPress dashboard.
Go to Plugins > Add New.
Click on Upload Plugin and select the ZIP file if you downloaded it.
Activate the Plugin:

After uploading, click on Activate Plugin to enable the Post Maintenance Plugin.
Configure the Plugin:

Once activated, you can start using the provided REST API endpoints to scan and maintain posts. No additional configuration is needed unless you want to customize the behavior.
Usage
REST API Endpoints
The plugin provides a REST API endpoint to trigger the post maintenance tasks:

Endpoint: /ltg/v1/maintenance/scan-posts
Method: POST
Parameters:
post_types (array): Specify the post types to scan (e.g., ['post', 'page']).
categories (array): Optionally filter posts by categories.
age_threshold (int): The minimum age (in days) of posts to include in the scan.
engagement_threshold (int): The minimum engagement level (e.g., comments, views) to include in the scan.
Example Request:
bash
Copy code
curl -X POST \
  'http://your-site.com/wp-json/ltg/v1/maintenance/scan-posts' \
  -d 'post_types[]=post' \
  -d 'age_threshold=30' \
  -d 'engagement_threshold=5'
Testing the Plugin
To ensure the Post Maintenance Plugin is working as expected, you can run unit tests provided with the plugin.

Prerequisites:
PHPUnit should be installed on your system.
Running Tests:
Navigate to the Plugin Directory:

bash
Copy code
cd wp-content/plugins/post-maintenance-plugin
Run PHPUnit:

bash
Copy code
./vendor/bin/phpunit
Filter Specific Tests:

To run a specific test method:

bash
Copy code
./vendor/bin/phpunit --filter test_scan_posts_updates_post_meta
Test Summary:
test_scan_posts_updates_post_meta: Verifies that the ltg_test_last_scan meta field is correctly updated after a scan.
test_scan_posts_no_posts: Ensures proper handling when no posts meet the criteria.
test_email_notification_sent: Checks if the email notification is sent when the criteria are met.
test_dashboard_notification_set: Verifies that a dashboard notification is set after the scan.
Contributing
We welcome contributions to enhance the functionality and usability of this plugin. Please fork the repository and submit pull requests for any improvements.

License
This plugin is open-source and distributed under the MIT License.


GitHub Repository Heading
Post Maintenance Plugin

Efficiently manage and maintain your WordPress posts with the Post Maintenance Plugin. This plugin provides REST API endpoints to scan, update, and manage posts based on custom criteria such as post type, age, and engagement. Ensure your content is always fresh and relevant by automating routine maintenance tasks.

README.md
markdown
Copy code
# Post Maintenance Plugin

The Post Maintenance Plugin for WordPress allows you to automate the management and maintenance of your posts. It provides REST API endpoints to scan posts, update metadata, send notifications, and perform other maintenance tasks based on custom criteria. Keep your content up-to-date and improve the overall quality of your website with this powerful tool.

## Features

- **Scan Posts**: Automatically scan posts by type, age, and engagement to apply maintenance rules.
- **Update Metadata**: Track the last scan date of each post with custom metadata.
- **Email Notifications**: Send notifications when certain criteria are met during a scan.
- **Dashboard Alerts**: Display maintenance notifications directly in the WordPress admin dashboard.

## Installation

Follow these steps to install the Post Maintenance Plugin on your WordPress site:

1. **Download the Plugin**: Clone this repository or download the ZIP file.

   ```bash
   git clone https://github.com/your-username/post-maintenance-plugin.git
Upload the Plugin:

Navigate to your WordPress dashboard.
Go to Plugins > Add New.
Click on Upload Plugin and select the ZIP file if you downloaded it.
Activate the Plugin:

After uploading, click on Activate Plugin to enable the Post Maintenance Plugin.
Configure the Plugin:

Once activated, you can start using the provided REST API endpoints to scan and maintain posts. No additional configuration is needed unless you want to customize the behavior.
Usage
REST API Endpoints
The plugin provides a REST API endpoint to trigger the post maintenance tasks:

Endpoint: /ltg/v1/maintenance/scan-posts
Method: POST
Parameters:
post_types (array): Specify the post types to scan (e.g., ['post', 'page']).
categories (array): Optionally filter posts by categories.
age_threshold (int): The minimum age (in days) of posts to include in the scan.
engagement_threshold (int): The minimum engagement level (e.g., comments, views) to include in the scan.
Example Request:
bash
Copy code
curl -X POST \
  'http://your-site.com/wp-json/ltg/v1/maintenance/scan-posts' \
  -d 'post_types[]=post' \
  -d 'age_threshold=30' \
  -d 'engagement_threshold=5'
Testing the Plugin
To ensure the Post Maintenance Plugin is working as expected, you can run unit tests provided with the plugin.

Prerequisites:
PHPUnit should be installed on your system.
Running Tests:
Navigate to the Plugin Directory:

bash
Copy code
cd wp-content/plugins/post-maintenance-plugin
Run PHPUnit:

bash
Copy code
./vendor/bin/phpunit
Filter Specific Tests:

To run a specific test method:

bash
Copy code
./vendor/bin/phpunit --filter test_scan_posts_updates_post_meta
Test Summary:
test_scan_posts_updates_post_meta: Verifies that the ltg_test_last_scan meta field is correctly updated after a scan.
test_scan_posts_no_posts: Ensures proper handling when no posts meet the criteria.
test_email_notification_sent: Checks if the email notification is sent when the criteria are met.
test_dashboard_notification_set: Verifies that a dashboard notification is set after the scan.
Contributing
We welcome contributions to enhance the functionality and usability of this plugin. Please fork the repository and submit pull requests for any improvements.

License
This plugin is open-source and distributed under the MIT License.

Support
For any questions or support, please open an issue on GitHub or contact us at labannjoroge8292@gmail.com
