<?php
/**
  * Plugin Name: Picasso Pro
  * Plugin URI: http://www.simonwbloom.com/
  * Description: This plugin adds custom functionality to the Picasso Pro website.
  * Version: 1.0.0
  * Author: Simon Bloom
  * Author URI: http://www.simonwbloom.com/
  * License: GPL2
  */



/**
 * Enqueue Font Awesome
 * @since Picasso Pro 1.0
 */
function picassopro_fontawesome(){
  wp_enqueue_style('font-awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css'); 
}
add_action('wp_enqueue_scripts','picassopro_fontawesome');

/**
 * Typekit Fonts
 * @since Picasso Pro 1.0
 */
function picassopro_typekit() {
  wp_enqueue_script('picassopro_typekit', '//use.typekit.net/ywe0hcg.js', array(), '1.0.0');
}

add_action( 'wp_enqueue_scripts', 'picassopro_typekit' );

add_action( 'wp_head', 'picassopro_typekit_inline' );

function picassopro_typekit_inline() {
  if ( wp_script_is( 'picassopro_typekit', 'enqueued') ) { ?>
    <script type="text/javascript">try{Typekit.load({});}catch(e){}</script>
  </script>
<?php }
}

/**
 * Allow SVG uploads in the media library
 * @since Picasso Pro 1.0
 */
function cc_mime_types($mimes) {
  $mimes['svg'] = 'image/svg+xml';
  return $mimes;
}
add_filter('upload_mimes', 'cc_mime_types');

/**
  * Modifications to the theme customizer for PP specific theme styles
  * @since Picasso Pro 1.0
  */
function picassopro_theme_customizer( $wp_customize ) {
  
  //
  // Social Media
  //

  $wp_customize->add_setting( 'picassopro_twitter_handle');
  $wp_customize->add_setting( 'picassopro_facebook_url');
  $wp_customize->add_setting( 'picassopro_vimeo_url');

  $wp_customize->add_section( 'picassopro_social_media', array(
    'title'    => __('Social Media', 'picassopro'), 
  ) );
  $wp_customize->add_control( 'twitter_handle', array(
    'label'    => __('Twitter Handle', 'picassopro'),
    'section'  => 'picassopro_social_media',
    'settings' => 'picassopro_twitter_handle', 
    'type'     => 'text',
  ) );
  $wp_customize->add_control( 'facebook_url', array(
    'label'    => __('Facebook URL', 'picassopro'),
    'section'  => 'picassopro_social_media',
    'settings' => 'picassopro_facebook_url', 
    'type'     => 'text',
  ) );
  $wp_customize->add_control( 'vimeo_url', array(
    'label'    => __('Vimeo URL', 'picassopro'),
    'section'  => 'picassopro_social_media',
    'settings' => 'picassopro_vimeo_url', 
    'type'     => 'text',
  ) );

  // Header Settings
  $wp_customize->add_setting( 'picassopro_searchbar');
  $wp_customize->add_control( new WP_Customize_Control( $wp_customize, 
    'picassopro_searchbar', array(
    'label'    => __( 'Show Search Bar in Header', 'picassopro' ),
    'section'  => 'title_tagline',
    'description' => 'Display the searchbar in the header.', 
    'settings' => 'picassopro_searchbar',
    'type' => 'checkbox',
    'priority' => 80,
  ) ) );

  $wp_customize->add_setting( 'picassopro_logo');
  $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'themeslug_logo', array(
    'label'    => __( 'Logo', 'picassopro' ),
    'section'  => 'title_tagline',
    'description' => 'Upload a logo that will appear in the site header. In order to fit properly, the logo will be resized to 150 pixels wide.', 
    'priority' => 20,
    'settings' => 'picassopro_logo',
  ) ) );
}
add_action( 'customize_register', 'picassopro_theme_customizer' );

/**
  * Shortcodes
  * @since Picasso Pro 1.0
  */
function quote_shortcode( $atts, $content = null ) {
  return '<div class="quote">' . $content . '</div>';
}
add_shortcode( 'quote', 'quote_shortcode' );

function link_shortcode( $atts, $content = null ) {
  $a = shortcode_atts( array(
    'href' => 'link',
  ), $atts );

  return '<a href="' . esc_attr($a['href']) . '" class="permalink">' . $content . '</a>';
}
add_shortcode( 'link', 'link_shortcode' );

/**
  * Remove 'posts' from menu
  * @since Picasso Pro 1.0
  */
function remove_menus(){
  remove_menu_page( 'edit.php' );
}
add_action( 'admin_menu', 'remove_menus' );

/**
  * Give editors permission to edit site appearance
  * @since Picasso Pro 1.0
  */
// get the the role object
$role_object = get_role('editor');

// add $cap capability to this role object
$role_object->add_cap( 'edit_theme_options' );

add_filter( 'comment_form_default_fields', 'pp_comment_placeholders' );

/**
 * Change default fields, add placeholder and change type attributes.
 *
 * @param  array $fields
 * @return array
 */
function pp_comment_placeholders( $fields )
{
    $fields['author'] = str_replace(
        '<input',
        '<input placeholder="'
        /* Replace 'theme_text_domain' with your theme’s text domain.
         * I use _x() here to make your translators life easier. :)
         * See http://codex.wordpress.org/Function_Reference/_x
         */
            . _x(
                'Name *',
                'comment form placeholder',
                'pp'
                )
            . '"',
        $fields['author']
    );
    $fields['email'] = str_replace(
        '<input id="email" name="email" type="email"',
        /* We use a proper type attribute to make use of the browser’s
         * validation, and to get the matching keyboard on smartphones.
         */
        '<input type="email" placeholder="Email *"  id="email" name="email"',
        $fields['email']
    );
    $fields['url'] = str_replace(
        '<input id="url" name="url" type="url"',
        // Again: a better 'type' attribute value.
        '<input placeholder="Website" id="url" name="url" type="url"',
        $fields['url']
    );

    return $fields;
}
function wpb_move_comment_field_to_bottom_add_placeholder( $fields ) {
$comment_field = $fields['comment'];
$comment_field = str_replace(
  '<textarea id="comment"',
  '<textarea placeholder="Comment" id="comment"',
  $fields['comment']
);
unset( $fields['comment'] );
$fields['comment'] = $comment_field; 

return $fields;
}

add_filter( 'comment_form_fields', 'wpb_move_comment_field_to_bottom_add_placeholder' );
