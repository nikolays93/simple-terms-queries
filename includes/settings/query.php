<?php
namespace CDevelopers\Query\Terms;

$data = array(
    array(
        'field_id'    => 'show_empty',
        'id'          => $args['widget']->get_field_id( 'show_empty' ),
        'name'        => $args['widget']->get_field_name( 'show_empty' ),
        'type'        => 'checkbox',
        'label'       => __( 'Show empty terms', DOMAIN ),
    ),
    array(
        'field_id'    => 'max',
        'id'          => $args['widget']->get_field_id( 'max' ),
        'name'        => $args['widget']->get_field_name( 'max' ),
        'type'        => 'number',
        'label'       => __( 'Max terms:', DOMAIN ),
        'input_class' => 'small-text',
        'desc'        => 'set -1 for no limited',
    ),
);

return $data;
