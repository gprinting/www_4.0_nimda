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

//회원 배송지 등록 및 수정
$param = array();
$param["table"] = "member_dlvr";
$param["col"]["dlvr_name"] = $fb->form("dlvr_name");
$param["col"]["recei"] = $fb->form("recei");
$param["col"]["tel_num"] = $fb->form("dlvr_tel_num1") . "-" . $fb->form("dlvr_tel_num2") . "-" . $fb->form("dlvr_tel_num3");
$param["col"]["cell_num"] = $fb->form("dlvr_cell_num1") . "-" . $fb->form("dlvr_cell_num2") . "-" . $fb->form("dlvr_cell_num3");
$param["col"]["zipcode"] = $fb->form("dlvr_zipcode");
$param["col"]["addr"] = $fb->form("dlvr_addr");
$param["col"]["addr_detail"] = $fb->form("dlvr_addr_detail");

if (!$fb->form("seqno")) {
    $param["col"]["member_seqno"] = $fb->form("member_seqno");
    $param["col"]["regi_date"] = date("Y-m-d H:i:s");
    $rs = $memberCommonListDAO->insertData($conn, $param);

} else {
    $param["prk"] = "member_dlvr_seqno";
    $param["prkVal"] = $fb->form("seqno");
    $rs = $memberCommonListDAO->updateData($conn, $param);
}

if (!$rs) {
    $check = 0;
}

echo $check;
$conn->CompleteTrans();
$conn->close();
?>
