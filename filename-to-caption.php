<?php

/**
 * Plugin Name:       Filename to Caption
 * Plugin URI:        https://nataliadrause.com/
 * Description:       Transforms filename of images and converts them to image captions.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Natalia Drause
 * Author URI:        https://nataliadrause.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 * Text Domain:       filename-to-caption
 * Domain Path:       /languages
 */

 class FilenameToCaptionPlugin {
  function __construct() {

    // Add menu link to admin sidebar and settings page.
    add_action( 'admin_menu', array ( $this, 'admin_page' ) );
    
  }

  // METHODS

  // Add menu link to admin sidebar and settings page.
  function admin_page() {
    add_options_page('Filename to Caption Settings', 'Filename to Caption', 'manage_options', 'filename-to-caption-settings', array( $this, 'settings_html'));
  }

  function settings_html() {
    echo "Hello World!";
  }

 }

 $filenameToCaptionPlugin = new FilenameToCaptionPlugin();