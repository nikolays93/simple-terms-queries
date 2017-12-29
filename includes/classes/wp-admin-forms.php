<?php

namespace CDevelopers\Query\Terms;

if ( ! defined( 'ABSPATH' ) )
  exit; // disable direct access

/**
 * @todo: add defaults
 */
class WP_Admin_Forms {
    static $clear_value = false;
    protected $inputs, $args, $is_table, $active;
    protected $hiddens = array();

    public function __construct($data = null, $is_table = true, $args = null)
    {
        if( ! is_array($data) )
            $data = array();

        if( ! is_array($args) )
            $args = array();

        if( isset($data['id']) || isset($data['name']) )
            $data = array($data);

        $args = self::parse_defaults($args, $is_table);
        if( $args['admin_page'] || $args['sub_name'] )
            $data = self::admin_page_options( $data, $args['admin_page'], $args['sub_name'] );

        $this->fields = $data;
        $this->args = $args;
        $this->is_table = $is_table;
    }

    public function render( $return=false )
    {
        $this->get_active();

        $html = $this->args['form_wrap'][0];
        foreach ($this->fields as $field) {
            if ( ! isset($field['id']) && ! isset($field['name']) )
                continue;

            // &$field
            $input = self::render_input( $field, $this->active, $this->is_table );
            $html .= self::_field_template( $field, $input, $this->is_table );
        }
        $html .= $this->args['form_wrap'][1];
        $result = $html . "\n" . implode("\n", $this->hiddens);
        if( $return )
            return $result;

        echo $result;
    }

    public function set_active( $active )
    {
        $this->active = $active;
    }

    public static function render_input( &$field, $active, $for_table = false )
    {
        $defaults = array(
            'type'              => 'text',
            'label'             => '',
            'description'       => isset($field['desc']) ? $field['desc'] : '',
            'placeholder'       => '',
            'maxlength'         => false,
            'required'          => false,
            'autocomplete'      => false,
            'id'                => '',
            'name'              => $field['id'],
            // 'class'             => array(),
            'label_class'       => array('label'),
            'input_class'       => array(),
            'options'           => array(),
            'custom_attributes' => array(),
            // 'validate'          => array(),
            'default'           => '',
            'before'            => '',
            'after'             => '',
            'check_active'      => false,
            'value'             => '',
            );

        $field = wp_parse_args( $field, $defaults );

        if( $field['default'] && ! in_array($field['type'], array('checkbox', 'select', 'radio')) ) {
            $field['placeholder'] = $field['default'];
        }

        $field['id'] = str_replace('][', '_', $field['id']);
        $entry = self::parse_entry($field, $active, $field['value']);

        return self::_input_template( $field, $entry, $for_table );
    }

    public function get_active()
    {
        if( ! $this->active ) {
            $this->active = $this->_active();
        }

        return $this->active;
    }

    /**
     * EXPEREMENTAL!
     * Get ID => Default values from $render_data
     * @param  array() $render_data
     * @return array(array(ID=>default),ar..)
     */
    public static function defaults( $render_data ){
        $defaults = array();
        if( empty($render_data) ) {
          return $defaults;
        }

        if( isset($render_data['id']) ) {
            $render_data = array($render_data);
        }

        foreach ($render_data as $input) {
            if(isset($input['default']) && $input['default']){
                $input['id'] = str_replace('][', '_', $input['id']);
                $defaults[$input['id']] = $input['default'];
            }
        }

        return $defaults;
    }

    /**
     * EXPEREMENTAL!
     *
     * @return array installed options
     */
    private function _active()
    {
        if( $this->args['postmeta'] ){
            global $post;

            if( ! $post instanceof WP_Post ) {
                return false;
            }

            $active = array();
            if( $sub_name = $this->args['sub_name'] ) {
                $active = get_post_meta( $post->ID, $sub_name, true );
            }
            else {
                foreach ($this->fields as $field) {
                    $active[ $field['id'] ] = get_post_meta( $post->ID, $field['id'], true );
                }
            }
        }
        else {
            $active = get_option( $this->args['admin_page'], array() );

            if( $sub_name = $this->args['sub_name'] ) {
                $active = isset($active[ $sub_name ]) ? $active[ $sub_name ] : false;
            }
        }

        /** if active not found */
        if( ! is_array($active) || $active === array() ) {
            return false;
        }

        /**
         * @todo: add recursive handle
         */
        $result = array();
        foreach ($active as $key => $value) {
            if( is_array($value) ){
                foreach ($value as $key2 => $value2) {
                    $result[$key . '_' . $key2] = $value2;
                }
            }
            else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /******************************** Templates *******************************/
    private function _field_template( $field, $input, $for_table )
    {
        // if ( $field['required'] ) {
        //     $field['class'][] = 'required';
        //     $required = ' <abbr class="required" title="' . esc_attr__( 'required' ) . '">*</abbr>';
        // } else {
        //     $required = '';
        // }

        $html = array();

        $desc = '';
        if( $field['description'] ){
            if( isset($this->args['hide_desc']) && $this->args['hide_desc'] === true )
                $desc = "<div class='description' style='display: none;'>{$field['description']}</div>";
            else
                $desc = "<span class='description'>{$field['description']}</span>";
        }

        $template = $field['before'] . $this->args['item_wrap'][0];
        $template.= $input;
        $template.= $this->args['item_wrap'][1] . $field['after'];
        $template.= $desc;

        if( ! $this->is_table ){
            $html[] = $template;
        }
        elseif( $field['type'] == 'hidden' ){
            $this->hiddens[] = $input;
        }
        elseif( $field['type'] == 'html' ){
            $html[] = $this->args['form_wrap'][1];
            $html[] = $input;
            $html[] = $this->args['form_wrap'][0];
        }
        else {
            $lc = implode( ' ', $field['label_class'] );
            $html[] = "<tr id='{$field['id']}'>";
            // @todo : add required symbol
            $html[] = sprintf("\t" . '<%$1s class="label">%$2s</%$1s>',
                $this->args['label_tag'],
                $field['label'] );

            $html[] = "  <td>";
            $html[] = "    " . $template;
            $html[] = "  </td>";
            $html[] = "</tr>";
        }

        return implode("\n", $html);
    }

    static function get_options_list( $arrOptions = array(), $entry = '' )
    {
        $options = '';
        foreach ( $arrOptions as $option_key => $option_text ) {
            if ( '' === $option_key ) {
                if ( empty( $field['placeholder'] ) )
                    $field['placeholder'] = $option_text ? $option_text : __( 'Choose an option' );
            }

            if( ! is_array( $option_text ) ){
                $options .= '<option value="' . esc_attr( $option_key ) . '" ' .
                    selected( $entry, $option_key, false ) . '>' .
                    esc_attr( $option_text ) . '</option>';
            }
            else {
                $options .= "<optgroup label='{$option_key}'>";
                foreach ($option_text as $sub_option_key => $sub_option_text) {
                    $options .= '<option value="' . esc_attr( $sub_option_key ) . '" ' .
                        selected( $entry, $sub_option_key, false ) . '>' .
                        esc_attr( $sub_option_text ) . '</option>';
                }
                $options .= "</optgroup>";
            }
        }

        return $options;
    }

    private static function _input_template( $field, $entry, $for_table = false )
    {
        $attributes = array();
        $attributes['name'] = sprintf('name="%s"', esc_attr( $field['name'] ) );
        $attributes[] = sprintf('id="%s"', esc_attr( $field['id'] ) );


        if( $field['input_class'] ) {
            if( is_array($field['input_class']) ) {
                $classes = array_map('sanitize_html_class', $field['input_class']);
                $class = implode( ' ', $classes );
            }
            else {
                $class = sanitize_html_class($field['input_class']);
            }

            $attributes['class'] = sprintf('class="%s"', $class);
        }

        if( $ph = esc_attr( $field['placeholder'] ) ) {
            $attributes[] = sprintf('placeholder="%s"', esc_attr( $ph ) );
        }

        if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {
            foreach ( $field['custom_attributes'] as $attribute => $attribute_value ) {
                $attributes[] = sprintf('%s="%s"', esc_attr( $attribute ), esc_attr( $attribute_value ));
            }
        }

        if( $field['maxlength'] ) {
            $attributes[] = sprintf('maxlength="%s"', absint( $field['maxlength'] ));
        }

        if( $field['autocomplete'] ) {
            $attributes[] = sprintf('autocomplete="%s"', esc_attr( $field['autocomplete'] ));
        }

        $label = ( ! $for_table && $field['label'] ) ?
            sprintf('<label for="%s">%s</label>', esc_attr($field['id']), $field['label']) : '';

        $input = '';
        switch ($field['type']) {
            /**
             * @todo: add recursive fieldset
             * @todo: add radio
             */
            case 'html' : break; // for debug
                $input .= $field['value'];
                break;

            case 'textarea' :
                $attributes['class'] = preg_replace("/(\"$)/", " input-text$1", $attributes['class']);

                if( empty( $field['custom_attributes']['rows'] ) ) {
                    $attributes[] = sprintf('rows="%d"', 5);
                }

                if( empty( $field['custom_attributes']['cols'] ) ) {
                    $attributes[] = sprintf('cols="%d"', 40);
                }

                $input .= $label;
                $input .= '<textarea ' . implode(' ', $attributes) . '>' . esc_textarea( $entry ) . '</textarea>';
            break;

            case 'checkbox' :
                $attributes[] = sprintf('type="%s"', esc_attr( $field['type'] ));
                $attributes[] = sprintf('value=%s',
                    esc_attr($field['value'] ? $field['value'] : 1));

                if( $entry )
                    $attributes[] = 'checked="checked"';

                // if $clear_value === false dont use defaults (couse default + empty value = true)
                if( isset($clear_value) || false !== ($clear_value = self::$clear_value) ) {
                    $input .= "<input type='hidden' {$attributes['name']} value='{$clear_value}'>\n";
                }

                $input .= '<input ' . implode(' ', $attributes) . ' />';
                $input .= $label;
            break;

            case 'select' :
                if ( empty( $field['options'] ) )
                    break;

                // if( $field['value'] || $field['value'] === '' ) {
                //     $entry = $field['value'];
                // }

                $options = self::get_options_list( $field['options'], $entry );

                $strAttributes = implode(' ', $attributes);
                $input .= $label;
                $input .= "<select {$strAttributes}>{$options}</select>";
            break;

            case 'hidden' :
            case 'password' :
            case 'text' :
            case 'email' :
            case 'tel' :
            case 'number' :
            default:
                if( ! $field['type'] ) $field['type'] = 'text';

                $attributes[] = sprintf('type="%s"', isset($field['type']) ?
                    esc_attr( $field['type'] ) : 'text' );

                $attributes[] = sprintf('value="%s"',
                    $field['value'] ? esc_attr( $field['value'] ) : esc_attr( $entry ) );

                $input .= $label;
                $input .= '<input ' . implode(' ', $attributes) . ' />';
            break;
        }

        return $input;
    }

    /********************************** Utils *********************************/
    private static function parse_defaults($args, $is_table)
    {
        $defaults = array(
            'admin_page'  => true, // set true for auto detect
            'item_wrap'   => array('<p>', '</p>'),
            'form_wrap'   => array('', ''),
            'label_tag'   => 'th',
            'hide_desc'   => false,
            'postmeta'    => false,
            'sub_name'    => '',
        );

        if( $is_table )
            $defaults['form_wrap'] = array('<table class="table form-table"><tbody>', '</tbody></table>');

        if( ( isset($args['admin_page']) && $args['admin_page'] !== false ) ||
            !isset($args['admin_page']) && is_admin() && !empty($_GET['page']) )
            $defaults['admin_page'] = $_GET['page'];

        $args = wp_parse_args( $args, $defaults );

        if( ! is_array($args['item_wrap']) )
            $args['item_wrap'] = array('', '');

        if( ! is_array($args['form_wrap']) )
            $args['form_wrap'] = array('', '');

        if( false === $is_table )
            $args['label_tag'] = 'label';

        return $args;
    }

    private static function parse_entry($field, $active)
    {
        if( ! is_array($active) || sizeof($active) < 1 )
            return false;

        $active_key = $field['check_active'] ? $field[$field['check_active']] : str_replace('[]', '', $field['name']);
        $active_value = isset($active[$active_key]) ? $active[$active_key] : false;

        if($field['type'] == 'checkbox' || $field['type'] == 'radio'){
            $entry = self::is_checked( $field, $active_value );
        }
        elseif($field['type'] == 'select'){
            $entry = ($active_value) ? $active_value : $field['default'];
        }
        else {
            // if text, textarea, number, email..
            $entry = $active_value;
        }
        return $entry;
    }

    private static function is_checked( $field, $active )
    {
        // if( $active === false && $value )
          // return true;

        $checked = ( $active === false ) ? false : true;
        if( $active === 'false' || $active === 'off' || $active === '0' )
            return false;

        if( $active === 'true'  || $active === 'on'  || $active === '1' )
            return true;

        if( $active || $field['default'] ){
            if( $field['value'] ){
                if( is_array($active) ){
                    if( in_array($field['value'], $active) )
                        return true;
                }
                else {
                    if( $field['value'] == $active || $field['value'] === true )
                        return true;
                }
            }
            else {
                if( $active || (!$checked && $field['default']) )
                    return true;
            }
        }

        return false;
    }

    private static function admin_page_options( $fields, $option_name, $sub_name = false )
    {
        foreach ($fields as &$field) {
            if ( ! isset($field['id']) && ! isset($field['name']) )
                continue;

            if( $option_name ) {
                if( isset($field['name']) ) {
                    $field['name'] = ($sub_name) ?
                        "{$option_name}[{$sub_name}][{$field['name']}]" : "{$option_name}[{$field['name']}]";
                }
                else {
                    $field['name'] = ($sub_name) ?
                        "{$option_name}[{$sub_name}][{$field['id']}]" : "{$option_name}[{$field['id']}]";
                }

                if( !isset($field['check_active']) )
                    $field['check_active'] = 'id';
            }
        }

        return $fields;
    }
}

// public static function render_fieldset( $input, $entry, $is_table, $label = '' ){
//     $result = '';

//     // <legend></legend>

//     foreach ($input['fields'] as $field) {
//       if( !isset($field['name']) )
//         $field['name'] = _isset_empty($field['id']);

//       $field['id'] = str_replace('][', '_', $field['id']);

//       $f_name = self::get_function_name($field['type']);
//       $result .= self::$f_name( $field, $entry, $is_table, $label );
//     }
//     return $result;
//   }
