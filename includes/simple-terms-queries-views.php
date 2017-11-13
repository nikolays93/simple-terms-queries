<?php

namespace CDevelopers\Query\Terms;

if ( ! defined( 'ABSPATH' ) )
	exit; // disable direct access

class Widget_Views
{

	private function __construct(){}

	public static function start_list( $instance, $categories, $echo = true )
	{
		if( ! in_array($instance['list_style'], array('div', 'ol', 'ul')) ) {
			$instance['list_style'] = 'ul';
		}

		$start = sprintf( '<%1$s class="simple-terms-list">', $instance['list_style'] );

		if( ! $echo )
			return $start;

		echo $start;
	}

	public static function start_list_item( $term, $instance, $categories, $echo = true )
	{
		if( ! $term ) return;

		$item_id    = self::get_item_id( $term, $instance );
		$item_class      = 'simple-terms-list-item ' . self::get_item_class( $term, $instance );

		if( 'div' !== ($list_item = $instance['list_style']) ) {
			$list_item = 'li';
		}

		$start = sprintf( '<%s id="%s" class="%s">', $list_item, $item_id, $item_class );

		if( ! $echo )
			return $start;

		echo $start;
	}

	public static function list_item( $term, $instance, $categories, $echo = true )
	{

		$args = wp_parse_args( $args, array(
			'item_id'    => self::get_item_id( $term, $instance ),
			'item_class' => self::get_item_class( $term, $instance ),
			'item_desc'  => self::get_term_excerpt( $term, $instance ),
			'show_thumb' => '',
			'show_count' => '',
			) );

		if( ! empty( $instance['show_thumb'] ) ) {
			$args['show_thumb'] = self::the_item_thumbnail_div( $term, $instance, false );
		}

		if( ! empty( $instance['show_count'] ) ) {
			$args['show_count'] = self::the_term_post_count( $term, $instance, false );
		}

		$result = array();
		$result[] = sprintf('<div id="term-%s" class="%s">', $args['id'], $args['item_class']);
		$result[] = $args['show_thumb'];
		$result[] = printf( '<h3 class="term-title simple-term-title"><a href="%s" rel="bookmark">%s</a></h3>',
					esc_url( get_term_link( $term ) ),
					$term->name
				);
		$result[] = $args['show_count'];
		if( $args['show_desc'] ) {
			$result[] = '<span class="term-summary acatw-term-summary">';
			$result[] = $args['item_desc'];
			$result[] = '</span><!-- /.term-summary -->';
		}
		$result[] = "</div><!-- #term-## -->";

		$result = implode("\n", $result);

		if( ! $echo ) {
			return $result;
		}

		echo $result;
	}

	public static function end_list_item( $term, $instance, $categories, $echo = true )
	{
		$html = sprintf( '</%s>',
			'div' === $instance['list_style'] ? 'div' : 'li' );

		if( ! $echo )
			return $html;

		echo $html;
	}


	public static function end_list( $instance, $categories, $echo = true )
	{
		if( ! in_array($instance['list_style'], array('div', 'ol', 'ul')) ) {
			$instance['list_style'] = 'ul';
		}

		$end = sprintf( '</%s>', $instance['list_style'] );

		if( ! $echo )
			return $end;

		echo $end;
	}

	private static function get_item_id( $term = 0, $instance = array() )
    {
        if( ! $term ) return '';

        return $instance['widget_id'] . '-term-' . $term->term_id;
    }

    private static function get_item_class( $term = 0, $instance = array() )
    {
        if( ! $term ) return '';

        $classes   = array('simple-term-item');
        $classes[] = 'simple-term-' . $term->taxonomy . '-item';
        $classes[] = 'simple-term-' . $term->taxonomy . '-item-' . $term->term_id;

        if ( $term->parent > 0 ) {
            $classes[] = 'child-term';
            $classes[] = 'parent-' . $term->parent;
        }

        $classes = array_map( 'sanitize_html_class', $classes );

        return implode( ' ', $classes );
    }

    public static function get_term_excerpt( $term = 0, $instance = array(), $trim = 'words' )
    {
        if ( empty( $term ) ) return '';

        $_text = $term->description;

        if( '' === $_text ) {
            return '';
        }

        $_text = strip_shortcodes( $_text );
        $_text = str_replace(']]>', ']]&gt;', $_text);

        $text = apply_filters( 'acatw_term_excerpt', $_text, $term, $instance );

        $_length = ( ! empty( $instance['desc_length'] ) ) ? absint( $instance['desc_length'] ) : 55 ;
        $length = apply_filters( 'acatw_term_excerpt_length', $_length );

        $_aposiopesis = ( ! empty( $instance['excerpt_more'] ) ) ? $instance['excerpt_more'] : '&hellip;' ;
        $aposiopesis = apply_filters( 'acatw_term_excerpt_more', $_aposiopesis );

        if( 'chars' === $trim ){
            $text = wp_html_excerpt( $text, $length, $aposiopesis );
        } else {
            $text = wp_trim_words( $text, $length, $aposiopesis );
        }

        return $text;
    }

	public static function the_item_thumbnail_div( $term = 0, $instance = array(), $echo = true )
	{
		if ( empty( $term ) ) {
			return '';
		}

		$_html = '';
		$_thumb = Advanced_Categories_Widget_Utils::get_term_thumbnail( $term, $instance );

		$_classes = array();
		$_classes[] = 'acatw-term-thumbnail';

		$classes = apply_filters( 'acatw_thumbnail_div_class', $_classes, $instance, $term );
		$classes = ( ! is_array( $classes ) ) ? (array) $classes : $classes ;
		$classes = array_map( 'sanitize_html_class', $classes );

		$class_str = implode( ' ', $classes );

		if( '' !== $_thumb ) {

			$_html .= sprintf('<span class="%1$s">%2$s</span>',
				$class_str,
				sprintf('<a href="%s">%s</a>',
					esc_url( get_term_link( $term ) ),
					$_thumb
				)
			);

		};

		$html = apply_filters( 'acatw_item_thumbnail_div', $_html, $term, $instance );

		if( $echo ) {
			echo $html;
		} else {
			return $html;
		}
	}

	public static function the_term_post_count( $term = 0, $instance = array(), $echo = true )
	{
		if ( empty( $term ) ) {
			return '';
		}

		$type_text = ( $term->count > 1 ) ? 'Posts' : 'Post' ;
		$type_text = apply_filters( 'acatw_post_count_posttype', $type_text, $term->count );

		$term_count = number_format_i18n( $term->count );

		/* translators: 1: Number of posts 2: post type name */
		$_count_text = sprintf( __( '%1$d %2$s', 'advanced-categories-widget'), 
			$term_count,
			$type_text
		);

		$_html = sprintf( '<span class="acatw-post-count term-post-count"><a href="%1$s" rel="bookmark">%2$s</a></span>',
			esc_url( get_term_link( $term ) ),
			$_count_text
		);

		$html = apply_filters( 'acatw_post_count_text', $_html, $term, $instance );

		if( $echo ) {
			echo $html;
		} else {
			return $html;
		}
	}

}

