<?php

/*
Implementation of hook_ctools_plugin_directory()
*/
function chroma_typeahead_search_ctools_plugin_directory($owner, $plugin_type) 
{
  if ($owner == 'ctools' && !empty($plugin_type)) 
  {
    return 'plugins/' . $plugin_type;
  }
}

/**
 * Implementation of hook_theme().
 */
function chroma_typeahead_search_theme($existing, $type, $theme, $path)
{
    $themes = array();
    $themes['type_ahead_search_box'] = array(
              'template'    => 'templates/type-ahead-search-box',
              'variables'   => array(),
    );
    $themes['type_ahead_search_results'] = array(
              'template'    => 'templates/type-ahead-search-results',
              'variables'   => array(),
    );
    $themes['type_ahead_search_no_results'] = array(
              'template'    => 'templates/type-ahead-search-no-results',
              'variables'   => array(),
    );
    return $themes;
}

/*
Implementation of hook_menu()
*/
function chroma_typeahead_search_menu() {
  $items = array();
  $items['search'] = array(
    'title' => t('Search Results'),
    'page callback' => 'chroma_typeahead_search_results',
    'access arguments' => array('access content'),
    'type' => MENU_NORMAL_ITEM
  );
  return $items;
}

function _sanitize_search_keyword($string)
{
  $string = trim(strip_tags($string));
  return preg_replace("/[^A-Za-z0-9 \&\,\.\-\/]/", ' ', $string);
}

function _typeahead_search_post_process_results($docs)
{
  $groups = array();
  foreach($docs AS $doc)
  {
    if(!isset($groups[$doc['item_type']]['count']))
      $groups[$doc['item_type']]['count'] = 0;
    $groups[$doc['item_type']]['count'] = $groups[$doc['item_type']]['count'] + 1;
    $groups[$doc['item_type']]['data'][] = $doc;
  }
  return $groups;
}

function chroma_typeahead_search_results()
{

  $keyword = false;
  $amount = 'all';
  $params = array();

  if(isset($_GET['keyword']) && trim(strip_tags($_GET['keyword'])))
    $keyword = _sanitize_search_keyword($_GET['keyword']);

  if(!$keyword)
    return 'sorry no keyword found';

  $keyword = urlencode($keyword);
  $searchDomain = variable_get('chroma_search_api_domain', 'https://www.chroma.com');

  $url = "{$searchDomain}/services/api/search?q={$keyword}&limit={$amount}";

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $results = curl_exec($ch);
  curl_close($ch);  

  $params['count'] = 0;
  $params['keyword'] = urldecode(htmlentities($keyword));

  if($results === false)
    return theme('type_ahead_search_no_results',$params);

  $results = json_decode($results,true);
  $docs = $results['docs'];

  if($results['numFound'] <= 0)
    return theme('type_ahead_search_no_results',$params);

  $params['count'] = $results['numFound'];
  $params['groups'] = _typeahead_search_post_process_results($docs);

  return theme('type_ahead_search_results', $params);

}

