<?php

namespace App;

class Page extends Model
{
  
  //change the route key to get slug instead of ID
  public function getRouteKeyName()
  {
      return 'slug';
  }

  //create a relationship to the content model
  public function contents()
  {
      return $this->hasMany('App\Content');
  }

}
