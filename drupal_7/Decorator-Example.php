<?php


abstract class FC_ATCPreProcessDecorator implements FC_PreprocessAddToCartInterface
{

  protected $preprocessor;
  

  public function __construct(FC_PreprocessAddToCartInterface $preprocessor)
  { 
    $this->preprocessor   = $preprocessor;
  }

  public function getError()
  {
    return $this->preprocessor->getError();
  }

  public function hasError()
  {
    return $this->preprocessor->hasError();
  }

  public function setError($error)
  {
    return $this->preprocessor->setError($error);
  }

  public function getInput($key=null)
  {
    return $this->preprocessor->getInput($key);
  }

  public function getHelpers()
  {
    return $this->preprocessor->getHelpers();
  }

  public function getConnection()
  {
    return $this->preprocessor->getConnection();
  }

  public function getOutput()
  {
    return $this->preprocessor->getOutput();
  } 

  public function getFCData()
  {
    return $this->preprocessor->getFCData();
  }

  public function getProductData($key=null)
  {
    return $this->preprocessor->getProductData($key);
  }

  public function getFCValue($key)
  {
    return $this->preprocessor->getFCValue($key);
  }

  public function setFCValue($key,$value)
  {
    return $this->preprocessor->setFCValue($key,$value);
  }

  public function setShipUnits($units)
  {
    $this->preprocessor->setShipUnits($units);
  }

  public function getShipUnits()
  {
    return $this->preprocessor->getShipUnits();
  }

  abstract function setOutput();

}
