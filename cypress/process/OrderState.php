<?
 /***********************************************************************************
 *** 프로 젝트 : CyPress
 *** 개발 영역 : 주문상태조회
 *** 개  발  자 : 김성진
 *** 개발 날짜 : 2016.06.21
 ***********************************************************************************/

 /***********************************************************************************
 *** 기본 인클루드
 ***********************************************************************************/
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/cypress_init.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once($_SERVER["DOCUMENT_ROOT"] . "/cypress/process/dao/mod_order_dao.php");
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
$dao = new CypressDAO();


 /***********************************************************************************
 *** 리퀘스트
 ***********************************************************************************/

$OrderNum  = $dao->selectOrderDetailFileNum($conn, $fb->form("OrderNum"));
CLS_File::FileWrite(_WPATH, "\n".date("H:i:s") . $OrderNum . "\n", "a+");

  /***********************************************************************************
 *** 변수값 유무 체크
 ***********************************************************************************/

 if (strlen($OrderNum) <= 0) {
	 echo "Res="._CYP_ORD_NUM_ERR_CD_01."&"._CYP_ORD_NUM_ERR_DC_01;
	 exit;
 }


 /***********************************************************************************
 *** 주문정보 가져오기
 ***********************************************************************************/

 $ordRes = $COrder->getOrderInfoDataValue($conn, $OrderNum);


 /***********************************************************************************
 *** 결과
 ***********************************************************************************/

 if (is_array($ordRes)) {
	 $nRes = _CYP_SUCCESS;

	 // [State]
	 $nStateName = $ordRes['oc_state_name'];
	 $nStateCode = $ordRes['oc_ord_state'];
	 $nPressGo = 0;
 } else if ($ordRes == "FAILED") {
	 $nRes = _CYP_ORD_NUM_ERR_CD_02."&"._CYP_ORD_NUM_ERR_DC_02;
 } else if ($ordRes == "ERROR") {
	 $nRes = _CYP_COMM_ERR_CD_01."&"._CYP_COMM_ERR_DC_01;
 } else {
	 $nRes = _CYP_COMM_ERR_CD_02."&"._CYP_COMM_ERR_DC_02;
 }


/***********************************************************************************
*** DB 컨넥션 종료
***********************************************************************************/

$conn->close();


  /***********************************************************************************
 *** 출력
 ***********************************************************************************/

 echo "Res=".$nRes."\n";

 if ($nRes == "0") {
	 echo "[State]\n";
	 echo "StateName=".$nStateName."\n";
	 echo "StateCode=".$nStateCode."\n";
	 echo "ProcessGo=".$nPressGo;
 }

?>