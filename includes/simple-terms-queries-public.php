<?php

namespace CDevelopers\Query\Terms;

if ( ! defined( 'ABSPATH' ) )
	exit; // disable direct access

class Simple_Terms_Queries_Public
{
	private function __construct(){}

    private static function get_item_id( $term = 0, $instance = array() )
    {

        return ( !empty($term->term_id) ) ? $instance['widget_id'] . '-term-' . $term->term_id : '';
    }

    private static function get_item_class( $term = 0, $instance = array() )
    {
        if( ! $term ) return 'undefined term';

        $classes   = array('stqt-item');
        $classes[] = 'stqt-' . $term->taxonomy . '-item';
        $classes[] = 'stqt-item-term-' . $term->term_id;

        if( $term->term_id == get_queried_object_id() ) {
            $classes[] = 'active';
        }

        if ( $term->parent > 0 ) {
            $classes[] = 'stqt-child-term';
            $classes[] = 'stqt-parent-' . $term->parent;
        }

        $classes = array_map( 'sanitize_html_class',
            apply_filters( 'stqt-item-term-class', $classes, $term ) );

        return implode( ' ', $classes );
    }

    // public static function get_term_excerpt( $term = 0, $instance = array(), $trim = 'words' )
    // {
    //     if ( empty( $term ) ) return '';

    //     $_text = $term->description;

    //     if( '' === $_text ) {
    //         return '';
    //     }

    //     $_text = strip_shortcodes( $_text );
    //     $_text = str_replace(']]>', ']]&gt;', $_text);

    //     $text = apply_filters( 'acatw_term_excerpt', $_text, $term, $instance );

    //     $_length = ( ! empty( $instance['desc_length'] ) ) ? absint( $instance['desc_length'] ) : 55 ;
    //     $length = apply_filters( 'acatw_term_excerpt_length', $_length );

    //     $_aposiopesis = ( ! empty( $instance['excerpt_more'] ) ) ? $instance['excerpt_more'] : '&hellip;' ;
    //     $aposiopesis = apply_filters( 'acatw_term_excerpt_more', $_aposiopesis );

    //     if( 'chars' === $trim ){
    //         $text = wp_html_excerpt( $text, $length, $aposiopesis );
    //     } else {
    //         $text = wp_trim_words( $text, $length, $aposiopesis );
    //     }

    //     return $text;
    // }

    // public static function the_item_thumbnail_div( $term = 0, $instance = array(), $echo = true )
    // {
    //     if ( empty( $term ) ) {
    //         return '';
    //     }

    //     $html = '';
    //     $thumb = Utils::get_term_thumbnail( $term, $instance );

    //     // $class_str = implode( ' ', $classes );

    //     if( '' !== $thumb ) {
    //         $html .= sprintf('<span class="term-thumbnail"><a href="%s">%2$s</a></span>',
    //             esc_url( get_term_link( $term ) ),
    //             $thumb
    //         );
    //     };

    //     if( ! $echo )
    //         return $html;

    //     echo $html;
    // }

    public static function display_widget($widget, $title, $categories, $instance, $args)
    {
        echo $args['before_widget'];

            if( $title )
                echo $args['before_title'] . $title . $args['after_title'];

            echo '<div class="st-widget st-wrap">';

                if( ! empty( $categories ) )
                    self::recursive_list_item( $categories, $instance );

            echo '</div><!-- /.st-widget.st-wrap -->';

        echo $args['after_widget'];
    }

    public static function recursive_list_item( $categories, $instance, $level = 0 ) {
        if( ! in_array($instance['list_style'], array('div', 'ol', 'ul')) ) {
            $instance['list_style'] = 'ul';
        }

        if( 'div' !== ($list_item = $instance['list_style']) ) {
            $list_item = 'li';
        }

        $_start = sprintf( '<%s class="simple-terms-list level-%d">',
            esc_html($instance['list_style']),
            intval($level)
            );

        echo apply_filters( 'stqt_start_list', $_start, $instance, $categories, $level );

        $level++;
        foreach( $categories as $term_id => $arrItem ) {
            if( empty($arrItem->term_id) )
                continue;

            $item_id    = self::get_item_id( $arrItem, $instance );
            $item_class = self::get_item_class( $arrItem, $instance );

            // open list item
            printf( '<%s id="%s" class="%s">',
                esc_html($list_item),
                esc_attr($item_id),
                esc_attr($item_class) );


            // $args = wp_parse_args( $instance, array(
            //  'item_desc'  => self::get_term_excerpt( $term, $instance ),
            //           'show_desc'  => '',
            //  ) );

            //       $args['show_thumb'] = ''; // $instance['show_thumb'] ?
                // self::the_item_thumbnail_div( $term, $instance, false ) : '';

                  $args['show_count'] = ''; // $instance['show_count'] ?
            //           " <small class='count'>( {$term->count} )</small>" : '';

            $result = array();
            // $result[] = sprintf('<div id="%s" class="%s">', $args['item_id'], $args['item_class']);
            // $result[] = $args['show_thumb'];
            $result[] = sprintf( '<a href="%s" rel="bookmark">%s</a>%s',
                esc_url( get_term_link( $arrItem ) ),
                $arrItem->name,
                $args['show_count'] ? " ( {$args['show_count']} )" : ''
                );

            // if( $args['show_desc'] ) {
            //  $result[] = '<span class="term-description">';
            //  $result[] = $args['item_desc'];
            //  $result[] = '</span><!-- /.term-summary -->';
            // }

            // $result[] = "</div>";

            echo implode("\n", $result);

                if( !empty($arrItem->children) ) {
                    /** recursive */
                    self::recursive_list_item( $arrItem->children, $instance, $level );
                }
                else {
                    $level = 0;
                }

            // close list item
            printf( '</%s>', esc_html($list_item) );
        }

        printf( '</%s>', esc_html($instance['list_style']) );
    }
}
