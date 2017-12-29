<?php
namespace CDevelopers\Query\Terms;

$data = array(
    array(
        'field_id' => 'show_title',
        'id'          => $args['widget']->get_field_id( 'show_title' ),
        'name'        => $args['widget']->get_field_name( 'show_title' ),
        'type'        => 'checkbox',
        'label'       => __( 'Display Widget Title', DOMAIN ),
        ),
    array(
        'field_id' => 'hierarchical',
        'id'    => $args['widget']->get_field_id( 'hierarchical' ),
        'name'  => $args['widget']->get_field_name( 'hierarchical' ),
        'type'  => 'checkbox',
        'label' => __( 'Hierarchical', 'advanced-categories-this' ),
        ),
    array(
        'field_id' => 'show_count',
        'id'    => $args['widget']->get_field_id( 'show_count' ),
        'name'  => $args['widget']->get_field_name( 'show_count' ),
        'type'  => 'checkbox',
        'label' => __( 'Display Post Count', 'advanced-categories-this' ),
        ),
    array(
        'field_id' => 'show_desc',
        'id'    => $args['widget']->get_field_id( 'show_desc' ),
        'name'  => $args['widget']->get_field_name( 'show_desc' ),
        'type'  => 'checkbox',
        'label' => __( 'Display Term Description', 'advanced-categories-this' ),
        ),
    array(
        'field_id' => 'desc_length',
        'id'    => $args['widget']->get_field_id( 'desc_length' ),
        'name'  => $args['widget']->get_field_name( 'desc_length' ),
        'type'  => 'number',
        'label' => __( 'Excerpt Length:', 'advanced-categories-this' ),
        'input_class' => 'small-text',
        'custom_attributes' => array(
            'step' => 1,
            'min' => 0,
            ),
        ),
    array(
        'id'    => 'excerpt-preview',
        'type'  => 'html',
        'value' => '
        <div class="widget-panel-excerptsize-wrap">
            <p>
                '.__( 'Preview:', 'advanced-categories-this' ).'<br />

                <span class="widget-panel-preview-container">
                    <span class="widget-panel-excerpt-preview">
                        <span class="widget-panel-excerpt">
                            '.wp_trim_words( Utils::sample_description(), 15, '&hellip;' ).'
                        </span>
                        <span class="widget-panel-excerpt-sample" aria-hidden="true" role="presentation">
                            '.Utils::sample_description().'
                        </span>
                    </span>
                </span>
            </p>
        </div>',
        ),
);

return $data;
