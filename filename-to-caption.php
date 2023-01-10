<?php

/**
 * Plugin Name:       Filename to Caption
 * Plugin URI:        https://nataliadrause.com/
 * Description:       Transforms filename of images into caption, title and description, and adds caption under images.
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
    add_filter( 'the_content', array( $this, 'filename_to_caption'));
    
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
      <p>This plugin will convert image filename to text that can be added to Caption, Title and Description of the media file.</p>
      <p>Additionally the caption can be displayed as <code>&lt;figcaption&gt;</code> element under images in the posts.</p>
      <p><strong>Recommended format for filenames:</strong></p>
      <ul>
        <li><code>this-is-my-caption.jpg</code></li>
        <li><code>this_is_also_my_caption.png</code></li>
        <li><code>and another caption.png</code></li>
      </ul>
      <br>
      <h2>Select an option:</h2>
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
    add_settings_section( 'fcp_first_section', null, null, 'filename-to-caption-settings' );

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
      return $this->add_meta($content);
    }
    return $content;
  }

  // 3.1. Add function we pass to the general functionality of the plugin.
  function add_meta($content) {
    global $post;
    $args = array(
    //'order'=>'ASC', 
    'post_type'=>'attachment', 
    'post_parent'=>$post->ID, 
    'post_mime_type'=>'image', 
    //'post_status'=>null,
    ); 
    $items = get_posts($args); // collect all the images within content post
    
    // 4. Add Caption meta data to every image.

    foreach ($items as $item) {
      // Get the filenames of all images in the post.
      $filename = basename ( get_attached_file( $item->ID ) );
      // Sanitize the title:  remove file extension:
      $my_image_caption = preg_replace('/\\.[^.\\s]{3,4}$/', '', $filename);
      // Sanitize the title:  remove hyphens, underscores & extra spaces:
      $my_image_caption = preg_replace( '%\s*[-_\s]+\s*%', ' ',  $my_image_caption );
      // Capitalize first letter in string:
      $my_image_caption = ucfirst( $my_image_caption );
      // Add the sanitized filename as caption meta:
      
      $meta = array(
        'ID' => $item->ID,
        'post_excerpt' => $my_image_caption,
        // 'post_title' => $my_image_caption,
        // 'post_content'  => $my_image_caption,
      );
      wp_update_post( $meta );

    }

    // 5. Add figcaption for every image.
    
    // Find all figure images in the content and return if there are none.
    if (!preg_match_all( '@<figure [^>]+><img[^<]*</figure>@', $content, $matches)) {
      return $content;
    }
    // For each match add figcaption.
    foreach ($matches[0] as $match) {
      // Find the image url in the tag.
      if (preg_match('@src="([0-9a-z:/._-]+)@i', $match, $src_list)) {
        $url = $src_list[1];
        
        if ($url) {
          $post_id = attachment_url_to_postid($url);
          if ($post_id) {
            $figcaption = wp_get_attachment_caption( $post_id );
            $new_html = str_replace('</figure>', '<figcaption class="wp-element-caption">' . $figcaption .'</figcaption>', $match);
            $content = str_replace($match, $new_html, $content);
          }
        }
      }
    }

    return $content;
  }
 }

 $filenameToCaptionPlugin = new FilenameToCaptionPlugin();