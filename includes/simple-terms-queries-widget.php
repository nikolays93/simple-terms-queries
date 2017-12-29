<?php

namespace CDevelopers\Query\Terms;

if ( ! defined( 'ABSPATH' ) )
	exit; // disable direct access

class Simple_Terms_Queries_Widget extends \WP_Widget
{
	public function __construct()
	{
		$this->alt_option_name = 'simple-terms-queries';
		parent::__construct(
			'simple-terms-queries',       // $this->id_base
			__( 'Simple Terms Queries' ), // $this->name
			array(                        // $this->widget_options
				'classname'                   => $this->alt_option_name,
				'description'                 => __( '' ),
				'customize_selective_refresh' => true,
			),
			array()                       // $this->control_options
		);
	}

	public static function _defaults()
	{
		// checkbox must be 0 or need will be update form file
		$d = array(
			'title'          => __( 'Terms' ),
			'orderby'        => 'name',
			'order'          => 'asc',
			'tax_term'       => '',
			'show_thumb'     => 0,
			'thumb_size'     => 0,
			'thumb_size_w'   => 55,
			'thumb_size_h'   => 55,
			'show_desc'      => 0,
			'desc_length'    => 15,
			'list_style'     => 'ul',
			'show_count'     => 0,
			'taxanomy'       => 'category',
			'number'         => -1,
			'show_title'     => 0,
			'hierarchical'   => 1,
			);

		return $d;
	}

	public function form( $instance )
	{
		$instance = wp_parse_args( (array) $instance, self::_defaults() );

		include( Utils::get_plugin_dir('includes') . '/widget-form.php' );
	}

	public function widget( $args, $instance )
	{
		if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		$d = self::_defaults();
		$instance = wp_parse_args( (array) $instance, array_merge($d, array(
			'id_base'       => $this->id_base,
			'widget_number' => $this->number,
			'widget_id'     => $this->id,
		) ) );

		$_title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );

		$categories = Utils::get_widget_categories( $instance, $this );

		echo $args['before_widget'];

		if( $_title ) {
			echo $args['before_title'] . $_title . $args['after_title'];
		};
		?>

		<div class="st-widget st-wrap">

			<?php
			if( ! empty( $categories ) || ! false ) :

				Widget_Views::recursive_list_item( $categories, $instance );

			endif;
			?>

		</div><!-- /.advanced-categories-wrap -->

		<?php echo $args['after_widget']; ?>

	<?php
		// $fields = array_merge(
		// 	Utils::get_settings( 'global.php',    array('widget' => $this, 'instance' => $instance) ),
		// 	Utils::get_settings( 'template.php',  array('widget' => $this, 'instance' => $instance) ),
		// 	Utils::get_settings( 'thumbnail.php', array('widget' => $this, 'instance' => $instance) ),
		// 	Utils::get_settings( 'query.php',     array('widget' => $this, 'instance' => $instance) )
		// );

		// echo "<pre>";
		// var_dump( $fields );
		// echo "</pre>";
	}

	// validate
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
		// if( is_array( $new_instance['tax_term'] ) ) {
		// 	$_tax_terms = array();
		// 	foreach( $new_instance['tax_term'] as $key => $val ) {
		// 		if( is_array( $val ) ){
		// 			$_val = array_map( 'absint', $val );
		// 			$_val = array_filter( $_val );
		// 		} else {
		// 			$_val = absint( $val );
		// 		}
		// 		$_tax_terms[$key] = $_val;
		// 	}
		// 	$instance['tax_term'] = $_tax_terms;
		// } else {
		// 	$instance['tax_term'] = absint( $new_instance['tax_term'] );
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
