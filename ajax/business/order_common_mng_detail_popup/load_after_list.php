<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/Template.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/order_mng/OrderCommonMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$template = new Template();
$dao = new OrderCommonMngDAO();

$seqno = $fb->form("seqno");
$flattyp_yn = $fb->form("flattyp_yn");


if ($flattyp_yn == "Y") {
    $param = array();
    $param["table"] = "amt_order_detail_sheet";
    $param["col"] = "order_detail_count_file_seqno";
    $param["where"]["amt_order_detail_sheet_seqno"] = $seqno;

    $rs = $dao->selectData($conn, $param);
    
    $order_detail_count_file_seqno = $rs->fields["order_detail_count_file_seqno"];

    $param = array();
    $param["table"] = "order_detail_count_file";
    $param["col"] = "order_detail_seqno";
    $param["where"]["order_detail_count_file_seqno"] = $order_detail_count_file_seqno;

    $rs = $dao->selectData($conn, $param);

    $param = array();
    $param["table"] = "order_detail";
    $param["col"] = "order_detail_dvs_num, order_common_seqno";
    $param["where"]["order_detail_seqno"] = $rs->fields["order_detail_seqno"];

    $rs = $dao->selectData($conn, $param);
    $order_detail_dvs_num = $rs->fields["order_detail_dvs_num"];
    $order_common_seqno = $rs->fields["order_common_seqno"];
    
//책자형일경우
} else {

}



$param = array();
$rs = $dao->selectOrderInfo($conn, $param);
$param["order_common_seqno"] = $order_common_seqno;
$cate_name = $rs->fields["cate_name"];

$param = array();
$param["table"] = "after_op";
$param["col"] = "seq ,after_name ,depth1 
,depth2 ,depth3 ,amt ,amt_unit ,orderer 
,extnl_brand_seqno, after_op_seqno";
$param["where"]["order_common_seqno"] = $order_common_seqno;

$rs = $dao->selectData($conn, $param);

echo makeAfterListHtml($conn, $dao, $rs, $cate_name);
$conn->close();
?>
