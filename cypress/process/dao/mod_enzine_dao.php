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
	*** 낱장형 등록된 경로 가져오기 (Local)
	***********************************************************************************/

 	function getSheetPathFileList($conn) {
		$this->Init();

		$this->sql = "SELECT sheet_typset_seqno, typset_num, paper_name, paper_dvs, paper_color, paper_basisweight, ";
		$this->sql .= "print_amt, print_amt_unit, dlvrboard, print_title, save_path, specialty_items ";
		$this->sql .= "FROM "._TBL_SHEET_TYPESET." ";
		$this->sql .= "WHERE save_yn = 'N' ORDER BY sheet_typset_seqno ASC";
		$rs = $conn->Execute($this->sql);

		if ($rs && !$rs->EOF) {
			$i = 0;
			while ($rs && !$rs->EOF) {
					$nRes[$i]['idx'] = $rs->fields['sheet_typset_seqno'];
					$nRes[$i]['typset_num'] = $rs->fields['typset_num'];
					$nRes[$i]['paper_name'] = $rs->fields['paper_name'];
					$nRes[$i]['paper_dvs'] = $rs->fields['paper_dvs'];
					$nRes[$i]['paper_color'] = $rs->fields['paper_color'];
					$nRes[$i]['paper_bw'] = $rs->fields['paper_basisweight'];
					$nRes[$i]['print_amt'] = $rs->fields['print_amt'];
					$nRes[$i]['print_amt_unit'] = $rs->fields['print_amt_unit'];
					$nRes[$i]['dlvrboard'] = $rs->fields['dlvrboard'];
					$nRes[$i]['print_title'] = $rs->fields['print_title'];
					$nRes[$i]['save_path'] = $rs->fields['save_path'];
					$nRes[$i]['specialty_items'] = $rs->fields['specialty_items'];

					$i++;
					$rs->moveNext();
			}
		} else if ($rs->EOF) {
			$nRes = "FAILED";
		} else {
			$nRes = "ERROR";
		}

		return $nRes;
	}


	 /***********************************************************************************
	  *** 낱장형 파일등록 (Local)
	  ***********************************************************************************/

	 function setSheetTypeFileDataInsertComplete($conn, $nFiles, $nFath, $nIdx) {
		 $this->Init();

		 $sucCount = 0;
		 $dupCount = 0;
		 $errCount = 0;

		 for ($i = 0; $i < count($nFiles); $i++) {
			 if (substr($nFiles[$i], strlen($nFiles[$i]) - 3, 3) == "pdf") {
				 if (strpos($nFiles[$i], "L") == false) {
					 $chkRes = $this->getLocalSheetTypeSetFileDataCheck($conn, $nFath, $nFiles[$i], $nIdx);

					 if ($chkRes == "SUCCESS") {
						 $this->sql = "INSERT INTO " . _TBL_SHEET_TYPESET_FILE . " (";
						 $this->sql .= "file_path, save_file_name, origin_file_name, sheet_typset_seqno";
						 $this->sql .= ") VALUE (";
						 $this->sql .= "'" . $nFath . "', '" . $nFiles[$i] . "', '" . $nFiles[$i] . "', '" . $nIdx . "'";
						 $this->sql .= ")";
						 $rs = $conn->Execute($this->sql);

						 if ($rs == true) {
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

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 낱장형 파일 중복체크 (Local)
	  ***********************************************************************************/

	 function getLocalSheetTypeSetFileDataCheck($conn, $nPath, $nFiles, $nIdx) {
		 $this->Init();

		 $this->sql = "SELECT sheet_typset_seqno FROM "._TBL_SHEET_TYPESET_FILE." ";
		 $this->sql .= "WHERE file_path = '".$nPath."' AND save_file_name = '".$nFiles."' ";
		 $this->sql .= "AND sheet_typset_seqno = '".$nIdx."' ORDER BY sheet_typset_seqno ASC";
		 $rs = $conn->Execute($this->sql);

		 //$CCFile = new CLS_File;
		 //$CCFile->FileWrite(_FPATH, "\n".date("H:i:s")." sheettype 파일 체크 쿼리 ->".$this->sql."\n", "a+");

		 if ($rs && !$rs->EOF) {
			 $nRes = "FAILED";
		 } else if ($rs->EOF) {
			 $nRes = "SUCCESS";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 낱장형 미리보기 파일등록 (Local)
	  ***********************************************************************************/

	 function setSheetTypePreviewFileInsertComplete($conn, $nFiles, $nFath, $nIdx) {
		 $this->Init();

		 $sucCount = 0;
		 $dupCount = 0;
		 $errCount = 0;

		 for ($i = 0; $i < count($nFiles); $i++) {
			 if (substr($nFiles[$i], strlen($nFiles[$i]) - 3, 3) == "png") {
				 $chkRes = $this->getLocalSheetTypeSetPreviewFileDataCheck($conn, $nFath, $nFiles[$i], $nIdx);

				 if ($chkRes == "SUCCESS") {
					 $this->sql = "INSERT INTO " . _TBL_SHEET_TYPESET_PREVIEW_FILE . " (";
					 $this->sql .= "file_path, save_file_name, origin_file_name, sheet_typset_seqno";
					 $this->sql .= ") VALUE (";
					 $this->sql .= "'" . $nFath . "', '" . md5($nFiles[$i]).".png" . "', '" . $nFiles[$i] . "', '" . $nIdx . "'";
					 $this->sql .= ")";
					 $rs = $conn->Execute($this->sql);

					 if ($rs == true) {
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

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 낱장형 미리보기 파일 중복체크 (Local)
	  ***********************************************************************************/

	 function getLocalSheetTypeSetPreviewFileDataCheck($conn, $nPath, $nFiles, $nIdx) {
		 $this->Init();

		 $this->sql = "SELECT sheet_typset_seqno FROM "._TBL_SHEET_TYPESET_PREVIEW_FILE." ";
		 $this->sql .= "WHERE file_path = '".$nPath."' AND save_file_name = '".$nFiles."' ";
		 $this->sql .= "AND sheet_typset_seqno = '".$nIdx."' ORDER BY sheet_typset_seqno ASC";
		 $rs = $conn->Execute($this->sql);

		 if ($rs && !$rs->EOF) {
			 $nRes = "FAILED";
		 } else if ($rs->EOF) {
			 $nRes = "SUCCESS";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
  *** 낱장형 파일등록 업데이트 (Local)
  ***********************************************************************************/

	 function setSheetTypeFileUpdateComplete($conn, $nIdx) {
		 $this->Init();

		 $this->sql = "UPDATE "._TBL_SHEET_TYPESET." SET ";
		 $this->sql .= "save_yn = 'Y' ";
		 $this->sql .= "WHERE sheet_typset_seqno = '".$nIdx."' LIMIT 1";
		 $rs = $conn->Execute($this->sql);

		 if ($rs == true) {
			 $nRes = "SUCCESS";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 작업지시서 등록완료
	  ***********************************************************************************/

	 function setProduceOrdInsertComplete($conn, $shtRes, $selRes, $ptData, $brName) {
		 $this->Init();

		 $today = date("Y-m-d H:i:s");
		 $nSize = substr(trim($ptData[1]), strlen(trim($ptData[1])) - 8, 7);
		 $printTmpt = $ptData[2];

         $this->sql = "DELETE FROM " . _TBL_PRODUCE_ORD . " WHERE typset_num = '" . $shtRes['typset_num'] . "'";
         $rs = $conn->Execute($this->sql);

		 $this->sql = "INSERT INTO "._TBL_PRODUCE_ORD." (";
		 $this->sql .= "date, dvs, ord_dvs, typset_num, paper, size, print_tmpt, amt, amt_unit, specialty_items";
		 $this->sql .= ") VALUE (";
		 $this->sql .= "'".$today."', '".$selRes['dvs'].$shtRes['dlvrboard']."', '".$brName."', '".$shtRes['typset_num']."', '".$selRes['paper']."', '".$nSize."', ";
		 //$this->sql .= "'".$printTmpt."', '".$shtRes['print_amt']."', '".$shtRes['print_amt_unit']."', '".$shtRes['specialty_items']."'";
		 $this->sql .= "'".$printTmpt."', '".$shtRes['print_amt']."', '장', '".$shtRes['specialty_items']."'";
		 $this->sql .= ")";
		 $rs = $conn->Execute($this->sql);

		 if ($rs == true) {
			 $nRes = "SUCCESS";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 판형 정보 가져오기
	  ***********************************************************************************/

	 function getTypeSetDataValue($conn, $name) {
		 $this->Init();

		 $this->sql = "SELECT typset_format_seqno, format_name, subpaper, honggak_yn, paper ";
		 $this->sql .= "FROM "._TBL_TYPSET_FORMAT." ";
		 $this->sql .= "WHERE preset_name = '".$name."' LIMIT 1";
		 $rs = $conn->Execute($this->sql);

		 if ($rs && !$rs->EOF) {
			 $nRes['idx'] = $rs->fields['typset_format_seqno'];
			 $nRes['dvs'] = $rs->fields['format_name']."_";
			 $nRes['subpaper'] = $rs->fields['subpaper'];
			 $nRes['honggak_yn'] = $rs->fields['honggak_yn'];
			 $nRes['paper'] = $rs->fields['paper'];
		 } else if ($rs->EOF) {
			 $nRes = "FAILED";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }


	 /***********************************************************************************
	  *** 브랜드 정보 가져오기1
	  ***********************************************************************************/

	 function getBrandNameDataValue($conn, $nIdx) {
		 $this->Init();

		 $this->sql = "SELECT eb.name ";
		 $this->sql .= "FROM basic_produce_print AS bpp, print AS pt, extnl_brand AS eb ";
		 $this->sql .= "WHERE bpp.print_seqno = pt.print_seqno AND pt.extnl_brand_seqno = eb.extnl_brand_seqno ";
		 $this->sql .= "AND bpp.typset_format_seqno = '".$nIdx."' ";
		 $rs = $conn->Execute($this->sql);

		 if ($rs && !$rs->EOF) {
			 $nRes = $rs->fields['name'];
		 } else if ($rs->EOF) {
			 $nRes = "FAILED";
		 } else {
			 $nRes = "ERROR";
		 }

		 return $nRes;
	 }
 }
?>
