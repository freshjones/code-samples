<?php
/*
Implementation of hook_init()
*/
function chroma_foxycart_init() {

  $fc_store_domain = variable_get('foxycart_store_domain', 'secure.chroma.com');
  $module = drupal_get_path('module', 'chroma_foxycart');

  drupal_add_js( 'https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.6/handlebars.min.js', array(
    'type' => 'external', 
    'scope' => 'footer', 
    'group' => JS_LIBRARY,
  ));

  drupal_add_js( $module . '/javascript/foxycart-handlebars.js', array(
    'scope' => 'footer', 
    'group' => JS_DEFAULT, 
    'weight' => 7
  ));

  drupal_add_js("//cdn.foxycart.com/{$fc_store_domain}/loader.js",
   array(
    'type' => 'external', 
    'scope' => 'footer', 
    'group' => JS_DEFAULT,
    'async' => TRUE, // This should work.
    'defer' => TRUE, // This should work.
    'weight' => 8
  ));

  drupal_add_js( $module . '/javascript/modal-functions.js', 
  array(
    'scope' => 'footer', 
    'group' => JS_DEFAULT, 
    'weight' => 9
  ));

  drupal_add_js( $module . '/javascript/foxycart-modal-configurator.js', 
  array(
    'scope' => 'footer', 
    'group' => JS_DEFAULT, 
    'weight' => 11
  ));

  if(module_exists('chroma_foxycart_addbyquote'))
  {
    drupal_add_js( $module . '/javascript/foxycart-quote-search.js', 
    array(
      'scope' => 'footer', 
      'group' => JS_DEFAULT, 
      'weight' => 12
    ));
  }
}

function chroma_foxycart_menu() 
{

  $items = array();

  $items['cart'] = array(
    'title' => t('Cart'),
    'page callback' => 'foxycart',
    'access arguments' => array('access content'),
    'type' => MENU_NORMAL_ITEM
  );

  $items['fcart_render'] = array(
    'page callback' => 'foxycart_render',
    'access arguments' => array('access content'),
    'type' => MENU_CALLBACK
  );

  $items['fcart_configure'] = array(
    'title' => t('Process Order'),
    'page callback' => 'foxycart_configure',
    'page arguments' => array(1,2,3,4),
    'access arguments' => array('access content'),
    'type' => MENU_CALLBACK
  );

  $items['fcart_atc_preprocess'] = array(
    'title' => t('PreProcess Add To Cart'),
    'page callback' => 'foxycart_atc_preprocess',
    'access arguments' => array('access content'),
    'type' => MENU_CALLBACK
  );

  $items['fcart_instrument_models'] = array(
    'title' => t('Foxycart Instrument Models'),
    'page callback' => 'foxycart_instrument_models',
    'access arguments' => array('access content'),
    'type' => MENU_CALLBACK
  );


  return $items;
}

/*
Implementation of hook_ctools_plugin_directory()
*/
function chroma_foxycart_ctools_plugin_directory($owner, $plugin_type) 
{
  
  if ($owner == 'ctools' && !empty($plugin_type)) 
  {
    return 'plugins/' . $plugin_type;
  }

}

function foxycart()
{

  $fc_store_url = variable_get('foxycart_store_url', 'https://secure.chroma.com');
  $module = drupal_get_path('module', 'chroma_foxycart');

  $settings = array();
  $settings['template_path'] = base_path() . $module . '/templates/handlebars';

  drupal_add_js(array('foxycart' => $settings), 'setting');

  drupal_add_js( $module . '/javascript/foxycart.js', array(
    'scope' => 'footer', 
    'group' => JS_DEFAULT, 
    'weight' => 13
  ));

  if(module_exists('chroma_foxycart_addbysearch'))
  {

    $searchModule = drupal_get_path('module', 'chroma_foxycart_addbysearch');

    drupal_add_js( $searchModule . '/javascript/search.js', array(
      'scope' => 'footer', 
      'group' => JS_DEFAULT, 
      'weight' => 14
    ));

  }

  $params = array();
  $params['checkout_url'] = "{$fc_store_url}/cart";
  $params['security_image'] = "/{$module}/images/ssl-encrypted.png";
  return theme('foxycart',$params);

}

function foxycart_render()
{

  $module = drupal_get_path('module', 'chroma_foxycart');

  drupal_add_js( $module . '/javascript/foxycart-checkout.js', 
  array(
    'scope' => 'footer', 
    'group' => JS_DEFAULT, 
    'weight' => 12
  ));

  $params = array();

  return theme('foxycart-checkout',$params);

}

function foxycart_getProcessor($postData,$helpers,$errors)
{

  /* CONCRETE */
  $type = ucFirst($postData['itemType']);
  $preProcessorName = 'FC_ATCPreprocess_'.$type;

  if(
    module_exists('chroma_foxycart_variant') &&
    (isset($postData['variantSizeOption']) && is_numeric($postData['variantSizeOption']))
  ) {
    $preProcessorName = 'FC_ATCPreprocess_'.$type.'WithVariant';
  }

  $preprocessor = new $preProcessorName($helpers, $errors, $postData);

  /* DECORATORS */

  /* decorate by instrument type or size depending on method chosen */
  if(isset($postData['chooseModelPart']))
  {
    $decoratorName = 'FC_ATCPreProcessDecorator_' . ucFirst($postData['chooseModelPart']);
    $preprocessor = new $decoratorName($preprocessor);
  }

  /* decorate by thickness */
  if(isset($postData['itemPartTypeVariant']))
  {
    $thicknessHelperName = 'ThicknessOptions' . ucfirst($postData['itemPartTypeVariant']);
    $thicknessHelper = new $thicknessHelperName();
    $preprocessor = new FC_ATCPreProcessDecorator_Thickness($preprocessor, $thicknessHelper);
  }

  /* decorate by partsize */
  if(isset($postData['itemPartSize']) && strlen($postData['itemPartSize']))
  {
    $preprocessor = new FC_ATCPreProcessDecorator_PartSize($preprocessor, $postData['itemPartSize']);
  }

  /* decorate by set and large set price */
  if( 
    $postData['itemType'] === 'set' && 
    ( isset($postData['largeSetPrice']) && is_numeric($postData['largeSetPrice']) && $postData['largeSetPrice'] > 0 ) 
  )
  {
    $preprocessor = new FC_ATCPreProcessDecorator_LargeSet($preprocessor);
  }

  if(module_exists('chroma_foxycart_tirf'))
  {
    if( 
      $postData['itemType'] === 'set' && 
      ( isset($postData['tirfsetcube']) && is_numeric($postData['tirfsetcube']) )
    )
    {
      $preprocessor = new FC_ATCPreProcessDecorator_TirfSet($preprocessor, $helpers, $postData['tirfsetcube']);
    }
  }

  return $preprocessor;

}


function foxycart_atc_preprocess()
{
  drupal_page_is_cacheable(FALSE);
  
  $mssqlConnection =& createDB();
  $errors = new ChromaErrorHelper();
  $helpers = new ChromaDataHelpers($mssqlConnection);

  // get the raw POST data
  $rawPost = file_get_contents("php://input");

  // this returns null if not valid json
  $postData = json_decode($rawPost, true);
  if(!$postData)
  {
    echo json_encode(array('message' => 'No Post Data Found', 'status' => 'error'));
    return;
  }

  if(
    isset( $postData['ndparts'] ) && 
    ( is_numeric($postData['ndparts'] ) || 
      ( is_array($postData['ndparts']) && !empty($postData['ndparts']) )
    )
  )
  {
    $bundle = _get_foxycart_atc_bundle_for_ndparts($postData,$helpers,$errors);
  } else {
    $bundle = _get_foxycart_atc_bundle($postData,$helpers,$errors);
  }

  $bundle->setOutput();
  echo $bundle->getOutput();
}

function _get_foxycart_atc_bundle_for_ndparts($postData,$helpers,$errors)
{
  
  $partData = new ChromaInternalMultiPartData($postData['ndparts'],$helpers);
  $partData->setSql();
  $partData->setResults();
  $results = $partData->getResults();
  if(!$results)
    return false;

  $bundle = new FC_ATCPreProcessDecorator_Multi();

  foreach($results AS $part)
  {
    $qty=1;
    if(isset($postData['ndpart_qty_' . $part['itemID'] ]))
      $qty=$postData['ndpart_qty_' . $part['itemID'] ];
    $part['quantity'] = $qty;
    $part['itemPartSize'] = $postData['itemPartSize'];
    $part['notes'] = trim(strip_tags($postData['notes']));

    $processor = foxycart_getProcessor($part,$helpers,$errors); 
    $bundle->addProcessor($processor);
  }

  $bundle = new FC_ATCPreProcessDecorator_MultiJSON($bundle);
  return $bundle;
}

function _get_foxycart_atc_bundle($postData,$helpers,$errors)
{

  $productProcessor = foxycart_getProcessor($postData,$helpers,$errors);
  
  /* if we are bundling a holder */
  if( 
    $postData['itemType'] === 'set' && 
    (isset($postData['holderType']) && is_numeric($postData['holderType']) && $postData['holderType'] > 0)
  )
  {
  
    $holderID = $postData['holderType'];
    $holderData = new ChromaInternalHolderData($holderID, null, $helpers);

    //decorate it for fc data
    $holderData = new ChromaInternalHolderDataForBundle($holderData);

    //set the holder processor
    $holderProcessor = foxycart_getProcessor($holderData->getResults(),$helpers,$errors);
    
    //set the holder output
    $holderProcessor->setOutput();

    //we must also decorate the product with holder info
    $productProcessor = new FC_ATCPreProcessDecorator_ProductAsBundle($productProcessor, $holderProcessor);

    //we must decorate the holder with some info from the product
    $holderProcessor = new FC_ATCPreProcessDecorator_HolderAsBundle($holderProcessor, $productProcessor);

  }

  $bundle = new FC_ATCPreProcessDecorator_Multi();

  $bundle->addProcessor($productProcessor);

  if(isset($holderProcessor))
    $bundle->addProcessor($holderProcessor);

  $bundle = new FC_ATCPreProcessDecorator_MultiJSON($bundle);

  return $bundle;

}

function foxycart_configure($itemID=null, $itemType=null, $actionKey=null, $actionValue=null)
{ 
    //base configurator
    $configurator = new FC_ModalEmptyConfigurator();
    
    //config service container
    if($serviceContainer = _getConfiguratorServiceContainer($itemID,$itemType,$actionKey,$actionValue))
      $configurator = _productConfigurator($serviceContainer);

    //if this is loaded through the search then decorate it further
    if(module_exists('chroma_foxycart_addbysearch') && isset($_GET['search']) && $_GET['search'] == true)
      $configurator = new FC_ModalSearchConfigurator($configurator);

    //set the output
    $configurator->setOutput();

    //return the output
    echo $configurator->getOutput();
}


function _getConfiguratorServiceContainer($itemID=null, $itemType=null, $actionKey=null, $actionValue=null)
{

  if(!$itemID || !is_numeric($itemID) || !$itemType || $itemType != 'node')
    return false;

  $serviceContainer = array();
  $serviceContainer['params']['itemID'] = $itemID;
  $serviceContainer['params']['itemType'] = $itemType;
  $serviceContainer['params']['actionKey'] = $actionKey;
  $serviceContainer['params']['actionValue'] = $actionValue;
  $serviceContainer['database'] =& createDB();
  $serviceContainer['helpers'] = new ChromaDataHelpers( $serviceContainer['database']  );

  $settings = _getConfiguratorProductSettings($serviceContainer);

  if(!$settings)
    return false;

  $serviceContainer['settings'] = $settings;
  $serviceContainer['widgets']  = new FC_ConfiguratorWidgets($serviceContainer);
  
  return $serviceContainer;
}
