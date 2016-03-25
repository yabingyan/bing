<?php
//该程序获取用户的手机号，然后将生成的随机验证码，

require_once (BASE_PATH . "/include/medoo.php");
require_once (BASE_PATH . "/include/sms.php");
//require_once (BASE_PATH . "/include/msg_func.php");


$arr = array("method" => $method, "status" =>200, "error" => "", "times_used" => 1, "timestamp" =>time());
$response = array(
	"exe_success"=>0,
	"has_send"=>0
);
//获取用户的电话号码
$mobile = htmlspecialchars($request["mobile"]);
$type =  htmlspecialchars($request["type"]);//1表示注册，2表示登录          ，3表示修改密码,4表示找回密码

if (!$mobile || !$type) {
	$arr["status"] =501;
	$arr["error"] = "Empty Data";
	goto error;
}
if (!check_phone($mobile)) {#手机号码不合法
	$arr["status"] = 502;
	$arr["error"] = "Phone Error";
	goto error;
}
$db = new medoo();
#查询手机号是否已注册
$phone = $db->get('user','uid',array('phone'=>$mobile));

if($type != 1){
	if(!$phone){
		$arr["status"] = 503;
		$arr["error"] = "Phone Error";
		goto error;
	}
}else{
	if($phone){
		$arr["status"] = 504;
		$arr["error"] = "Phone have been regesiter";
		goto error;
	}
}
#查询验证码数据表，和当前时间比对，60秒之后才能发送
$key = "CHECKCODE_".$mobile;
$var_code = $db->get('temp_meta',array('join_time'),array('AND'=>array('key'=>$key)));
#如果不是第一次发送，那么需要和当前时间比对，60秒之后才能发送
// var_dump($var_code);
// echo date('Y-m-d H:i:s',$var_code['join_time']);
if($var_code){
	#距离上次发送时间还未超时,不让发送
	if($var_code['join_time']>(time()-TIMEOUT)){
		$arr["status"] = 505;
		$arr["error"] = "time not out";
		goto error;
	}
}



$message = $db->get('msgModule','msgContext',array('type'=>$type));
if(!$message){
	$message = "【农资验证码】这是您的验证码，打死都不要告诉他人";
}
/* switch($type){
	case 1:
		$message = "【农资验证码】这是您注册的验证码，打死都不要告诉他人";
	break;
	case 2:
		$message = "【农资验证码】这是您登录的验证码，打死都不要告诉他人";
	break;
	case 3:
		$message = "【农资验证码】这是您修改密码的验证码，打死都不要告诉他人";
	break;
	case 4:
		$message = "【农资验证码】这是您找回密码的验证码，打死都不要告诉他人";
	break;
	default:
		$arr["status"] = 502;
		$arr["error"] = "type Error";
		goto error;
} */

#生成验证码
$random_num = createrandom();
#向手机发送验证码
if(sendSMS($mobile,$message.$random_num) != 1){
	#验证码发送失败
	$arr["status"] = 202;
	$arr["error"] = "Checknum send error";

	goto error;
}

//这里删除前需要做一个判断，判断 "key"=>"CHECKCODE_".$mobile 是否存在？？？（bing）

$db->delete("temp_meta",array(
	"key"=>"CHECKCODE_".$mobile
));

$db->insert("temp_meta",array(
	"key"=>"CHECKCODE_".$mobile,
	"value"=>$random_num,
	"join_time"=>time()
));

#记录发送
	
$db->insert("msg_log",array("to_id"=>1,"oid"=>1,"msg"=>$random_num,"time"=>date('Y-m-d h:i:s'),'phone'=>$mobile));

$response["exe_success"] = "1";
$response["has_send"] = "1";
#$response["num"]=$random_num;
error:
$arr["response"] = $response;
