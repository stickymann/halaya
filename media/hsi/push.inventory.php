<?php

require_once(dirname(__FILE__).'/hsiconfig.php');
require_once(dirname(__FILE__).'/curlops.php');


//$data = array("objID"=>"536340","sku" => "881116", "name" => "IPHONE 5S 16GB SILVER AAA", "category" => "CELLPHONES");
//$data = array("sku" => "100010", "name" => "PVC COMBO PACK 1/2 AAA","category" => "CELLPHONES");
$data = array("category" => "CELLPHONES2");

$cfg		= new HSIConfig();
$config 	= $cfg->get_config();
$appurl		= $config['appurl'];
//$item_processing_opt = "api/v2/items/532058?recalculatePositions=1";
$item_processing_opt = "api/v2/items/536340";
$curlops 	= new CurlOps($config);

$url	= $appurl.$item_processing_opt;
print "[DEBUG]---> "; print("URL: ".$url); print( sprintf("\n[line %s - %s, %s]\n\n",__LINE__,__FUNCTION__,__FILE__) );
$data_json = json_encode($data);
print "[DEBUG]---> "; print("JSON: ".$data_json); print( sprintf("\n[line %s - %s, %s]\n\n",__LINE__,__FUNCTION__,__FILE__) );

//print "[DEBUG]---> "; print(http_build_query($data)); print( sprintf("\n[line %s - %s, %s]\n\n",__LINE__,__FUNCTION__,__FILE__) );

$response = $curlops->put_remote_data($url,$data_json,$status);
print "[DEBUG]---> "; print("RESPONSE: ".$response."\n"); print_r($status); print( sprintf("\n[line %s - %s, %s]\n\n",__LINE__,__FUNCTION__,__FILE__) );


?>
