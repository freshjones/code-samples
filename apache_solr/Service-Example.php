<?php

namespace App\Services;

Class SolrService {

  private $params;
  private $query;
  private $results;

  function __construct($config) {

    //default params
    $this->params = $config;

    //set some defaults
    $this->setQueryParam('stopwords','true');
    $this->setQueryParam('lowercaseOperators','true');
    $this->setQueryParam('wt','json');
    $this->setQueryParam('indent',true);
    $this->setQueryParam('rows',25);
    $this->setQueryParam('fl',array(
      'index_id',
      'item_id',
      'ss_field_product_number',
      'is_status',
      'ss_title',
      'ss_name',
      'ss_type',
      'ss_field_rc_set_number',
      'ss_field_rc_cube_number',
      'fs_field_price',
      'bs_field_discontinued',
      'ss_url',
      'score',
    ));

    $this->setQueryParam('fq',array(
      '-ss_type:events',
    ));

    $this->setQueryParam('qf',array(
      'tm_search_api_aggregation_2^10',
      'tm_search_api_aggregation_4^5'
    ));

    $this->setQueryParam('sort',array(
      'score desc',
      'ss_field_product_number asc',
      'ss_title asc',
    ));

    $this->setQueryParam('bq',array(
      '(*:* -index_id:files)^1000000',
      '(*:* -bs_field_discontinued:true)^1000000',
      'ss_type:fluorochrome^5000',
      'ss_type:parts_display^2000',
      'ss_type:sets_display^1000',
    ));

    $this->setDefaultResponse();

  }

  private function setDefaultResponse()
  {
      $response = array(
                        "numFound"=>0,
                        "start"=>0,
                        "docs"=>array(),
                        );

      $this->results = json_encode($response);

  }


  private function normalizeUrlSection($section, $addSlash=true)
  {
    $url = trim($section,'/');
    return $addSlash === true ? $url . '/' : $url;
  }

  private function getUrl()
  {
    $query = $this->buildQueryString();
    $url = $this->normalizeUrlSection($this->params['url'])
          . $this->normalizeUrlSection($this->params['index'])
          . $this->normalizeUrlSection($this->params['endpoint'], false);

    return $url . '?' . $query;
  }

  private function sanitizeQueryString($string)
  {
    $string = trim(strip_tags($string));
    return preg_replace("/[^A-Za-z0-9 \,\.\-\/]/", ' ', $string);
  }

  public function setQuery($v)
  {
    $queries = array();
    $this->setQueryParam('q', $this->sanitizeQueryString($v) );
  }

  public function setLimit($limit)
  {
    if($limit === 'all')
    {
      $this->setQueryParam('rows',1000);
    } else if(is_numeric($limit))
    {
      $this->setQueryParam('rows',$limit);
    }
  }

  public function setParam($k,$v)
  {
    $this->params[$k] = $v;
  }

  public function setQueryParam($k,$v)
  {
    $this->query[$k] = $v;
  }

  public function getQueryParams()
  {
    return $this->query;
  }

  private function buildQueryString()
  {
      $query = $this->getQueryParams();
      $query['fq'] = implode(' ',$query['fq']);
      $query['qf'] = implode(' ',$query['qf']);
      $query['fl'] = implode(',',$query['fl']);
      $query['sort'] = implode(',',$query['sort']);
      $query['bq'] = $query['bq'];

      $queryString = http_build_query($query, null, '&');
      $queryString = preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', $queryString);

      return $queryString;

  }

  private function setNumberAndName($item)
  {

    if($item['index_id'] === "files" && isset($item['ss_name']) && strlen($item['ss_name']))
      return $item['ss_name'];

    $parts = array();

    if(isset($item['ss_field_product_number']) && strlen($item['ss_field_product_number']))
      $parts[] = $item['ss_field_product_number'];

    if(isset($item['ss_field_rc_set_number']) && strlen($item['ss_field_rc_set_number']))
      $parts[] = $item['ss_field_rc_set_number'];

    if(isset($item['ss_title']) && strlen($item['ss_title']))
    {
      if(
        !isset($item['ss_field_product_number']) || 
        ( isset($item['ss_field_product_number']) && strtolower($item['ss_field_product_number']) != strtolower($item['ss_title']) )
      )
        $parts[] = $item['ss_title'];
    }

    return implode(' - ', $parts);

  }

  private function setNumberOrName($item)
  {

    if($item['index_id'] === "files" && isset($item['ss_name']) && strlen($item['ss_name']))
      return $item['ss_name'];

    $string = '';

    if(isset($item['ss_field_product_number']) && strlen($item['ss_field_product_number']))
    {
      $string = $item['ss_field_product_number'];
    } 
    else if (isset($item['ss_title']) && strlen($item['ss_title']))
    {
      $string = $item['ss_title'];
    }

    return $string;
  }

  private function setItemType($item)
  {
    $string = 'Page';

    if($item['index_id'] == 'files')
      return 'PDF';

    if(!isset($item['ss_type']) || strlen($item['ss_type']) <= 0 )
      return $string;

    switch($item['ss_type'])
    {
      case 'sets_display':
        $string = 'Complete Filter Set';
      break;
      case 'parts_display':
        $string = 'Individual Filter';
      break;
      case 'holders_display':
        $string = 'Cube, Slider, Ring';
      break;
      case 'accessories_display':
        $string = 'Filter Accessory';
      break;
      case 'reclaimed_cubes_display':
        $string = 'Reclaimed Cube';
      break;
      case 'fluorochrome':
        $string = 'Fluorochrome';
      break;
      case 'custom_inventory':
        $string = 'Inventory Item';
      break;
    }
    return $string;
  }

  private function setUrl($url)
  {
    $url_array = parse_url($url);
    return $url_array['path'];
  }

  private function validateOutput($json)
  {
    $data = json_decode($json, TRUE);

    if(!isset($data['response']))
      return;

    if(!isset($data['response']['numFound']))
      return;

    if($data['response']['numFound'] <= 0 )
      return;

    foreach($data['response']['docs'] AS &$item)
    {
      $item['ss_url'] = $this->setUrl($item['ss_url']);
      $item['number_and_name'] = $this->setNumberAndName($item);
      $item['number_or_name'] = $this->setNumberOrName($item);
      $item['item_type'] = $this->setItemType($item);
      $item['discontinued'] = isset($item['bs_field_discontinued']) && $item['bs_field_discontinued'] ? true : false;
      $item['target'] = $item['index_id'] === 'files' ? true : false;
    }

    $this->results = json_encode($data['response']);

  }

  public function setResults()
  {
    
    $url = $this->getUrl();

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $this->getUrl()); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

    $response=curl_exec($ch);

    if($response !== false)
    {
      $this->validateOutput($response);
    }

    curl_close($ch); 

  }

  public function getResults()
  {
    return $this->results;
  }

}
