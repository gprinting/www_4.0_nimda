<?
define(INC_PATH, $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/order_mng/OrderCommonMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new OrderCommonMngDAO();
$fb = new FormBean();

$session = $fb->getSession();
$param = [];
$param['order_common_seqno'] = $fb->form("seqno");
$param['wid'] = $fb->form("wid");
$param['vert'] = $fb->form("vert");

$rs = $dao->selectAcceptSize($conn, $param);
$param['old_wid'] = $rs->fields['receipt_size_wid'];
$param['old_vert'] = $rs->fields['receipt_size_vert'];

$param['kind'] = "접수사이즈";
$param['before'] = $param['old_wid'] . " x " . $param['old_vert'];
$param['after'] = $param['wid'] . " x " . $param['vert'];
$param['empl_id'] = $fb->getSession()["id"];

$dao->insertOrderInfoHistory($conn, $param);

$rs = $dao->updateAcceptSize($conn, $param);



?>