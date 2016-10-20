<?PHP
// require_once 'functions.php';
require_once 'facesdk.php';
########################
###     example      ###
########################
// $facepp = new Facepp();

// #detect image by url
// $params = array('url' => 'http://www.faceplusplus.com.cn/wp-content/themes/faceplusplus/assets/img/demo/1.jpg');
// $response = $facepp->execute('/detection/detect', $params);
// print_r($response);

#detect image by url

$response = face('https://mp.weixin.qq.com/cgi-bin/getimgdata?token=613703288&msgid=409476262&mode=large&source=&fileId=0&ow=-1');

print_r($response);
