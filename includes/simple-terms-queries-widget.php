<?php

namespace CDevelopers\Query\Terms;

if ( ! defined( 'ABSPATH' ) )
	exit; // disable direct access

class Simple_Terms_Queries_Widget extends \WP_Widget
{
	/**
	 * Set global widget options
	 */
	public function __construct()
	{
		$this->alt_option_name = 'simple-terms-queries-widget';
		parent::__construct(
			'st-queries-widget',               // $this->id_base
			__( 'Simple Terms Queries' ), // $this->name
			array(                             // $this->widget_options
				'classname'                   => $this->alt_option_name,
				'description'                 => __( '' ),
				'customize_selective_refresh' => true,
			),
			array()                            // $this->control_options
		);
	}

	/**
	 * Register hook from Utils
	 */
	public static function register_himself() {
		register_widget( __CLASS__ );
	}

	public static function _defaults()
	{
		$def = array(
			'title'          => __( 'Terms' ),
			'orderby'        => 'name',
			'order'          => 'asc',
			// 'include'        => array(),
			'exclude'        => array(),
			// 'show_thumb'     => 0,
			// 'thumb_size'     => 0,
			// 'thumb_size_w'   => 55,
			// 'thumb_size_h'   => 55,
			'show_desc'      => 0,
			'desc_length'    => 15,
			'list_style'     => 'ul',
			'show_count'     => 0,
			'taxanomy'       => 'category',
			'number'         => -1,
			// 'show_title'     => 0,
			'hierarchical'   => 1,
		);

		return $def;
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
        $header = ob_get_clean();
        return $header;
    }

	/**
	 * @WordPress builtin
	 * Admin widget form
	 */
	public function form( $instance )
	{
		$instance = wp_parse_args( (array) $instance, self::_defaults() );
		$active = array();
		foreach ($instance as $key => $val) {
			$active[ $this->get_field_name( $key ) ] = $val;
		}

		$params = array(
			'widget' => $this,
			'instance' => $instance,
		);

		$global_data = Utils::get_settings( 'global.php', $params );
		$thumbnail_data = Utils::get_settings( 'thumbnail.php', $params );
		$template_data = Utils::get_settings( 'template.php', $params );
		$query_data = Utils::get_settings( 'query.php', $params );
		$terms_data = Utils::get_settings( 'terms.php', array(
			'widget' => $this,
			'instance' => $instance,
			'action' => 'exclude'
			) );

		include( Utils::get_plugin_dir('includes') . '/widget-form.php' );
	}

	/**
	 * Terms Query for Widget list
	 */
	public static function get_widget_categories( $instance, $widget )
    {
        $result = array();
        $args = array(
            'taxonomy'      => $instance['taxanomy'],
            'orderby'       => $instance['orderby'],
            'order'         => $instance['order'],
            'hide_empty'    => ! $instance['show_empty'] ? true : false,
            // 'object_ids'    => null,
            // 'include'       => array(),
            'exclude'       => $instance['exclude'],
            // 'exclude_tree'  => array(),
            'number'        => '',
            // 'fields'        => 'all',
            // 'count'         => false,
            // 'slug'          => '',
            // 'parent'         => '',
            'hierarchical'  => 1, // $instance['hierarchical'],
            // 'child_of'      => 0,
            // 'get'           => '',
            // 'name__like'    => '',
            // 'pad_counts'    => false,
            // 'offset'        => '',
            // 'search'        => '',
            // 'cache_domain'  => 'core',
            // 'name'          => '',
            // 'childless'     => false,
            // 'update_term_meta_cache' => true,
            // 'meta_query'    => '',
        );

        $terms = get_terms( $args );
        if ( ! is_wp_error( $terms ) ) {
            if( $instance['hierarchical'] ) {
                Utils::sort_terms_hierarchicaly($terms, $result);
            }
            else {
                foreach ($terms as $term) {
                    $result[ $term->term_id ] = $term;
                }
            }
        }

        return $result;
    }

	/**
	 * @WordPress builtin
	 * Public widget views
	 */
	public function widget( $args, $instance )
	{
		if ( ! isset( $args['widget_id'] ) )
			$args['widget_id'] = $this->id;

		$instance = wp_parse_args( (array) $instance, array_merge(self::_defaults(), array(
			'id_base'       => $this->id_base,
			'widget_number' => $this->number,
			'widget_id'     => $this->id,
		) ) );

		$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
		$categories = self::get_widget_categories( $instance, $this );

		Simple_Terms_Queries_Public::display_widget($this, $title, $categories, $instance, $args);
	}

	/**
	 * @WordPress builtin
	 * Validate
	 */
	public function update( $new_instance, $instance )
	{
		$instance = (array) $instance;
		$fields = array_merge(
			Utils::get_settings( 'global.php',    array('widget' => $this, 'instance' => $instance) ),
			Utils::get_settings( 'template.php',  array('widget' => $this, 'instance' => $instance) ),
			Utils::get_settings( 'thumbnail.php', array('widget' => $this, 'instance' => $instance) ),
			Utils::get_settings( 'query.php',     array('widget' => $this, 'instance' => $instance) )
		);

		// file_put_contents(__DIR__ . '/some.log', print_r($new_instance, 1));
		$res = array();
		foreach ($fields as $field) {
			if( ! isset($field['field_id']) ) {
				continue;
			}

			$defaults = self::_defaults();
			if( ! isset($new_instance[ $field['field_id'] ]) && isset($defaults[ $field['field_id'] ]) ) {
				$instance[ $field['field_id'] ] = $defaults[ $field['field_id'] ];
				continue;
			}

			// if( isset($new_instance[ $field['name'] ])
			// 	&& is_array($new_instance[ $field['name'] ]) )
			// {
			// }

			switch ($field['type']) {
				case 'checkbox':
					$instance[ $field['field_id'] ] = ( isset($new_instance[ $field['field_id'] ]) ) ? 1 : 0;
					break;

				case 'number':
					if( isset($new_instance[ $field['field_id'] ]) )
						$instance[ $field['field_id'] ] = intval( $new_instance[ $field['field_id'] ] );
					break;

				case 'select':
				case 'text':
				// default:
					if( isset($new_instance[ $field['field_id'] ]) ) {
						$instance[ $field['field_id'] ] = sanitize_text_field( $new_instance[ $field['field_id'] ] );
					}
					break;
			}
		}

		// file_put_contents(__DIR__ . '/debug.log', print_r($res,1) );

		// general
		// $instance['title']     = sanitize_text_field( $new_instance['title'] );
		// $instance['orderby']   = sanitize_text_field( $new_instance['orderby'] );
		// $instance['order']     = sanitize_text_field( $new_instance['order'] );
		// $instance['number']       = (int) $new_instance['number'];
		// $instance['hide_title'] = absint( $new_instance['hide_title'] );

		// taxonomies & filters
		if( !empty( $new_instance['exclude'] ) && is_array( $new_instance['exclude'] ) ) {
			$instance['exclude'] = array_filter( array_map( 'absint', $new_instance['exclude'] ) );
		}
		// else {
		// 	// $instance['tax_term'] = absint( $new_instance['tax_term'] );
		// }


		// thumbnails
		// $instance['show_thumb']   = isset( $new_instance['show_thumb'] ) ? 1 : 0 ;
		// $instance['thumb_size']   = sanitize_text_field( $new_instance['thumb_size'] );

		// $_thumb_size_w            = absint( $new_instance['thumb_size_w'] );
		// $instance['thumb_size_w'] = ( $_thumb_size_w < 1 ) ? 55 : $_thumb_size_w ;

		// $_thumb_size_h            = absint( $new_instance['thumb_size_h'] );
		// $instance['thumb_size_h'] = ( $_thumb_size_h < 1 ) ? $_thumb_size_w : $_thumb_size_h ;

		// excerpts
		// $instance['show_desc']    = isset( $new_instance['show_desc'] ) ? 1 : 0 ;
		// $instance['desc_length']  = absint( $new_instance['desc_length'] );

		// list format
		// $instance['list_style']   = ( '' !== $new_instance['list_style'] ) ? sanitize_key( $new_instance['list_style'] ) : 'ul ';

		// post count
		// $instance['show_count']   = isset( $new_instance['show_count'] ) ? 1 : 0 ;

		// styles & layout
		// $instance['taxanomy'] = isset( $new_instance['taxanomy'] ) ?
			// sanitize_text_field( $new_instance['taxanomy'] ) : 'category';

		// build out the instance for devs
		$instance['id_base']       = $this->id_base;
		$instance['widget_number'] = $this->number;
		$instance['widget_id']     = $this->id;

		return $instance;
	}
}
