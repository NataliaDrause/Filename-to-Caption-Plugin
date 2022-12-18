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

    // Add database options for settings.
    add_action( 'admin_init', array( $this, 'settings' ) );
    
  }

  // METHODS

  // Add menu link to admin sidebar and settings page.
  function admin_page() {
    add_options_page('Filename to Caption Settings', 'Filename to Caption', 'manage_options', 'filename-to-caption-settings', array( $this, 'settings_html'));
  }

  function settings_html() { ?>
    <div class="wrap">
      <h1>Filename to Caption Settings</h1>
      <p>Select an option.</p>
      <form action="options.php" action="POST">
        <?php 
          settings_fields('filename_to_caption_plugin');
          do_settings_sections('filename-to-caption-settings');
          submit_button();
        ?>
      </form>
    </div>
  <?php }

  // Add database options for settings.
  function settings() {

    // Add settings page section.
    add_settings_section( 'fcp_first_section', null, null, 'filename-to-caption-settings' );

    // Add Caption field.
    add_settings_field( 'fcp_caption', 'Caption', array( $this, 'checkbox_html' ), 'filename-to-caption-settings', 'fcp_first_section', array( 'theName' => 'fcp_caption') );
    register_setting('filename_to_caption_plugin', 'fcp_caption', array(
      'sanitize_callback' => 'sanitize_text_field', 
      'default' => '1',
    ));

  }

  // Checkbox HTML on Settings page.
  function checkbox_html($args) { ?>
    <input type="checkbox" name="<?php echo $args['theName']; ?>" value="1" <?php checked(get_option($args['theName']), '1') ?>>
  <?php }

 }

 $filenameToCaptionPlugin = new FilenameToCaptionPlugin();