<?php

define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/common_define/common_config.inc");

$sFileInfo = '';
$headers = array();

foreach($_SERVER as $k => $v) {
    if(substr($k, 0, 9) == "HTTP_FILE") {
        $k = substr(strtolower($k), 5);
        $headers[$k] = $v;
    } 
}

/**
 * 파일을 저장할 절대경로를 반환한다
 */
function getFilePath() {
    return "/" . date("Y")
        . "/" . date("m") . "/" . date("d") . "/";
}

/**
 * 중복되지않는 파일명생성
 */
function getUniqueNm(){

    $filename = substr(time() . md5(uniqid()) , 0 , 20);

    if (is_file($filename)) {
        $this->getUniqueNm();
    }

    return $filename;
}

$file = new stdClass;
$file->name = str_replace("\0", "", rawurldecode($headers['file_name']));
$file->size = $headers['file_size'];
$file->content = file_get_contents("php://input");
$filename_ext = strtolower(array_pop(explode('.',$file->name)));
$allow_file = array("jpg", "png", "bmp", "gif"); 

if(!in_array($filename_ext, $allow_file)) {
    echo "NOTALLOW_".$file->name;
} else {
    $uploadDir = $_SERVER["SiteHome"]
                 . INC_PATH
                 . SITE_DEFAULT_NOTICE_IMG_FILE
                 . getFilePath();
    if(!is_dir($uploadDir)){
        mkdir($uploadDir, 0777, true);
    }

    $file->name = getUniqueNm() . "." . $filename_ext;
    //$newPath = $uploadDir . iconv("utf-8", "cp949", $file->name);
    $newPath = $uploadDir . $file->name;
    
    if(file_put_contents($newPath, $file->content)) {
        $sFileInfo .= "&bNewLine=true";
        $sFileInfo .= "&sFileName=".$file->name;
        $sFileInfo .= "&sFileURL=" . SITE_DEFAULT_NOTICE_IMG_FILE . getFilePath() .$file->name;
    }

    //echo $uploadDir;
    
    echo $sFileInfo;
}
?>
