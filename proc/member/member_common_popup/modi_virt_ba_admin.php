<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/common_config.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/member/member_mng/MemberCommonListDAO.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/file/FileAttachDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$fileDAO = new FileAttachDAO();
$dao = new MemberCommonListDAO();

$check = 1;
$member_seqno = $fb->form("member_seqno");

$conn->StartTrans();

$param = array();
$param["table"] = "virt_ba_admin";
$param["col"]["member_seqno"] = NULL;
$param["col"]["cpn_admin_seqno"] = NULL;
$param["col"]["state"] = "N";
$param["prk"] = "member_seqno";
$param["prkVal"] = $member_seqno;

$rs = $dao->updateData($conn,$param);

if (!$rs) {
    $check = 0;
}

echo $check;
$conn->CompleteTrans();
$conn->close();
?>
