<?
 /***********************************************************************************
 *** 프로 젝트 : CyPress
 *** 개발 영역 : 판등록 데이터 병합
 *** 개  발  자 : 김성진
 *** 개발 날짜 : 2016.09.27
 ***********************************************************************************/

/***********************************************************************************
 *** 기본 인클루드
 ***********************************************************************************/
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/cypress_init.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once($_SERVER["DOCUMENT_ROOT"] . "/cypress/process/dao/mod_div_dao.php");
include_once(INC_PATH . "/common_lib/cypress_com.inc");
include_once(INC_PATH . "/common_lib/cypress_file.inc");


/***********************************************************************************
 *** 클래스 선언
 ***********************************************************************************/

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$fb = new FormBean();
$CDiv = new CLS_Div;
$CCommon = new CLS_Common;
$CCFile = new CLS_File;


/***********************************************************************************
 *** 리퀘스트
 ***********************************************************************************/

$ON  = $fb->form("ON");


/***********************************************************************************
 *** 변수값 유무 체크
 ***********************************************************************************/

if (strlen($ON) <= 0) {
    echo "Res="._CYP_QTY_NUM_ERR_CD_01."&"._CYP_QTY_NUM_ERR_DC_01;
    exit;
}


/***********************************************************************************
*** 변수선언
***********************************************************************************/

$rData['on'] = $ON;


/***********************************************************************************
 *** 주문번호 체크
 **********************************************************************************/

$ordRes = $CDiv->getDivOrderDetailCountFileInfoDataValue($conn, $rData);


/***********************************************************************************
 *** 처리
 **********************************************************************************/

if ($ordRes == "ERROR") {
    $nRes = _CYP_COMM_ERR_CD_01."&"._CYP_COMM_ERR_DC_01;
} else if ($ordRes == "FAILED") {
    $nRes = _CYP_ORD_NUM_ERR_CD_01."&"._CYP_ORD_NUM_ERR_DC_01;
} else {
    $amtRes = $CDiv->getDivAmtOrderDetailSheetMergeDataValue($conn, $ordRes);

    if (is_array($amtRes)) {
        $delRes = $CDiv->setDivAmtOrderDetailSheetDataDeleteComplete($conn, $ordRes);

        if ($delRes == "SUCCESS") {
            $insRes = $CDiv->setDivAmtOrderDetailSheetMergeDataInsertComplete($conn, $amtRes, $ordRes);

            if ($insRes == "SUCCESS") {
                $nRes = _CYP_SUCCESS;
            } else {
                $nRes = _CYP_COMM_ERR_CD_01."&"._CYP_COMM_ERR_DC_01;
            }
        } else {
            $nRes = _CYP_COMM_ERR_CD_01."&"._CYP_COMM_ERR_DC_01;
        }
    } else if ($amtRes == "FAILED") {
        $nRes = _CYP_QTY_NUM_ERR_CD_03."&"._CYP_QTY_NUM_ERR_DC_03;
    } else {
        $nRes = _CYP_COMM_ERR_CD_01."&"._CYP_COMM_ERR_DC_01;
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