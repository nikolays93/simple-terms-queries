<?php

/*
Plugin Name: Simple Terms Queries
Plugin URI: https://github.com/nikolays93
Description: Build Terms list widget
Version: 0.0.1
Author: NikolayS93
Author URI: https://vk.com/nikolays_93
Author EMAIL: nikolayS93@ya.ru
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

namespace CDevelopers\Query\Terms;

if ( ! defined( 'ABSPATH' ) )
  exit; // disable direct access

const DOMAIN = 'simple-terms-queries';

class Utils
{
    const OPTION = 'st-queries';
    const SHORTCODE = 'st-queries';

    private static $initialized;
    private static $settings;
    private function __construct() {}
    private function __clone() {}

    static function activate() { // add_option( self::OPTION, array() );
    }
    static function uninstall() { // delete_option(self::OPTION);
    }

    private static function include_required_classes()
    {
        $dir_include = self::get_plugin_dir('includes');
        $dir_class = self::get_plugin_dir('classes');

        $classes = array(
            __NAMESPACE__ . '\WP_Admin_Forms'     => $dir_class . '/wp-admin-forms.php',
        );

        foreach ($classes as $classname => $dir) {
            if( ! class_exists($classname) ) {
                self::load_file_if_exists( $dir );
            }
        }

        // includes
        self::load_file_if_exists( $dir_include . '/simple-terms-queries-widget.php' );
        self::load_file_if_exists( $dir_include . '/simple-terms-queries-public.php' );
        self::load_file_if_exists( $dir_include . '/simple-terms-queries-shortcode.php' );
    }

    public static function register_shortcode( $atts = array(), $content = '' ) {
        $atts = shortcode_atts( array(
            'id' => 'value',
            ), $atts, self::SHORTCODE );
    }

    public static function initialize()
    {
        if( self::$initialized ) {
            return false;
        }

        load_plugin_textdomain( DOMAIN, false, DOMAIN . '/languages/' );
        self::include_required_classes();

        add_action('widgets_init', array(__NAMESPACE__ . '\Simple_Terms_Queries_Widget', 'register_himself'));
        add_shortcode( self::SHORTCODE, array(__NAMESPACE__ . '\Simple_Terms_Queries_Shortcode', 'init') );

        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueues' ) );
        add_action( 'customize_controls_enqueue_scripts', array( __CLASS__, 'admin_enqueues' ) );

        self::$initialized = true;
    }

    /**
     * Подключаем нужные скрипты
     */
    public static function admin_enqueues( $hook )
    {
        global $pagenow;

        $enqueue = false;
        if( 'customize.php' == $pagenow || 'widgets.php' == $pagenow || 'widgets.php' == $hook ){
            $enqueue = true;
        };

        wp_enqueue_style(
            'widget-panels',
            self::get_plugin_url('assets') . '/widget-panels.css',
            array(),
            '1.0.0',
            'all'
        );

        if( ! $enqueue ){
            return;
        };

        wp_enqueue_script( 'widget-panels', self::get_plugin_url('assets') . '/widget-panels.js', array( 'jquery' ), '', true );

        #wp_enqueue_script( 'acatw-admin-scripts', self::get_plugin_url() . 'js/admin.js', array( 'widget-panels' ), '', true );
    }

    /**
     * Записываем ошибку
     */
    public static function write_debug( $msg, $dir )
    {
        if( ! defined('WP_DEBUG_LOG') || ! WP_DEBUG_LOG )
            return;

        $dir = str_replace(__DIR__, '', $dir);
        $msg = str_replace(__DIR__, '', $msg);

        $date = new \DateTime();
        $date_str = $date->format(\DateTime::W3C);

        if( $handle = @fopen(__DIR__ . "/debug.log", "a+") ) {
            fwrite($handle, "[{$date_str}] {$msg} ({$dir})\r\n");
            fclose($handle);
        }
        elseif (defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY) {
            echo sprintf( __('Can not have access the file %s (%s)', DOMAIN),
                __DIR__ . "/debug.log",
                $dir );
        }
    }

    /**
     * Загружаем файл если существует
     */
    public static function load_file_if_exists( $file_array, $args = array(), $once = false )
    {
        $cant_be_loaded = __('The file %s can not be included', DOMAIN);
        if( is_array( $file_array ) ) {
            $result = array();
            foreach ( $file_array as $id => $path ) {
                if ( ! is_readable( $path ) ) {
                    self::write_debug(sprintf($cant_be_loaded, $path), __FILE__);
                    continue;
                }

                $result[] = ($once) ? include_once( $path ) : include( $path );
            }
        }
        else {
            if ( ! is_readable( $file_array ) ) {
                self::write_debug(sprintf($cant_be_loaded, $file_array), __FILE__);
                return false;
            }

            $result = ($once) ? include_once( $file_array ) : include( $file_array );
        }

        return $result;
    }

    public static function get_plugin_dir( $path = false )
    {
        $result = __DIR__;

        switch ( $path ) {
            case 'classes': $result .= '/includes/classes'; break;
            case 'settings': $result .= '/includes/settings'; break;
            default: $result .= '/' . $path;
        }

        return $result;
    }

    public static function get_plugin_url( $path = false )
    {
        $result = plugins_url(basename(__DIR__) );

        switch ( $path ) {
            default: $result .= '/' . $path;
        }

        return $result;
    }

    /**
     * Получает настройку из self::$settings или из кэша или из базы данных
     */
    public static function get( $prop_name, $default = false )
    {
        if( ! self::$settings )
            self::$settings = get_option( self::OPTION, array() );

        if( 'all' === $prop_name ) {
            if( is_array(self::$settings) && count(self::$settings) )
                return self::$settings;

            return $default;
        }

        return isset( self::$settings[ $prop_name ] ) ? self::$settings[ $prop_name ] : $default;
    }

    public static function get_settings( $filename, $args = array() )
    {

        return self::load_file_if_exists( self::get_plugin_dir('settings') . '/' . $filename, $args );
    }

    /**
     * Sanitize option values (escape html) and native wordpress sanitize keys.
     * @param  Array   $options list of options
     * @param  boolean $sort    need sorts?
     * @return Array   $options results
     */
    public static function sanitize_select_array( $options, $sort = false )
    {
        $options = ( ! is_array( $options ) ) ? (array) $options : $options ;

        // Clean the values (since it can be filtered by other plugins)
        $options = array_map( 'esc_html', $options );

        // Flip to clean the keys (used as <option> values in <select> field on form)
        $options = array_flip( $options );
        $options = array_map( 'sanitize_key', $options );

        // Flip back
        $options = array_flip( $options );

        if( $sort ) {
            asort( $options );
        };

        return $options;
    }

    /**
     * Recursively sort an array of taxonomy terms hierarchically. Child categories will be
     * placed under a 'children' member of their parent term.
     * @param Array   $cats     taxonomy term objects to sort
     * @param Array   $into     result array to put them in
     * @param integer $parentId the current parent ID to put them in
     */
    static function sort_terms_hierarchicaly(Array &$cats, Array &$into, $parentId = 0)
    {
        foreach ($cats as $i => $cat) {
            if ($cat->parent == $parentId) {
                $into[$cat->term_id] = $cat;
                unset($cats[$i]);
            }
        }

        foreach ($into as $topCat) {
            $topCat->children = array();
            self::sort_terms_hierarchicaly($cats, $topCat->children, $topCat->term_id);
        }
    }
}

register_activation_hook( __FILE__, array( __NAMESPACE__ . '\Utils', 'activate' ) );
register_uninstall_hook( __FILE__, array( __NAMESPACE__ . '\Utils', 'uninstall' ) );
// register_deactivation_hook( __FILE__, array( __NAMESPACE__ . '\Utils', 'deactivate' ) );

add_action( 'plugins_loaded', array( __NAMESPACE__ . '\Utils', 'initialize' ), 10 );
