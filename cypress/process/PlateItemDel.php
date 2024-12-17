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
include_once($_SERVER["DOCUMENT_ROOT"] . "/cypress/process/dao/mod_order_dao.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/cypress/process/dao/mod_state_dao.php");
include_once(INC_PATH . "/common_lib/cypress_com.inc");
include_once(INC_PATH . "/common_lib/cypress_file.inc");
include_once(INC_PATH . '/com/dprinting/CypressDAO.inc');

/***********************************************************************************
 *** 클래스 선언
 ***********************************************************************************/

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$fb = new FormBean();
$COrder = new CLS_Order;
$CCommon = new CLS_Common;
$CCFile = new CLS_File;
$CState = new CLS_State;
$dao = new CypressDAO();


/***********************************************************************************
 *** 리퀘스트
 ***********************************************************************************/

$ONS  = $fb->form("ONS");

for ($i = 1; $i <= $ONS; $i++) {
    $rData['ON'.$i] = $dao->selectOrderDetailFileNum($conn, $fb->form('ON' . $i));
}


/***********************************************************************************
 *** 변수값 유무 체크
 ***********************************************************************************/

if (strlen($ONS) <= 0) {
    echo "Res="._CYP_ORD_NUM_ERR_CD_01."&"._CYP_ORD_NUM_ERR_DC_01;
    exit;
}

for ($i = 1; $i <= $ONS; $i++) {
    if (strlen($rData['ON'.$i]) <= 0) {
        echo "Res="._CYP_ORD_NUM_ERR_CD_01."&"._CYP_ORD_NUM_ERR_DC_01;
        exit;
    }
}


/***********************************************************************************
 *** 변수 정의
 ***********************************************************************************/

$rData['ONS']  = $ONS;


/***********************************************************************************
 *** 결과
 ***********************************************************************************/

$ordRes = $COrder->getOrderComposeAvailCheck2($conn, $rData);

if ($ordRes == "SUCCESS") {
    $nRes = _CYP_SUCCESS;
} else if ($ordRes == "ERROR") {
    $nRes = _CYP_COMM_ERR_CD_02."&"._CYP_COMM_ERR_DC_02;
} else {
    $nRes = _CYP_TSF_NUM_ERR_CD_05."&"._CYP_TSF_NUM_ERR_DC_05;
}

//$CCFile->FileWrite(_WPATH, "\n".date("H:i:s")." -> ".$ordRes."\n", "a+");


/***********************************************************************************
 *** 상태 업데이트
 ***********************************************************************************/

if ($nRes == 0) {
    for ($i = 1; $i <= $ONS; $i++) {
         $itmRes = $COrder->setItemStateValueOPTDataDeleteComplete($conn, $rData['ON'.$i]);
         $CCFile->FileWrite(_WPATH, "\n".date("H:i:s")." ITMRES 결과 -> ".$itmRes."\n", "a+");

         if ($itmRes != "ERROR") {
             $CState->setItemStateValueDataDelChangeComplete($conn, $rData['ON'.$i], _CYP_STS_CD_READY);
         }
    }
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