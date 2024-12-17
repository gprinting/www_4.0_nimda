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
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/cypress_init.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once($_SERVER["DOCUMENT_ROOT"] . "/cypress/process/dao/mod_preset_dao.php");
include_once(INC_PATH . "/common_lib/cypress_com.inc");
include_once(INC_PATH . "/common_lib/cypress_file.inc");


/***********************************************************************************
 *** 클래스 선언
 ***********************************************************************************/

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$fb = new FormBean();
$CPreset = new CLS_Preset;
$CCommon = new CLS_Common;
$CCFile = new CLS_File;


/***********************************************************************************
 *** 리퀘스트
 ***********************************************************************************/

$PRC  = $fb->form("PRC");            // 프리셋 카테고리 이름
$PRN  = $fb->form("PRN");            // 프리셋 이름
$PD  = $fb->form("PD");              // 생성시간 (2016-03-03-20:30)
$PW  = $fb->form("PW");              // 작업자 계정아이디 (test)
$PT  = $fb->form("PT");              // 판형::이름 (NN::일반명함)
$PS  = $fb->form("PS");              // 대지사이즈 이름 (국전)
$PSW  = $fb->form("PSW");            // 대지사이즈 가로 (904)
$PSH  = $fb->form("PSH");            // 대지사이즈 세로 (604)
$PP  = $fb->form("PP");              // 재질 (스노우)


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

if ($psData[0] == "국전절") {
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
*** 카테고리 코드 가져오기
***********************************************************************************/

$rData['cd'] = $CPreset->getPresetCateCodeDataValue($conn, $rData['pt']);
//$CCFile->FileWrite(_WPATH, "\n".date("H:i:s")."-> ".$rData['cd']."\n", "a+");


/***********************************************************************************
 *** 기존 판형 체크
 **********************************************************************************/

$getRes = $CPreset->getPresetInfoDataValue($conn, $rData);
//$CCFile->FileWrite(_WPATH, "\n".date("H:i:s")."-> ".$getRes."\n", "a+");


/***********************************************************************************
 *** 데이터 존재유무에 따라 삽입 또는 업데이트
 **********************************************************************************/

if ($getRes == "INSERT") {
    $insRes = $CPreset->setPresetInsertDataComplete($conn, $rData);

    if ($insRes == "SUCCESS") {
        $nRes = _CYP_SUCCESS;
    } else {
        $nRes = _CYP_COMM_ERR_CD_01."&3"._CYP_COMM_ERR_DC_01;
    }
} else if ($getRes == "UPDATE") {
    $updRes = $CPreset->setPresetUpdateDataComplete($conn, $rData);

    if ($updRes == "SUCCESS") {
        $nRes = _CYP_SUCCESS;
    } else {
        $nRes = _CYP_COMM_ERR_CD_01."&2"._CYP_COMM_ERR_DC_01;
    }
} else {
    $nRes = _CYP_COMM_ERR_CD_01."&1"._CYP_COMM_ERR_DC_01;
}


/***********************************************************************************
 *** DB 컨넥션 종료
 ***********************************************************************************/

$conn->close();


/***********************************************************************************
 *** 출력
 ***********************************************************************************/

echo "Res=".$nRes."\n";

?>
