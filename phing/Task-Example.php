<?php

$basepath = realpath(__DIR__ . '/../../../');

require_once "phing/Task.php";

class syncDatabaseTask extends Task {

  
  private $from;
  private $to;
  private $outputfile;
  
  public function setFrom($str) 
    {
      $this->from = $str;
  }

  public function setTo($str)
  {
      $this->to = $str;
  }
  
  public function setLogfile($str)
  {
      $basepath = realpath(__DIR__ . '/../../../');
      
      $this->outputfile = $basepath . '/phing/environments/logs/running.txt';
  }
   
  /**
   * The main entry point method.
   */
  public function main() 
  {
      
      $switch='--skip-tables-key=exclude';
      
      if($this->from === 'development')
      {
          $switch='--tables-key=development';
      } 
          
      $command = "/usr/local/bin/drush --yes sql-sync @chroma-" . $this->from . " @chroma-" . $this->to . ' ' . $switch . ' >> ' . $this->outputfile . ' 2>&1';
      
      $output = array();
      $return = null;
      
      exec($command, $output);
      
  }
  
}
