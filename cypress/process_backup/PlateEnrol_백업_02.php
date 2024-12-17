<?
 /***********************************************************************************
 *** 프로 젝트 : CyPress
 *** 개발 영역 : 판등록 및 추가
 *** 개  발  자 : 김성진
 *** 개발 날짜 : 2016.06.23
 ***********************************************************************************/

 /***********************************************************************************
 *** 기본 인클루드
 ***********************************************************************************/

 $curDirectory = dirname(__FILE__);
 require_once $curDirectory."/config/set/webinfo/init.php";
 require_once $curDirectory."/config/lib/local/include.php";


 /***********************************************************************************
 *** 리퀘스트
 ***********************************************************************************/

 $PEN = $CCommon->STags($CRequest->GetValue("PEN"));
 $PENO = $CCommon->STags($CRequest->GetValue("PENO"));
 $PD = $CCommon->STags($CRequest->GetValue("PD"));
 $PW = $CCommon->STags($CRequest->GetValue("PW"));
 $PT = $CCommon->STags($CRequest->GetValue("PT"));
 $PS = $CCommon->STags($CRequest->GetValue("PS"));
 $PSW = $CCommon->STags($CRequest->GetValue("PSW"));
 $PSH = $CCommon->STags($CRequest->GetValue("PSH"));
 $PL = $CCommon->STags($CRequest->GetValue("PL"));
 $PP = $CCommon->STags($CRequest->GetValue("PP"));
 $PQ = $CCommon->STags($CRequest->GetValue("PQ"));
 $PN = $CCommon->STags($CRequest->GetValue("PN"));

 for ($i = 1; $i <= $PN; $i++) {
	 $PC[$i]  = $CCommon->STags($CRequest->GetValue("PC".$i));
 }

 $AWN  = $CCommon->STags($CRequest->GetValue("AWN"));

 for ($i = 1; $i <= $AWN; $i++) {
	  $AW[$i] = $CCommon->STags($CRequest->GetValue("AW".$i));
 }

 $ONS  = $CCommon->STags($CRequest->GetValue("ONS"));

 for ($i = 1; $i <= $ONS; $i++) {
	  $ON[$i]  = $CCommon->STags($CRequest->GetValue("ON".$i));
	  $OC[$i]  = $CCommon->STags($CRequest->GetValue("OC".$i));
	  $OP[$i]  = $CCommon->STags($CRequest->GetValue("OP".$i));
 }

 $PM  = $CCommon->STags($CRequest->GetValue("PM"));
 $FILES  = $CCommon->STags($CRequest->GetValue("FILES"));
 $PRC  = $CCommon->STags($CRequest->GetValue("PRC"));
 $PRN  = $CCommon->STags($CRequest->GetValue("PRN"));


 /***********************************************************************************
 *** 변수값 유무 체크
 ***********************************************************************************/

 if (strlen($PEN) <= 0 && strlen($PENO) <= 0) {
	 echo "Res="._CYP_ORD_PEN_ERR_CD_01."&"._CYP_ORD_PEN_ERR_DC_01;
	 exit;
 }

 if (strlen($PENO) > 0) {
	 if (strlen($PD) <= 0) {
		 echo "Res="._CYP_ORD_PENO_ERR_CD_01."&"._CYP_ORD_PENO_ERR_DC_01;
		 exit;
	 }

	 if (strlen($PW) <= 0) {
		 echo "Res="._CYP_ORD_PENO_ERR_CD_02."&"._CYP_ORD_PENO_ERR_DC_02;
		 exit;
	 }

	 if (strlen($PT) <= 0) {
		 echo "Res="._CYP_ORD_PENO_ERR_CD_03."&"._CYP_ORD_PENO_ERR_DC_03;
		 exit;
	 }

	 if (strlen($PS) <= 0) {
		 echo "Res="._CYP_ORD_PENO_ERR_CD_04."&"._CYP_ORD_PENO_ERR_DC_04;
		 exit;
	 }

	 if (strlen($PSW) <= 0) {
		 echo "Res="._CYP_ORD_PENO_ERR_CD_05."&"._CYP_ORD_PENO_ERR_DC_05;
		 exit;
	 }

	 if (strlen($PSH) <= 0) {
		 echo "Res="._CYP_ORD_PENO_ERR_CD_06."&"._CYP_ORD_PENO_ERR_DC_06;
		 exit;
	 }

	 if (strlen($PL) <= 0) {
		 echo "Res="._CYP_ORD_PENO_ERR_CD_07."&"._CYP_ORD_PENO_ERR_DC_07;
		 exit;
	 }

	 if (strlen($PP) <= 0) {
		 echo "Res="._CYP_ORD_PENO_ERR_CD_08."&"._CYP_ORD_PENO_ERR_DC_08;
		 exit;
	 }

	 if (strlen($PQ) <= 0) {
		 echo "Res="._CYP_ORD_PENO_ERR_CD_09."&"._CYP_ORD_PENO_ERR_DC_09;
		 exit;
	 }

	 if (strlen($PN) <= 0) {
		 echo "Res="._CYP_ORD_PENO_ERR_CD_10."&"._CYP_ORD_PENO_ERR_DC_10;
		 exit;
	 }

	 if (strlen($AWN) <= 0) {
		 echo "Res="._CYP_ORD_PENO_ERR_CD_11."&"._CYP_ORD_PENO_ERR_DC_11;
		 exit;
	 }

	 if (strlen($ONS) <= 0) {
		 echo "Res="._CYP_ORD_PENO_ERR_CD_12."&"._CYP_ORD_PENO_ERR_DC_12;
		 exit;
	 }

	 if (strlen($FILES) <= 0) {
		 echo "Res="._CYP_ORD_PENO_ERR_CD_13."&"._CYP_ORD_PENO_ERR_DC_13;
		 exit;
	 }
 }


 /***********************************************************************************
 *** 변수 정의
 ***********************************************************************************/

 $rData['pen']  = $PEN;
 $rData['peno']  = $PENO;
 $rData['pd']  = $PD;
 $rData['pw']  = $PW;
 $rData['pt']  = $PT;
 $rData['ps']  = $PS;
 $rData['psw']  = $PSW;
 $rData['psh']  = $PSH;
 $rData['pl']  = $PL;
 $rData['pp']  = $PP;
 $rData['pq']  = $PQ;
 $rData['pn']  = $PN;
 $rData['awn']  = $AWN;
 $rData['ons']  = $ONS;
 $rData['pm']  = $PM;
 $rData['files']  = $FILES;
 $rData['prc']  = $PRC;
 $rData['prn']  = $PRN;


 /***********************************************************************************
 *** 모듈 인클루드
 ***********************************************************************************/

 require_once _MOD_DIR."/mod_order.php";
 $COrder = new CLS_Order;

 require_once _MOD_DIR."/mod_state.php";
 $CState = new CLS_State;


 /***********************************************************************************
 *** 낱장형 및 책자형구분
 ***********************************************************************************/

 if (strlen($PEN) > 0) {
	 $pfCode = $CCommon->getPenDivsion($PEN);
 } else {
	 $pfCode = $CCommon->getPenDivsion($PENO);
 }


 /***********************************************************************************
 *** 처리
 ***********************************************************************************/

 if (strlen($PEN) > 0) {
	 $ordRes = $COrder->getPlateEnrolPenCheck(_DB_SERVER, $rData, $pfCode);

	 if ($ordRes == "SUCCESS") {
		 $nRes = _CYP_SUCCESS;
	 } else if ($ordRes == "ERROR") {
		 $nRes = _CYP_COMM_ERR_CD_02."&"._CYP_COMM_ERR_DC_02;
	 } else {
		 $pedRes = $COrder->setPlateEnrolPenDataDeleteComplete(_DB_SERVER, $ordRes, $pfCode);

		 if ($pedRes == "ERROR") {
			 $nRes = _CYP_COMM_ERR_CD_02."&"._CYP_COMM_ERR_DC_02;
		 } else {
			 $pfdRes = $COrder->setPlateEnrolPenFileDataDeleteComplete(_DB_SERVER, $ordRes, $pfCode);

			 if ($nRes == "ERROR") {
				 $nRes = _CYP_COMM_ERR_CD_02."&"._CYP_COMM_ERR_DC_02;
			 } else {
				 $filRes = $COrder->setPlateEnrolPenPreviewFileDataDeleteComplete(_DB_SERVER, $ordRes, $pfCode);

				 if ($filRes == "ERROR") {
					 $nRes = _CYP_COMM_ERR_CD_02."&"._CYP_COMM_ERR_DC_02;
				 } else {
					 $nRes = _CYP_SUCCESS;
				 }
			 }
		 }
	 }
 } else {
	 $panData = explode("::", $rData['pt']);
	 $panName = $panData['1'];

	 $tpsRes = $COrder->setPlateEnrolPenoITypeSetInsertDataComplete(_DB_SERVER, $rData, $panName, $pfCode);  // 조판

	 if ($tpsRes == "SUCCESS") {
		 $opoRes = $COrder->setPlateEnrolPenoIOutPutOPInsertDataComplete(_DB_SERVER, $rData, $panName);  // 출력발주

		 if ($opoRes == "SUCCESS") {
			 $prtRes = $COrder->setPlateEnrolPenoIPrintOPInsertDataComplete(_DB_SERVER, $rData, $panName);  // 인쇄발주

			 if ($prtRes == "SUCCESS") {
				 $nRes = _CYP_SUCCESS;

				 for ($i = 1; $i <= $ONS; $i++) {
					  $CState->setStateValueDataChangeComplete(_DB_SERVER, $rData['ON'.$i], _CYP_STS_CD_READY, _CYP_STS_CD_COMP);
				 }
			 } else if ($prtRes == "FAILED") {
				 $nRes = _CYP_COMM_ERR_CD_03."&"._CYP_COMM_ERR_DC_03;
			 } else {
				 $nRes = _CYP_COMM_ERR_CD_02."&"._CYP_COMM_ERR_DC_02;
			 }

		 } else if ($opoRes == "FAILED") {
			 $nRes = _CYP_COMM_ERR_CD_03."&"._CYP_COMM_ERR_DC_03;
		 } else {
			 $nRes = _CYP_COMM_ERR_CD_02."&"._CYP_COMM_ERR_DC_02;
		 }

	 } else if ($tpsRes == "FAILED") {
		 $nRes = _CYP_COMM_ERR_CD_03."&"._CYP_COMM_ERR_DC_03;
	 } else {
		 $nRes = _CYP_COMM_ERR_CD_02."&"._CYP_COMM_ERR_DC_02;
	 }
 }


 /***********************************************************************************
 *** 출력
 ***********************************************************************************/

 echo "Res=".$nRes."\n";

?>