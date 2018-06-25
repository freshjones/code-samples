<?php

/* CONCRETES */
class FC_ATCPreprocess_Set extends FC_PreprocessAddToCart
{

  protected $productData;

  public function __construct($helpers, $errors, $input=null)
  {

    parent::__construct($helpers, $errors, $input);

    $this->setProductData();

  }

  public function getProductData($key=NULL)
  {
    
    if($key)
      return isset($this->productData[$key]) ? $this->productData[$key] : false;

    return $this->productData;

  }  

  protected function setProductData()
  {
    $connection = $this->getConnection();

    $sql = "SELECT Name AS name, SetPrice AS price, ShippingUnit FROM Sets WHERE sID = {$connection->quote($this->getInput('itemID'))}";

    $result = $connection->queryRow($sql, array(), MDB2_FETCHMODE_ASSOC);

    $this->productData = $result;

  }

  public function setOutput()
  {
    parent::setOutput();
    
    $this->setFCValue('price', $this->productData['price']);

    if( $this->productData['ShippingUnit'] > $this->getShipUnits() )
    {
      $this->setShipUnits( $this->productData['ShippingUnit'] );
      $this->setFCValue('shipunits', $this->getShipUnits() );
    }

  }
   
}


class FC_ATCPreprocess_Holder extends FC_ATCPreprocess_Set
{

}

class FC_ATCPreprocess_Accessory extends FC_ATCPreprocess_Set
{

}
