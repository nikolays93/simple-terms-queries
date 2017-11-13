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
        'field_id'    => 'tax',
        'id'          => $args['widget']->get_field_id( 'tax' ),
        'name'        => $args['widget']->get_field_name( 'tax' ),
        'default'     => 'category',
        'type'        => 'select',
        'options'     => $taxes,
        'label'       => __('Taxanomy:', DOMAIN),
        'input_class' => 'widefat',
        ),
    array(
        'field_id'    => 'orderby',
        'id'          => $args['widget']->get_field_id( 'orderby' ),
        'name'        => $args['widget']->get_field_name( 'orderby' ),
        'type'        => 'select',
        'options'      => array(
            'name'       => __( 'Category Name', 'advanced-categories-widget' ),
            'count'      => __( 'Post Count', 'advanced-categories-widget' ),
        ),
        'label'       => __( 'Order by:', DOMAIN ),
        'input_class' => 'widefat',
    ),
    array(
        'field_id'    => 'order',
        'id'          => $args['widget']->get_field_id( 'order' ),
        'name'        => $args['widget']->get_field_name( 'order' ),
        'type'        => 'select',
        'options'      => array(
            'desc' => __('Descending', DOMAIN),
            'asc'  => __('Ascending', DOMAIN),
        ),
        'label'       => __( 'Order:', DOMAIN ),
        'input_class' => 'widefat',
    ),
);

return $data;
