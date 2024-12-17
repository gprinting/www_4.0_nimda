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
 require_once $curDirectory."/config/set/webinfo/init.php";
 require_once $curDirectory."/config/lib/local/include.php";


 /***********************************************************************************
 *** 모듈 인클루드
 ***********************************************************************************/

 require_once _MOD_DIR."/mod_enzine.php";
 $CEnzine = new CLS_Enzine;


/***********************************************************************************
 *** 시작
 ***********************************************************************************/

echo "\n====================================================================================\n";
echo "===================================파일엔진 시작====================================\n";
echo "====================================================================================\n\n";


 /***********************************************************************************
 *** 등록된 경로 가져오기
 ***********************************************************************************/

 $shtRes = $CEnzine->getSheetPathFileList(_DB_SERVER);


/***********************************************************************************
 *** 총 갯수
 ***********************************************************************************/

echo "===> 총 갯수는 ".number_format(count($shtRes))."개 입니다.\n\n";


 /***********************************************************************************
 *** 처리
 ***********************************************************************************/

 if (is_array($shtRes)) {
	 for ($z = 0; $z < count($shtRes); $z++) {
		  $num = $i + 1;
		  $nRes = $num."번 경로에 데이터 정리를 시작중... ";

		  if (substr($shtRes[$z]['save_path'], 0, 1) == "/") {
			  $nPath = $shtRes[$z]['save_path'];
		  } else {
			  $nPath = "/".$shtRes[$z]['save_path'];
		  }

		  $fullPath = _CYP_FILE_ENZINE_OUTPUT.$nPath;

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

			  $nfPath = "/attach/typset_preview_file".$nPath."/";

			  if (count($fileName) > 0) {
				  $filRes = $CEnzine->setSheetTypeFileDataInsertComplete(_DB_SERVER, $fileName, $nfPath, $shtRes[$z]['idx']);

				  if ($filRes == "SUCCESS") {
					  $preRes = $CEnzine->setSheetTypePreviewFileInsertComplete(_DB_SERVER, $fileName, $nfPath, $shtRes[$z]['idx']);

					  if ($preRes == "SUCCESS") {
						  // 미리보기 파일 카피
						  $fullDirPath = _WEB_FILE_ENZINE_PREVIEW.$nPath;

						  if (is_dir($fullDirPath)) {
							  for ($i = 0; $i < count($fileName); $i++) {
								  if (substr($fileName[$i], strlen($fileName[$i]) - 3, 3) == "png") {
									  if (file_exists($fullPath."/".$fileName[$i])) {
										  $fnsRes = $CFile->FileCopy($fullPath."/".$fileName[$i], $fullDirPath."/".md5($fileName[$i]).".png");
									  } else {
										  $fnsRes = "FAILED";
										  $nRes .= "복사 할 파일이 아직 생성되지 않았습니다.\n";
									  }
								  }
							  }
						  } else {
							  $nPathCount = 0;
							  $nPathData = explode("/", $nPath);

							  for ($i = 1; $i < count($nPathData); $i++) {
								   if ($i == "1") $nPathing = _WEB_FILE_ENZINE_PREVIEW."/".$nPathData[$i];
								   else $nPathing .= "/".$nPathData[$i];

								   if (!is_dir($nPathing)) {
									   $mkdRes = $CFile->MKdirCreate($nPathing, "0755");

									   if ($mkdRes == "SUCCESS") {
										   $nRes .= "[".$nPathing."] 해당 경로가 생성되었습니다.\n";
									   } else {
										   $nRes .= "[".$nPathing."] 해당 경로가 생성되지 않았습니다.\n";
									   }
								   }
							  }

							  if (is_dir($nPathing)) {
								  for ($i = 0; $i < count($fileName); $i++) {
									  if (substr($fileName[$i], strlen($fileName[$i]) - 3, 3) == "png") {
										  if (file_exists($fullPath."/".$fileName[$i])) {
											  $fnsRes = $CFile->FileCopy($fullPath."/".$fileName[$i], $fullDirPath."/".md5($fileName[$i]).".png");
										  } else {
											  $fnsRes = "FAILED";
											  $nRes .= "복사 할 파일이 아직 생성되지 않았습니다.\n";
										  }
									  }
								  }
							  } else {
								  $nRes .= "디렉토리가 생성되지 않았습니다.\n";
							  }
						  }

						  if ($fnsRes == "SUCCESS") {
							  $updRes = $CEnzine->setSheetTypeFileUpdateComplete(_DB_SERVER, $shtRes[$z]['idx']);

							  if ($updRes == "SUCCESS") {
								  // 생산지시서 추가
								  $fNameData = explode("-", $fileName[0]);
								  $selRes = $CEnzine->getTypeSetDataValue(_DB_SERVER, $fNameData[2]);
								  $ptData = explode("_", $fNameData[2]);

								  if (is_array($selRes)) {
									  // 타입정보 가져오기
									  $typRes = $CEnzine->getBrandNameDataValue(_DB_SERVER, $selRes['idx']);

									  if ($typRes != "ERROR" && $typRes != "FAILED") {
										  $insRes = $CEnzine->setProduceOrdInsertComplete(_DB_SERVER, $shtRes[$z], $selRes, $ptData, $typRes);

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
	 }
 } else if ($shtRes == "FAILED") {
	 echo "===> 등록된 파일이 존재하지 않습니다.\n";
 } else {
	 echo "===> 예기치 않은 쿼리 오류입니다.\n";
 }


/***********************************************************************************
 *** 종료
 ***********************************************************************************/

echo "\n====================================================================================\n";
echo "===================================파일엔진 종료====================================\n";
echo "====================================================================================\n\n";
?>