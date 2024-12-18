<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/common/NimdaCommonDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$commonDAO = new NimdaCommonDAO();
$session = $fb->getSession();

$param = array();
$param["empl_seqno"] = $session["empl_seqno"];
$param["section"] = $fb->form("section");

$rs = $commonDAO->selectAuthPage($conn, $param);

if ($rs->fields["page_url"]) {
    echo $rs->fields["page_url"];
} else {
    echo false;
}

$conn->Close();
?>
