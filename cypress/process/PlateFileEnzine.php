#!/usr/local/bin/php -q
<?
 /***********************************************************************************
 *** 프로 젝트 : CyPress
 *** 개발 영역 : 파일등록 엔진
 *** 개  발  자 : 김성진
 *** 개발 날짜 : 2016.09.29
 ***********************************************************************************/

 /***********************************************************************************
 *** 기본 인클루드
 ***********************************************************************************/

$curDirectory = dirname(__FILE__);
include_once($curDirectory . "/../../../inc/define/nimda/cypress_init.inc");
include_once($curDirectory . "/common/ConnectionPool.php");
include_once($curDirectory . "/dao/mod_enzine_dao.php");
include_once($curDirectory . "/../../../inc/common_lib/cypress_file.inc");
define("LOGPATH", $curDirectory . "/logs/" . date("Y_m_d"));
/***********************************************************************************
 *** 클래스 선언
 ***********************************************************************************/

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$CEnzine = new CLS_Enzine;


/***********************************************************************************
*** Log
***********************************************************************************/

CLS_File::FileWrite(LOGPATH, "\n-----------------------------------------------------------------------------\n\n", "a+");
CLS_File::FileWrite(LOGPATH, "\n".date("H:i:s")." -> [#################### Cypress 파일엔진 시작 ####################] \n", "a+");


/***********************************************************************************
 *** 시작
 ***********************************************************************************/

echo "\n====================================================================================\n";
echo "===================================파일엔진 시작====================================\n";
echo "====================================================================================\n\n";


 /***********************************************************************************
 *** 등록된 경로 가져오기
 ***********************************************************************************/

 $shtRes = $CEnzine->getSheetPathFileList($conn);


/***********************************************************************************
 *** 총 갯수
 ***********************************************************************************/

if (is_array($shtRes)) {
	echo "===> 총 갯수는 ".number_format(count($shtRes))."개 입니다.\n\n";
    CLS_File::FileWrite(LOGPATH, "\n".date("H:i:s")." -> [데이터 : ".number_format(count($shtRes))." 개]\n", "a+");
} else {
	echo "===> 총 갯수는 0개 입니다.\n\n";
    CLS_File::FileWrite(LOGPATH, "\n".date("H:i:s")." -> [데이터 : 0 개]\n", "a+");
}


 /***********************************************************************************
 *** 처리
 ***********************************************************************************/

 if (is_array($shtRes)) {
	 for ($z = 0; $z < count($shtRes); $z++) {
		  $num = $z + 1;
		  $nRes = $num."번 경로에 데이터 정리를 시작중... ";

		  if (substr($shtRes[$z]['save_path'], 0, 1) == "/") {
			  $nPath = $shtRes[$z]['save_path'];
		  } else {
			  $nPath = "/".$shtRes[$z]['save_path'];
		  }

		  $fullPath = _CYP_FILE_ENZINE_OUTPUT.$nPath;
         CLS_File::FileWrite(LOGPATH, "\n".date("H:i:s")." -> " . $fullPath . "\n", "a+");
        echo $fullPath;
		  if (is_dir($fullPath)) {
			  if ($handle = opendir($fullPath."/")) {
				  $nCount = 0;
				  while ($file = readdir($handle)) {
					  if ($file != "." && $file != "..") {
						  $fileName[$nCount] = $file;
						  $nCount++;
					  }
				  }
			  }
			  closedir($handle);

			  if (count($fileName) > 0) {
			      $nfPath = "/typeset/typset_file".$nPath."/";
				  $filRes = $CEnzine->setSheetTypeFileDataInsertComplete($conn, $fileName, $nfPath, $shtRes[$z]['idx']);
                  CLS_File::FileWrite(LOGPATH, "\n".date("H:i:s")." -> [".$num."번 엔진 파일 결과 : ".$filRes."]\n", "a+");

				  if ($filRes == "SUCCESS") {
			          $nfPath = "/typeset/typset_preview_file".$nPath."/";
					  $preRes = $CEnzine->setSheetTypePreviewFileInsertComplete($conn, $fileName, $nfPath, $shtRes[$z]['idx']);
                      CLS_File::FileWrite(LOGPATH, "\n".date("H:i:s")." -> [".$num."번 엔진 미리보기 파일 결과 : ".$preRes."]\n", "a+");

					  if ($filRes == "SUCCESS" && $preRes == "SUCCESS") {
						  // 미리보기 파일 카피
						  $fullDirPath = _WEB_FILE_ENZINE_PREVIEW.$nPath;
                          $labelFullDirPath = _WEB_FILE_ENZINE_LABEL.$nPath;

						  if (is_dir($fullDirPath) && is_dir($labelFullDirPath)) {
							  for ($i = 0; $i < count($fileName); $i++) {
								  if (substr($fileName[$i], strlen($fileName[$i]) - 3, 3) == "png") {
									  if (file_exists($fullPath."/".$fileName[$i])) {
										  $fnsRes = CLS_File::FileCopy($fullPath."/".$fileName[$i], $fullDirPath."/".md5($fileName[$i]).".png");
									  } else {
										  $fnsRes = "FAILED";
										  $nRes .= "복사 할 파일이 아직 생성되지 않았습니다.\n";
									  }
								  } else if (substr($fileName[$i], strlen($fileName[$i]) - 3, 3) == "pdf") {
                                      $tmp_file_path = explode("-", $fileName[$i]);
                                      if($tmp_file_path[6] == "P" || $tmp_file_path[6] == "L") {
                                          $label_file_name = explode("-", $fileName[$i])[0] . "-" . explode("-", $fileName[$i])[1] . ".pdf";
                                          CLS_File::FileCopy($fullPath."/".$fileName[$i], $labelFullDirPath."/". $label_file_name .".png");
                                      }
                                  }
							  }
						  } else {
							  $nPathCount = 0;
							  $nPathData = explode("/", $nPath);

							  for ($i = 1; $i < count($nPathData); $i++) {
								   if ($i == "1") {
                                       $nPathing = _WEB_FILE_ENZINE_PREVIEW."/".$nPathData[$i];
                                       $nLabelPathing = _WEB_FILE_ENZINE_LABEL."/".$nPathData[$i];
                                   }
								   else {
                                       $nPathing .= "/".$nPathData[$i];
                                       $nLabelPathing .= "/".$nPathData[$i];
                                       echo $nLabelPathing;
                                   }

								   if (!is_dir($nPathing)) {
                                       $old = umask(0);
									   mkdir($nPathing, 0777, true);
                                       umask($old);

                                       $nRes .= "[".$nPathing."] 해당 경로가 생성되었습니다.\n";
								   }

                                  if (!is_dir($nLabelPathing)) {
                                      $old = umask(0);
                                      mkdir($nLabelPathing, 0777, true);
                                      umask($old);

                                      $nRes .= "[".$nLabelPathing."] 해당 경로가 생성되었습니다.\n";
                                  }
							  }

							  if (is_dir($nPathing)) {
								  for ($i = 0; $i < count($fileName); $i++) {
									  if (substr($fileName[$i], strlen($fileName[$i]) - 3, 3) == "png") {
                                          if (file_exists($fullPath."/".$fileName[$i])) {
                                              $fnsRes = CLS_File::FileCopy($fullPath."/".$fileName[$i], $fullDirPath."/".md5($fileName[$i]).".png");
                                          } else {
                                              $fnsRes = "FAILED";
                                              $nRes .= "복사 할 파일이 아직 생성되지 않았습니다.\n";
                                          }
                                      }
								  }
							  } else {
								  $nRes .= "디렉토리가 생성되지 않았습니다.\n";
							  }

                              if(is_dir($nLabelPathing)) {
                                  for ($i = 0; $i < count($fileName); $i++) {
                                      echo $fileName[$i];
                                      if (substr($fileName[$i], strlen($fileName[$i]) - 3, 3) == "pdf") {
                                          $tmp_file_path = explode("-", $fileName[$i]);
                                          if ($tmp_file_path[6] == "P" || $tmp_file_path[6] == "L") {
                                              $label_file_name = explode("-", $fileName[$i])[0] . "-" . explode("-", $fileName[$i])[1] . ".pdf";
                                              CLS_File::FileCopy($fullPath . "/" . $fileName[$i], $labelFullDirPath . "/" . $label_file_name);
                                          }
                                      }
                                  }
                              }
						  }

						  if ($fnsRes == "SUCCESS") {
							  $updRes = $CEnzine->setSheetTypeFileUpdateComplete($conn, $shtRes[$z]['idx']);

							  if ($updRes == "SUCCESS") {
								  // 생산지시서 추가
								  $fNameData = explode("-", $fileName[0]);
                                  $preset_name = str_replace(".pdf", "", $fNameData[5]);
								  $selRes = $CEnzine->getTypeSetDataValue($conn, $preset_name);
								  $ptData = explode("_", $fNameData[5]);

								  if (is_array($selRes)) {
                                      // 타입정보 가져오기
                                      //$typRes = $CEnzine->getBrandNameDataValue($conn, $selRes['idx']);

									  if ($typRes != "ERROR" && $typRes != "FAILED") {
										  $insRes = $CEnzine->setProduceOrdInsertComplete($conn, $shtRes[$z], $selRes, $ptData, $typRes);

										  if ($insRes == "SUCCESS") {
											  $nRes .= "정상적으로 업데이트가 완료 되었습니다.";
										  } else {
											  $nRes .= "생산지시서가 생성되지 않았습니다.";
										  }
									  } else {
										  $nRes .= "브랜드 정보를 가져올 수 없습니다.";
									  }

								  } else {
									  $nRes .= "판형 정보를 가져올 수 없습니다.";
								  }
							  } else {
								  $nRes .= "최종 업데이트가 진행되지 않았습니다.";
							  }
						  } else {
							  $nRes .= "미리보기 파일이 복사되지 않았습니다.";
						  }
					  } else if ($preRes == "DUPLE") {
						  $nRes .= "중복 등록된 미리보기 파일입니다.";
					  } else {
						  $nRes .= "미리보기 파일이 존재하지 않습니다.";
					  }
				  } else if ($filRes == "DUPLE") {
					  $nRes .= "중복 등록된 파일입니다.";
				  } else {
					  $nRes .= "pdf 파일이 존재하지 않습니다.";
				  }
			  } else {
				  $nRes .= "파일 리스트가 존재하지 않습니다.";
			  }
	 	  } else {
			  $nRes .= "해당 경로가 존재하지 않습니다.[판등록 미진행]";
		  }

		  echo "*** ".$nRes."\n";


		 /***********************************************************************************
		 *** Log
		 ***********************************************************************************/

         CLS_File::FileWrite(LOGPATH, "\n".date("H:i:s")." -> [".$nRes."] \n", "a+");

	 }
 } else if ($shtRes == "FAILED") {
	 echo "===> 등록된 파일이 존재하지 않습니다.\n";
	 CSL_File::FileWrite(LOGPATH, "\n".date("H:i:s")." -> [등록된 파일이 존재하지 않습니다.] \n", "a+");
 } else {
	 echo "===> 예기치 않은 쿼리 오류입니다.\n";
     CLS_File::FileWrite(LOGPATH, "\n".date("H:i:s")." -> [예기치 않은 쿼리 오류입니다.] \n", "a+");
 }


/***********************************************************************************
 *** 종료
 ***********************************************************************************/

echo "\n====================================================================================\n";
echo "===================================파일엔진 종료====================================\n";
echo "====================================================================================\n\n";

/***********************************************************************************
*** Log
***********************************************************************************/

$CFile->FileWrite(LOGPATH, "\n".date("H:i:s")." -> [#################### Cypress 파일엔진 종료 ####################] \n\n", "a+");

?>

