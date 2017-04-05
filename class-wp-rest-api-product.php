<?php

class WP_Rest_API_Product{
  // Here initialize our namespace and resource name.
  public function __construct() {
      $this->namespace     = 'gaeasys/v1';
      $this->resource_name = 'product';
  }

  public function test(){
    return "test in WP_Rest_API_Product..";
  }
}
