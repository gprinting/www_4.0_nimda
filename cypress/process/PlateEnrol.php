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
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/cypress_init.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/dprinting/CypressDAO.inc');
include_once(INC_PATH . "/common_lib/cypress_com.inc");
include_once(INC_PATH . "/common_lib/cypress_file.inc");


/***********************************************************************************
 *** 클래스 선언
 ***********************************************************************************/

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$dao = new CypressDAO();
$fb = new FormBean();

$logpath = _WPATH . "_PlateEnrol";


/***********************************************************************************
 *** 리퀘스트
 ***********************************************************************************/

$param = array();
foreach($fb->fb as $key=>$value)
{
	$param[$key] = $value;
	CLS_File::FileWrite($logpath, "\n" . date("H:i:s")." 파라미터 ". $key . " : " . $param[$key] . "\n", "a+");
}


$i = 1;
foreach($param as $p) {
	$param["ON" . $i] = $dao->selectOrderDetailFileNum($conn, $param["ON" . $i]);
	$i++;
}

/***********************************************************************************
 *** 처리1
 ***********************************************************************************/

if (strlen($param['PEN']) > 0) {
	$sheet_typset_seqnos = $dao->isEnrolledPen($conn, $param);

	CLS_File::FileWrite(_WPATH, "\n" . date("H:i:s") . " : ".count($sheet_typset_seqnos)."\n", "a+");
	if(($sheet_typset_seqnos) == 0) {
		echo "Res="._CYP_SUCCESS."\n";
		exit;
	}

	for($i = 0; $i < count($sheet_typset_seqnos); $i++) {
		$param['sheet_typset_seqno'] = $sheet_typset_seqnos[$i];
		CLS_File::FileWrite(_WPATH, "\n" . date("H:i:s") . " : " . $sheet_typset_seqnos[$i] ."\n", "a+");
		/*
		 * amt_order_detail_sheet sheet_typset_seqno -> null
		 * sheet_typset_file 삭제
		 * sheet_typset_preview_file 삭제
		 */

		$dao->resetAlreadyEnrolPenInfo($conn, $param);
		$rs = $dao->deleteAlreadyEnrolPenInfo($conn, $param);
	}

} else {
	// 판등록(추가)
	$new_pan_info = array();

	CLS_File::FileWrite($logpath, "\n" . date("H:i:s") . " ---------------------------------------------- 조판 시작 ----------------------------------------------\n", "a+");
	$insert_param = $dao->insertSheetTypset($conn, $param);
	if ($insert_param === false) {
		echo "Res=41&판등록 실패\n";
		exit;
	}

	$param['from_state'] = '2130';
	$param['to_state'] = '2220';

	for ($i = 1; $i <= $param['ONS']; $i++) {
		$param['order_detail_file_num'] = $param['ON' . $i];
		//$param['typset_num'] = substr($param['PENO'], 0, strlen($param['PENO']) - 3);
		$param['typset_num'] = $param['PENO'];
		$param["idx"] = $i;

		$dao->updateState($conn, $param);
	}
}
/***********************************************************************************
 *** DB 컨넥션 종료
 ***********************************************************************************/

$conn->close();


/***********************************************************************************
 *** 출력
 ***********************************************************************************/

echo "Res=0\n";

?>