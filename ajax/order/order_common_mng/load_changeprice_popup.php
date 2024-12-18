<?

define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_mng/PrdtBasicRegiDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$prdtBasicRegiDAO = new PrdtBasicRegiDAO();

$seqno = $fb->form("seqno");


$param = [];
$param["seqno"] = $seqno;

$rs = $prdtBasicRegiDAO->selectPrice($conn,$param);


$select_el = $fb->form("selectEl");
$seqno = $fb->form("seqno");
$amt = $fb->form("amt");
$count = $fb->form("count");
$price = $fb->form("price");

$param = [];
$param["count"] = $count;
$param["amt"] = $amt;
$param["price"] = $price;

$val = [];
$val["seq"] = $seqno;
$val["price"] = $rs->fields['pay_price'] == NULL ? 0 : $rs->fields['pay_price'];

echo changePricePopupHtml($param, $val);

?>