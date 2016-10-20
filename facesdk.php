<?php

//FACE++ 参数 自行到face++官网注册并创建应用
define("API_KEY", "&api_key=cd9e37360a9acef2262299d7c476efea"); //你的face++应用 api_key
define("API_SECRET", "&api_secret=hspZPahHULzI_WLxyc31z-yzyUvts1yE"); //你的face++应用 api_secret
define("ATTRIBUTE", "&attribute=glass,pose,gender,age,race,smiling"); //需要返回的内容的参数
define("DETECT_URL", "http://apius.faceplusplus.com/v2/detection/detect?"); //检测给定图片(Image)中的所有人脸(Face)的位置和相应的面部属性api地址
define("LANDMARK_URL", "http://apius.faceplusplus.com/detection/landmark?"); //检测给定人脸(Face)相应的面部轮廓，五官等关键点的位置，包括25点和83点两种模式api地址
define("COMPARE_URL", "https://apius.faceplusplus.com/v2/recognition/compare?"); //计算两个Face的相似性以及五官相似度api地址
define("TYPE", "&type=83p"); //83点模式

// 调用人脸识别的API返回识别结果
function face($imgUrl) {
    // face++ 链接
    $jsonStr = curl_get_contents(DETECT_URL . API_KEY . API_SECRET . "&url=" . $imgUrl . ATTRIBUTE);
    $replyDic = json_decode($jsonStr, true);
    $faceArray = $replyDic['face'];

    $resultStr = "";

    for ($i = 0; $i < count($faceArray); $i++) {

        $resultStr .= "<----第" . ($i + 1) . "张脸---->\n";

        $tempFace = $faceArray[$i];
        $faceId = $tempFace['face_id'];

        $tempAttr = $tempFace['attribute'];
        // 年龄：包含年龄分析结果
        // value的值为一个非负整数表示估计的年龄, range表示估计年龄的正负区间
        $tempAge = $tempAttr['age'];
        // 性别：包含性别分析结果
        // value的值为Male/Female, confidence表示置信度
        $tempGenger = $tempAttr['gender'];
        // 种族：包含人种分析结果
        // value的值为Asian/White/Black, confidence表示置信度
        $tempRace = $tempAttr['race'];
        // 微笑：包含微笑程度分析结果
        //value的值为0-100的实数，越大表示微笑程度越高
        $tempSmiling = $tempAttr['smiling'];

        // 返回性别
        $sex = $tempGenger['value'];
        if ($sex === "Male") {
            $resultStr .= "性别：男\n";
        } else if ($sex === "Female") {
            $resultStr .= "性别：女\n";
        }

        //返回年龄
        $maxAge = $tempAge['value'] + ($tempAge['range']) / 2;
        $age = ceil($maxAge);
        $resultStr .= "年龄：" . $age . "岁左右吧~ \n";

        //返回种族
        if ($tempRace['value'] === "Asian") {
            $resultStr .= "肤色：很健康哦~\n";
        } else if ($tempRace['value'] === "White") {
            $resultStr .= "肤色：皮肤好白哟！^ 3^\n";
        } else if ($tempRace['value'] === "Black") {
            $resultStr .= " 肤色：你有点黑？！！！\n";
        }

        //返回微笑度
        $smiling = intval($tempSmiling['value']);
        $smile = round($tempSmiling['value'], 3);
        $resultStr .= "微笑：" . $smile . "％\n";

        //计算颜值
        $yanzhi = getYanZhi($faceId, $smiling);
        $resultStr .= "外貌协会专家评分：" . $yanzhi . "分\n";
        $resultStr .= "\xe2\x9c\xa8本机器人想说：\n";
        switch ($yanzhi) {
        case $yanzhi > 94:
            $resultStr .= "这颜值，爆表了！\n";
            break;
        case $yanzhi > 87:
            $resultStr .= "你这么好看，咋不上天呢！\n";
            break;
        case $yanzhi > 82:
            $resultStr .= "百看不厌，继续加油！\n";
            break;
        case $yanzhi > 72:
            $resultStr .= "还好，还能看！\n";
            break;
        case $yanzhi > 67:
            $resultStr .= "哎，只是丑的不明显！\n";
            break;
        case $yanzhi > 62:
            $resultStr .= "如果有钱，可以去整整！\n";
            break;
        default:
            $resultStr .= "让我静静，你家没镜子么？\n";
        }
    }

    //图片中两个人时，计算相似度
    if (count($faceArray) === 2) {
        // 获取face_id
        $tempFace1 = $faceArray[0];
        $tempId1 = $tempFace1['face_id'];
        $tempFace2 = $faceArray[1];
        $tempId2 = $tempFace2['face_id'];

        // face++ 链接
        $jsonStr1 = curl_get_contents(COMPARE_URL . API_KEY . API_SECRET . "&face_id2=" . $tempId2 . "&face_id1=" . $tempId1);
        $replyDic1 = json_decode($jsonStr1, true);

        //取出相似程度
        $tempResult = $replyDic1['similarity'];

        $tempSimilarity = $replyDic1['component_similarity'];
        $tempEye = $tempSimilarity['eye'];
        $tempEyebrow = $tempSimilarity['eyebrow'];
        $tempMouth = $tempSimilarity['mouth'];
        $tempNose = $tempSimilarity['nose'];

        $resultStr .= "<----相似分析---->\n";
        $resultStr .= "眼睛：" . round($tempEye, 3) . "％\n";
        $resultStr .= "眉毛：" . round($tempEyebrow, 3) . "％\n";
        $resultStr .= "嘴巴：" . round($tempMouth, 3) . "％\n";
        $resultStr .= "鼻子：" . round($tempNose, 3) . "％\n";

        $resultStr .= "\n<----匹配结果---->\n两人相似程度：" . round($tempResult, 3) . "％\n";

        if ($tempResult > 70) {
            $resultStr .= "哇塞！绝对的夫妻相了！\n";
        } elseif ($tempResult > 50) {
            $resultStr .= "哎哟，长得挺像！你们快点在一起吧！\n";
        } else {
            $resultStr .= "0.0 长得不太一样哦。\n";
        }

    }

    //如果没有检测到人脸
    if ($resultStr === "") {
        $resultStr = "对不起,俺没有识别出来，请换张正脸照试试=.=";
    }

    return $resultStr;

}

//颜值算法
function getYanZhi($faceId, $smiling) {

    $jsonStr = curl_get_contents(LANDMARK_URL . API_KEY . API_SECRET . "&face_id=" . $faceId . TYPE);

    if ($jsonStr != false) {
        $replyDic = json_decode($jsonStr, true);

        $result = $replyDic['result'];
        $landmarkArry = $result[0];
        $landmark = $landmarkArry['landmark'];

        $right_eyebrow_left_corner = $landmark['right_eyebrow_left_corner'];
        $left_eyebrow_right_corner = $landmark['left_eyebrow_right_corner'];

        $left_eye_left_corner = $landmark['left_eye_left_corner'];
        $left_eye_right_corner = $landmark['left_eye_right_corner'];

        $mouth_left_corner = $landmark['mouth_left_corner'];
        $mouth_right_corner = $landmark['mouth_right_corner'];

        $nose_left = $landmark['nose_left'];
        $nose_right = $landmark['nose_right'];
        $nose_contour_lower_middle = $landmark['nose_contour_lower_middle'];

        $right_eye_left_corner = $landmark['right_eye_left_corner'];
        $right_eye_right_corner = $landmark['right_eye_right_corner'];

        $contour_left1 = $landmark['contour_left1'];
        $contour_right1 = $landmark['contour_right1'];
        $contour_chin = $landmark['contour_chin'];
        $contour_left6 = $landmark['contour_left6'];
        $contour_right6 = $landmark['contour_right6'];

        //计算两眉头间的距离
        $c1 = distance($left_eyebrow_right_corner['x'], $left_eyebrow_right_corner['y'], $right_eyebrow_left_corner['x'], $right_eyebrow_left_corner['y']);

        //眉毛之间的中点坐标;
        $c1_x = ($right_eyebrow_left_corner['x'] - $left_eyebrow_right_corner['x']) / 2 + $left_eyebrow_right_corner['x'];
        $c1_y = ($right_eyebrow_left_corner['y'] - $left_eyebrow_right_corner['y']) / 2 + $left_eyebrow_right_corner['y'];

        //眉毛中点到鼻子最低处的距离
        $c2 = distance($nose_contour_lower_middle['x'], $nose_contour_lower_middle['y'], $c1_x, $c1_y);

        //眼角之间的距离
        $c3 = distance($left_eye_right_corner['x'], $left_eye_right_corner['y'], $right_eye_left_corner['x'], $right_eye_left_corner['y']);

        //鼻子的宽度
        $c4 = distance($nose_left['x'], $nose_left['y'], $nose_right['x'], $nose_right['y']);

        //脸的宽度
        $c5 = distance($contour_left1['x'], $contour_left1['y'], $contour_right1['x'], $contour_right1['y']);

        //下巴到鼻子下方的高度
        $c6 = distance($contour_chin['x'], $contour_chin['y'], $nose_contour_lower_middle['x'], $nose_contour_lower_middle['y']);

        //眼睛的大小
        $c7_left = distance($left_eye_left_corner['x'], $left_eye_left_corner['y'], $left_eye_right_corner['x'], $left_eye_right_corner['y']);
        $c7_right = distance($right_eye_left_corner['x'], $right_eye_left_corner['y'], $right_eye_right_corner['x'], $right_eye_right_corner['y']);

        //嘴巴的大小
        $c8 = distance($mouth_left_corner['x'], $mouth_left_corner['y'], $mouth_right_corner['x'], $mouth_right_corner['y']);

        //嘴巴处的face大小
        $c9 = distance($contour_left6['x'], $contour_left6['y'], $contour_right6['x'], $contour_right6['y']);

        /* 开始计算步骤 */
        $yourmark = 100;
        $mustm = 0;

        //眼角距离为脸宽的1/5，
        $mustm += abs(($c3 / $c5) * 100 - 25);

        //鼻子宽度为脸宽的1/5
        $mustm += abs(($c4 / $c5) * 100 - 25);

        //眼睛的宽度，应为同一水平脸部宽度的!/5
        $eyepj = ($c7_left + $c7_right) / 2;
        $mustm += abs($eyepj / $c5 * 100 - 25);

        //理想嘴巴宽度应为同一脸部宽度的1/2
        $mustm += abs(($c8 / $c9) * 100 - 50);

        //下巴到鼻子下方的高度 == 眉毛中点到鼻子最低处的距离
        $mustm += abs($c6 - $c2);

        return round($yourmark - $mustm + $smiling / 10, 3);
    } else {
        return 60;
    }

}

//两点之间的距离
function distance($px1, $py1, $px2, $py2) {
    return sqrt(abs(pow($px2 - $px1, 2)) + abs(pow($py2 - $py1, 2)));
}

function curl_get_contents($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    $result = curl_exec($ch);
    return $result;
}