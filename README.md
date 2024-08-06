# SCSS WordPress Compiler

SCSS WordPress Compiler is a WordPress plugin that allows you to compile and enqueue SCSS files with a single click from the admin toolbar. This plugin uses the SCSSPHP library to handle SCSS compilation and provides a simple way to manage your stylesheets in WordPress.

## Features

- **Compile SCSS**: Compile your SCSS files into CSS directly from the WordPress admin toolbar.
- **Enqueue CSS**: Automatically enqueue the compiled CSS in the WordPress frontend.
- **Minified Output**: Generates both regular and minified CSS files for optimal performance.
- **AJAX Handling**: Uses AJAX for seamless SCSS compilation without page reloads.

## Installation

1. **Download and Install**: Download the plugin and upload it to your WordPress site's `wp-content/plugins` directory.
2. **Activate the Plugin**: Go to the Plugins menu in WordPress and activate the SCSS WordPress Compiler plugin.

## Usage

1. **SCSS Directory**: Place your SCSS files in the `scss` directory inside the plugin folder. Ensure there is a `main.scss` file as the entry point.
2. **Compile SCSS**: Click the "Compile SCSS" button in the WordPress admin toolbar to compile your SCSS files.
3. **Enqueued CSS**: The compiled and minified CSS files will be automatically enqueued in your WordPress theme.

## File Structure

```
scss-wp-compiler/
│
├── libs/
│ └── scssphp/ # SCSSPHP library
│ └── scss.inc.php
│
├── output-css/ # Compiled CSS output
│ ├── style.css
│ └── style.min.css
│
├── scss/ # SCSS source files
│ └── main.scss
│
├── scss-wp-compiler.php # Main plugin file
└── scss-compilation.js # JavaScript for AJAX handling
```


## Code Overview

### Main Plugin File: `scss-wp-compiler.php`

- **Admin Toolbar Button**: Adds a "Compile SCSS" button to the WordPress admin toolbar.
- **AJAX Handler**: Handles AJAX requests to compile SCSS files.
- **Enqueue Styles**: Enqueues the compiled and minified CSS files in the WordPress frontend.
- **Enqueue Script**: Enqueues the JavaScript file that handles the AJAX request.

### JavaScript File: `scss-compilation.js`

- Handles the click event on the "Compile SCSS" button.
- Sends an AJAX request to the server to trigger SCSS compilation.
- Displays success or error messages based on the AJAX response.

## Security

- Nonce verification is used to secure the AJAX requests for SCSS compilation.

## Contribution

Feel free to contribute to the development of this plugin by submitting pull requests or opening issues on GitHub.

## Contribution

Feel free to contribute to the development of this plugin by submitting pull requests or opening issues on GitHub.

## Support

For any issues or support requests, please contact me at **mic.paolino@gmail.com.**

Thank you for using Swell Scales! We hope this plugin helps you achieve beautiful and responsive typography and color management for your WordPress site.
