<?
class FileAttachDAO {

    const DEFAULT_PATH = '/home/dprinting/nimda';

	function FileAttachDAO() {
	}

	/**
	 * 파일을 저장할 절대경로를 반환한다
	 */
	function getFilePath($btlnm){
		return "/" . $btlnm . "/" . date("Y")
			. "/" . date("m") . "/" . date("d") . "/";
	}

	/**
	 * 중복되지않는 파일명생성
	 * @param path : 파일의 절대경로
	 */
	function getUniqueNm($path){

		$filename = substr(time() . md5(uniqid()) , 0 , 20);

		if ( is_file($path . $filename) ) {
			$this->getUniqueNm($path);
		}

		return $filename;
	}

	/**
	 * 폴더의 권한을 777로 변경한다
	 * @param path : 파일업로드 절대 경로
	 */
	function chkChmod($path){

		try {
			@exec("chmod 777 " . $path);
		} catch ( Exception $e ) {
			echo "\n path authority grant failure \n";
			return false;
		}
		/*
		   if ( !chmod($path , 0777) ) {
		   echo "\n path authority grant failure \n";
		   return false;
		   }
		 */
		return true;

	}

	/**
	 * 파일저장할 경로가 존재하는지 알아보고
	 * 없을경우 생성한 후 문제없으면 true 반환
	 * @param path : 파일을 저장할 절대경로
	 */
	function chkPath($path){


		if (is_dir(SELF::DEFAULT_PATH . "/" . $path) )  {
			return true;
		}

		$tmp = explode("/" , $path);

		for ( $x = 0; $x < count($tmp); $x++ ) {

			$chkdir .= "/" . $tmp[$x];

			if ( @is_dir(SELF::DEFAULT_PATH . $chkdir) ) continue;

            mkdir(SELF::DEFAULT_PATH . $chkdir, 0777, true); 
		}

		return true;
	}

	/**
	 * 파일의 확장자를 구한다
	 */
	function getExt($filename) {

		if ( !strstr($filename, ".") ) return false;

		$tmp = explode(".", $filename);
		return $tmp[1];
	} 

	/**
	 * 파일 업로드
	 * @param origin_name : 원본 파일명 
	 * @param file_path : 저장될 파일 경로(날짜는 자동 생성)
	 * @param tmp_name : 업로드 tmp 파일 
	 */
	function upLoadFile($param) {

        $rs = array();
		$path = $this->getFilePath($param["file_path"]);

		$ext = $this->getExt($param["origin_file_name"]);
        
        $ext = strtolower($ext);

        if ($ext == "jpg" || $ext == "jpeg" || $ext == "jpe" || 
            $ext == "jfif" || $ext == "gif" || $ext == "tif" || 
            $ext == "tiff" || $ext == "png" || $ext == "ai" ||
            $ext == "psd" || $ext == "cdr"|| $ext == "qxd" ||
            $ext == "qxt") {

		//if ( $ext == "php" || $ext == "html" || $ext == "inc" ) return false;
		    if ( $this->chkPath($path) !== true ) return false; 

            $unique_name = $this->getUniqueNm($path);

		    $save_name = SELF::DEFAULT_PATH . $path
			    . $unique_name . "." . $ext;
        
            if (move_uploaded_file($param["tmp_name"] , $save_name)) {
                $rs["file_path"] = $path;
                $rs["save_file_name"] = $unique_name . "." . $ext;

                return $rs;

            } else {
                echo "\n error on moving file \n";
                return false;
            }
        } else {
            return false;
        }

	}

	/**
	 * 썸네일 생성
	 * @param fs : 원본 파일경로 
	 */
	function makeThumbnail($param){

		$fs = $param["fs"];
		/*썸네일 생성*/
		$arrImgInfo = getimagesize($_SERVER["DOCUMENT_ROOT"] . $fs);
		$width_orig = $arrImgInfo[0];
		$height_orig = $arrImgInfo[1];
		$w_offset = 0;
		$h_offset = 0;

		//썸네일 사이즈
		$req_width = $param["req_width"];
		$req_height = $param["req_height"];

		//썸네일 PATH 
		$arrPath = explode(".", $fs);
		$thumb_path = $arrPath[0] . "_" . $req_width . "_" . $req_height . "." . $arrPath[1];

		if($height_orig > $width_orig){
			$h_offset = ((($req_width * ($height_orig/$width_orig))-$req_width)/2);
			$thumbCmd = $req_width.'x';
		}
		if($height_orig < $width_orig){
			$w_offset = ((($req_height * ($width_orig/$height_orig))-$req_height)/2);
			$thumbCmd = 'x'.$req_height;
		}
		if($height_orig == $width_orig){
			$thumbCmd = $req_width.'x'.$req_height;
		} 


		//썸내일 파일명 설정
		$size = "[".$req_width."-".$req_height."]";

		//cut offset설정
		$crop = $req_width.'x'.$req_height.'+'.$w_offset."+".$h_offset;

		$convertString = sprintf("convert %s -resize '%s>' -crop %s +repage  -quality 100 %s",
				$_SERVER["DOCUMENT_ROOT"] . $fs,
				$thumbCmd,
				$crop,
				$_SERVER["DOCUMENT_ROOT"] . $thumb_path
				);
        //echo "convertString=\n";
        //echo $convertString;
		@exec($convertString);
		/*썸네일 생성 끝*/ 
	}


	/**
	 * 업로드 한 파일/실제파일명을 tmp폴더 내에 특별한 값을 파일명으로 기록한다
	 * @param uniquename : 업로드 되는 때마다 고유하게 주어지는 값
	 * @param visualname : 사용자가 올린 파일명
	 * @param realname : 업로드 된 절대경로및 파일명
	 */
	function recordFilename($uniquename, $visualname, $realname) {

		$handle = @fopen($this->recordpath . $uniquename, "a+");

		if ( !$handle ) return false;

		@fwrite($handle, $visualname . "|" . $realname . "\n");
		@fclose($handle);

		return true;
	} 

    /**
     * @brief 엑셀 파일 제대로 업로드 된건지 초전역배열 체크
     *
     * @details http://php.net/manual/en/features.file-upload.php 참조
     *
     * @param $files = $_FILES 초전역배열
     * @param $name  = file 태그 name
     *
     * @return 파일이 제대로 존재하면 true 아니면 false
     */
    function checkExcelFiles($files, $name) {
        $ret = FALSE;

        // Undefined | Multiple Files | $_FILES Corruption Attack
        // If this request falls under any of them, treat it invalid.
        if (!isset($files[$name]['error']) ||
                is_array($files[$name]['error'])) {
           return false;
        }

        // Check $_FILES['upfile']['error'] value.
        switch ($files[$name]['error']) {
        case UPLOAD_ERR_OK :
            break;
        case UPLOAD_ERR_NO_FILE :
        case UPLOAD_ERR_INI_SIZE :
        case UPLOAD_ERR_FORM_SIZE :
            return false;
        }
        
        // Check File Extension.
        if (($ext = $this->getExt($files[$name]["name"]))) {
            $ext = strtolower($ext);

            if ($ext !== "xlsx" && $ext !== "xls") {
                return false;
            }
        } else {
            return false;
        }

        return true;
    }
}
?>

