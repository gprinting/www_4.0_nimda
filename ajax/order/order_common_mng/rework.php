<?
define("INC_PATH", $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once($_SERVER["INC"] . "/common_define/order_status.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/common/entity/FormBean.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/job/front/mypage/OrderInfoDAO.inc");
include_once($_SERVER["INC"] . "/com/nexmotion/job/front/order/CartDAO.inc");


$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$frontUtil = new FrontCommonUtil();

$fb = new FormBean();
$orderDAO = new OrderInfoDAO();
$cartDAO = new CartDAO();

$order_seqno  = $fb->form("seqno");
$member_seqno = $fb->session("org_member_seqno");
$state_arr = $fb->session("state_arr");

$amt = $fb->form("amt");
$count = $fb->form("count");
$price = $fb->form("rework_price");
$rework_cause_detail = $fb->form("rework_cause_detail");
$rework_request_empl = $fb->form("rework_request_empl");
$rework_empl_id = $fb->form("rework_empl_id");
$type_rework = $fb->form("type_rework");

$param = array();
$param["order_seqno"] = $order_seqno;
$order_result = $orderDAO->selectOrderNum($conn, $param);

/**
 * @brief 주문공통 재주문 INSERT
 */
$insert_param = array();
$insert_param["amt"] = $amt;
$insert_param["count"] = $count;
$insert_param["order_num"] = $order_result;
$insert_param["price"] = $price;
$insert_param["rework_empl_id"] = $fb->session("empl_seqno");
$insert_param["rework_cause"] = $type_rework;
$insert_param["rework_cause_detail"] = $rework_cause_detail;
$insert_param["rework_request_empl"] = $rework_request_empl;

$cate_sortcode = $orderDAO->selectOrderCateSortcode($conn,$order_seqno);

$_SESSION["sell_site"] = substr($insert_param["order_num"],0,3);
$insert_param["new_order_num"] = $frontUtil->makeOrderNum($conn, $cartDAO,
    $cate_sortcode);

// order_common
$insert_param["old_order_seqno"] = $order_seqno;
$result = $orderDAO->insertReorder($conn, $insert_param);
$order_seqno = $conn->insert_ID();


// order_detail
if (!$result) $check = 0;
$insert_param["order_seqno"] = $order_seqno;
$insert_param["order_detail_dvs_num"] = "S" . $insert_param["new_order_num"] . "01";
$orderDAO->insertReworkOrderDetail($conn, $insert_param);

// order_file
$orderDAO->insertReworkOrderFile($conn, $insert_param);

// order_after_history
$orderDAO->insertReworkOrderAfterHistory($conn, $insert_param);

// order_opt_history
$orderDAO->insertReworkOrderOptHistory($conn, $insert_param);

// order_dlvr
$orderDAO->insertReworkOrderDelivery($conn, $insert_param);

// rework_list
$orderDAO->insertReworkList($conn,$insert_param);

echo $check;


//$conn->CompleteTrans();
$conn->Close();
?>
