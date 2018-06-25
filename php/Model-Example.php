<?php

namespace Api\Models;

use PDO;

class Branch extends Model
{
  
  private $conditions;

  public function getOne($code)
  {

  }

  private function setCondition($key,$op,$values)
  {
    $this->conditions[] = array(
      'key' => $key,
      'op' => $op,
      'values' => $values
    );

  }

  private function getWhere()
  {

    $return =  array(
      'clause' => ''
    );

    if(!$this->conditions)
      return $return;
    
    $conditions = array();
    $replacements = array();
    foreach($this->conditions AS $condition)
    {
      $placeholders = array();
      foreach($condition['values'] AS $value)
      {
        $placeholders[] = '?';
        $replacements[] = $value;
      }
      $placeholders = implode(',', $placeholders );
      $conditions[] = "{$condition['key']} {$condition['op']} ({$placeholders})";
    }
    
    $return['clause'] = ' WHERE ' . implode(' AND ', $conditions);
    $return['replacements'] = $replacements;

    return $return;

  }

  private function setIds($ids=array())
  {
    $all = array();
    foreach($ids AS $id)
    {
      if(!is_numeric($id))
        continue;
      $all[] = $id;
    } 
    if($all)
    {
      $this->setCondition('code','IN',$all);
    }
  }

  public function getAll($params=array())
  {
    if(isset($params['ids']))
      $this->setIds($params['ids']);

    $where = $this->getWhere();

    $query = "SELECT code,name FROM branches" . $where['clause'];

    try {
      $stmt = $this->db->prepare($query);
      if($where['replacements'])
      {
        foreach($where['replacements'] AS $k => $v)
        {
          $stmt->bindValue($k+1,"{$v}");
        }
      }
      $stmt->execute();
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e) {
      return $e->getMessage();
    }

  }
 
}
