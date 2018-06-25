<?php

namespace Drupal\old_colony_ymca_yoc_megamenus\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id = "megamenu_location_finder_block",
 *   admin_label = @Translation("MegaMenu Location Finder"),
 * )
 */
class MegaMenuLocationFinder extends BlockBase {
  
  private $nodes;
  private $branch_data;
  private $map_data;

  private function setNodes()
  {
    $config = $this->getConfiguration();
    $this->nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($config['locations']);
  }

  private function setBranchData()
  {
    $this->branch_data = array();
    foreach($this->nodes AS $node)
    {
      $this->branch_data[] =  array(
        'url' => $node->url(),
        'title' => $node->getTitle(),
        'address' => $node->get('field_location_address')->getValue(),
        'coordinates' => $node->get('field_location_coordinates')->getValue(),
        'phone' => $node->get('field_location_phone')->getValue(),
      );
    }
  }

  private function getBranchData()
  {
    return $this->branch_data;
  }

  private function getAddress($branch)
  {
    $address = $branch['address'][0];
    $markup = "<div>";
    $markup .= "<h3><a href='{$branch['url']}'>{$branch['title']}</a></h3>";
    $markup .= "<p>";
    $markup .= "{$address['address_line1']}</br>";
    if($address['address_line2']){ $markup .= "{$address['address_line2']}</br>"; }
    $markup .= "{$address['locality']}, {$address['administrative_area']} {$address['postal_code']}";
    if($branch['phone']){ $markup .= "<br/>Phone: {$branch['phone'][0]['value']}</br>"; }
    $markup .= "</p>";
    $markup .= "</div>";
    return $markup;
  }

  private function setMapData()
  {
    $markers = array();
    foreach($this->branch_data AS $branch)
    {
      if(!$branch['coordinates'])
        continue;
      
      $coordinates = $branch['coordinates'][0];

      $markers[] = array(
        'coordinates' => array('lat' => floatval($coordinates['lat']), 'lng' => floatval($coordinates['lng'])),
        'message' => $this->getAddress($branch),
      );
    }
   
    $this->map_data = array(
      'markers' => $markers,
    );
  }

  private function getMapData()
  {
    return $this->map_data;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    
    $this->setNodes();
    $this->setBranchData();
    $this->setMapData();

    return array(
      '#theme' => 'old_colony_ymca_yoc_megamenus_location_finder',
      '#params' => array('branches' => $this->getBranchData() ),
      '#attached' => array(
        'library' => array('old_colony_ymca_yoc_megamenus/yoc_location_finder'),
        'drupalSettings' => array('megamenu_locator' => $this->getMapData() )
      ),
    );
  
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    //$this->configuration['my_block_settings'] = $form_state->getValue('my_block_settings');
  }
}
