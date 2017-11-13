<?php

/*
Plugin Name: Simple Terms Queries Widget
Description: Build Terms list widget
Plugin URI: http://#
Version: 1.0
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

    private static $initialized;
    private static $settings;
    private function __construct() {}
    private function __clone() {}

    static function uninstall() { delete_option(self::OPTION); }
    static function activate() { add_option( self::OPTION, array() ); }

    private static function include_required_classes()
    {
        $classes = self::get_plugin_dir('classes');
        $includes = self::get_plugin_dir('includes');
        $arrClasses = array(
            __NAMESPACE__ . '\WP_Admin_Forms'     => $classes . '/wp-admin-forms.php',
            );

        foreach ($arrClasses as $classname => $path) {
            if( ! class_exists($classname) ) {
                require_once $path;
            }
        }

        // includes
        $arrIncludes = array(
        	$includes . '/simple-terms-queries-widget.php',
        	$includes . '/simple-terms-queries-views.php',
        	);
        self::load_file_if_exists( $arrIncludes );
    }

    public static function initialize()
    {
        if( self::$initialized ) {
            return false;
        }

        load_plugin_textdomain( DOMAIN, false, DOMAIN . '/languages/' );
        self::include_required_classes();

        add_action( 'widgets_init', function(){ register_widget( __NAMESPACE__ . '\Simple_Terms_Queries_Widget' ); } );

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueues' ) );
		add_action( 'customize_controls_enqueue_scripts', array( __CLASS__, 'admin_enqueues' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueues' ) );
		add_action( 'customize_controls_enqueue_scripts', array( __CLASS__, 'admin_enqueues' ) );

        self::$initialized = true;
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

        $handle = fopen(__DIR__ . "/debug.log", "a+");
        fwrite($handle, "[{$date_str}] {$msg} ({$dir})\r\n");
        fclose($handle);
    }

    /**
     * Загружаем файл если существует
     */
    public static function load_file_if_exists( $file_array, $args = array() )
    {
        $cant_be_loaded = __('The file %s can not be included', DOMAIN);
        if( is_array( $file_array ) ) {
            $result = array();
            foreach ( $file_array as $id => $path ) {
                if ( ! is_readable( $path ) ) {
                    self::write_debug(sprintf($cant_be_loaded, $path), __FILE__);
                    continue;
                }

                $result[] = include( $path );
            }
        }
        else {
            if ( ! is_readable( $file_array ) ) {
                self::write_debug(sprintf($cant_be_loaded, $file_array), __FILE__);
                return false;
            }

            $result = include( $file_array );
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

    public static function sample_description()
    {
        $description = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Amet officiis laborum aspernatur ipsum quos. Similique doloribus reprehenderit perspiciatis aliquid mollitia, id quae earum ipsa harum delectus deserunt vel, non, alias. Lorem ipsum dolor sit amet, consectetur adipisicing elit. Asperiores reiciendis nulla odio harum vero, nisi quos quaerat velit. Ipsum nam, eius illum odio nulla aliquid, distinctio excepturi esse molestiae modi.' . "\n";
        $description.= 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Eos assumenda nulla illum, eligendi inventore odit pariatur repellat eius quaerat error, illo ad tenetur quis ducimus. Saepe repellat maiores eaque voluptates!';

        return $description;
    }

    public static function get_allowed_image_sizes( $fields = 'name' )
    {
        global $_wp_additional_image_sizes;
        $wp_defaults = array( 'thumbnail', 'medium', 'medium_large', 'large' );

        $_sizes = get_intermediate_image_sizes();

        if( count( $_sizes ) ) {
            sort( $_sizes );
            $_sizes = array_combine( $_sizes, $_sizes );
        }

        $_sizes = apply_filters( 'acatw_allowed_image_sizes', $_sizes );
        $sizes = self::sanitize_select_array( $_sizes );

        if( count( $sizes )&& 'all' === $fields ) {

            $image_sizes = array();
            natsort( $sizes );

            foreach ( $sizes as $_size ) {
                if ( in_array( $_size, $wp_defaults ) ) {
                    $width = absint( get_option( "{$_size}_size_w" ) );
                    $height = absint(  get_option( "{$_size}_size_h" ) );
                    $dimensions = ' (' . $width . ' x ' . $height . ')';

                    $image_sizes[ $_size ] = __( ucfirst($_size) ) . $dimensions;
                    // $image_sizes[$_size]['crop']   = (bool) get_option( "{$_size}_crop" );
                } else if( isset( $_wp_additional_image_sizes[ $_size ] )  ) {
                    $width = absint( $_wp_additional_image_sizes[ $_size ]['width'] );
                    $height = absint( $_wp_additional_image_sizes[ $_size ]['height'] );
                    $dimensions = ' (' . $width . ' x ' . $height . ')';

                    $image_sizes[ $_size ] = __( ucfirst($_size) ) . $dimensions;
                    // $image_sizes[$_size]['crop']   = (bool) $_wp_additional_image_sizes[ $_size ]['crop'];
                }
            }

            $sizes = $image_sizes;

        };

        return $sizes;
    }

    public static function get_image_size( $size = 'thumbnail', $fields = 'all' )
    {
        $sizes = self::get_allowed_image_sizes( $_fields = 'all' );

        if( count( $sizes ) && isset( $sizes[$size] ) ) :
            if( 'all' === $fields ) {
                return $sizes[$size];
            } else {
                return $sizes[$size]['name'];
            }
        endif;

        return false;
    }

    public static function get_term_thumbnail( $term = 0, $instance = array() )
    {

        if ( empty( $term ) ) {
            return '';
        }

        // future compatible?
        $meta_field = apply_filters( 'acatw_thumb_meta_field', '_thumbnail_id', $term, $instance );

        $_thumbnail_id = get_term_meta( $term->term_id, $meta_field, true );
        $_thumbnail_id = absint( $_thumbnail_id );

        // no thumbnail
        // @todo placeholder?
        if( ! $_thumbnail_id ) {
            return '';
        }

        $_classes = array();
        $_classes[] = 'acatw-term-image';
        $_classes[] = 'acatw-alignleft';

        // was registered size selected?
        $_size = $instance['thumb_size'];

        // custom size entered
        if( empty( $_size ) ){
            $_w = absint( $instance['thumb_size_w'] );
            $_h = absint( $instance['thumb_size_h'] );
            $_size = "acatw-thumbnail-{$_w}-{$_h}";
        }

        // check if the size is registered
        $_size_exists = self::get_image_size( $_size );

        if( $_size_exists ){
            $_get_size = $_size;
            $_w = absint( $_size_exists['width'] );
            $_h = absint( $_size_exists['height'] );
            $_classes[] = "size-{$_size}";
        } else {
            $_get_size = array( $_w, $_h);
        }

        $classes = apply_filters( 'acatw_term_thumb_class', $_classes, $term, $instance );
        $classes = ( ! is_array( $classes ) ) ? (array) $classes : $classes ;
        $classes = array_map( 'sanitize_html_class', $classes );

        $class_str = implode( ' ', $classes );

        $_thumb = wp_get_attachment_image(
            $_thumbnail_id,
            $_get_size,
            false,
            array(
                'class' => $class_str,
                'alt' => $term->name,
                )
            );

        $thumb = apply_filters( 'acatw_term_thumbnail', $_thumb, $term, $instance );

        return $thumb;

    }

    public static function sanitize_select_array( $options, $sort = true )
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

    public static function get_widget_categories( $instance, $widget )
    {
        if( empty( $instance['tax_term'] ) ) {
            return array();
        }

        $_include_taxonomies = array();
        $_include_ids = array();

        foreach( $instance['tax_term'] as $taxonomy => $term_ids ) {
            $_include_taxonomies[] = $taxonomy;
            array_walk_recursive( $term_ids, function( $value, $key ) use ( &$_include_ids ) {
                $_include_ids[$key] = $value;
            } );
        }

        $r = array(
            'taxonomy'   => $_include_taxonomies,
            'orderby'    => $instance['orderby'],
            'order'      => $instance['order'],
            'hide_empty' => 0,
            'include'    => $_include_ids
        );

        $categories = get_terms( $_include_taxonomies, $r );

        if ( is_wp_error( $categories ) ) {
            $categories = array();
        } else {
            $categories = (array) $categories;
        }

        return $categories;

    }

    public static function build_section_header( $title = 'Settings' )
    {
        ob_start();
        ?>

        <div class="widget-panel-section-top">
            <div class="widget-panel-top-action">
                <a class="widget-panel-action-indicator hide-if-no-js" href="#"></a>
            </div>
            <div class="widget-panel-section-title">
                <h4 class="widget-panel-section-heading">
                    <?php printf( __( '%s', 'advanced-categories-widget' ), $title ); ?>
                </h4>
            </div>
        </div>

        <?php
        $field = ob_get_clean();

        return $field;
    }

    public static function build_term_select( $taxonomy, $label, $instance, $widget )
    {
        $args = apply_filters( 'acatw_build_term_select_args', array( 'hide_empty' => 0, 'number' => 99 ) );
        $args['fields'] = 'all'; // don't allow override
        $args['taxonomy'] = $taxonomy; // don't allow override
        $_terms = get_terms( $taxonomy, $args );

        if( empty( $_terms ) || is_wp_error( $_terms ) ) {
            return;
        }
        ?>

        <?php printf( '<p>%s:</p>', sprintf( __( '%s', 'advanced-categories-widget' ), $label ) ); ?>

        <div class="widget-panel-multi-check">
            <?php foreach( $_terms as $_term ) : ?>
                <?php
                $checked = (  ! empty( $instance['tax_term'][$_term->taxonomy][$_term->term_id] )) ? 'checked="checked"' : '' ;

                printf( '<input id="%1$s" name="%2$s" value="%3$s" type="checkbox" %4$s/><label for="%1$s">%5$s (%6$s)</label><br />',
                    $widget->get_field_id( 'tax_term-' . $taxonomy . '-' . $_term->term_id ),
                    $widget->get_field_name( 'tax_term' ) . '['.$taxonomy.']['.$_term->term_id.']',
                    $_term->term_id,
                    $checked,
                    sprintf( __( '%s', 'advanced-categories-widget' ), $_term->name ),
                    $_term->count
                );
                ?>
            <?php endforeach; ?>
        </div>
        <?php
    }
}

register_activation_hook( __FILE__, array( __NAMESPACE__ . '\Utils', 'activate' ) );
register_uninstall_hook( __FILE__, array( __NAMESPACE__ . '\Utils', 'uninstall' ) );
// register_deactivation_hook( __FILE__, array( __NAMESPACE__ . '\Utils', 'deactivate' ) );

add_action( 'plugins_loaded', array( __NAMESPACE__ . '\Utils', 'initialize' ), 10 );
