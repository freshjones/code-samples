<?php

abstract class FC_PreprocessAddToCart implements FC_PreprocessAddToCartInterface
{

  protected $helpers;
  protected $errors;
  protected $input;
  protected $fc_data;
  protected $shipunits;

  public function __construct($helpers, $errors, $input=null)
  { 

    //add the db connection object
    $this->helpers = $helpers;

    //add the error tracking object
    $this->errors = $errors;

    //only if we have an array and its not empty
    if(is_array($input) && !empty($input))
      $this->input = $input;

    //validate the input
    $this->validate();

    //set default ship units
    $this->setShipUnits(1);

  } 

  public function setShipUnits($units)
  {
    $this->shipunits = $units;
  }

  public function getShipUnits()
  {
    return $this->shipunits;
  }

  public function getInput($key=null)
  {
    if(is_null($key))
      return $this->input;

    return isset($this->input[$key]) ? $this->input[$key] : '';

  }

  public function hasError()
  {
    return $this->errors->hasError();
  }

  public function getError()
  {
    return $this->errors->getError();
  }

  public function setError($error)
  {
    $this->errors->setError($error);
  }

  public function getHelpers()
  {
    return $this->helpers;
  }

  public function getConnection()
  {
    return $this->helpers->getDBConnection();
  }

  protected function validate()
  {
    
    //if we have no data its an error
    if(!is_array($this->input))
    {
      $this->setError('No Input Data Given');
      return;
    }

    //if we dont have a quantity its an error
    if(!isset($this->input['quantity']))
    {
      $this->setError('No Quantity Specified');
      return;
    }

    //if we dont have a price its an error
    if(!isset($this->input['itemPrice']))
    {
      $this->setError('No Price Specified');
      return;
    }

    //if price is not numeric
    if(!is_numeric($this->input['itemPrice']))
    {
      $this->setError('Price Is Not Numeric');
      return;
    }

    //if we dont have a price its an error
    if($this->input['itemPrice'] <= 0)
    {
      $this->setError('Price must be Greater Than Zero');
      return;
    }
    
    //if we dont have a name its an error
    if(!isset($this->input['itemName']) || strlen($this->input['itemName']) < 0 )
    {
      $this->setError('No Name Specified');
      return;
    }

    //if we dont have a id its an error
    if(!isset($this->input['itemID']))
    {
      $this->setError('No ID Specified');
      return;
    }

    if(!is_numeric($this->input['itemID']))
    {
      $this->setError('ID Specified Is Not Numeric');
      return;
    }

    //if we dont have a id its an error
    //if(!isset($this->input['nodeID']))
    //{
    //  $this->setError('No Node ID Specified');
    //  return;
    //}

    //if(!is_numeric($this->input['nodeID']))
    //{
    //  $this->setError('Node ID Specified Is Not Numeric');
    //  return;
    //}

    //if we dont have a type its an error
    if(!isset($this->input['itemType']) || strlen($this->input['itemType']) < 0 )
    {
      $this->setError('No Type Specified');
      return;
    }
  }


  public function getFCData()
  {
    return $this->hasError() ? array('status' => 'error', 'message' => $this->errors->getError() ) : array('status' => 'success', 'data' => $this->fc_data);
  }

  public function getFCValue($key)
  {
      return isset($this->fc_data[$key]) ?  $this->fc_data[$key] : '';
  }

  public function setFCValue($key, $value)
  {
      $this->fc_data[$key] = $value;
  }

  public function setFCValueFromInput($inputKey,$fcKey)
  {
    if( isset($this->input[$inputKey]) && strlen($this->input[$inputKey]) )
      $this->setFCValue($fcKey, $this->input[$inputKey]);
  }

  public function setOutput()
  {
    if($this->hasError())
      return;

    //set id 
    $this->setFCValueFromInput('itemID','productid');

    //set the node ID
    $this->setFCValueFromInput('nodeID','nodeid');

    //set name 
    $this->setFCValueFromInput('itemName','name');

     //set price 
    $this->setFCValueFromInput('itemPrice','price');

    //set quantity
    $this->setFCValueFromInput('quantity','quantity');

    //set type
    $this->setFCValueFromInput('itemType','type');

    //set description
    $this->setFCValueFromInput('itemDescription','description');

    //set description
    $this->setFCValueFromInput('itemShipUnit','shipunits');

    //set notes
    $this->setFCValueFromInput('notes','notes');

  }

  public function getOutput()
  {
    //return json_encode( $this->getFCData() );

    return $this->getFCData();

  } 

}
