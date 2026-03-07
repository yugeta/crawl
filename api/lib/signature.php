<?php

class Signature{
  public $datas = null;
  public $message = null;

  public function __construct($options=[]){
    if(!$options || !@$options["signature"] || !@$options["timestamp"]){return;}

    $this->check_timestamp($options["timestamp"]);

    // データの取得
    $endpoint_data = $this->load_setting_json();
    if(!$endpoint_data || !$endpoint_data["secret_key"]){
      $this->message = "signature not found";
      return;
    }

    // IPアドレスチェック
    if($endpoint_data["ip"] && count($endpoint_data["ip"])){
      $access_ip = $_SERVER['REMOTE_ADDR'];
      $ip_error = $this->check_ip_match($endpoint_data["ip"], $access_ip);
      if(!$ip_error ){
        $this->message = "IP access denied ({$access_ip})";
        return;
      }
    }
    
    // 認証処理
    $signature_res = $this->check_signature($options["timestamp"], $endpoint_data["secret_key"], $options["signature"]);
    if($signature_res){
      $this->datas = 1;
    }
    else{
      $this->datas = 0;
    }
  }

  private function load_setting_json(){
    $path = __DIR__ . "/setting.json";
    if(!is_file($path)){return;}
    $json = file_get_contents($path);
    return json_decode($json, true);
  }

  // 5分以内
  private function check_timestamp($timestamp=null){
    try {
      $requestTime = new \DateTimeImmutable(
        $timestamp,
        new \DateTimeZone('UTC')
      );
    } catch (\Exception $e) {
      http_response_code(400);
      exit('invalid timestamp format');
    }

    $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    $diffSeconds = abs($now->getTimestamp() - $requestTime->getTimestamp());

    if ($diffSeconds > 300) {
      http_response_code(401);
      exit('request expired');
    }
  }


  // 署名検証
  private function check_signature(string $timestamp, string $client_secret, string $signature){
    $rawBody   = file_get_contents('php://input');
    $expected = hash_hmac(
      'sha256',
      $timestamp . "\n" . $rawBody,
      $client_secret
    );

    if (hash_equals($expected, $signature)) {
      return true;
    }
    else{
      http_response_code(401);
      echo json_encode(['error' => 'invalid signature']);
      exit;
    }
  }

  // check-ipアドレス : マッチしたら（または$setting_ipがなければ） trueを返す
  private function check_ip_match($ip_arraws=[], $access_ip=null){
    $res = array_search($access_ip, $ip_arraws);
    return $res === false ? false : true;
  }
}