<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/calcul_mng/virt_ba_mng/VirtBaListDAO.inc");
$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$virtDAO = new VirtBaListDAO();
$conn->StartTrans();

//가상계좌 회원 맵핑 끊기
$param = array();
$param["virt_seqno"] = $fb->form("virt_ba_admin_seqno");
$result = $virtDAO->updateMemberVirtBa($conn, $param);

echo $result;
$conn->CompleteTrans();
$conn->close();
?>
