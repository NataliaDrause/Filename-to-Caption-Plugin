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

    // 1. Add menu link to admin sidebar and settings page.
    add_action( 'admin_menu', array ( $this, 'admin_page' ) );

    // 2. Add database options for settings.
    add_action( 'admin_init', array( $this, 'settings' ) );

    // 3. Add the functionality of the plugin.
    add_filter('the_content', array( $this, 'filename_to_caption'));
    
  }

  // METHODS

  // 1. Add menu link to admin sidebar and settings page.
  function admin_page() {
    add_options_page('Filename to Caption Settings', 'Filename to Caption', 'manage_options', 'filename-to-caption-settings', array( $this, 'settings_html'));
  }

  // 1.2. Setting page HTML.
  function settings_html() { ?>
    <div class="wrap">
      <h1>Filename to Caption Settings</h1>
      <p>Select an option.</p>
      <form action="options.php" method="POST">
        <?php 
          settings_fields('filename_to_caption_plugin');
          do_settings_sections('filename-to-caption-settings');
          submit_button();
        ?>
      </form>
    </div>
  <?php }

  // 2. Add database options for settings.
  function settings() {

    // 2.3. Add settings page section.
    add_settings_section( 'fcp_first_section', 'Select what you want to add', null, 'filename-to-caption-settings' );

    // 2.2.2 Add Caption field: build html input field.
    add_settings_field( 'fcp_caption', 'Caption', array( $this, 'checkbox_html' ), 'filename-to-caption-settings', 'fcp_first_section', array( 'theName' => 'fcp_caption') );
    // 2.2.1 Add Caption field: register DB setting.
    register_setting('filename_to_caption_plugin', 'fcp_caption', array(
      'sanitize_callback' => 'sanitize_text_field', 
      'default' => '0',
    ));

  }

  // 2.4. Caption checkbox HTML on Settings page.
  function checkbox_html($args) { ?>
    <input type="checkbox" name="<?php echo $args['theName']; ?>" value="1" <?php checked(get_option($args['theName']), '1') ?>>
  <?php }

  // 3. Add the general functionality of the plugin.
  function filename_to_caption($content) {
    if (is_main_query() AND is_single() AND get_option('fcp_caption', '1')) {
      return $this->create_html($content);
    }
    return $content;
  }

  // 3.1. Add function we pass to the general functionality of the plugin.
  function create_html($content) {
    global $post;
    $args = array(
    'order'=>'ASC', 
    'post_type'=>'attachment', 
    'post_parent'=>$post->ID, 
    'post_mime_type'=>'image', 
    'post_status'=>null,
    ); 
    $items = get_posts($args); // collect all the images within content post
    
    foreach ($items as $item) {
      //get the filenames
      $filename = basename ( get_attached_file( $item->ID ) );
      // Sanitize the title:  remove file extension:
      $my_image_caption = preg_replace('/\\.[^.\\s]{3,4}$/', '', $filename);
      // Sanitize the title:  remove hyphens, underscores & extra spaces:
      $my_image_caption = preg_replace( '%\s*[-_\s]+\s*%', ' ',  $my_image_caption );
      $my_image_caption = ucwords( strtolower( $my_image_caption ) );
      //add filename as caption
      $data = array(
        'ID' => $item->ID,
        'post_excerpt' => $my_image_caption,
      );
      wp_update_post( $data );
      print_r($item);
    }
    return $content;
  }

 }

 $filenameToCaptionPlugin = new FilenameToCaptionPlugin();