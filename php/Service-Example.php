<?php

namespace Api\Services;

class SearchService
{
  private $errors;
  private $config;
  private $type;
  private $keywords;
  private $filters;
  private $sort;
  private $limit;
  //private $group;
  private $results;
  private $total;
  private $categories;
  private $models;

  public function __construct($config, $models)
  {
    $this->config = $config;
    $this->models = $models;
  }

  private function setError($error)
  {
    $this->errors[] = $error;
  }

  public function getErrors()
  {
    return $this->errors;
  }

  public function setType($type)
  {
    if(!in_array($type,$this->config['allowed_type']))
    {
      $this->setError('Type Not Valid');
      return;
    }
    $this->type = $type;
  }

  public function setKeyword($keyword)
  {
    $keyword = strip_tags($keyword);
    $keyword = preg_replace("/[^A-Za-z0-9., ]/", " ", $keyword);
    $keyword = preg_replace("/\s+/", " ", $keyword);
    $keyword = trim($keyword);
    if(!$keyword)
    {
      $this->setError('Keyword must not be empty');
      return;
    }
    $this->keywords = explode(" ", $keyword);
  }

  public function setFilters($filters)
  {
    if( !is_array($filters) || empty($filters) )
    {
      $this->setError('Filters must not be empty');
      return;
    }
    foreach($filters AS $key => $filter)
    {
      if(in_array($key,$this->config['allowed_filters']))
      {
        $filterMethod = 'setFilter' . ucfirst($key);
        if(!method_exists($this,$filterMethod))
        {
          $this->setError("Method {$filterMethod} Does not exist");
          return;
        }
        $this->{$filterMethod}($filter);
      }
    }
  }

  private function setFilter($k,$v)
  {
    $this->filters[$k] = $v;
  }

  private function setFilterLocation($locations = array())
  {

    if(!is_array($locations) || empty($locations))
      return;
   
    $allowed_locations = array();
    foreach($locations AS $location)
    {
      if(!is_numeric(trim($location)))
        continue;

      $allowed_locations[] = $location;
    }

    if($allowed_locations)
      $this->setFilter('branch_code',$allowed_locations);
   
  }

  private function setFilterProgram($programs = array())
  {

    if(!is_array($programs) || empty($programs))
      return;
    
    $allowed_programs = array();
    foreach($programs AS $program)
    {
      if(!is_numeric(trim($program)))
        continue;

      $allowed_programs[] = $program;
    }

    $this->setFilter('program_code',$allowed_programs);
   
  }

  private function setFilterClass($classes = array())
  {

    if(!is_array($classes) || empty($classes))
      return;
    
    $allowed_classes = array();
    foreach($classes AS $class)
    {
      if(!is_numeric(trim($class)))
        continue;

      $allowed_classes[] = $class;
    }

    $this->setFilter('class_code',$allowed_classes);
   
  }

  private function setFilterAge($ages = array())
  {

    if(!is_array($ages) || empty($ages))
      return;
    
    $allowed_ages = array();
    foreach($ages AS $key => $age)
    {
      if(!in_array($key,array('min','max')))
        continue;

      if(!is_numeric(trim($age)))
        continue;

      $allowed_ages[$key] = $age;
    }

    $this->setFilter('age',$allowed_ages);

  }

  private function setFilterGender($genders = array())
  {

    if(!is_array($genders) || empty($genders))
      return;

    $allowed_genders = array();
    foreach($genders AS $gender)
    {
      $gender = trim(strip_tags($gender));

      if(!$gender)
        continue;

      $gender = strtoupper($gender);

      if(!in_array($gender,$this->config['allowed_gender']))
        continue;

      $allowed_genders[] = $gender;
    }

    $this->setFilter('gender',$allowed_genders);

  }

  private function setFilterWeekday($days = array())
  {

    if(!is_array($days) || empty($days))
      return;
    
    $allowed_days = array();
    foreach($days AS $day)
    {
      $day = ucfirst($day);
      if(!in_array($day,$this->config['allowed_weekday']))
        continue;
      $allowed_days[] = $day;
    }

    if($allowed_days)
      $this->setFilter('days_offered',$allowed_days);
  }

  public function setSort($sorts)
  {

    if(!is_array($sorts) || empty($sorts))
      return;

    $allowed_sorts = array();
    foreach($sorts AS $idx => $ord)
    {
      
      if(!in_array($idx,$this->config['allowed_sort_idx']) || !in_array($ord,$this->config['allowed_sort_ord']))
          continue;

      $allowed_sorts[ $this->config['sort_idx_map'][$idx]  ] = $ord;
    }

    $this->sort = $allowed_sorts;
  }

  public function setLimit($limit)
  {
    $this->limit = $limit;
  }

  private function count()
  {
    $this->total = $this->models[$this->type]->count($this->keywords, $this->filters, $this->sort);

    if($this->limit && isset($this->limit['start']) && isset($this->limit['amount']))
    {

      $start = $this->total - $this->limit['amount'];

      if($start < 0)
        $start = 0;

      if($start < $this->limit['start'] )
      {
        $this->limit['start'] = $start;
      }
    }
  }
  
  public function search()
  {
    $this->count();
    $this->results = $this->models[$this->type]->search($this->keywords, $this->filters, $this->sort, $this->limit);
  }

  public function results()
  {
    if($errors = $this->getErrors())
      return array('message' => $errors);

    return array('data' => $this->results,'total'=>$this->total);
  }

}
