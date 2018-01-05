<?php

namespace CDevelopers\Query\Terms;

if ( ! defined( 'ABSPATH' ) )
	exit; // disable direct access

class Widget_Views
{

	private function __construct(){}

	public static function start_list( $instance, $categories, $echo = true, $level = 0 )
	{
		if( ! in_array($instance['list_style'], array('div', 'ol', 'ul')) ) {
			$instance['list_style'] = 'ul';
		}

		$start = sprintf( '<%s class="simple-terms-list level-%d">',
            $instance['list_style'], $level );

		if( ! $echo )
			return $start;

		echo $start;
	}

	public static function start_list_item( $term, $instance, $categories, $echo = true )
	{
		if( ! $term ) return;

		$item_id    = self::get_item_id( $term, $instance );
		$item_class      = 'st-list-item ' . self::get_item_class( $term, $instance );

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
		$args = wp_parse_args( $instance, array(
			'item_id'    => self::get_item_id( $term, $instance ),
			'item_class' => self::get_item_class( $term, $instance ),
			'item_desc'  => self::get_term_excerpt( $term, $instance ),
            'show_desc'  => '',
			) );

        $args['show_thumb'] = $instance['show_thumb'] ?
            self::the_item_thumbnail_div( $term, $instance, false ) : '';

        $args['show_count'] = $instance['show_count'] ?
            " <small class='count'>( {$term->count} )</small>" : '';

		$result = array();
		// $result[] = sprintf('<div id="%s" class="%s">', $args['item_id'], $args['item_class']);
		$result[] = $args['show_thumb'];
		$result[] = sprintf( '<h4><a href="%s" rel="bookmark">%s</a>%s</h4>',
					esc_url( get_term_link( $term ) ),
					$term->name,
                    $args['show_count']
				);
		if( $args['show_desc'] ) {
			$result[] = '<span class="term-description">';
			$result[] = $args['item_desc'];
			$result[] = '</span><!-- /.term-summary -->';
		}
		// $result[] = "</div>";

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

        $classes   = array('st-item');
        $classes[] = 'st-' . $term->taxonomy . '-item';
        $classes[] = 'st-item-term-' . $term->term_id;

        if( $term->term_id == get_queried_object_id() ) {
            $classes[] = 'active';
        }

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

		$html = '';
		$thumb = Utils::get_term_thumbnail( $term, $instance );

		// $class_str = implode( ' ', $classes );

		if( '' !== $thumb ) {
			$html .= sprintf('<span class="term-thumbnail"><a href="%s">%2$s</a></span>',
                esc_url( get_term_link( $term ) ),
                $thumb
			);
		};

		if( ! $echo )
			return $html;

        echo $html;
	}

    public static function recursive_list_item( $categories, $instance, $level = 0 ) {

        Widget_Views::start_list( $instance, $categories, true, $level );
        foreach( $categories as $term_id => $arrItem ) {
            if( ! isset($arrItem['term']) )
                continue;

            Widget_Views::start_list_item( $arrItem['term'], $instance, $categories );

                Widget_Views::list_item( $arrItem['term'], $instance, $categories );

                if( isset($arrItem['child']) ) {
                    $level++;

                    self::recursive_list_item( $arrItem['child'], $instance, $level );
                }
                else {
                    $level = 0;
                }

            Widget_Views::end_list_item( $arrItem['term'], $instance, $categories );
        }

        Widget_Views::end_list( $instance, $categories );
    }

	// public static function the_term_post_count( $term = 0, $instance = array(), $echo = true )
	// {
	// 	if ( empty( $term ) ) {
	// 		return '';
	// 	}

	// 	$type_text = (  > 1 ) ? 'Posts' : 'Post' ;

	// 	$term_count = number_format_i18n( $term->count );

	// 	/* translators: 1: Number of posts 2: post type name */
	// 	$_count_text = sprintf( __( '%1$d %2$s', 'advanced-categories-widget'), 
	// 		$term_count,
	// 		$type_text
	// 	);

	// 	$_html = sprintf( '<span class="acatw-post-count term-post-count"><a href="%1$s" rel="bookmark">%2$s</a></span>',
	// 		esc_url( get_term_link( $term ) ),
	// 		$_count_text
	// 	);

	// 	$html = apply_filters( 'acatw_post_count_text', $_html, $term, $instance );

	// 	if( $echo ) {
	// 		echo $html;
	// 	} else {
	// 		return $html;
	// 	}
	// }

}

