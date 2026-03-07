<?php

/**
 * RequestApi Class
 * - crawl.myntinc.com へアクセスするためのクラス
 * - urlからページのHTML（静的）を取得するAPIを呼び出すためのクラス
 * - 対象言語 : PHP
 * 
 * [Prepare]
 * - setting.jsonファイルを作成してください。
 * ```[ex]
 * {
*   "endpoint"  : "http://uranow.jp/endpoint/",
*   "secret"    : "@your secret-key"
* }
 * ```
 * - 本モジュールと同じディレクトリに設置するのが望ましいですが、別の場所に設置する場合は、`$this->setting_json_path`のパスを適切に変更してください。
 * 
 * 
 * [Howto]
 * - 以下のように、APIへアクセスして、jsonデータを受け取る。
 * ```[ex : site_info.php]
 * <?php
 * require_once __DIR__."/request_api.php";
 * $res = new RequestApi([
 *   "url" => "html取得したいURL",
 * ]);
 * 
 * // 受け取った情報は、"datas"プロパティに格納されている。
 * echo "<pre>";
 * print_r($res->datas);
 * ```
 * - status : "success"であれば、正常にデータが取得できている。"error"の場合は、結果が無いまたはパラメータが不正。
 * 
 * [Modules]
 * - APIのモジュールについては、LeoのAPIドキュメントを参照または管理者に相談してください。
 * 
 * [caution]
 * - settiong.jsonは、webサイトでアクセスユーザーがダウンロードできない場所に設置してください。（またはファイルアクセスかパーミッションを適切に設置してください）
 * 
 */

class RequestApi{
  public $datas = null;
  public $html  = null;
  private $setting_json_path = null;
  private $body;
  private $setting;
  private $timestamp;
  private $signature;
  private $headers;

  public function __construct(array $body){
    $this->setting_json_path = __DIR__."/setting.json"; // setting.jsonがこのファイルとは別の場所に配置する場合はパスを指定してください。
    $this->body      = json_encode($body);
    $this->setting   = $this->load_setting();
    $now             = new DateTimeImmutable('now', new DateTimeZone('UTC'));
    $this->timestamp = $now->format(DateTimeInterface::ATOM);
    $this->signature = $this->create_signature();
    $this->headers   = $this->create_header();
    // print_r($this->headers);
    $res = $this->endpoint_send();
    // print_r($res);exit;
    $this->datas = json_decode($res, true);
    $this->html = $this->datas["html"];
    
  }

  private function load_setting(){
    if(!is_file($this->setting_json_path)){
      http_response_code(404);
      exit('Not found "setting.json"');
    }
    $json = file_get_contents($this->setting_json_path);
    return json_decode($json, true);
  }
  
  private function create_signature(){
    return hash_hmac(
      'sha256',
      $this->timestamp . "\n" . $this->body,
      $this->setting["secret_key"]
    );
  }
  
  private function create_header(){
    return [
      'Content-Type: application/json',
      'X-TIMESTAMP: ' . $this->timestamp,
      'X-SIGNATURE: ' . $this->signature,
    ];
  }
  
  private function endpoint_send(){
    $ch = curl_init($this->setting["endpoint"]);
    curl_setopt_array($ch, [
      CURLOPT_POST           => true,
      CURLOPT_POSTFIELDS     => $this->body,
      CURLOPT_HTTPHEADER     => $this->headers,
      CURLOPT_RETURNTRANSFER => true
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
  }
}