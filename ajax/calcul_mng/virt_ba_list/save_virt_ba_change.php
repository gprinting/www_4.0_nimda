<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/calcul_mng/virt_ba_mng/VirtBaListDAO.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/pageLib.inc");
$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$virtDAO = new VirtBaListDAO();

$param = array();
$param["virt_ba_change_history_seqno"] = $fb->form("virt_ba_change_history_seqno");
$param["member_seqno"] = $fb->form("member_seqno");
$param["after_bank_name"] = $fb->form("after_bank_name");
$param["after_bank_account"] = $fb->form("after_bank_account");

$param_update = array();
$param_update["table"] = "virt_ba_admin";
$param_update["col"]["use_yn"] = "N";
$param_update["prk"] = "member_seqno";
$param_update["prkVal"] = $fb->form("member_seqno");
$virtDAO->updateData($conn, $param_update);

$param_insert = array();
$param_insert["table"] = "virt_ba_admin";
//$param["table"] = "virt_ba_admin";
$param_insert["col"]["member_seqno"] = $fb->form("member_seqno");
$param_insert["col"]["ba_num"] = $fb->form("after_bank_account");
$param_insert["col"]["bank_name"] = $fb->form("after_bank_name");
$param_insert["col"]["use_yn"] = "Y";
$param_insert["col"]["depo_name"] = $fb->form("depo_name");
$virtDAO->insertData($conn, $param_insert);

$param_update = array();
$param_update["table"] = "virt_ba_change_history";
$param_update["col"]["prog_state"] = "변경성공";
$param_update["prk"] = "virt_ba_change_history_seqno";
$param_update["prkVal"] = $fb->form("virt_ba_change_history_seqno");
$virtDAO->updateData($conn, $param_update);


echo "1";

$conn->close();
?>
