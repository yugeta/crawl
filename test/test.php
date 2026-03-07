<?php

require_once __DIR__."/../client/request_api.php";

$res = new RequestApi([
  "url" => "https://myntinc.com",
]);

echo "<pre>";
print_r($res->html);
exit;