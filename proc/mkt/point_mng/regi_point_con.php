<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/mkt/mkt_mng/PointMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$pointDAO = new PointMngDAO();

$conn->StartTrans();

$check = 1;
$param = array();
//테이블명
$param["table"] = "con_list";
//관리자 아이디
$param["col"]["con_man"] = $fb->form("con_name");
//고객 아이디
$param["col"]["con_custom"] = $fb->form("con_custom");
//등록일자
$param["col"]["con_date"] = $fb->form("con_date");
//상담내역
$param["col"]["con_text"] = $fb->form("con_msg");


$result = $pointDAO->insertData($conn, $param);

if (!$result) $check = 0;

if ($check == 1) {
    echo "등록 되었습니다.";

} else {
    echo "등록에 실패하였습니다.";
}

$conn->CompleteTrans();
$conn->close();
?>
