<?
 /***********************************************************************************
 *** 프로젝트 : CyPress
 *** 개발영역 : File Enzine Module
 *** 개 발 자 : 김성진
 *** 개발날짜 : 2016.09.27
 ***********************************************************************************/

 class CLS_Enzine {

	 var $dbi;
	 var $sql;
	 var $rowsData;
	 var $idx;
	 var $order;
	 var $code;
	 var $curtime;
	 var $remoteip;


	 /***********************************************************************************
	 *** 초기화 ***
	 ***********************************************************************************/

	 function Init() {
		 $this->dbi		     = null;
		 $this->sql		     = null;
		 $this->rowsData = null;
		 $this->idx		     = null;
		 $this->order       = null;
		 $this->code       = null;
		 $this->curtime    = time();
		 $this->remoteip  = $_SERVER["REMOTE_ADDR"];
	 }


	 /***********************************************************************************
	 *** DB 연결 ***
	 ***********************************************************************************/

	 function DBCon($_DBS) {
		 $this->Init();

		 $this->dbi = new CLS_DBSet($_DBS);
		 $CDbcon = new CLS_DBConect($this->dbi->host, $this->dbi->user, $this->dbi->passwd, $this->dbi->name, $this->dbi->alias, $this->dbi->charset, $this->dbi->collate);

		 return $CDbcon;
	 }


	/***********************************************************************************
	*** 낱장형 등록된 경로 가져오기 (Local)
	***********************************************************************************/

 	function getSheetPathFileList($_DBS) {
		$dbc = $this->DBCon($_DBS);
		$this->Init();

		$this->sql = "SELECT sheet_typset_seqno AS idx, typset_num, paper_name, paper_dvs, paper_color, paper_basisweight, ";
		$this->sql .= "print_amt, print_amt_unit, print_title, save_path ";
		$this->sql .= "FROM "._TBL_SHEET_TYPESET." ";
		$this->sql .= "WHERE save_yn = 'N' ORDER BY sheet_typset_seqno ASC";
		$this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		if (!trim($this->rowsData->error)) {
			if ($this->rowsData->num_rows > 0) {
				for ($i = 0; $i < $this->rowsData->num_rows; $i++) {
					 $nRes[$i]['idx'] = $this->rowsData->data[$i]['idx'];
					 $nRes[$i]['typset_num'] = $this->rowsData->data[$i]['typset_num'];
					 $nRes[$i]['paper_name'] = $this->rowsData->data[$i]['paper_name'];
					 $nRes[$i]['paper_dvs'] = $this->rowsData->data[$i]['paper_dvs'];
					 $nRes[$i]['paper_color'] = $this->rowsData->data[$i]['paper_color'];
					 $nRes[$i]['paper_bw'] = $this->rowsData->data[$i]['paper_basisweight'];
					 $nRes[$i]['print_amt'] = $this->rowsData->data[$i]['print_amt'];
					 $nRes[$i]['print_amt_unit'] = $this->rowsData->data[$i]['print_amt_unit'];
					 $nRes[$i]['print_title'] = $this->rowsData->data[$i]['print_title'];
					 $nRes[$i]['save_path'] = $this->rowsData->data[$i]['save_path'];
				}
			} else {
				$nRes = "FAILED";
			}
		} else {
			$nRes = "ERROR";
		}

		$dbc->DBClose();

		return $nRes;
	}


	 /***********************************************************************************
	  *** 낱장형 파일등록 (Local)
	  ***********************************************************************************/

	 function setSheetTypeFileDataInsertComplete($_DBS, $nFiles, $nFath, $nIdx) {
		 $dbc = $this->DBCon($_DBS);
		 $this->Init();

		 $sucCount = 0;
		 $dupCount = 0;
		 $errCount = 0;

		 for ($i = 0; $i < count($nFiles); $i++) {
			 if (substr($nFiles[$i], strlen($nFiles[$i]) - 3, 3) == "pdf") {
				 if (substr($nFiles[$i], strlen($nFiles[$i]) - 5, 5) != "L.pdf") {
					 $chkRes = $this->getLocalSheetTypeSetFileDataCheck($dbc, $nFath, $nFiles[$i], $nIdx);

					 if ($chkRes == "SUCCESS") {
						 $this->sql = "INSERT INTO " . _TBL_SHEET_TYPESET_FILE . " (";
						 $this->sql .= "file_path, save_file_name, origin_file_name, sheet_typset_seqno";
						 $this->sql .= ") VALUE (";
						 $this->sql .= "'" . $nFath . "', '" . $nFiles[$i] . "', '" . $nFiles[$i] . "', '" . $nIdx . "'";
						 $this->sql .= ")";
						 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

						 if (!trim($this->rowsData->error)) {
							 $sucCount++;
						 } else {
							 $errCount++;
						 }
					 } else if ($chkRes == "FAILED") {
						 $dupCount++;
					 } else {
						 $errCount++;
					 }
				 }
			 }
		 }

		 if ($sucCount > 0) {
			 $nRes = "SUCCESS";
		 } else if ($dupCount > 0) {
			 $nRes = "DUPLE";
		 } else {
			 $nRes = "ERROR";
		 }

		 $dbc->DBClose();

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 낱장형 파일 중복체크 (Local)
	  ***********************************************************************************/

	 function getLocalSheetTypeSetFileDataCheck($dbc, $nPath, $nFiles, $nIdx) {
		 $this->Init();

		 $this->sql = "SELECT sheet_typset_seqno AS idx FROM "._TBL_SHEET_TYPESET_FILE." ";
		 $this->sql .= "WHERE file_path = '".$nPath."' AND save_file_name = '".$nFiles."' ";
		 $this->sql .= "AND sheet_typset_seqno = '".$nIdx."' ORDER BY sheet_typset_seqno ASC";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 if ($this->rowsData->num_rows <= 0) {
				 $nRes = "SUCCESS";
			 } else {
				 $nRes = "FAILED";
			 }
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 낱장형 미리보기 파일등록 (Local)
	  ***********************************************************************************/

	 function setSheetTypePreviewFileInsertComplete($_DBS, $nFiles, $nFath, $nIdx) {
		 $dbc = $this->DBCon($_DBS);
		 $this->Init();

		 $sucCount = 0;
		 $dupCount = 0;
		 $errCount = 0;

		 for ($i = 0; $i < count($nFiles); $i++) {
			 if (substr($nFiles[$i], strlen($nFiles[$i]) - 3, 3) == "png") {
				 $chkRes = $this->getLocalSheetTypeSetPreviewFileDataCheck($dbc, $nFath, $nFiles[$i], $nIdx);

				 if ($chkRes == "SUCCESS") {
					 $this->sql = "INSERT INTO " . _TBL_SHEET_TYPESET_PREVIEW_FILE . " (";
					 $this->sql .= "file_path, save_file_name, origin_file_name, sheet_typset_seqno";
					 $this->sql .= ") VALUE (";
					 $this->sql .= "'" . $nFath . "', '" . md5($nFiles[$i]).".png" . "', '" . $nFiles[$i] . "', '" . $nIdx . "'";
					 $this->sql .= ")";
					 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

					 if (!trim($this->rowsData->error)) {
						 $sucCount++;
					 } else {
						 $errCount++;
					 }
				 } else if ($chkRes == "FAILED") {
					 $dupCount++;
				 } else {
					 $errCount++;
				 }
			 }
		 }

		 if ($sucCount > 0) {
			 $nRes = "SUCCESS";
		 } else if ($dupCount > 0) {
			 $nRes = "DUPLE";
		 } else {
			 $nRes = "ERROR";
		 }

		 $dbc->DBClose();

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 낱장형 미리보기 파일 중복체크 (Local)
	  ***********************************************************************************/

	 function getLocalSheetTypeSetPreviewFileDataCheck($dbc, $nPath, $nFiles, $nIdx) {
		 $this->Init();

		 $this->sql = "SELECT sheet_typset_seqno AS idx FROM "._TBL_SHEET_TYPESET_PREVIEW_FILE." ";
		 $this->sql .= "WHERE file_path = '".$nPath."' AND save_file_name = '".$nFiles."' ";
		 $this->sql .= "AND sheet_typset_seqno = '".$nIdx."' ORDER BY sheet_typset_seqno ASC";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 if ($this->rowsData->num_rows <= 0) {
				 $nRes = "SUCCESS";
			 } else {
				 $nRes = "FAILED";
			 }
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
  *** 낱장형 파일등록 업데이트 (Local)
  ***********************************************************************************/

	 function setSheetTypeFileUpdateComplete($_DBS, $nIdx) {
		 $dbc = $this->DBCon($_DBS);
		 $this->Init();

		 $this->sql = "UPDATE "._TBL_SHEET_TYPESET." SET ";
		 $this->sql .= "save_yn = 'Y' ";
		 $this->sql .= "WHERE sheet_typset_seqno = '".$nIdx."' LIMIT 1";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 $nRes = "SUCCESS";
		 } else {
			 $nRes = "ERROR";
		 }

		 $dbc->DBClose();

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 작업지시서 등록완료
	  ***********************************************************************************/

	 function setProduceOrdInsertComplete($_DBS, $shtRes, $selRes, $ptData, $brName) {
		 $dbc = $this->DBCon($_DBS);
		 $this->Init();

		 $today = date("Y-m-d H:i:s");
		 $nSize = $ptData[0]." ".$ptData[1];

		 if ($selRes['honggak_yn'] == "Y") $selRes['honggak_yn'] = "홍각";
		 else $selRes['honggak_yn'] = "돈땡";

		 $printTmpt = $selRes['honggak_yn']."-".substr($ptData[2], 1, 1)."도";

		 $this->sql = "INSERT INTO "._TBL_PRODUCE_ORD." (";
		 $this->sql .= "date, dvs, ord_dvs, typset_num, paper, size, print_tmpt, amt, amt_unit";
		 $this->sql .= ") VALUE (";
		 $this->sql .= "'".$today."', '".$selRes['dvs']."', '".$brName."', '".$shtRes['typset_num']."', '".$selRes['mat']."', '".$nSize."', ";
		 $this->sql .= "'".$printTmpt."', '".$shtRes['print_amt']."', '".$shtRes['print_amt_unit']."'";
		 $this->sql .= ")";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 $nRes = "SUCCESS";
		 } else {
			 $nRes = "ERROR";
		 }

		 $dbc->DBClose();

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 판형 정보 가져오기
	  ***********************************************************************************/

	 function getTypeSetDataValue($_DBS, $name) {
		 $dbc = $this->DBCon($_DBS);
		 $this->Init();

		 $this->sql = "SELECT typset_format_seqno AS idx, format_name, dlvrboard, subpaper, honggak_yn, mat ";
		 $this->sql .= "FROM "._TBL_TYPSET_FORMAT." ";
		 $this->sql .= "WHERE name = '".$name."' LIMIT 1";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 if ($this->rowsData->num_rows > 0) {
				 $nRes['idx'] = $this->rowsData->data[0]['idx'];
				 $nRes['dvs'] = $this->rowsData->data[0]['format_name']."_".$this->rowsData->data[0]['dlvrboard'];
				 $nRes['subpaper'] = $this->rowsData->data[0]['subpaper'];
				 $nRes['honggak_yn'] = $this->rowsData->data[0]['honggak_yn'];
				 $nRes['mat'] = $this->rowsData->data[0]['mat'];
			 } else {
				 $nRes = "FAILED";
			 }
		 } else {
			 $nRes = "ERROR";
		 }

		 $dbc->DBClose();

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 브랜드 정보 가져오기
	  ***********************************************************************************/

	 function getBrandNameDataValue($_DBS, $nIdx) {
		 $dbc = $this->DBCon($_DBS);
		 $this->Init();

		 $this->sql = "SELECT eb.name AS br_name ";
		 $this->sql .= "FROM basic_produce_print AS bpp, print AS pt, extnl_brand AS eb ";
		 $this->sql .= "WHERE bpp.print_seqno = pt.print_seqno AND pt.extnl_brand_seqno = eb.extnl_brand_seqno ";
		 $this->sql .= "AND bpp.typset_format_seqno = '".$nIdx."' ";
		 $this->rowsData = new CLS_DBQuery($this->sql, $dbc);

		 if (!trim($this->rowsData->error)) {
			 if ($this->rowsData->num_rows > 0) {
				 $nRes = $this->rowsData->data[0]['br_name'];
			 } else {
				 $nRes = "FAILED";
			 }
		 } else {
			 $nRes = "ERROR";
		 }

		 $dbc->DBClose();

		 return $nRes;
	 }

 }
?>