<?php

//  Get Comparitive Dimension
function wen_image_cleaner_get_comparitive_dimension( $isLandscape ) {

    //  Get Dimension
    $mainDimension = ( $isLandscape ? wen_image_cleaner_get_landscape_dimension() : wen_image_cleaner_get_portrait_dimension() );
    if( $isLandscape && !$mainDimension ) $mainDimension = wen_image_cleaner_get_portrait_dimension();
    if( !$isLandscape && !$mainDimension ) $mainDimension = wen_image_cleaner_get_landscape_dimension();

    //  Return
    return $mainDimension;
}

//  Get Size Info
function wen_image_cleaner_get_image_size( $size ) {

    //  Check
    if( !$size || empty($size) )  return null;

    //  Get the Sizes
    $sizes = wen_image_cleaner_get_image_sizes();

    //  Return
    return ( isset($sizes[$size]) ? $sizes[$size] : null );
}

//  Get WP Available Image Sizes
function wen_image_cleaner_get_image_sizes( $cache = true ) {

    //  Global
    global $wen_image_cleaner_wp_image_sizes;

    //  Check
    if( $wen_image_cleaner_wp_image_sizes && $cache ) {

        //  Return
        return $wen_image_cleaner_wp_image_sizes;
    }

    //  Global
    global $_wp_additional_image_sizes;

    //  Get the Intermediate Sizes
    $sizes = get_intermediate_image_sizes();

    //  Dimensions
    $dimensions = array();

    //  Loop Each
    foreach( $sizes as $size ) {

        //  Store
        $dimensions[$size] = array_merge( array('name' => $size), ( isset($_wp_additional_image_sizes[$size]) ? $_wp_additional_image_sizes[$size] : array(
            'width' => get_option($size . '_size_w'),
            'height' => get_option($size . '_size_h'),
            'crop' => (get_option($size . '_crop') == '1')
        ) ) );
    }

    //  Store
    $wen_image_cleaner_wp_image_sizes = $dimensions;

    //  Return
    return $dimensions;
}

//  Check is Portrait Dimension
function wen_image_cleaner_is_portrait_dimension( $width, $height ) {
    return ( $height - 25 ) > ( $width + 25 );
}

//  Check is Landscape Dimension
function wen_image_cleaner_is_landscape_dimension( $width, $height ) {
    return ( $width + 25 ) >= ( $height - 25 );
}

//  Compare the Dimension
function wen_image_cleaner_compare_get_bigger_dimension( $dimen1, $dimen2, $both = false ) {

    //  Check
    if( !$dimen1 )    return $dimen2;
    if( !$dimen2 )    return $dimen1;

    //  Bigger Dimen
    $dimen = null;

    //  Check
    if( $both ) {

        //  Check
        if( $dimen1['width'] > $dimen2['width']
            && $dimen1['height'] > $dimen2['height'] ) {

            //  Dimen
            $dimen = $dimen1;
        } else {

            //  Dimen
            $dimen = $dimen2;
        }
    }
    else if( $dimen1['width'] > $dimen2['height'] ) {

        //  Check
        if( $dimen1['width'] > $dimen2['width'] ) {

            //  Dimen
            $dimen = $dimen1;
        } else {

            //  Dimen
            $dimen = $dimen2;
        }
    } else {

        //  Check
        if( $dimen1['height'] > $dimen2['height'] ) {

            //  Dimen
            $dimen = $dimen1;
        } else {

            //  Dimen
            $dimen = $dimen2;
        }
    }

    //  Return
    return $dimen;
}

//  Check has Portrait Dimension
function wen_image_cleaner_has_portrait_dimension() {
    return ( sizeof( wen_image_cleaner_get_portrait_dimensions() ) > 0 );
}

//  Get the Portrait Dimensions
function wen_image_cleaner_get_portrait_dimensions() {

    //  Available Image Sizes
    $imageSizes = array();

    //  Loop Each
    foreach( wen_image_cleaner_get_image_sizes() as $key => $size ) {

        //  Check
        if( wen_image_cleaner_is_portrait_dimension( $size['width'], $size['height'] ) ) {

            //  Store
            $imageSizes[$key] = $size;
        }
    }

    //  Return
    return $imageSizes;
}

//  Get the Portrait Dimension
function wen_image_cleaner_get_portrait_dimension() {

    //  Dimension
    $dimension = null;

    //  Available Image Portrait Sizes
    $imageSizes = wen_image_cleaner_get_portrait_dimensions();

    //  Loop Each
    foreach( $imageSizes as $key => $size ) {

        //  Compare & Store
        $dimension = wen_image_cleaner_compare_get_bigger_dimension( $dimension, $size );
    }

    //  Return
    return apply_filters( 'wen_image_cleaner_get_portrait_dimension', $dimension, $imageSizes );
}

//  Check has Landscape Dimension
function wen_image_cleaner_has_landscape_dimension() {
    return ( sizeof( wen_image_cleaner_get_landscape_dimensions() ) > 0 );
}

//  Get the Landscape Dimensions
function wen_image_cleaner_get_landscape_dimensions() {

    //  Available Image Sizes
    $imageSizes = array();

    //  Loop Each
    foreach( wen_image_cleaner_get_image_sizes() as $key => $size ) {

        //  Check
        if( wen_image_cleaner_is_landscape_dimension( $size['width'], $size['height'] ) ) {

            //  Store
            $imageSizes[$key] = $size;
        }
    }

    //  Return
    return $imageSizes;
}

//  Get the Landscape Dimension
function wen_image_cleaner_get_landscape_dimension() {

    //  Dimension
    $dimension = null;

    //  Available Image Landscape Sizes
    $imageSizes = wen_image_cleaner_get_landscape_dimensions();

    //  Loop Each
    foreach( $imageSizes as $key => $size ) {

        //  Compare & Store
        $dimension = wen_image_cleaner_compare_get_bigger_dimension( $dimension, $size );
    }

    //  Return
    return apply_filters( 'wen_image_cleaner_get_landscape_dimension', $dimension, $imageSizes );
}

//  Get IC Option
function wen_image_cleaner_get_option( $key, $def = null ) {

    //  Global
    global $wen_image_cleaner_options;

    //  Check
    if( !$wen_image_cleaner_options ) {

        //  Load
        $wen_image_cleaner_options = get_option('wen_image_cleaner_options');
    }

    //  Check
    if( isset( $wen_image_cleaner_options[$key] ) )    return $wen_image_cleaner_options[$key];

    //  Return
    return $def;
}

//  Get Valid Image Formats
function wen_image_cleaner_get_valid_image_formats() {

    //  Return
    return apply_filters('wen_image_cleaner_image_formats', explode(',', 'jpg,jpeg,png,gif,bmp,ico,tiff'));
}

//  Get the Stats for Folder
function wen_image_cleaner_get_folder_stats( $year, $month, $cache = true ) {

    //  Cache Key
    $cacheKey = 'wen_image_cleaner_folder_stats_' . get_current_blog_id() . '_' . $year . '_' . $month;

    //  Check for Cache
    if( $cache ) {

        //  Get
        $tData = get_transient( $cacheKey );

        //  Check
        if( $tData )   return $tData;
    }

    //  Extend Timeout
    @set_time_limit( 900 );

    //  Global
    global $wpdb;

    //  Dir Base
    $dirBase = $year . '/' . $month;

    //  Get Upload Dir
    $upload_dir = wp_upload_dir( $dirBase );

    //  Valid Image Formats
    $valid_image_formats = wen_image_cleaner_get_valid_image_formats();

    //  Pattern
    $pattern1 = $upload_dir['path'] . '/*.{' . implode(',', $valid_image_formats) . '}';

    //  Pattern
    $pattern2 = '/.*\/(.*)\-([0-9]+)x([0-9]+)\.(' . implode('|', $valid_image_formats) . ')$/i';

    //  Match
    $match = null;

    //  Stats
    $stats = array('unused' => array(), 'used' => array(), 'missing' => array());

    //  Lists
    $filesInfo = array();

    //  Current Type
    $currentType = null;

    //  Loop Each
    foreach( glob( $pattern1, GLOB_BRACE ) as $file ) {

        //  Main
        $mainName = 'unknown';

        //  Match
        preg_match( $pattern2, $file, $match );

        //  Is Main File
        $isMainFile = false;

        //  Check Match
        if($match) {

            //  Set Main
            $mainName = $match[1];
        } else {

            //  Is Main File
            $isMainFile = true;

            //  Explode
            $explodes = explode('.', basename($file));

            //  Set Main Name
            $mainName = $explodes[0];
        }

        //  Check
        if( !isset( $filesInfo[$mainName] ) ) {

            //  Create Main
            $filesInfo[$mainName] = array();

            //  Filename
            $fName = ( $isMainFile ? basename($file) : $mainName . '.' . $match[4] );

            //  Main File
            $mainFile = ( $isMainFile ? $file : $upload_dir['path'] . '/' . $fName );

            //  The Post ID
            $thePostID = intval( $wpdb->get_var("SELECT post_id FROM {$wpdb->prefix}postmeta WHERE `meta_key` = '_wp_attached_file' and `meta_value` = '" . $dirBase . '/' . $fName . "' LIMIT 1") );

            //  Get the Attached Stats
            $totalAttached = sizeof( $wpdb->get_col("SELECT post_id FROM {$wpdb->prefix}postmeta WHERE `meta_key` = '_thumbnail_id' and `meta_value` = '{$thePostID}' LIMIT 1") );

            //  Is Used
            $isUsed = apply_filters( 'wen_image_cleaner_image_is_used', $totalAttached > 0, $thePostID, $mainFile );

            //  Set the Main Image Info
            $filesInfo[$mainName]['info'] = array(
                'file' => $mainFile,
                'exists' => file_exists( $mainFile ),
                'post_id' => $thePostID,
                'is_used' => (int)$isUsed
            );

            //  Check if Not Missing
            if( file_exists( $mainFile ) ) {

                //  Set Type
                $currentType = ( $isUsed ? '' : 'un' ) . 'used';
            } else {

                //  Set Type
                $currentType = 'missing';
            }

            //  Set Images Holder
            $filesInfo[$mainName]['images'] = array();

            //  Check
            if( !in_array( $mainName, $stats[$currentType] ) ) {

                //  Append
                $stats[$currentType][] = $mainName;
            }
        }

        //  Check
        if( !$isMainFile) {

            //  Add
            $filesInfo[$mainName]['images'][] = ($match ? array(
                'file' => $file,
                'width' => $match[2],
                'height' => $match[3],
                'ext' => $match[4]
            ) : array('file' => $file));
        }
    }

    //  Folder Stats
    $folderStats = array('stats' => $stats, 'data' => $filesInfo);

    //  Set Transient
    set_transient( $cacheKey, $folderStats, WEN_IMAGE_CLEANER_CACHE_TIME );

    //  Return
    return $folderStats;
}

//  Get Unwanted Images List
function wen_image_cleaner_get_unwanted_media_files( $year, $month, $unused = true, $cache = true, $deep_cache = true ) {

    //  Cache Key
    $cacheKey = 'wen_image_cleaner_unwanted_media_files_' . get_current_blog_id() . '_' . $year . '_' . $month . '_' . (int)$unused;

    //  Check for Cache
    if( $cache ) {

        //  Get
        $tData = get_transient( $cacheKey );

        //  Check
        if( $tData )   return $tData;
    }

    //  Extend Timeout
    @set_time_limit( 900 );

    //  Get Folder Stats
    $folderStats = wen_image_cleaner_get_folder_stats( $year, $month, $deep_cache );

    //  Unwanted Lists
    $unwantedFiles = array();

    //  Get the Keys
    $theFileKeys = array_unique( array_merge( $folderStats['stats']['missing'], ( $unused ? $folderStats['stats']['unused'] : array() ) ) );

    //  Loop Each
    foreach( $theFileKeys as $tfKey ) {

        //  Info
        $tfData = $folderStats['data'][$tfKey];

        //  Get Post Type
        $postType = get_post_type($tfData['info']['post_id']);

        //  Check & Append the File
        if($tfKey == 'missing' || $tfData['info']['post_id'] == 0)
            $unwantedFiles[] = $tfData['info']['file'];
        else
            $unwantedFiles[$postType . '-' . $tfData['info']['post_id']] = array($tfData['info']['file']);

        //  Loop Each Images
        foreach( $tfData['images'] as $tfeImage ) {

            //  Check & Append the File
            if($tfKey == 'missing' || $tfData['info']['post_id'] == 0)
                $unwantedFiles[] = $tfeImage['file'];
            else
                $unwantedFiles[$postType . '-' . $tfData['info']['post_id']][] = $tfeImage['file'];
        }
    }

    //  Set Transient
    set_transient( $cacheKey, $unwantedFiles, WEN_IMAGE_CLEANER_CACHE_TIME );

    //  Return
    return $unwantedFiles;
}

//  Get Unwanted Images List for Web
function wen_image_cleaner_get_unwanted_media_files_web( $year, $month, $unused = true, $cache = true, $deep_cache = true ) {

    //  Cache Key
    $cacheKey = 'wen_image_cleaner_unwanted_media_files_web_' . get_current_blog_id() . '_' . $year . '_' . $month . '_' . (int)$unused;

    //  Check for Cache
    if( $cache ) {

        //  Get
        $tData = get_transient( $cacheKey );

        //  Check
        if( $tData )   return $tData;
    }

    //  Extend Timeout
    @set_time_limit( 900 );

    //  Get Folder Stats
    $folderStats = wen_image_cleaner_get_folder_stats( $year, $month, $deep_cache );

    //  Unwanted Lists
    $unwantedFiles = array();

    //  Get the Keys
    $theFileKeys = array_unique( array_merge( $folderStats['stats']['missing'], ( $unused ? $folderStats['stats']['unused'] : array() ) ) );

    //  Loop Each
    foreach( $theFileKeys as $tfKey ) {

        //  Info
        $tfData = $folderStats['data'][$tfKey];

        //  Get Post Type
        $postType = get_post_type($tfData['info']['post_id']);

        //  Check & Append the File
        if($tfKey == 'missing' || $tfData['info']['post_id'] == 0)
            $unwantedFiles[] = array('file' => base64_encode($tfData['info']['file']), 'name' => $tfKey);
        else
            $unwantedFiles[$postType . '-' . $tfData['info']['post_id']] = array(array('file' => base64_encode($tfData['info']['file']), 'name' => $tfKey));

        //  Loop Each Images
        foreach( $tfData['images'] as $tfeImage ) {

            //  Check & Append the File
            if($tfKey == 'missing' || $tfData['info']['post_id'] == 0)
                $unwantedFiles[] = array('file' => base64_encode($tfeImage['file']), 'name' => $tfKey . '-' . $tfeImage['width'] . 'x' . $tfeImage['height']);
            else
                $unwantedFiles[$postType . '-' . $tfData['info']['post_id']][] = array('file' => base64_encode($tfeImage['file']), 'name' => $tfKey . '-' . $tfeImage['width'] . 'x' . $tfeImage['height']);
        }
    }

    //  Set Transient
    set_transient( $cacheKey, $unwantedFiles, WEN_IMAGE_CLEANER_CACHE_TIME );

    //  Return
    return $unwantedFiles;
}

//  Scan the Available Directories under Uploads
function wen_image_cleaner_upload_dirs_scan( $cache = true ) {

    //  Cache Key
    $cacheKey = 'wen_image_cleaner_upload_dirs_scan';

    //  Check for Cache
    if( $cache ) {

        //  Get
        $tData = get_transient( $cacheKey );

        //  Check
        if( $tData )   return $tData;
    }

    //  Extend Timeout
    @set_time_limit( 900 );

    //  Dirs
    $dirs = array();

    //  Get Upload Dir
    $uploadDir = wp_upload_dir();

    //  Years
    $yDirs = glob( $uploadDir['basedir'] . '/[0-9]*', GLOB_ONLYDIR );

    //  Loop Each
    foreach( $yDirs as $yDir ) {

        //  Year
        $year = basename( $yDir );

        //  Store
        $dirs[$year] = array();

        //  Months
        $mDirs = glob( $yDir . '/[0-9]*', GLOB_ONLYDIR );

        //  Loop Each
        foreach( $mDirs as $mDir ) {

            //  Store
            $dirs[$year][] = basename( $mDir );
        }
    }

    //  Set Transient
    set_transient( $cacheKey, $dirs, WEN_IMAGE_CLEANER_CACHE_TIME );

    //  Return
    return $dirs;
}

//  Get Attachment Files
function wen_image_cleaner_get_attachment_files( $path ) {

    //  Pathinfo
    $pathinfo = pathinfo( $path );

    //  Pattern
    $pattern = '/' . $pathinfo['filename'] . '\-[0-9]*x[0-9]*.' . $pathinfo['extension'];

    //  Get Files
    $files = glob( $pathinfo['dirname'] . $pattern, GLOB_BRACE );

    //  Return
    return $files;
}

//  Refresh the Attachment
function wen_image_cleaner_refresh_the_attachment( $ID ) {

    //  Success
    $success = true;

    //  Message
    $message = null;

    //  Get Image
    $image = get_post( $ID );

    //  Validate Attachment
    if ( ! $image || 'attachment' != $image->post_type || 'image/' != substr( $image->post_mime_type, 0, 6 ) ) {

        //  Set Error
        $success = false;
        $message = sprintf( __( 'Failed resize: %s is an invalid image ID.', 'wen-image-cleaner' ), esc_html( $ID ) );
    }

    //  Validate Permission
    if ( $success && ! current_user_can( 'manage_options' ) ) {

        //  Set Error
        $success = false;
        $message = __( "Your user account doesn't have permission to resize images", 'wen-image-cleaner' );
    }

    //  Check
    if( $success ) {

        //  Get Full Image
        $fullsizepath = get_attached_file( $image->ID );

        //  Validate File Exists
        if ( false === $fullsizepath || ! file_exists( $fullsizepath ) ) {

            //  Set Error
            $success = false;
            $message = sprintf( __( 'The originally uploaded image file cannot be found at %s', 'wen-image-cleaner' ), '<code>' . esc_html( $fullsizepath ) . '</code>' );
        }

        //  Extend Timeout
        @set_time_limit( 900 );

        //  Check
        if( $success ) {

            //  Get the Files for Attachment
            $aFiles = wen_image_cleaner_get_attachment_files( $fullsizepath );

            //  Loop Each
            foreach( $aFiles as $aFile ) {

                //  Unlink
                @unlink( $aFile );
            }

            //  Get Metadata
            $metadata = wp_generate_attachment_metadata( $image->ID, $fullsizepath );

            //  Check for WP Error
            if ( is_wp_error( $metadata ) ) {

                //  Set Error
                $success = false;
                $message = $metadata->get_error_message();
            }

            //  Check
            if( $success && empty( $metadata ) ) {

                //  Set Error
                $success = false;
                $message = __( 'Unknown failure reason.', 'wen-image-cleaner' );
            }

            //  Check
            if( $success ) {

                //  Update Metadata
                wp_update_attachment_metadata( $image->ID, $metadata );
            }
        }
    }

    //  Check
    if( $success ) {

        //  Set Message
        $message = sprintf( __( '"%1$s" (ID %2$s) was successfully resized.', 'wen-image-cleaner' ), esc_html( get_the_title( $image->ID ) ), $image->ID );
    }

    //  Return
    return array( 'success' => $success, 'message' => $message );
}
