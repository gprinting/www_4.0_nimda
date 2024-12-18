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
$param['accept_memo'] = $fb->form("memo");

$conn->debug = 1;
$rs = $dao->updateAcceptMemo($conn, $param);



?>