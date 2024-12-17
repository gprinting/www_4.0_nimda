<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/member/member_mng/MemberCommonListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$memberCommonListDAO = new MemberCommonListDAO();

$check = 1;
$conn->StartTrans();

$param = array();
$param["table"] = "licensee_info";
$param["col"] = "member_seqno";
$param["where"]["member_seqno"] = $fb->form("seqno");

$rs = $memberCommonListDAO->selectData($conn, $param);

$param = array();
$param["table"] = "licensee_info";
$param["col"]["repre_name"] = $fb->form("repre_name");
$param["col"]["crn"] = $fb->form("crn");
$param["col"]["bc"] = $fb->form("bc");
$param["col"]["tob"] = $fb->form("tob");
//$param["col"]["tel_num"] = $fb->form("tel_num1") . "-" . $fb->form("tel_num2") . "-" . $fb->form("tel_num3");
$param["col"]["zipcode"] = $fb->form("zipcode");
$param["col"]["addr"] = $fb->form("addr");
$param["col"]["addr_detail"] = $fb->form("addr_detail");

if ($rs->EOF == 1) {
    $param["col"]["member_seqno"] = $fb->form("seqno");
    $rs = $memberCommonListDAO->insertData($conn, $param);
} else {
    $param["prk"] = "member_seqno";
    $param["prkVal"] = $fb->form("seqno");
    $rs = $memberCommonListDAO->updateData($conn,$param);
}

if (!$rs) {
    $check = 0;
}

echo $check;
$conn->CompleteTrans();
$conn->close();
?>
