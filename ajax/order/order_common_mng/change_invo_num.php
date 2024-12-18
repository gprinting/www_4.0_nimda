<?
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once($_SERVER["INC"] . "/common_define/order_status.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/common/entity/FormBean.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/job/front/mypage/OrderInfoDAO.inc");


$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$frontUtil = new FrontCommonUtil();

$fb = new FormBean();
$orderDAO = new OrderInfoDAO();

$order_seqno  = $fb->form("seqno");
$change_invo_num = $fb->form("change_invo_num");
$bun_group_dvs = $fb->form("bun_group_dvs");
$invo_kind = $fb->form("invo_kind");

$param = array();
$param["order_common_seqno"] = $order_seqno;
$param["change_invo_num"] = $change_invo_num;
$param["bun_group_dvs"] = $bun_group_dvs;
$param["invo_kind"] = $invo_kind;

if($bun_group_dvs == "1")
    $order_result = $orderDAO->updateOrderDlvrInfo1($conn, $param);

if($bun_group_dvs == "2")
    $order_result = $orderDAO->updateOrderDlvrInfo2($conn, $param);

echo 1;


//$conn->CompleteTrans();
$conn->Close();
?>
