<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/common_config.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/Template.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/order_mng/OrderCommonMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$template = new Template();
$dao = new OrderCommonMngDAO();

$after_op_seqno = $fb->form("after_op_seqno");
$cate_name = $fb->form("cate_name");
$flattyp_yn = $fb->form("flattyp_yn");

$return = array();
$return["after_op_seqno"] = $after_op_seqno;

$param = array();
$param["table"] = "after_op";
$param["col"] = "after_name ,depth1 ,depth2 ,depth3 
                ,orderer ,extnl_brand_seqno ,op_typ ,op_typ_detail
                ,amt ,amt_unit ,memo, order_common_seqno";
$param["where"]["after_op_seqno"] = $after_op_seqno;

$rs = $dao->selectData($conn, $param);

$order_common_seqno = $rs->fields["order_common_seqno"];

$return["after_name"] = $rs->fields["after_name"];
$return["depth1"] = $rs->fields["depth1"];
$return["depth2"] = $rs->fields["depth2"];
$return["depth3"] = $rs->fields["depth3"];
$return["orderer"] = $rs->fields["orderer"];
$return["op_typ"] = $rs->fields["op_typ"];
$return["op_typ_detail"] = $rs->fields["op_typ_detail"];
$return["amt"] = $rs->fields["amt"];
$return["amt_unit"] = $rs->fields["amt_unit"];
$return["memo"] = $rs->fields["memo"];

$param = array();
$param["table"] = "extnl_brand";
$param["col"] = "extnl_etprs_seqno";
$param["where"]["extnl_brand_seqno"] = $rs->fields["extnl_brand_seqno"];

$brand_rs = $dao->selectData($conn, $param);

$param = array();
$param["table"] = "extnl_etprs";
$param["col"] = "manu_name";
$param["where"]["extnl_etprs_seqno"] = $brand_rs->fields["extnl_etprs_seqno"];

$etprs_rs = $dao->selectData($conn, $param);

$return["manu_name"] = $etprs_rs->fields["manu_name"];

$param = array();
$param["table"] = "after_work_report";
$param["col"] = "worker_memo ,work_start_hour 
,work_end_hour ,worker ,work_price ,adjust_price";
$param["where"]["after_op_seqno"] = $after_op_seqno;
$param["where"]["valid_yn"] = "Y";

$report_rs = $dao->selectData($conn, $param);

$return["worker_memo"] = $report_rs->fields["worker_memo"];
$return["work_start_hour"] = $report_rs->fields["work_start_hour"];
if ($report_rs->fields["work_end_hour"]) {
    $return["work_end_hour"] = " ~ " . $report_rs->fields["work_end_hour"]; 
}
$return["worker"] = $report_rs->fields["worker"];
$return["work_price"] = number_format($report_rs->fields["work_price"]) . "원";
$return["adjust_price"] = number_format($report_rs->fields["adjust_price"]) . "원";


//주문상세파일
/*
$param = array();
$param["table"] = "order_detail_count_file";
$param["col"] = "file_path, save_file_name";
$param["where"]["order_detail_seqno"] = $order_detail_seqno;

$picture_rs = $dao->selectData($conn, $param);

$file_path = $picture_rs->fields["file_path"];
$file_name = $picture_rs->fields["save_file_name"];

$full_path = $file_path . $file_name;
$chk_path = INC_PATH . $full_path;

if (is_file($chk_path) === false) {
    $full_path = NO_IMAGE;
}

$return["pic"] = $full_path; 
*/
//후공정 발주 작업파일
$param = array();
$param["table"] = "after_op_work_file";
$param["col"] = "file_path, save_file_name";
$param["where"]["after_op_seqno"] = $after_op_seqno;

$picture_rs = $dao->selectData($conn, $param);

$file_path = $picture_rs->fields["file_path"];
$file_name = $picture_rs->fields["save_file_name"];

$full_path = $file_path . $file_name;
$chk_path = INC_PATH . $full_path;

if (is_file($chk_path) === false) {
    $full_path = NO_IMAGE;
}

$return["after_pic"] = $full_path; 

echo afterInfo($return);
$conn->close();
?>
