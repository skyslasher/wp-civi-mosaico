<?php
/*
--------------------------------------------------------------------------------
Plugin Name: Wordpress content in CiviCRM with Mosaico
Plugin URI: https://github.com/skyslasher/wp-civi-mosaico
Description: Wordpress-Plugin for the integration of WordPress content in CiviMail with Mosaico
Author: Andreas Hahn
Version: 1.0
Author URI: http://ergomation.de
Text Domain: wp-civi-mosaico
Domain Path: /languages
Depends: CiviCRM
--------------------------------------------------------------------------------
*/
define('WP_CIVI_MOSAICO_PHP_PATH', 'civicrm_php');
define('WP_CIVI_MOSAICO_XML_PATH', 'civicrm_xml');

// Set plugin version here.
define('WP_CIVI_MOSAICO_VERSION', '1.0');

// Store reference to this file.
if (! defined('WP_CIVI_MOSAICO_FILE')) {
    define('WP_CIVI_MOSAICO_FILE', __FILE__);
}

// Store URL to this plugin's directory.
if (! defined('WP_CIVI_MOSAICO_URL')) {
    define('WP_CIVI_MOSAICO_URL', plugin_dir_url(WP_CIVI_MOSAICO_FILE));
}

// Store PATH to this plugin's directory.
if (! defined('WP_CIVI_MOSAICO_PATH')) {
    define('WP_CIVI_MOSAICO_PATH', plugin_dir_path(WP_CIVI_MOSAICO_FILE));
}

require_once(WP_CIVI_MOSAICO_PATH . WP_CIVI_MOSAICO_PHP_PATH . '/CRM/WpCiviMosaico/Utils.php');

class WP_Civi_Mosaico
{
    public function __construct()
    {
        // Register string translations
        CRM_WpCiviMosaico_Utils::RegisterPluginTranslationPhrases();

        // Register PHP and template directories.
        add_action('civicrm_config', array( $this, 'register_directories' ), 10);

        // register XML menu
        add_action('civicrm_xmlMenu', array( $this, 'hook_xml_menu' ));

        // register tokens
        add_action('civicrm_tokens', array( $this, 'hook_tokens' ));
        add_action('civicrm_tokenValues', array( $this, 'hook_token_values' ), 10, 5);

        // Mosaico configuration hooks
        add_action('civicrm_mosaicoPlugins', array( $this, 'hook_mosaico_plugins__' ));
        add_action('civicrm_mosaicoBaseTemplates', array( $this, 'hook_mosaico_base_templates' ));
    }

    public function glob($pattern)
    {
        $result = glob($pattern);
        return is_array($result) ? $result : array();
    }

    /*
     * XML menu hook. Sets up URL routes to ajax handler, image upload and wordpress media gallery
     */
    public function hook_xml_menu(&$files)
    {
        foreach ($this->glob(WP_CIVI_MOSAICO_PATH . WP_CIVI_MOSAICO_XML_PATH . '/Menu/*.xml') as $file) {
            $files[] = $file;
        }
    }

    /*
     * Token hook. Sets up two frequently used date tokens
     */
    public function hook_tokens(&$tokens)
    {
        $tokens[ 'date' ] = [
          'date.date_short' => CRM_WpCiviMosaico_Utils::__(WP_Civi_Mosaico_TermPhrase_FourDigitYear),
          'date.date_med' => CRM_WpCiviMosaico_Utils::__(WP_Civi_Mosaico_TermPhrase_FullDate)
        ];
    }

    /*
     * Token value hook
     */
    public function hook_token_values(&$values, $cids, $job = null, $tokens = [], $context = null)
    {
        if (!empty($tokens[ 'date' ])) {
            $date = [
              'date.date_short' => date('Y'),
              'date.date_med' => date('d.m.Y'),
            ];
            foreach ($cids as $cid) {
                $values[ $cid ] = empty($values[ $cid ]) ? $date : $values[ $cid ] + $date;
            }
        }
    }

    /**
     * Implementation of hook_civicrm_mosaicoBaseTemplates to insert our enhanced versafix template
     */
    public function hook_mosaico_base_templates(&$templatesArray)
    {
        $templatesDir = WP_CIVI_MOSAICO_PATH . '/mosaico-templates';
        $templatesUrl = WP_CIVI_MOSAICO_URL . '/mosaico-templates';

        foreach (glob("{$templatesDir}/*", GLOB_ONLYDIR) as $dir) {
            $template = basename($dir);
            $templateHTML = $templatesUrl . "/{$template}/template-{$template}.html";
            $templateThumbnail = $templatesUrl . "/{$template}/edres/_full.png";
            $templatesArray[ $template ] = array(
        'name' => $template,
        'title' => $template,
        'thumbnail' => $templateThumbnail,
        'path' => $templateHTML
      );
        }
    }

    /*
     * Plugin hook provided by the CiviCRM de.ergomation.civi-mosaico-plugininterface extension
     */
    public function hook_mosaico_plugins__(&$plugins)
    {
        $plugin = 'wordpressPostsWidgetPlugin';

        $config = [];
        $scripts = [];
        $styles = [];

        $cacheCode = CRM_Core_Resources::singleton()->getCacheCode();
        $scripts[] = WP_CIVI_MOSAICO_URL . "js/wppostid.js?r=" . $cacheCode;
        $styles[] = WP_CIVI_MOSAICO_URL . "css/wppostid.css?r=" . $cacheCode;

        $config[ 'imgProcessorBackend' ] = CRM_WpCiviMosaico_Utils::getUrl('civicrm/wp_civi_mosaico/img', null, true);
        $config[ 'fileuploadConfig' ][ 'url' ] = CRM_WpCiviMosaico_Utils::getUrl('civicrm/wp_civi_mosaico/upload', null, false);

        // add bullet and numbered lists to the default menu bar
        $config[ 'tinymceConfigFull' ][ 'toolbar1' ] = 'bold italic forecolor backcolor hr styleselect removeformat bullist numlist | civicrmtoken | link unlink | pastetext code';

        $plugins[ 'wordpressPostsWidgetPlugin'] = [
            'plugin' => $plugin,
            'scripts' => $scripts,
            'styles' => $styles,
            'config' => $config
        ];
    }

    /*
     * Register directories that CiviCRM searches in.
     */
    public function register_directories(&$config)
    {
        Civi::service('dispatcher')->addListener(
            'hook_civicrm_coreResourceList',
            array( $this, 'register_php_directory' ),
            -200
        );
    }

    /*
     * Register directory that CiviCRM searches in for new PHP files.
     */
    public function register_php_directory($event, $hook)
    {
        // Kick out if no CiviCRM.
        if (! civi_wp()->initialize()) {
            return;
        }

        // Define our custom path.
        $custom_path = WP_CIVI_MOSAICO_PATH . WP_CIVI_MOSAICO_PHP_PATH;

        // Add to include path.
        $include_path = $custom_path . PATH_SEPARATOR . get_include_path();
        set_include_path($include_path);
    }
}


/*
 * Load plugin if not yet loaded and return reference.
 */
function wp_civi_mosaico()
{

    // Declare as static.
    static $wp_civi_mosaico;

    // Instantiate plugin if not yet instantiated.
    if (! isset($wp_civi_mosaico)) {
        $wp_civi_mosaico = new WP_Civi_Mosaico();
    }

    return $wp_civi_mosaico;
}

// Load only when CiviCRM has loaded.
add_action('civicrm_instance_loaded', 'wp_civi_mosaico');


/*
 * Admin interface
 */
class WP_Civi_Mosaico_Admin
{
    const SETTINGS_PAGE_TITLE  = 'Wordpress content in CiviCRM with Mosaico - Flush image cache';
    const PLUGIN_MENU_TITLE    = 'Flush WP CiviCRM Mosaico image cache';
    const MAIN_PAGE_ID         = 'wp_civi_mosaico_adm';

    public function __construct()
    {
        add_action('admin_menu', array( $this, 'admin_add_page' ));
    }

    public function admin_add_page()
    {
        // submenu in "Tools" main menu
        add_submenu_page(
            'tools.php',
            self::SETTINGS_PAGE_TITLE,
            self::PLUGIN_MENU_TITLE,
            'edit_others_posts',
            self::MAIN_PAGE_ID,
            array( $this, 'admin_page_main' ),
            99
        );
    }

    public function admin_page_main()
    {
        if (isset($_GET[ 'flush' ]) && (1 == $_GET[ 'flush' ])) {
            CRM_WpCiviMosaico_Utils::flushCache(); ?>
            <div class="notice notice-info is-dismissible">
              <p><?php _e('Image cache flushed'); ?></p>
            </div>
            <?php
        } ?>
            <div class="wrap">
              <h1><?php echo self::SETTINGS_PAGE_TITLE; ?></h1>
              <a href="?page=<?php echo self::MAIN_PAGE_ID; ?>&flush=1" class="wp-core-ui button is-large"><?php _e('Flush image cache') ?></a></h2>
            </div>
            <?php
    }
}

new WP_Civi_Mosaico_Admin();
