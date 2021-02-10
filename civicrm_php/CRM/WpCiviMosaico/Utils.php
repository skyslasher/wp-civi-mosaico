<?php

// translations
define('WP_Civi_Mosaico_TextGroup', 'WP Civi Mosaico');
define('WP_Civi_Mosaico_TermPhrase_From', 'From');
define('WP_Civi_Mosaico_TermDesc_From', 'From');
define('WP_Civi_Mosaico_TermPhrase_And', 'and');
define('WP_Civi_Mosaico_TermDesc_And', 'and');
define('WP_Civi_Mosaico_TermPhrase_ContinueReading', 'Continue reading');
define('WP_Civi_Mosaico_TermDesc_ContinueReading', 'Continue reading');
define('WP_Civi_Mosaico_TermPhrase_ReadingTime', 'Reading time:');
define('WP_Civi_Mosaico_TermDesc_ReadingTime', 'Reading time:');
define('WP_Civi_Mosaico_TermPhrase_Minutes', 'minutes');
define('WP_Civi_Mosaico_TermDesc_Minutes', 'minutes');
define('WP_Civi_Mosaico_TermPhrase_Minute', 'minute');
define('WP_Civi_Mosaico_TermDesc_Minute', 'minute');
define('WP_Civi_Mosaico_TermPhrase_FourDigitYear', '4 digit year');
define('WP_Civi_Mosaico_TermDesc_FourDigitYear', '4 digit year: yyyy');
define('WP_Civi_Mosaico_TermPhrase_FullDate', 'Full date');
define('WP_Civi_Mosaico_TermDesc_FullDate', 'Full date: dd.mm.yyyy');


class CRM_WpCiviMosaico_Utils
{
    const DEFAULT_POSTS_COUNT = 10;
    const DEFAULT_AVATAR_SIZE = 150;
    const DEFAULT_MAX_IMAGE_PIXELS = 36000000;

    public static function logme( $line )
    {
        $dt = new DateTime();
        file_put_contents( '/var/www/vhosts/upgrade-jetzt.de/httpdocs/log/wp_civi_mosaico.log',  "[" . $dt->format('Y-m-d\TH:i:s.u') . "] " . $line . "\n", FILE_APPEND | LOCK_EX );
    }
    public static function RegisterPluginTranslationPhrases()
    {
        self::pll_register_string(WP_Civi_Mosaico_TermDesc_From, WP_Civi_Mosaico_TermPhrase_From);
        self::pll_register_string(WP_Civi_Mosaico_TermDesc_And, WP_Civi_Mosaico_TermPhrase_And);
        self::pll_register_string(WP_Civi_Mosaico_TermDesc_ContinueReading, WP_Civi_Mosaico_TermPhrase_ContinueReading);
        self::pll_register_string(WP_Civi_Mosaico_TermDesc_ReadingTime, WP_Civi_Mosaico_TermPhrase_ReadingTime);
        self::pll_register_string(WP_Civi_Mosaico_TermDesc_Minutes, WP_Civi_Mosaico_TermPhrase_Minutes);
        self::pll_register_string(WP_Civi_Mosaico_TermDesc_Minute, WP_Civi_Mosaico_TermPhrase_Minute);
        self::pll_register_string(WP_Civi_Mosaico_TermDesc_FourDigitYear, WP_Civi_Mosaico_TermPhrase_FourDigitYear);
        self::pll_register_string(WP_Civi_Mosaico_TermDesc_FullDate, WP_Civi_Mosaico_TermPhrase_FullDate);
    }
    public static function __($string = '', $PostID = 0)
    {
        if (function_exists('pll__')) {
            if (0 != $PostID) {
                return pll_translate_string($string, pll_get_post_language($PostID));
            }
            return pll__($string);
        }
        return __($string);
    }
    public static function pll_register_string($desc = '', $phrase = '')
    {
        if (function_exists('pll_register_string')) {
            pll_register_string($desc, $phrase, WP_Civi_Mosaico_TextGroup, false);
        }
    }
    public static function getUrl($path, $query, $frontend)
    {
        return CRM_Utils_System::url($path, $query, true, null, false, $frontend);
    }
    public static function getPluginBaseDir()
    {
        return CRM_Core_Resources::singleton()->getPath('de.ergomation.wp-civi-mosaico') . '/';
    }
    public static function getPluginBaseUrl()
    {
        return CRM_Core_Resources::singleton()->getUrl('de.ergomation.wp-civi-mosaico');
    }

    protected static function getAuthorArray($author_id)
    {
        $email = get_the_author_meta('user_email', $author_id);
        return [
            "author" => get_the_author_meta('display_name', $author_id),
            "url" => esc_url(get_author_posts_url($author_id)),
            "email" => $email,
            "image" => get_avatar_url($author_id, [ "size" => DEFAULT_AVATAR_SIZE, "height" => DEFAULT_AVATAR_SIZE, "width" => DEFAULT_AVATAR_SIZE, "default" => "blank" ])
        ];
    }

    protected static function getAuthors($PostID)
    {
        $posting_authors = [];
        if (function_exists('coauthors_IDs')) {
            $posting_authors_Objects = get_coauthors($PostID);
            foreach ($posting_authors_Objects as $WP_User_author) {
                $posting_authors[] = self::getAuthorArray($WP_User_author->ID);
            }
        } else {
            $WP_Post = get_post($PostID);
            $author_id = $WP_Post->post_author;
            $posting_authors[] = self::getAuthorArray($author_id);
        }
        return $posting_authors;
    }

    protected static function getAuthorImages($PostID)
    {
        $posting_authors_IDs = [];
        if (function_exists('coauthors_IDs')) {
            $posting_authors_Objects = get_coauthors($PostID);
            foreach ($posting_authors_Objects as $WP_User_author) {
                $posting_authors_IDs[] = $WP_User_author->ID;
            }
        } else {
            $author_id = get_post_field('post_author', $PostID);
            $posting_authors_IDs[] = $author_id;
        }
        $result = '';
        foreach ($posting_authors_IDs as $posting_authors_ID) {
            $result .= get_avatar(get_the_author_meta('user_email', $posting_authors_ID), DEFAULT_AVATAR_SIZE);
        }
        return $result;
    }

    protected static function getAuthorFrom($PostID)
    {
        return self::__(WP_Civi_Mosaico_TermPhrase_From, $PostID);
    }

    protected static function getAuthorConjunction($PostID)
    {
        return self::__(WP_Civi_Mosaico_TermPhrase_And, $PostID);
    }

    protected static function getReadingTime($PostID)
    {
        if (shortcode_exists('rt_reading_time')) {
            $rt_string = self::__(WP_Civi_Mosaico_TermPhrase_ReadingTime, $PostID);
            $rt_time = self::__(WP_Civi_Mosaico_TermPhrase_Minutes, $PostID);
            $rt_time_singular = self::__(WP_Civi_Mosaico_TermPhrase_Minute, $PostID);
            return ' (' . do_shortcode('[rt_reading_time post_id="' . $PostID . '" label="' . $rt_string . '" postfix="' . $rt_time . '" postfix_singular="' . $rt_time_singular . '"]') . ')';
        }
        return '';
    }

    protected static function getReadingTimeCaption($PostID)
    {
        return self::__(WP_Civi_Mosaico_TermPhrase_ContinueReading, $PostID);
    }

    public static function getAjaxPosts()
    {
        global $http_return_code;

        try { // exceptions in ajax-calls lead to strange 500 errors
            $post_id = (empty($_REQUEST[ '$post_id' ])) ? 0 : $_REQUEST[ '$post_id' ];
            $language = (empty($_REQUEST[ 'language' ])) ? '' : $_REQUEST[ 'language' ];
            $page_num = (empty($_REQUEST[ 'page_num' ])) ? 1 : $_REQUEST[ 'page_num' ];
            $post_status = (empty($_REQUEST[ 'post_status' ])) ? 'any' : $_REQUEST[ 'post_status' ];
            if (0 != $post_id) {
                $num_pages = 1;
                $ajaxposts = [ get_post($post_id, ARRAY_A) ];
            } else {
                $query_args = [
                    'post_type' => 'post',
                    'posts_per_page' => self::DEFAULT_POSTS_COUNT,
                    'paged' => $page_num,
                    'post_status' => $post_status,
                ];
                $custom_query = new WP_Query($query_args);
                $num_pages = $custom_query->max_num_pages;
                $ajaxposts = $custom_query->get_posts();
            }
            foreach ($ajaxposts as $key => $value) {
                $PostID = $value->ID;
                // render shortcodes
                $ajaxposts[ $key ]->post_content = do_shortcode($ajaxposts[ $key ]->post_content);
                // additional author info
                $ajaxposts[ $key ]->author_info = self::getAuthors($PostID);
                $ajaxposts[ $key ]->author_images = self::getAuthorImages($PostID);
                $ajaxposts[ $key ]->author_from = self::getAuthorFrom($PostID);
                $ajaxposts[ $key ]->author_conjunction = self::getAuthorConjunction($PostID);
                // reading time, if plugin exists
                $ajaxposts[ $key ]->reading_time = self::getReadingTime($PostID);
                $ajaxposts[ $key ]->reading_time_caption = self::getReadingTimeCaption($PostID);
                // featured image
                $ajaxposts[ $key ]->featured_image = get_the_post_thumbnail_url($PostID, 'full');
            }
            echo json_encode([ "num_pages" => $num_pages, "posts" => $ajaxposts ]);
        } catch (\Exception $e) {
            $http_return_code = 400;
            // error message to frontend, found in inspector console
            echo 'Error in AJAX call to Wordpress: ' . $e->getMessage();
            return;
        }
        CRM_Utils_System::civiExit();
    }

    private static function delTree( $dir, $removeSelf = false ) {
      if (!file_exists( $dir )) {
        return false;
      }
      $files = array_diff( scandir( $dir ), array( '.', '..' ) );
      foreach ( $files as $file ) {
        ( is_dir( "$dir" . DIRECTORY_SEPARATOR . "$file" ) ) ? self::delTree( "$dir" . DIRECTORY_SEPARATOR . "$file", true ) : unlink( "$dir" . DIRECTORY_SEPARATOR . "$file" );
      }
      if ( $removeSelf ) {
        return rmdir( $dir );
      } else {
        return true;
      }
    }

    /*
     * returns the current cache dir, cleans weekly cache when called
     */
    private static function getCacheDir() {
      // every week a new cache directory
      $UploadDir = wp_upload_dir();
      $CacheRootDir = $UploadDir['basedir'] . DIRECTORY_SEPARATOR . 'wp_civi_mosaico_cache';
      $CacheDir = $CacheRootDir . DIRECTORY_SEPARATOR . date( 'W' );
      if ( !file_exists( $CacheDir ) ) {
        // clean old cache dirs, create new
        self::delTree( $CacheRootDir, false );
        if ( !mkdir( $CacheDir, 0755, true ) ) {
          throw new Exception("Failed to create cache directory $CacheDir");
        }
      }
      return $CacheDir;
    }

    public static function flushCache() {
      self::delTree( self::getCacheDir(), false );
    }

    /*
     * ajax handler for upload requests
     */
    public static function processUpload()
    {
        global $http_return_code;

        try { // exceptions in ajax-calls lead to strange 500 errors
            $files = [];

            if ($_SERVER[ "REQUEST_METHOD" ] == "GET") {
                // image list request
                $query_images_args = [
                    'post_type'      => 'attachment',
                    'post_mime_type' => 'image',
                    'post_status'    => 'inherit',
                    'posts_per_page' => - 1,
                ];

                $query_images = new WP_Query($query_images_args);

                foreach ($query_images->posts as $image) {
                    $file = [
                        "name" => "",
                        "url" => wp_get_attachment_url($image->ID),
                        "size" => 123456,
                        "thumbnailUrl" => wp_get_attachment_image_src($image->ID, 'thumbnail')[ 0 ]
                    ];
                    $files[] = $file;
                }
            } elseif (!empty($_FILES)) {
                // image upload. only one image a time, more will come in sequencially
                foreach ($_FILES[ 'files' ][ 'name' ] as $key => $value) {
                    if ($_FILES[ 'files' ][ 'error' ][ $key ] == UPLOAD_ERR_OK) {
                        $uploadedfile = [
                            'name'     => $_FILES[ 'files' ][ 'name' ][ $key ],
                            'type'     => $_FILES[ 'files' ][ 'type' ][ $key ],
                            'tmp_name' => $_FILES[ 'files' ][ 'tmp_name' ][ $key ],
                            'error'    => $_FILES[ 'files' ][ 'error' ][ $key ],
                            'size'     => $_FILES[ 'files' ][ 'size' ][ $key ]
                        ];
                        $result = wp_handle_upload($uploadedfile, [ 'test_form' => false ]);
                        if ($result && !isset($result[ 'error' ])) {
                            require_once(ABSPATH . 'wp-admin/includes/image.php');
                            require_once(ABSPATH . 'wp-admin/includes/file.php');

                            $dt = new DateTime();
                            // create attachment post, so the file shows up in the media library
                            $attachment = [
                                "guid" => $result[ 'file' ],
                                "post_mime_type" => $result[ 'type' ],
                                "post_title" => 'Mosaico upload (' . $dt->format('Y-m-d H:i') . ') - ' . $uploadedfile[ 'name' ],
                                "post_content" => "",
                                "post_status" => "draft",
                                "post_author" => 1
                            ];
                            $attachment_id = wp_insert_attachment($attachment, $result[ 'file' ], 0);
                            $attachment_data = wp_generate_attachment_metadata($attachment_id, $result[ 'file' ]);
                            wp_update_attachment_metadata($attachment_id, $attachment_data);

                            // push upload to return array
                            $file = [
                                "name" => $uploadedfile[ 'name' ],
                                "url" => $result[ 'url' ],
                                "size" => $uploadedfile[ 'size' ]
                            ];
                            $files[] = $file;
                        } else {
                            $http_return_code = 400;
                            // error message to frontend, found in inspector console
                            echo 'Error uploading file: ' . $result[ 'error' ];
                            return;
                        }
                    } else {
                        $http_return_code = 400;
                        // error message to frontend, found in inspector console
                        echo "Error uploading file:\n";
                        print_r($_FILES[ 'files' ][ 'error' ]);
                        return;
                    }
                }
            } else {
                $http_return_code = 400;
                // error message to frontend, found in inspector console
                echo "Error uploading file - empty file or no file given";
                return;
            }

            header("Content-Type: application/json; charset=utf-8");
            header("Connection: close");

            echo json_encode([ "files" => $files ]);
        } catch (\Exception $e) {
            $http_return_code = 400;
            // error message to frontend, found in inspector console
            echo 'Error uploading file: ' . $e->getMessage();
            return;
        }
        CRM_Utils_System::civiExit();
    }

    /*
     * handler for img requests
     */
    public static function processImg()
    {
        global $http_return_code;

        try { // exceptions in ajax-calls lead to strange 500 errors
            $config = CRM_Mosaico_Utils::getConfig();
            $methods = [ 'placeholder', 'resize', 'cover' ];
            if ($_SERVER[ "REQUEST_METHOD" ] == "GET") {
                $method = CRM_Utils_Array::value('method', $_GET, 'cover');
                if (!in_array($method, $methods)) {
                    $method = 'cover';
                }

                self::logme('$_GET ist:' . print_r($_GET, true));
                $params = explode(",", $_GET[ "params" ]);
                $width = ( int ) $params[ 0 ];
                $height = ( int ) $params[ 1 ];

                // Apply a sensible maximum size for images in an email
                if ($width * $height > self::DEFAULT_MAX_IMAGE_PIXELS) {
                    throw new \Exception("The requested image size is too large");
                }

                switch ($method) {
                    case 'placeholder':
                        Civi::service('mosaico_graphics')->sendPlaceholder($width, $height);
                        break;

                    case 'resize':
                    case 'cover':
                        $func = ($method === 'resize') ? 'createResizedImage' : 'createCoveredImage';

                        $my_home_url = get_home_url();
                        $img_file_src = $_GET[ "src" ];
                        $my_home_url_parts = parse_url( $my_home_url );
                        $img_file_src_parts = parse_url( $img_file_src );
                        // we do not take remote images. since this image proxy is
                        // available without access control one could flood the
                        // cache directory
                        if ( $img_file_src_parts[ 'host' ] != $my_home_url_parts[ 'host' ] ) {
                          throw new \Exception("Cannot work on remote images");
                        }
                        $img_file_alias = md5( $img_file_src . $width . 'x' . $height );
                        $img_file_cache = self::getCacheDir() . DIRECTORY_SEPARATOR . 'ms_mb_' . $img_file_alias;
                        $img_file_src_cache = $img_file_cache . '_src';
                        if (!file_exists($img_file_cache)) {
                          if ( false === file_put_contents( $img_file_src_cache, fopen( $img_file_src, 'r') ) ) {
                            throw new \Exception("The file could not be loaded into the cache");
                          }
                          Civi::service('mosaico_graphics')->$func($img_file_src_cache, $img_file_cache, $width, $height);
                          unlink($img_file_src_cache);
                        }
                        $img_file_contents = file_get_contents($img_file_cache);
                        if ( false === $img_file_contents ) {
                          throw new \Exception("The cached file could not be loaded");
                        }
                        $img_mimetype = mime_content_type( $img_file_cache );
                        $expiry_time = 2592000;  // 30d (60s * 60m * 24h * 30d)
                        header("Pragma: cache");
                        header("Cache-Control: max-age=" . $expiry_time . ", public");
                        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expiry_time) . ' GMT');
                        header("Content-type:" . $img_mimetype);
                        echo $img_file_contents;
                }
            }
        } catch (\Exception $e) {
            $http_return_code = 400;
            // error message to frontend, found in inspector console
            echo 'Error processing image: ' . $e->getMessage();
            return;
        }
        CRM_Utils_System::civiExit();
    }
}
