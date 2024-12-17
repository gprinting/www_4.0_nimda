<?
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once($_SERVER["INC"] . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/common/entity/FormBean.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/job/front/common/FrontCommonDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new FrontCommonDAO();

$fb = new FormBean();
$conn->StartTrans();
$check = 1;

// $conn->debug = 1;

$param = array();

$param['add_minus_check'] = $_POST['add_minus_check'];
$param['send_points'] = $_POST['send_points'];
$param['mb_id_point'] = $_POST['mb_id_point'];
$param['member_seqno'] = $_POST['member_seqno'];

if($_POST['selboxDirect']){
	$param['add_minus_reason'] = $_POST['selboxDirect'];
} else {
	$param['add_minus_reason'] = $_POST['add_minus_reason'];
}

if($param['add_minus_check'] == "chk"){
	
	$re = $dao->updateMemberChk($conn, $param);

}else{

	$rs = $dao->selectMemberInfoPoint($conn, $param);
	$result = $dao->updatePoint($conn, $param, $rs, $dao);

}



echo $check;
$conn->CompleteTrans();
$conn->close();
?>