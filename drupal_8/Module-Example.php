<?php

function old_colony_ymca_yoc_megamenus_theme() {
  $path = drupal_get_path('module', 'old_colony_ymca_yoc_megamenus');
  $theme_templates  = [];
  $theme_templates['field__block_content__megamenu_block'] = array (
     'base hook' => 'field',
     'path' => $path . '/templates'
  );

  $theme_templates['old_colony_ymca_yoc_megamenus_location_finder'] = array (
     'variables' => array('params' => array()),
     'path' => $path . '/templates'
  );

  return $theme_templates;
}


/**
 * Implements hook_form_BASE_FORM_ID_alter()
 *
 * @param $form
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 * @param $form_id
 */
function old_colony_ymca_yoc_megamenus_form_menu_link_content_form_alter(&$form, \Drupal\Core\Form\FormStateInterface $form_state, $form_id) {
  $menu_link = $form_state->getFormObject()->getEntity();
  $menu_link_options = $menu_link->link->first()->options ?: [];

  $result = db_query('SELECT * FROM block_content_field_data where type = :type', array(':type' => 'megamenu_block'));
  $options = array();
  foreach ($result as $record) {
    $options[$record->id] = $record->info;
  }
  
  $form['megamenu'] = array(
    '#type' => 'select',
    '#options' => $options,
    '#title' => t('MegaMenu Block'),
    '#description' => t("Apply a megamenu to this menu item"),
    '#default_value' => !empty($menu_link_options['megamenu']) ? $menu_link_options['megamenu'] : '',
    '#required' => false,
    '#empty_value' => '',
  );

  $form['actions']['submit']['#submit'][] = 'old_colony_ymca_yoc_megamenus_menu_link_content_form_submit';

}

/**
 * Process the submitted form.
 *
 * @param $form
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 */
function old_colony_ymca_yoc_megamenus_menu_link_content_form_submit($form, \Drupal\Core\Form\FormStateInterface $form_state) 
{
  //$classes = $form_state->getValue('classes');
  //'attributes' => array('class' => explode(' ', $classes)), 
  $megamenu = $form_state->getValue('megamenu');
  $menu_link = $form_state->getFormObject()->getEntity();
  $options = ['megamenu' => $megamenu];
  $menu_link_options = $menu_link->link->first()->options;
  $menu_link->link->first()->options = array_merge($menu_link_options, $options);
  $menu_link->save();
}

/**
 * Implements hook_library_info_alter().
 */
function old_colony_ymca_yoc_megamenus_library_info_alter(&$libraries, $extension) {
  if ($extension != 'old_colony_ymca_yoc_megamenus') {
    return;
  }
  // Adding Google Maps API key.
  foreach ($libraries['yoc_location_finder']['js'] as $key => $value) {
    if ($key != 'https://maps.googleapis.com/maps/api/js')
      continue;
    $api_key = \Drupal::configFactory()->get('geolocation.settings')->get('google_map_api_key');
    unset($libraries['yoc_location_finder']['js'][$key]);
    $libraries['yoc_location_finder']['js'][$key . '?key=' . $api_key] = $value;
    $js = $libraries['yoc_location_finder']['js'];
    $js = array_reverse( $js );
    $libraries['yoc_location_finder']['js'] = $js;
  }
}
