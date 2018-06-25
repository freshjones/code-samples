<?php

namespace Api\Controllers;

use Api\Models\ModelInterface;

class BranchController
{
  private $model;

  public function __construct(ModelInterface $model)
  {
    $this->model = $model;
  }

  public function __invoke()
  { 
    $rawData = file_get_contents("php://input");
    $params = json_decode($rawData,true);
    $data = $this->model->getAll($params);
    $result = $data ? $data : array();
    header("Access-Control-Allow-Origin: *");
    header('Content-type: application/json');
    echo json_encode($result);
  }

}
