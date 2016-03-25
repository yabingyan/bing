<?php
require_once ("../settings.php");
require_once (BASE_PATH . "/include/function.php");
require_once(BASE_PATH."/include/medoo.php");

#检查是否含有sessionid
/*
if(!isset($_SERVER["HTTP_JSSID"])){
	session_start();
	header("JSSID:".session_id()); 
}else{
	session_id($_SERVER["HTTP_JSSID"]);
	session_start();
} 
*/

	$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];//接受android发来的数据
	logger("postStr=$postStr");
	if(empty($postStr)) 
	{
	  echo "";
	  return;
	}
	/*$bruce=fopen("data.txt",'w+');
	fwrite($bruce, $postStr);
	fclose($bruce);
	*/
	
	//$postStr = '{"sign":"d431ed0cfa988b520ff934fe87cdb91e","rtimes":"1","timestamp":1409035218,"appkey":"888","sn":"89860312100204312740","method":"sort","request":{"vip_post":"免费","weclome_flag":"人气最高","sort_flag":"历史"}}';
	//echo($postObj);
	#appkey=001&goods_id=141&method=product_info&rtimes=1&sn=89860089261479375945&timestamp=1448174147337&uid=12&secret=753159842564855248546518489789签名啦啦啦
	//公共的包头
	$postObj = json_decode($postStr,true);//返回数组形式
	$sign = $postObj["sign"];
	$rtimes = $postObj["rtimes"];
	$timestamp = $postObj["timestamp"];
	$appkey = $postObj["appkey"];
	$sn = $postObj["sn"];
	$method = $postObj["method"];
	$request = $postObj["request"];
	
	unset($postObj["sign"]);
	
	$keys = array_keys($postObj);
	$values = array_values($postObj);
	$count = count($keys);
	
	$keys1 = array_keys($request);
	$values1 = array_values($request);
	$count1 = count($keys1);
	
	$str = array();
	$j = 0;
	for($i = 0; $i < $count; $i ++)
	{
		if(strcmp($keys[$i],"request"))
		{
			$str[$j]=$keys[$i]."=".$values[$i]; 
			$j++;
		}
		else
		{
			for($k = 0; $k < $count1; $k ++)
			{
				$str[$j]=$keys1[$k]."=".$values1[$k];
				//echo $str[$j]."<br>";
				$j ++;
			}
		}
	}
	
	sort($str,SORT_STRING);
	
	$count = count($str);
	$rst = "";
	for($i = 0; $i < $j; $i ++)
	{
		$rst .= $str[$i].'&';
	}
	$secret="secret=yabingyan";
	$rst .= $secret;//字符串连接成功
	#logger($rst."签名啦啦啦");

	$serverSign = md5($rst);
	$arr = array();
	
	logger($sign."签名");
	logger($serverSign."生成签名");

	$request['from_app'] = 1;
	
	if(strcmp($sign,$serverSign)){
		$state = "签名错啦,请查看是否有参数是中文，且没有用url_encode编码";
		$arr['state'] = $state;
		
		logger('签名错啦,请查看是否有参数是中文，且没有用url_encode编码');
	}else{
		//$ip = $_SERVER["REMOTE_ADDR"];
		//echo "您的IP为".$ip;
		include_once($method.".php");
	}
	$result = json_encode($arr);
	logger("result=".$result."\n");
	echo $result;
?>