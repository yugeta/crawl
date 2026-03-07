<?php

// die("--");

// require_once __DIR__."/lib/util.php";
require_once __DIR__."/lib/signature.php";
require_once __DIR__."/lib/crawl.php";

header('Content-Type: application/html');

$signature_data = new Signature([
  "timestamp" => @$_SERVER['HTTP_X_TIMESTAMP'] ?? '',
  "signature" => @$_SERVER['HTTP_X_SIGNATURE'] ?? '',
]);

if(!$signature_data->datas){
  http_response_code(401);
  if($signature_data->message){
    exit("Errot ! ". $signature_data->message);
  }
  else{
    exit('access error');
  }
}

$rawBody   = file_get_contents('php://input');


if(!$rawBody){
  http_response_code(400);
  exit('Not body error');
}

$_POST = json_decode($rawBody, true);

if(!$_POST["url"]){
  http_response_code(400);
  exit('Not api error');
}

$html = Crawl::get_html($_POST["url"]);
if(!$html){
  http_response_code(400);
  exit('Not get HTML');
}

$data = [
  "status" => $html ? "success" : "error",
  "html" => $html,
];
echo json_encode($data);

// echo "---";
// echo $html;

// require_once(__DIR__. "/../api/php/main.php");