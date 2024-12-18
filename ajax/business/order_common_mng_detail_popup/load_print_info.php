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

$seqno = $fb->form("seqno");
$flattyp_yn = $fb->form("flattyp_yn");
$return = array();


//낱장형일경우
if ($flattyp_yn == "Y") {
    $param = array();
    $param["table"] = "amt_order_detail_sheet";
    $param["col"] = "sheet_typset_seqno";
    $param["where"]["amt_order_detail_sheet_seqno"] = $seqno;

    $rs = $dao->selectData($conn, $param);

    $param = array();
    $param["table"] = "sheet_typset";
    $param["col"] = "typset_format_seqno, typset_num 
        ,print_amt ,print_amt_unit ,regi_date 
        ,honggak_yn ,empl_seqno ,memo ,sheet_typset_seqno";
    $param["where"]["sheet_typset_seqno"] = $rs->fields["sheet_typset_seqno"];

    $rs = $dao->selectData($conn, $param);

    $typset_num = $rs->fields["typset_num"];
    $return["typset_num"] = $typset_num;
//책자형일경우
} else {

}

$param = array();
$param["table"] = "print_op";
$param["col"] = "name ,orderer ,extnl_brand_seqno 
,print_op_seqno ,typ ,typ_detail ,size ,amt 
,amt_unit ,beforeside_spc_tmpt ,beforeside_tmpt ,aftside_spc_tmpt ,aftside_tmpt ,memo";
$param["where"]["typset_num"] = $typset_num;

$rs = $dao->selectData($conn, $param);

$return["output_name"] = $rs->fields["name"];
$return["orderer"] = $rs->fields["orderer"];
$return["typ"] = $rs->fields["typ"];
$return["typ_detail"] = $rs->fields["typ_detail"];
$return["size"] = $rs->fields["size"];
$return["amt"] = $rs->fields["amt"];
$return["amt_unit"] = $rs->fields["amt_unit"];
$return["beforeside_tmpt"] = $rs->fields["beforeside_tmpt"];
$return["beforeside_spc_tmpt"] = $rs->fields["beforeside_spc_tmpt"];
$return["aftside_tmpt"] = $rs->fields["aftside_tmpt"];
$return["aftside_spc_tmpt"] = $rs->fields["aftside_spc_tmpt"];
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
$param["table"] = "print_work_report";
$param["col"] = "worker_memo ,work_start_hour 
,work_end_hour ,worker ,work_price ,adjust_price";
$param["where"]["print_op_seqno"] = $rs->fields["print_op_seqno"];
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

if ($flattyp_yn == "Y") {
    $table = "sheet_typset_file";

    $param = array();
    $param["table"] = "sheet_typset";
    $param["col"] = "sheet_typset_seqno";
    $param["where"]["typset_num"] = $typset_num;

    $rs = $dao->selectData($conn, $param);
    $seqno = $rs->fields["sheet_typset_seqno"];
} else {

    $table = "brochure_typset_file";
    $param = array();
    $param["table"] = "brochure_typset_detail";
    $param["col"] = "brochure_typset_seqno";
    $param["where"]["typset_num"] = $typset_num;

    $rs = $dao->selectData($conn, $param);
    $seqno = $rs->fields["brochure_typset_seqno"];
}

$param = array();
$param["table"] = $table;
$param["col"] = "file_path ,save_file_name";
$param["where"]["sheet_typset_seqno"] = $seqno;

$picture_rs = $dao->selectData($conn, $param);

$file_path = $picture_rs->fields["file_path"];
$file_name = $picture_rs->fields["save_file_name"];

$full_path = $file_path . $file_name;
$chk_path = INC_PATH . $full_path;

if (is_file($chk_path) === false) {
    $full_path = NO_IMAGE;
}

$return["pic"] = $full_path; 

echo printInfo($return);
$conn->close();
?>
