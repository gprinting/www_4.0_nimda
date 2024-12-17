<?
 /***********************************************************************************
 *** 프로 젝트 : CyPress
 *** 개발 영역 : 판등록 데이터 분할
 *** 개  발  자 : 김성진
 *** 개발 날짜 : 2016.09.27
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

$ON  = $CCommon->STags($CRequest->GetValue("ON"));      // 주문번호
$QT  = $CCommon->STags($CRequest->GetValue("QT"));      // 매수


/***********************************************************************************
 *** 변수값 유무 체크
 ***********************************************************************************/

if (strlen($ON) <= 0) {
    echo "Res="._CYP_QTY_NUM_ERR_CD_01."&"._CYP_QTY_NUM_ERR_DC_01;
    exit;
}

if (strlen($QT) <= 0) {
    echo "Res="._CYP_QTY_NUM_ERR_CD_02."&"._CYP_QTY_NUM_ERR_DC_02;
    exit;
}


/***********************************************************************************
*** 변수선언
***********************************************************************************/

$rData['on'] = $ON;
$rData['qt'] = $QT;


/***********************************************************************************
 *** 모듈 인클루드
 ***********************************************************************************/

require_once _MOD_DIR."/mod_div.php";
$CDiv = new CLS_Div;


/***********************************************************************************
 *** 주문번호 체크
 **********************************************************************************/

$ordRes = $CDiv->getDivOrderDetailCountFileInfoDataValue(_DB_SERVER, $rData);


/***********************************************************************************
 *** 처리
 **********************************************************************************/

if ($ordRes == "ERROR") {
    $nRes = _CYP_COMM_ERR_CD_01."&"._CYP_COMM_ERR_DC_01;
} else if ($ordRes == "FAILED") {
    $nRes = _CYP_ORD_NUM_ERR_CD_01."&"._CYP_ORD_NUM_ERR_DC_01;
} else {
    $amtRes = $CDiv->getDivAmtOrderDetailSheetInfoDataValue(_DB_SERVER, $ordRes);

    if (is_array($amtRes)) {
        $delRes = $CDiv->setDivAmtOrderDetailSheetDataDeleteComplete(_DB_SERVER, $ordRes);

        if ($delRes == "SUCCESS") {
            $insRes = $CDiv->setDivAmtOrderDetailSheetDataInsertComplete(_DB_SERVER, $rData, $amtRes, $ordRes);

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
 *** 출력
 ***********************************************************************************/

echo "Res=".$nRes."\n";

?>