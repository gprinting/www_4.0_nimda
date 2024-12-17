<?php
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/common_define/common_config.inc");

// default redirection
$url = $_REQUEST["callback"].'?callback_func='.$_REQUEST["callback_func"];
$bSuccessUpload = is_uploaded_file($_FILES['Filedata']['tmp_name']);

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
function getUniqueNm() {

    $filename = substr(time() . md5(uniqid()) , 0 , 20);

    if (is_file($filename)) {
        $this->getUniqueNm();
    }

    return $filename;
}

// SUCCESSFUL
if($bSuccessUpload) {
	$tmp_name = $_FILES['Filedata']['tmp_name'];
	$name = $_FILES['Filedata']['name'];
	$filename_ext = strtolower(array_pop(explode('.',$name)));
	$allow_file = array("jpg", "png", "bmp", "gif");
	
	if(!in_array($filename_ext, $allow_file)) {
		$url .= '&errstr='.$name;
	} else {
		$uploadDir = $_SERVER["SiteHome"]
                     . SITE_NET_DRIVE
                     . SITE_DEFAULT_NOTICE_IMG_FILE
                     . getFilePath();

		if(!is_dir($uploadDir)){
			mkdir($uploadDir, 0777, true);
		}
		
        $name = getUniqueNm() . "." . $filename_ext;
		$newPath = $uploadDir.urlencode($name);
		
		@move_uploaded_file($tmp_name, $newPath);
		
		$url .= "&bNewLine=true";
		$url .= "&sFileName=".urlencode(urlencode($name));
		$url .= "&sFileURL=" . SITE_DEFAULT_NOTICE_IMG_FILE . getFilePath() . urlencode(urlencode($name));
	}
}
// FAILED
else {
	$url .= '&errstr=error';
}
	
header('Location: '. $url);
?>
