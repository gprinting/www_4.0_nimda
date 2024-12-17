<?
 /***********************************************************************************
 *** 프로 젝트 : CyPress
 *** 개발 영역 : 프리셋
 *** 개  발  자 : 김성진
 *** 개발 날짜 : 2016.09.20
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

$PRC  = $CCommon->STags($CRequest->GetValue("PRC"));    // 프리셋 카테고리 이름
$PRN  = $CCommon->STags($CRequest->GetValue("PRN"));    // 프리셋 이름
$PD  = $CCommon->STags($CRequest->GetValue("PD"));      // 생성시간 (2016-03-03-20:30)
$PW  = $CCommon->STags($CRequest->GetValue("PW"));      // 작업자 계정아이디 (test)
$PT  = $CCommon->STags($CRequest->GetValue("PT"));      // 판형::이름 (NN::일반명함)
$PS  = $CCommon->STags($CRequest->GetValue("PS"));      // 대지사이즈 이름 (국전)
$PSW  = $CCommon->STags($CRequest->GetValue("PSW"));    // 대지사이즈 가로 (904)
$PSH  = $CCommon->STags($CRequest->GetValue("PSH"));    // 대지사이즈 세로 (604)
$PP  = $CCommon->STags($CRequest->GetValue("PP"));      // 재질 (스노우)


/***********************************************************************************
 *** 변수값 유무 체크
 ***********************************************************************************/

if (strlen($PRC) <= 0) {
    echo "Res="._CYP_PRES_NUM_ERR_CD_01."&"._CYP_PRES_NUM_ERR_DC_01;
    exit;
}

if (strlen($PRN) <= 0) {
    echo "Res="._CYP_PRES_NUM_ERR_CD_02."&"._CYP_PRES_NUM_ERR_DC_02;
    exit;
}

if (strlen($PD) <= 0) {
    echo "Res="._CYP_PRES_NUM_ERR_CD_03."&"._CYP_PRES_NUM_ERR_DC_03;
    exit;
}

if (strlen($PW) <= 0) {
    echo "Res="._CYP_PRES_NUM_ERR_CD_04."&"._CYP_PRES_NUM_ERR_CD_04;
    exit;
}

if (strlen($PT) <= 0) {
    echo "Res="._CYP_PRES_NUM_ERR_CD_05."&"._CYP_PRES_NUM_ERR_DC_05;
    exit;
}

if (strlen($PS) <= 0) {
    echo "Res="._CYP_PRES_NUM_ERR_CD_06."&"._CYP_PRES_NUM_ERR_DC_06;
    exit;
}

if (strlen($PSW) <= 0) {
    echo "Res="._CYP_PRES_NUM_ERR_CD_07."&"._CYP_PRES_NUM_ERR_DC_07;
    exit;
}

if (strlen($PSH) <= 0) {
    echo "Res="._CYP_PRES_NUM_ERR_CD_08."&"._CYP_PRES_NUM_ERR_DC_08;
    exit;
}

if (strlen($PP) <= 0) {
    echo "Res="._CYP_PRES_NUM_ERR_CD_09."&"._CYP_PRES_NUM_ERR_CD_09;
    exit;
}


/***********************************************************************************
*** 변수선언
***********************************************************************************/

$rData['prc'] = $PRC;           // 프리셋 카테고리 이름
$rData['prn'] = $PRN;           // 프리셋 이름
$rData['pd'] = $PD;             // 생성시간 (2016-03-03-20:30)
$rData['pw'] = $PW;             // 작업자 계정아이디 (test)
$rData['pt'] = $PT;             // 판형::이름 (NN::일반명함)
$rData['ps'] = $PS;             // 대지사이즈 이름 (국전)
$rData['psw'] = $PSW;           // 대지사이즈 가로 (904)
$rData['psh'] = $PSH;           // 대지사이즈 세로 (604)
$rData['pp'] = $PP;             // 재질 (스노우)

$psData = explode("_", $rData['prn']);

if ($psData[0] == "국전") {
    $rData['ps'] = "국";
    $rData['sp'] = "전절";
} else if ($psData[0] == "2절") {
    $rData['ps'] = "46";
    $rData['sp'] = "2절";
} else if ($psData[0] == "3절") {
    $rData['ps'] = "46";
    $rData['sp'] = "3절";
} else {
    $rData['ps'] = "국";
    $rData['sp'] = "2절";
}


/***********************************************************************************
*** 기타 변수 정의
***********************************************************************************/

$ptData = explode("::", $rData['pt']);
$rData['pt'] = $ptData[1];


/***********************************************************************************
 *** 모듈 인클루드
 ***********************************************************************************/

require_once _MOD_DIR."/mod_preset.php";
$CPreset = new CLS_Preset;


/***********************************************************************************
*** 카테고리 코드 가져오기
***********************************************************************************/

$rData['cd'] = $CPreset->getPresetCateCodeDataValue(_DB_SERVER, $rData['pt']);


/***********************************************************************************
 *** 기존 판형 체크
 **********************************************************************************/

$getRes = $CPreset->getPresetInfoDataValue(_DB_SERVER, $rData);


/***********************************************************************************
 *** 데이터 존재유무에 따라 삽입 또는 업데이트
 **********************************************************************************/

if ($getRes == "INSERT") {
    $insRes = $CPreset->setPresetInsertDataComplete(_DB_SERVER, $rData);

    if ($insRes == "SUCCESS") {
        $nRes = _CYP_SUCCESS;
    } else {
        $nRes = _CYP_COMM_ERR_CD_01."&"._CYP_COMM_ERR_DC_01;
    }
} else if ($getRes == "UPDATE") {
    $updRes = $CPreset->setPresetUpdateDataComplete(_DB_SERVER, $rData);

    if ($updRes == "SUCCESS") {
        $nRes = _CYP_SUCCESS;
    } else {
        $nRes = _CYP_COMM_ERR_CD_01."&"._CYP_COMM_ERR_DC_01;
    }
} else {
    $nRes = _CYP_COMM_ERR_CD_01."&"._CYP_COMM_ERR_DC_01;
}


/***********************************************************************************
 *** 출력
 ***********************************************************************************/

echo "Res=".$nRes."\n";

?>