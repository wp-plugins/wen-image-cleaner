<?php
/**
 * Plugin Name: WEN Image Cleaner
 * Plugin URI: https://wordpress.org/plugins/wen-image-cleaner/
 * Description: A must required tool for WordPress to clean images which is ideal for blogs that do not require hi-resolution original images to be stored and/or the contributors don't want (or understand how) to scale images before uploading.
 * Version: 1.0.0
 * Author: WEN Themes
 * Author URI: http://wenthemes.com
 * Requires at least: 3.8
 * Tested up to: 4.1
 * License: GPLv3
 * Text Domain: wen-image-cleaner
 * Domain Path: /languages
 */


//  Define Constants
define( 'WEN_IMAGE_CLEANER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WEN_IMAGE_CLEANER_PLUGIN_URI', plugin_dir_url( __FILE__ ) );

//  Define Other Dirs & Uris
define( 'WEN_IMAGE_CLEANER_VIEWS_DIR', WEN_IMAGE_CLEANER_PLUGIN_DIR . 'views/' );
define( 'WEN_IMAGE_CLEANER_ASSETS_URI', WEN_IMAGE_CLEANER_PLUGIN_URI . 'assets/' );
define( 'WEN_IMAGE_CLEANER_ASSET_JS_URI', WEN_IMAGE_CLEANER_ASSETS_URI . 'js/' );
define( 'WEN_IMAGE_CLEANER_ASSET_CSS_URI', WEN_IMAGE_CLEANER_ASSETS_URI . 'css/' );
define( 'WEN_IMAGE_CLEANER_INC_DIR', WEN_IMAGE_CLEANER_PLUGIN_DIR . 'inc/' );

//  Define Cache Time
define( 'WEN_IMAGE_CLEANER_CACHE_TIME', 60 * 60 * 4 );

//  Load WEN Addons
//require_once WEN_IMAGE_CLEANER_PLUGIN_DIR . 'wen_addons.php';

//  Require Helper File
require_once WEN_IMAGE_CLEANER_INC_DIR . 'plugin_helper.php';


//  Add Activation Action
register_activation_hook( __FILE__, 'wen_image_cleaner_activated' );

//  Callback
function wen_image_cleaner_activated() {

    //  Check
    if( !get_option('wen_image_cleaner_options') ) {

        //  Save
        add_option('wen_image_cleaner_options', array(
            'landscape_dimension' => '_auto_',
            'portrait_dimension' => '_auto_',
            'clear_larger' => 'yes',
            'strict_mode' => 'no',
            'clear_settings' => 'no'
        ));
    }
}


//  Add Deactivation Action
register_deactivation_hook( __FILE__, 'wen_image_cleaner_deactivated' );

//  Callback
function wen_image_cleaner_deactivated() {

    //  Check
    if( wen_image_cleaner_get_option('clear_settings') == 'yes' ) {

        //  Save
        delete_option('wen_image_cleaner_options');
    }
}


//  Add Action to Plugin Loaded
add_action( 'plugins_loaded', 'wen_image_cleaner_load_textdomain' );

//  Callback
function wen_image_cleaner_load_textdomain() {

    //  Load Language Files
    load_plugin_textdomain( 'wen-image-cleaner', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}


//  Add Filter to Plugin Action Links
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'wen_image_cleaner_add_plugin_links' );

//  Callback
function wen_image_cleaner_add_plugin_links( $links ) {

    //  Link
    $settings_link = '<a href="tools.php?page=wen-image-cleaner&req=settings">Settings</a>';

    //  Add the Link
    array_unshift($links, $settings_link);

    //  Return
    return $links;
}


//  Add Action to Add Admin Menu
add_action( 'admin_menu', 'wen_image_cleaner_admin_menu' );

//  Callback
function wen_image_cleaner_admin_menu() {

    //  Check
    if( function_exists('wen_register_plugin') ) {

        //  Add Plugin Settings Page
        add_submenu_page( 'wen-addons', __('WEN Image Cleaner', 'wen-image-cleaner'), __('Image Cleaner', 'wen-image-cleaner'), 'manage_options', 'wen-image-cleaner', 'wen_image_cleaner_settings_page_render' );
    } else {

        //  Add Plugin Settings Page
        add_management_page( __('WEN Image Cleaner', 'wen-image-cleaner'), __('Image Cleaner', 'wen-image-cleaner'), 'manage_options', 'wen-image-cleaner', 'wen_image_cleaner_settings_page_render' );
    }
}

//  Check
if( !function_exists('wen_addons_page_render') ) {

    //  Callback to Render WEN Addons
    function wen_addons_page_render() {

        //  Include Addons File
        require_once WEN_IMAGE_CLEANER_VIEWS_DIR . 'landing_page.php';
    }
}

//  Callback to Render Settings Page
function wen_image_cleaner_settings_page_render() {

    //  Include Settings File
    require_once WEN_IMAGE_CLEANER_VIEWS_DIR . 'settings.php';
}

//  Listen Admin Init
add_action( 'admin_init', 'wen_image_cleaner_save_settings' );

//  Callback
function wen_image_cleaner_save_settings() {

    //  Global
    global $pagenow;

    //  Load Settings
    wen_image_cleaner_get_option('');

    //  Register the Plugin
    if( function_exists('wen_register_plugin') )    wen_register_plugin( __FILE__ );

    //  Check for Page
    if( $pagenow == 'tools.php' && isset($_GET['page']) && $_GET['page'] == 'wen-image-cleaner' ) {

        //  Check Settings Posted
        if( sizeof( $_POST ) > 0 && isset( $_POST['option_page'] )
            && $_POST['option_page'] == 'wen-image-cleaner' ) {

            //  Prepare Data
            $settingsData = array(
                'landscape_dimension' => filter_input(INPUT_POST, 'landscape_dimension'),
                'portrait_dimension' => filter_input(INPUT_POST, 'portrait_dimension'),
                'clear_larger' => filter_input(INPUT_POST, 'clear_larger'),
                'strict_mode' => filter_input(INPUT_POST, 'strict_mode'),
                'clear_settings' => filter_input(INPUT_POST, 'clear_settings')
            );

            //  Check
            if(!$settingsData['landscape_dimension'])
                $settingsData['landscape_dimension'] = $settingsData['portrait_dimension'];
            if(!$settingsData['portrait_dimension'])
                $settingsData['portrait_dimension'] = $settingsData['landscape_dimension'];

            //  Save
            update_option( 'wen_image_cleaner_options', $settingsData );

            //  Global
            global $wen_image_cleaner_options;

            //  Set
            $wen_image_cleaner_options = $settingsData;

            //  Redirect
            wp_redirect( admin_url( 'tools.php?page=wen-image-cleaner&req=' . (isset($_GET['req']) ? $_GET['req'] : '') . '&updated=true' ) );
        }

        //  Check for Run Cleaner Page
        if( isset( $_GET['req'] ) && $_GET['req'] == 'cleaner' ) {

            //  Enqueue Styles
            //wp_enqueue_style('thickbox');
            wp_enqueue_style('wen-image-cleaner', WEN_IMAGE_CLEANER_ASSET_CSS_URI . 'wen-image-cleaner.css');

            //  Enqueue Scripts
            //wp_enqueue_script('thickbox');
            wp_enqueue_script('jquery-ui-progressbar');
            wp_enqueue_script('wen-image-cleaner', WEN_IMAGE_CLEANER_ASSET_JS_URI . 'wen-image-cleaner.js', array('jquery'));

            //  Localize
            wp_localize_script('wen-image-cleaner', 'WEN_IMAGE_CLEANER_INFO', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce(),
                'i18n' => array(
                    'confirm_remove_leftover_images' => __( 'Are you sure to delete the leftover images?', 'wen-image-cleaner' ),
                    'confirm_remove_unused_attachments' => __( 'Are you sure to delete the unused attachments?', 'wen-image-cleaner' ),
                    'confirm_remove_both' => __( 'Are you sure to delete the leftover images and unused attachments?', 'wen-image-cleaner' ),
                    'processing' => __( 'Processing', 'wen-image-cleaner' ),
                    'deleted_x_attachments' => __( 'Deleted :deleted attachment(s)', 'wen-image-cleaner' ),
                    'failed_x_attachments' => __( 'and Failed for :failed attachment(s)', 'wen-image-cleaner' ),
                    'saved_storage_space' => __( 'Saved Storage Space: ', 'wen-image-cleaner' ),
                    'deleted_x_files' => __( 'Deleted :deleted file(s)', 'wen-image-cleaner' ),
                    'failed_x_files' => __( 'and Failed for :failed file(s)', 'wen-image-cleaner' )
                )
            ));
        }
    }

    //  Check
    if( $pagenow == 'upload.php' ) {

        //  Enqueue Scripts
        wp_enqueue_script('wen-image-cleaner-upload', WEN_IMAGE_CLEANER_ASSET_JS_URI . 'wen-image-cleaner-upload.js', array('jquery'));

        //  Localize
        wp_localize_script('wen-image-cleaner-upload', 'WEN_IMAGE_CLEANER_INFO', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce(),
            'i18n' => array(
                'confirm_refresh_thumbnails' => __( 'Are you sure to refresh the thumbnails for this image?', 'wen-image-cleaner' ),
                'processing' => __( 'Processing', 'wen-image-cleaner' )
            )
        ));
    }
}


//  Filter the Upload
add_filter( 'wp_handle_upload_prefilter', 'wen_image_cleaner_wphandle_upload_prefilter', 50, 5 );

//  Callback
function wen_image_cleaner_wphandle_upload_prefilter( $file ) {

    //  Check
    if( wen_image_cleaner_get_option('strict_mode') == 'yes' ) {

        //  Get File Type Info
        $info = wp_check_filetype( $file['name'] );

        //  Check for Image
        if( substr( $info['type'], 0, 6 ) == 'image/' ) {

            //  Error
            $errorResponse = array(
                'name' => $file['name'],
                'error' => null
            );

            //  Get Image Info
            $imageInfo = getimagesize( $file['tmp_name'] );

            //  Check for Image Info
            if( $imageInfo ) {

                //  Get Max Dimension
                $registeredMax = wen_image_cleaner_get_comparitive_dimension( wen_image_cleaner_is_landscape_dimension( $imageInfo[0], $imageInfo[1] ) );

                //  Validate Min. Width & Height
                if( $imageInfo[0] < $registeredMax['width'] && $imageInfo[1] < $registeredMax['height'] ) {

                    //  Set Error
                    $errorResponse['error'] = 'Image should be min. of dimension ' . $registeredMax['width'] . 'x' . $registeredMax['height'];
                }
            } else {

                //  Set Error
                $errorResponse['error'] = 'Invalid Image file uploaded.';
            }

            //  Check for Error
            if( $errorResponse['error'] ) {

                //  Return Error
                return $errorResponse;
            }
        }
    }

    //  Return File
    return $file;
}


//  Filter the Attachment Metadata
add_filter( 'wp_generate_attachment_metadata', 'wen_image_cleaner_wpgenerate_attachment_metadata', 10, 2 );

//  Callback
function wen_image_cleaner_wpgenerate_attachment_metadata( $metadata, $attachment_id ) {

    //  Check
    if( wen_image_cleaner_get_option('clear_larger') == 'yes' ) {

        //  Clear Image Flag
        $clearMain = true;

        //  Max. Width & Height with Info
        $max_info = null;

        //  Check the Orientation
        $isLandscape = wen_image_cleaner_is_landscape_dimension( $metadata['width'], $metadata['height'] );

        //  Main Dimension
        $registeredMax = wen_image_cleaner_get_comparitive_dimension( $isLandscape );

        //  Check for Sizes Available
        if( sizeof( $metadata['sizes'] ) > 0 ) {

            //  Check
            if( $registeredMax ) {

                //  Check the Size Exists
                if( isset( $metadata['sizes'][$registeredMax['name']] ) ) {

                    //  Set
                    $max_info = $metadata['sizes'][$registeredMax['name']];
                }
            }
        }

        //  Validate
        if( $max_info && $metadata['width'] < $registeredMax['width']
            && $metadata['height'] < $registeredMax['height'] ) {

            //  Clear Info
            $max_info = null;
        }

        //  Check
        if( !$max_info ) {

            //  Set no Clear
            $clearMain = false;
        }

        //  Clear Main
        $clearMain = apply_filters( 'wen_clear_main_image', $clearMain, $registeredMax, $max_info, $metadata );

        //  Check for Clear
        if( $clearMain ) {

            //  Upload Dir
            $upload_dir = wp_upload_dir();

            //  Main Path
            $mainImagePath = $upload_dir['basedir'] . '/' . $metadata['file'];

            //  Source Path
            $sourceImagePath = trim( pathinfo($mainImagePath, PATHINFO_DIRNAME) ) . '/' . $max_info['file'];

            //  Check Function Exists
            if( function_exists('copy') && file_exists($sourceImagePath) ) {

                //  Unlink the Main Image
                @unlink( $mainImagePath );

                //  Copy the Bigger Image as Main Image
                //@copy( trim( $upload_dir['path'] ) . '/' . $max_info['file'], $mainImagePath );
                @copy( $sourceImagePath, $mainImagePath );

                //  Change Mode
                @chmod( $mainImagePath, 0777 );

                //  Get Image Info
                $imageInfo = getimagesize( $mainImagePath );

                // Updating meta data width and height
                $metadata['width'] = $imageInfo[0];
                $metadata['height'] = $imageInfo[1];
            }
        }
    }

    //  Return
    return $metadata;
}


//  Add Filter to Portrait Dimension
add_filter( 'wen_image_cleaner_get_portrait_dimension', 'wen_image_cleaner_get_portrait_dimension_update' );

//  Callback
function wen_image_cleaner_get_portrait_dimension_update( $dimension ) {

    //  Get
    if( ( $newSize = wen_image_cleaner_get_image_size( wen_image_cleaner_get_option('portrait_dimension') ) ) != null ) {

        //  Change
        $dimension = $newSize;
    }

    //  Return
    return $dimension;
}


//  Add Filter to Landscape Dimension
add_filter( 'wen_image_cleaner_get_landscape_dimension', 'wen_image_cleaner_get_landscape_dimension_update' );

//  Callback
function wen_image_cleaner_get_landscape_dimension_update( $dimension ) {

    //  Get
    if( ( $newSize = wen_image_cleaner_get_image_size( wen_image_cleaner_get_option('landscape_dimension') ) ) != null ) {

        //  Change
        $dimension = $newSize;
    }

    //  Return
    return $dimension;
}


//  Add Ajax Filter
add_filter( 'wp_ajax_wen_image_cleaner_delete_image', 'wen_image_cleaner_delete_image_callback' );

//  Callback
function wen_image_cleaner_delete_image_callback() {

    //  Response
    $response = array('success' => true, 'filesize' => 0);

    //  Check
    if( current_user_can('manage_options') ) {

        //  Month and Year
        $mediaYear = $_POST['media_year'];
        $mediaMonth = $_POST['media_month'];

        //  File Data
        $fileData = $_POST['file'];

        //  File Path
        $filePath = base64_decode( $fileData['file'] );

        //  Check File Exists
        if( $fileData && file_exists( $filePath ) ) {

            //  Set Filesize
            $response['filesize'] = filesize( $filePath );

            //  Delete
            @unlink( $filePath );

            //  Check
            if( file_exists ( $filePath ) ) {

                //  Set Error
                $response['success'] = false;
            }
        }
    } else {

        //  Set Error
        $response['success'] = false;
    }

    //  Send Response
    wp_send_json($response);
}


//  Add Ajax Filter
add_filter( 'wp_ajax_wen_image_cleaner_delete_attachment', 'wen_image_cleaner_delete_attachment_callback' );

//  Callback
function wen_image_cleaner_delete_attachment_callback() {

    //  Response
    $response = array('success' => true, 'filesize' => 0);

    //  Check
    if( current_user_can('manage_options') ) {

        //  Month and Year
        $mediaYear = $_POST['media_year'];
        $mediaMonth = $_POST['media_month'];

        //  Attachment Data
        $attachmentData = $_POST['attachment'];

        //  Explodes
        $explodes = explode( '-', $attachmentData['name'] );

        //  Attachment ID
        $attachmentID = end( $explodes);

        //  Check
        if($attachmentID && get_post($attachmentID)) {

            //  Loop Each
            foreach( $attachmentData['files'] as $fileData) {

                //  File Path
                $theFilePath = base64_decode( $fileData['file'] );

                //  Check
                if( file_exists( $theFilePath ) ) {

                    //  Add the File Size
                    $response['filesize'] += filesize( $theFilePath );
                }
            }

            //  Delete
            wp_delete_attachment( $attachmentID );

            //  Loop Each Again
            foreach( $attachmentData['files'] as $fileData) {

                //  File Path
                $theFilePath = base64_decode( $fileData['file'] );

                //  Check
                if( file_exists( $theFilePath ) ) {

                    //  Delete
                    @unlink( $theFilePath );
                }
            }
        } else {

            //  Set Error
            $response['success'] = false;
        }
    } else {

        //  Set Error
        $response['success'] = false;
    }

    //  Send Response
    wp_send_json($response);
}


//  Add Ajax Filter
add_filter( 'wp_ajax_wen_image_cleaner_get_media_data', 'wen_image_cleaner_get_media_data_callback' );

//  Callback
function wen_image_cleaner_get_media_data_callback() {

    //  Response
    $response = array(
        'success' => true,
        'data' => array()
    );

    //  Check
    if( current_user_can('manage_options') ) {

        //  Month and Year
        $mediaYear = $_POST['media_year'];
        $mediaMonth = $_POST['media_month'];

        //  Set File Data
        $response['data'] = wen_image_cleaner_get_unwanted_media_files_web( $mediaYear, $mediaMonth, true, false, false );
    } else {

        //  Set Error
        $response['success'] = false;
    }

    //  Send Response
    wp_send_json($response);
}


//  Add Ajax Filter
add_filter( 'wp_ajax_wen_image_cleaner_get_dir_data', 'wen_image_cleaner_get_dir_data_ajax_callback' );

//  Callback
function wen_image_cleaner_get_dir_data_ajax_callback() {

    //  Response
    $response = array(
        'success' => true,
        'data' => array()
    );

    //  Check
    if( current_user_can('manage_options') ) {

        //  Dirs Scan
        $response['data'] = wen_image_cleaner_upload_dirs_scan( false );
    } else {

        //  Set Error
        $response['success'] = false;
    }

    //  Send Response
    wp_send_json($response);
}


//  Add Action to Add/Update/Delete Attachment
add_action( 'add_attachment' , 'wen_image_cleaner_update_image_cache' );
//add_action( 'edit_attachment' , 'wen_image_cleaner_update_image_cache' );
add_action( 'delete_attachment' , 'wen_image_cleaner_update_image_cache' );

//  Callback
function wen_image_cleaner_update_image_cache( $attachment_id ) {

    //  Get Post
    $post = get_post( $attachment_id );

    //  Check
    if( $post ) {

        //  Refresh Cache
        wen_image_cleaner_get_unwanted_media_files_web( date( 'Y', strtotime( $post->post_date ) ), date( 'm', strtotime( $post->post_date ) ), true, false, false );
    }
}


//  Add Filter to Media Row Actions
add_filter( 'media_row_actions', 'wen_image_cleaner_add_media_row_action', 50, 2 );

//  Callback
function wen_image_cleaner_add_media_row_action( $actions, $post ) {

    //  Check
    if ( 'image/' != substr( $post->post_mime_type, 0, 6 ) || ! current_user_can( 'manage_options' ) )
        return $actions;

    //  Add Action
    $actions['wen_image_cleaner'] = '<a href="javascript:void(0);" title="' . esc_attr( __( "Refresh the media file's thumbnails", 'wen-image-cleaner' ) ) . '" data-id="' . $post->ID . '" class="wen-ic-refresh-media">' . __( 'Refresh Image', 'wen-image-cleaner' ) . '</a>';

    //  Return
    return $actions;
}


//  Add Ajax Filter
add_filter( 'wp_ajax_wen_image_cleaner_refresh_media', 'wen_image_cleaner_refresh_media_ajax_callback' );

//  Callback
function wen_image_cleaner_refresh_media_ajax_callback() {

    //  Get Attachment ID
    $attachmentID = $_POST['attachment_id'];

    //  Refresh Media
    $response = wen_image_cleaner_refresh_the_attachment( $attachmentID );

    //  Send Response
    wp_send_json($response);
}


//  Add FIlter to Admin Init for Registering Settings
add_action( 'admin_init', 'wen_image_cleaner_register_settings' );

//  Callback
function wen_image_cleaner_register_settings() {

    //  Register General Section
    add_settings_section(
        'wen_image_cleaner_general_section',
        '', '',
        'wen-image-cleaner'
    );

    //  Check Landscape Dimension Available
    if( wen_image_cleaner_has_landscape_dimension() ) {

        //  Add Setting Field: Landscape Dimension
        add_settings_field(
            'landscape_dimension',
            __('Landscape Main Size', 'wen-image-cleaner'),
            'wen_image_cleaner_setting_landscape_dimension_render',
            'wen-image-cleaner',
            'wen_image_cleaner_general_section'
        );
    }

    //  Check Portrait Dimension Available
    if( wen_image_cleaner_has_portrait_dimension() ) {

        //  Add Setting Field: Portrait Dimension
        add_settings_field(
            'portrait_dimension',
            __('Portrait Main Size', 'wen-image-cleaner'),
            'wen_image_cleaner_setting_portrait_dimension_render',
            'wen-image-cleaner',
            'wen_image_cleaner_general_section'
        );
    }

    //  Add Setting Field: Clear Larger
    add_settings_field(
        'clear_larger',
        '',
        'wen_image_cleaner_setting_clear_larger_render',
        'wen-image-cleaner',
        'wen_image_cleaner_general_section'
    );

    //  Add Setting Field: Strict Mode
    add_settings_field(
        'strict_mode',
        '',
        'wen_image_cleaner_setting_strict_mode_render',
        'wen-image-cleaner',
        'wen_image_cleaner_general_section'
    );

    //  Add Setting Field: Clear Settings
    add_settings_field(
        'clear_settings',
        '',
        'wen_image_cleaner_setting_clear_settings_render',
        'wen-image-cleaner',
        'wen_image_cleaner_general_section'
    );

    //  Register the Fields to Wordpress
    register_setting(
        'wen-image-cleaner',
        'wen_image_cleaner_options'
    );
}

//  Render Landscape Dimension Choose Setting
function wen_image_cleaner_setting_landscape_dimension_render() {
?>
<fieldset>
    <legend class="screen-reader-text"><span><?php echo __('Landscape Main Size', 'wen-image-cleaner'); ?></span></legend>
    <label title="auto detect">
        <input type="radio" name="wen_image_cleaner_options[landscape_dimension]" value="_auto_" <?php echo (wen_image_cleaner_get_option('landscape_dimension') == '_auto_' ? 'checked="checked"' : ''); ?> />
        <span><?php echo __('Auto detect', 'wen-image-cleaner'); ?></span>
    </label><br>
    <?php foreach(wen_image_cleaner_get_landscape_dimensions() as $dimension) { ?>
    <label title="<?php echo $dimension['width'] . 'x' . $dimension['height'] . ' : ' . $dimension['name']; ?>">
        <input type="radio" name="wen_image_cleaner_options[landscape_dimension]" value="<?php echo $dimension['name']; ?>" <?php echo (wen_image_cleaner_get_option('landscape_dimension') == $dimension['name'] ? 'checked="checked"' : ''); ?> />
        <span><?php echo $dimension['width'] . 'x' . $dimension['height'] . ' : <em>' . $dimension['name'] . '</em>'; ?></span>
    </label><br>
    <?php } ?>
</fieldset>
<?php
}

//  Render Portait Dimension Choose Setting
function wen_image_cleaner_setting_portrait_dimension_render($args) {
?>
<fieldset>
    <legend class="screen-reader-text"><span><?php echo __('Portrait Main Size', 'wen-image-cleaner'); ?></span></legend>
    <label title="auto detect">
        <input type="radio" name="wen_image_cleaner_options[portrait_dimension]" value="_auto_" <?php echo (wen_image_cleaner_get_option('portrait_dimension') == '_auto_' ? 'checked="checked"' : ''); ?> />
        <span><?php echo __('auto detect', 'wen-image-cleaner'); ?></span>
    </label><br>
    <?php foreach(wen_image_cleaner_get_portrait_dimensions() as $dimension) { ?>
    <label title="<?php echo $dimension['width'] . 'x' . $dimension['height'] . ' : ' . $dimension['name']; ?>">
        <input type="radio" name="wen_image_cleaner_options[portrait_dimension]" value="<?php echo $dimension['name']; ?>" <?php echo (wen_image_cleaner_get_option('portrait_dimension') == $dimension['name'] ? 'checked="checked"' : ''); ?> />
        <span><?php echo $dimension['width'] . 'x' . $dimension['height'] . ' : <em>' . $dimension['name'] . '</em>'; ?></span>
    </label><br>
    <?php } ?>
</fieldset>
<?php
}

//  Render Clear Larger Setting
function wen_image_cleaner_setting_clear_larger_render($args) {
?>
<input type="hidden" name="wen_image_cleaner_options[clear_larger]" value="no" />
<label>
    <input type="checkbox" name="wen_image_cleaner_options[clear_larger]" value="yes" <?php echo (wen_image_cleaner_get_option('clear_larger') == 'yes' ? 'checked="checked"' : ''); ?> />
    <?php echo __('Replace larger image uploads by <strong>optimized version (as selected size above)</strong> of image', 'wen-image-cleaner'); ?>
</label>
<?php
}

//  Render Clear Larger Setting
function wen_image_cleaner_setting_strict_mode_render($args) {
?>
<input type="hidden" name="wen_image_cleaner_options[strict_mode]" value="no" />
<label>
    <input type="checkbox" name="wen_image_cleaner_options[strict_mode]" value="yes" <?php echo (wen_image_cleaner_get_option('strict_mode') == 'yes' ? 'checked="checked"' : ''); ?> />
    <?php echo __('Prevent image upload when images less than the <strong>related dimension (landscape and/or portrait)</strong> are uploaded', 'wen-image-cleaner'); ?>
</label>
<?php
}

//  Render Clear Larger Setting
function wen_image_cleaner_setting_clear_settings_render($args) {
?>
<input type="hidden" name="wen_image_cleaner_options[clear_settings]" value="no" />
<label>
    <input type="checkbox" name="wen_image_cleaner_options[clear_settings]" value="yes" <?php echo (wen_image_cleaner_get_option('clear_settings') == 'yes' ? 'checked="checked"' : ''); ?> />
    <?php echo __('Clear Settings on plugin deactivation', 'wen-image-cleaner'); ?>
</label>
<?php
}
