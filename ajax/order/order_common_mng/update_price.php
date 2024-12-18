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
$param['sell_price'] = $param['pay_price'] = $param['new_price'] = $fb->form("price");

$rs = $dao->selectPrice($conn, $param);
$param['old_price'] = $rs->fields['pay_price'] == NULL ? 0 : $rs->fields['pay_price'];

$param['kind'] = "가격변경";
$param['before'] = $param['old_price'] . "원";
$param['after'] = $param['new_price'] . "원";
$param['empl_id'] = $fb->getSession()["id"];

$dao->insertOrderInfoHistory($conn, $param);

$rs = $dao->updatePrice($conn, $param);



?>