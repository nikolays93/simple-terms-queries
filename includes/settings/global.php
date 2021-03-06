<?php
namespace CDevelopers\Query\Terms;

if ( ! defined( 'ABSPATH' ) )
    exit; // disable direct access

$taxes = array();
$taxanomies = get_taxonomies( array('public' => true), 'objects' );
foreach ($taxanomies as $tax_slug => $tax) {
    $taxes[ $tax_slug ] = $tax->label;
}

/** Exclude WC staff */
unset( $taxes['product_shipping_class'] );

$data = array(
    array(
        'field_id'    => 'title',
        'id'          => $args['widget']->get_field_id( 'title' ),
        'name'        => $args['widget']->get_field_name( 'title' ),
        'type'        => 'text',
        'label'       => __('Title:', DOMAIN),
        'input_class' => 'widefat',
        ),
    array(
        'field_id'    => 'taxanomy',
        'id'          => $args['widget']->get_field_id( 'taxanomy' ),
        'name'        => $args['widget']->get_field_name( 'taxanomy' ),
        'default'     => 'category',
        'type'        => 'select',
        'options'     => $taxes,
        'label'       => __('Taxanomy:', DOMAIN),
        'input_class' => 'widefat',
        ),
    array(
        'field_id' => 'list_style',
        'id'          => $args['widget']->get_field_id( 'list_style' ),
        'name'        => $args['widget']->get_field_name( 'list_style' ),
        'type'        => 'select',
        'options'      => array(
            'div' => __( 'Blocks (div)' ),
            'ul' => __( 'Unordered List (ul)' ),
            'ol' => __( 'Ordered List (ol)' ),
            ),
        'label'       => __('List Format:', DOMAIN),
        'input_class' => 'widefat',
        ),
);

return $data;
