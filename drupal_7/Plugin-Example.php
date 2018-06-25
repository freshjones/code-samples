<?php
/**
 * Plugins are described by creating a $plugin array which will be used
 * by the system that includes this file.
 */
$plugin = array(
  'title' => t('FoxyCart Add To Cart Button'),
  'description' => t('Chroma Foxycart Add to cart button'),
  // 'single' => TRUE means has no subtypes.
  'single' => TRUE,
  'required context' => new ctools_context_required(t('Node'), 'node'),
  // Constructor.
  'content_types' => array('chroma_panels_content_type'),
  // Name of a function which will render the block.
  'render callback' => 'foxycart_addtocartbtn_render',
  // The default context.
  //'defaults' => array(),
  'edit form' => 'foxycart_addtocartbtn_edit_form',
  'category' => array(t('Chroma Cart'), -9),
  //'all contexts' => TRUE,
);

/**
 * Run-time rendering of the body of the block.
 *
 * @param $subtype
 * @param $conf
 *   Configuration as done at admin time.
 * @param $args
 * @param $context
 *   Context - in this case we don't have any.
 *
 * @return
 *   An object with at least title and content members.
 */
function foxycart_addtocartbtn_render($subtype, $conf, $args, $context) {
  
  $btn = '';
  //$queryString = '';
  $mount = null;
  $tirf = false;
  $button_text = isset($conf['button_text']) && strlen($conf['button_text']) ? $conf['button_text'] : 'Add to Cart';
  $button_title = isset($conf['button_title']) && strlen($conf['button_title']) ? $conf['button_title'] : 'Add to Cart';

  try
  {
    
    if(isset($context->data) && !empty($context->data))
    {

      $nodeID = $context->data->nid;
      $type = $context->data->type;
      
      $entityWrapper = entity_metadata_wrapper('node', $context->data);

      if($entityWrapper->__isset('field_price')) {
        $price = (int)$entityWrapper->field_price->value();
      }
      
      $discontinued = 0;
      
      if($entityWrapper->__isset('field_discontinued')) {
        $discontinued   = $entityWrapper->field_discontinued->value();
      }
      
      $variants = false;
      $ndSeries = false;

      if(isset($context->data->field_series) && $entityWrapper->field_series->raw() == 263)
        $ndSeries = true;

      if( ($price <= 0 && !$variants && !$ndSeries) || $discontinued == 1)
      {
        if($discontinued == 1 )
        {
          $btn = 'DISCONTINUED';
        }
      } else {

        $style = 'default';

        if (isset($conf['conf_style']) ) {
          $style = $conf['conf_style'];
        }
        
        $classes = array();

        switch($style)
        {
          case 'default':
            $classes[] = 'btn-green';
          break;

          case 'clearance':
            //$classes[] = 'cart-btn';
            $classes[] = 'btn-orange';
          break;

          case 'text':
            $classes[] = 'btn-text';
          break;

        }

        $classString = implode(' ', $classes);

        $btn = '<form class="fc-product-order-form" name="product-order-id-' . $nodeID . '" autocomplete="off">';
        $btn .= '<input name="node-id" type="hidden" value="' . $nodeID . '">';
        $btn .= '<button title="' . $button_title . '" class="' . $classString . '" disabled="disabled">' . $button_text . '</button>';
        $btn .= '</form>';

      }
    
    }

  }
  catch( ErrorException $e )
  {
    $btn = '';
  }

  $block = new stdClass();

  $block->title = '';
  $block->content = $btn;
  
  return $block;

}


/**
 * 'Edit form' callback for the content type.
 * This example just returns a form; validation and submission are standard drupal
 * Note that if we had not provided an entry for this in hook_content_types,
 * this could have had the default name
 * ctools_plugin_example_no_context_content_type_edit_form.
 *
 */
function foxycart_addtocartbtn_edit_form($form, &$form_state) {

  $conf = $form_state['conf'];
  
  $form['override_title']['#type'] = 'hidden';
  $form['override_title_text']['#type'] = 'hidden';
  $form['override_title_heading']['#type'] = 'hidden';
  $form['context']['#type'] = 'hidden';
  $form['context']['#value'] = 'node';

  $form['button_text'] = array(
      '#type' => 'textfield',
      '#title' => t('Button Text'),
      '#default_value' => !empty($conf['button_text']) ? $conf['button_text'] : '',
      '#description' => t('Override the default "Add to Cart" text'),
  );

  $form['button_title'] = array(
      '#type' => 'textfield',
      '#title' => t('Button Title'),
      '#default_value' => !empty($conf['button_title']) ? $conf['button_title'] : '',
      '#description' => t('Override the default "Add to Cart" title text'),
  );

  $form['action_type'] = array(
      '#type' => 'select',
      '#title' => t('Button Action'),
      '#options' => array(
        'default' => t('Default'),
        'tirf-mount' => t('Tirf Mount Option'),
      ),
      '#default_value' => !empty($conf['action_type']) ? $conf['action_type'] : 'default',
      '#description' => t('Configure the Button Action'),
  );
  
  $form['conf_style'] = array(
      '#type' => 'select',
      '#title' => t('Button Style'),
      '#options' => array(
        'default' => t('Button - Catalog'),
        'clearance' => t('Button - Clearance'),
        'text' => t('Text Link'),
      ),
      '#default_value' => !empty($conf['conf_style']) ? $conf['conf_style'] : 'default',
      '#description' => t('Configure the Button Style'),
  );

    return $form;
    
}


function foxycart_addtocartbtn_edit_form_submit($form, &$form_state) {
  foreach (element_children($form) as $key) {
    //if (!empty($form_state['values'][$key])) {
      $form_state['conf'][$key] = $form_state['values'][$key];
    //}
  }
}

