<?php

class Crawl{
  public $data = null;
  public function __construct($options =[]){
    
  }

  public static function get_html($url = null){
    if(!$url){return;}
    $html = file_get_contents($url);
    return $html;
  }
}