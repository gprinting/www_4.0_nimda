<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/member/member_mng/DlvrListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dlvrDAO = new DlvrListDAO();

//메인 일련번호
$seqno = $fb->form("seqno");

$param = array();
$param["seqno"] = $seqno;

$result = $dlvrDAO->selectDlvrMain($conn, $param);

$nick = $result->fields["office_nick"];
$regi_date = $result->fields["regi_date"];
$regi_date = substr($regi_date, 0, 10);
$addr = $result->fields["addr"];
$addr_detail = $result->fields["addr_detail"];
$tel_num = $result->fields["tel_num"];

$param = array();
$result = $dlvrDAO->selectDlvrMainList($conn, $param);

$main_list = makeDlvrMainList($result);

echo $main_list . "♪@♭" .$regi_date . "♩§¶" . $nick . "♩§¶" . 
     $addr . " " . $addr_detail . "♩§¶" . $tel_num;

$conn->close();
?>
