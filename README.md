# WPMUDEV Test Plugin #

This is a plugin that can be used for testing coding skills for WordPress and PHP.

# Development

## Composer
Install composer packages
`composer install`

## Build Tasks (npm)
Everything should be handled by npm.

Install npm packages
`npm install`

| Command              | Action                                                |
|----------------------|-------------------------------------------------------|
| `npm run watch`      | Compiles and watch for changes.                       |
| `npm run compile`    | Compile production ready assets.                      |
| `npm run build`  | Build production ready bundle inside `/build/` folder |

## WP-CLI Command for Terminal
Scan Posts Command

For system administrators, a WP-CLI command is included to execute the Scan Posts action directly from the terminal.

## Usage
To scan all published posts and pages, run the following command in the terminal:
`wp wp-posts-maintenance scan_posts`

## What It Does
Scans all published posts and pages.

Updates the wpmudev_test_last_scan meta field with the current timestamp for each post and page.

Outputs a success message upon completion.


