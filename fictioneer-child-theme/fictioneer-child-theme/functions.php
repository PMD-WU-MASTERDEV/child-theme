<?php

// =============================================================================
// CONSTANTS
// =============================================================================

//define( 'FICTIONEER_SHORTCODE_TRANSIENT_EXPIRATION', -1 );
define( 'CHILD_VERSION', '1.0.0' );
define( 'CHILD_NAME', 'Fictioneer Child Theme' );
define( 'FICTIONEER_SHORTCODE_TRANSIENT_EXPIRATION', 0 );

// =============================================================================
// CHILD THEME SETUP
// =============================================================================

/**
 * Enqueue child theme styles and scripts
 */

function fictioneer_child_enqueue_styles_and_scripts() {
  $parenthandle = 'fictioneer-application';

  // Enqueue styles
  wp_enqueue_style(
    'fictioneer-child-style',
    get_stylesheet_directory_uri() . '/css/fictioneer-child-style.css',
    array( $parenthandle )
  );

  // Register scripts
  wp_register_script(
    'child-script-handle',
    get_stylesheet_directory_uri() . '/js/fictioneer-child-script.js',
    ['fictioneer-application-scripts'],
    false,
    true
  );

  // Enqueue scripts
  wp_enqueue_script( 'fictioneer-child-scripts' );
}
add_action( 'wp_enqueue_scripts', 'fictioneer_child_enqueue_styles_and_scripts', 99 );

//New code added by Frenzy
function child_customize_parent() {
  remove_action( 'fictioneer_chapter_after_content', 'fictioneer_chapter_support_links', 20 );
  unregister_post_type( 'fcn_recommendation' );
  remove_action( 'wp_head', 'fictioneer_add_fiction_css', 10 );
}

/**
 * Remove ACF groups and fields
 */

function child_remove_acf_items( $fields ) {
  // Skip for admins
  if ( current_user_can( 'administrator' ) ) {
    return $fields;
  }

  // Group: support links
  foreach ( $fields as $key => &$field ) {
    if ( $field['parent'] == 'group_62b2da501468a' ) {
      unset( $fields[$key] );
    }
  }

  // Fields: top web fiction, sticky in lists, custom story css, custom page css, custom ePUB css
  $field_keys = ['field_62b499da863c2', 'field_619a91f85da9d', 'field_636d81d34cab1', 'field_621b5610818d2', 'field_60edba4ff33f8'];

  foreach ( $fields as $key => &$field ) {
    if ( in_array( $field['key'], $field_keys ) ) {
      unset( $fields[$key] );
    }
  }

  // Return modified fields array
  return $fields;
}
add_filter( 'acf/pre_render_fields', 'child_remove_acf_items', 9999 );

/**
 * Add custom CSS to admin panel
 */

function child_custom_admin_styles() {
  // Skip for admins
  if ( current_user_can( 'administrator' ) ) {
    return;
  }

  // Hide items via CSS
  echo '<style>#acf-group_62b2da501468a,.user-support-message-wrap,.user-support-links-wrap,.user-url-wrap{display: none !important;}</style>';
}
add_action( 'admin_head', 'child_custom_admin_styles' );


function custom_excerpt_length($length) {
    return 55; // Set the desired excerpt length in words
}

function custom_excerpt_more($more) {
    return '...';
}

function child_hide_fandoms() {
  echo '<style>.wp-submenu li a[href*="taxonomy=fcn_fandom"],.components-panel__body:has([aria-label="Fandoms"]){display: none !important;}</style>';
}
add_action( 'admin_head', 'child_hide_fandoms' );


function child_admin_screen_tabula_rasa() {
  // Setup
  $screen = get_current_screen();
  $base = $screen->id;
  $admin_menus = ['tools', 'export', 'import', 'site-health', 'export-personal-data', 'erase-personal-data', 'themes', 'customize', 'nav-menus', 'theme-editor', 'users', 'user-new', 'options-general'];

  // Administration
  if ( ! current_user_can( 'manage_options' ) && in_array( $base, $admin_menus ) ) {
    wp_die( __( 'Access denied.', 'fictioneer' ) );
  }

  // Comments
  if ( ! current_user_can( 'moderate_comments' ) && in_array( $base, ['edit-comments', 'comment'] ) ) {
    wp_die( __( 'Access denied.', 'fictioneer' ) );
  }
}

function child_admin_menu_tabula_rasa() {
  // Administration
  if ( ! current_user_can( 'manage_options' ) ) {
    remove_menu_page( 'index.php' );
    remove_menu_page( 'tools.php' );
    remove_menu_page( 'plugins.php' );
    remove_menu_page( 'themes.php' );
  }

  // Comments
  if ( ! current_user_can( 'moderate_comments' ) ) {
    remove_menu_page( 'edit-comments.php' );
  }
}

function child_admin_dashboard_tabula_rasa() {
  global $wp_meta_boxes;

  // Administration
  if ( ! current_user_can( 'manage_options' ) ) {
    $wp_meta_boxes['dashboard']['normal']['core'] = [];
    $wp_meta_boxes['dashboard']['side']['core'] = [];

    remove_action( 'welcome_panel', 'wp_welcome_panel' );
  }
}

function child_admin_bar_tabula_rasa() {
  global $wp_admin_bar;

  // Remove comments
  if ( ! current_user_can( 'moderate_comments' ) ) {
    $wp_admin_bar->remove_node( 'comments' );
  }
}


function child_admin_upload_media_type_tabula_rasa( $file ) {
  // Setup
  $filetype = wp_check_filetype( $file['name'] );
  $mime_type = $filetype['type'];

  // Limit upload file types for non-administrators
  if ( ! current_user_can( 'manage_options' ) ) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/avif', 'image/gif', 'application/pdf', 'image/svg+xml'];

    if ( ! in_array( $mime_type, $allowed_types ) ){
      $file['error'] = __( 'You are not allowed to upload files of this type.', 'fictioneer' );
    }
  }

  return $file;
}

add_action( 'admin_menu', 'child_admin_menu_tabula_rasa', 9999 );
add_action( 'current_screen', 'child_admin_screen_tabula_rasa', 9999 );
add_action( 'wp_dashboard_setup', 'child_admin_dashboard_tabula_rasa', 9999 );
add_action( 'admin_bar_menu', 'child_admin_bar_tabula_rasa', 9999 );
add_filter( 'wp_handle_upload_prefilter', 'child_admin_upload_media_type_tabula_rasa', 9999 );


/*function child_redis_me_this( $post_id ) {
  // Do not when...
  if (
    ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ||
    wp_is_post_autosave( $post_id ) ||
    wp_is_post_revision( $post_id ) ||
    in_array( get_post_status( $post_id ), ['auto-draft'] )
  ) {
    return;
  }

  // Nuclear option
  wp_cache_flush();
}
add_action( 'save_post', 'child_redis_me_this', 9 );
add_action( 'untrash_post', 'child_redis_me_this', 9 );
add_action( 'trashed_post', 'child_redis_me_this', 9 );
add_action( 'delete_post', 'child_redis_me_this', 9 );*/

function child_admin_landing_page_tabula_rasa() {
  global $pagenow;

  if (
    $pagenow == 'index.php' &&
    ! current_user_can( 'manage_options' ) &&
    ! ( defined( 'DOING_AJAX' ) && DOING_AJAX )
  ) {
    // Skip dashboard, go to user profile
    wp_redirect( home_url( '/wp-admin/profile.php' ) );
    exit;
  }
}
add_action( 'admin_init', 'child_admin_landing_page_tabula_rasa' );

function child_add_identity_above_navigation( $args ) {
  ?>
  <div class="child-navigation-identity">
    <a href="<?php echo esc_url('https://pmdfanfiction.com'); ?>">
      <img src="<?php echo esc_url('https://pmdfanfiction.com/wp-content/uploads/2023/08/logo5.webp'); ?>" height="64" width="172" alt="Home page">
    </a>
  </div>
  <?php
}
add_action( 'fictioneer_navigation_top', 'child_add_identity_above_navigation' );

//Remove Litespeed Page Options for non-admin
function remove_ols_metabox() {
    if ( is_admin() && !current_user_can('administrator') ) {
        $args = array(
           'public' => true,
        );

        $post_types = get_post_types( $args ); 
        foreach ( $post_types  as $post_type ) {
            remove_meta_box( 'litespeed_meta_boxes', $post_type, 'side' );
        }
    }
}
add_action( 'add_meta_boxes', 'remove_ols_metabox', 999 );
?>