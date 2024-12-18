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

    $return["typset_num"] = $rs->fields["typset_num"];
    $return["print_amt"] = $rs->fields["print_amt"];
    $return["print_amt_unit"] = $rs->fields["print_amt_unit"];
    $return["regi_date"] = $rs->fields["regi_date"];
    if ($rs->fields["honggak_yn"] == "Y") {
        $return["honggak_yn"] = "홍각기";
    } else {
        $return["honggak_yn"] = "돈땡"; 
    }
    $return["memo"] = $rs->fields["memo"];

    $param = array();
    $param["table"] = "empl";
    $param["col"] = "name";
    $param["where"]["empl_seqno"] = $rs->fields["empl_seqno"];

    $empl_rs = $dao->selectData($conn, $param);

    $return["empl_name"] = $empl_rs->fields["name"];

    $param = array();
    $param["table"] = "sheet_typset_file";
    $param["col"] = "sheet_typset_file_seqno, file_path ,save_file_name ,origin_file_name";
    $param["where"]["sheet_typset_seqno"] = $rs->fields["sheet_typset_seqno"];

    $picture_rs = $dao->selectData($conn, $param);

    $return["sheet_typset_file_seqno"] = $picture_rs->fields["sheet_typset_file_seqno"];
    $return["file_path"] = $picture_rs->fields["file_path"];
    $return["save_file_name"] = $picture_rs->fields["save_file_name"];
    $return["origin_file_name"] = $picture_rs->fields["origin_file_name"];

    $file_path = $picture_rs->fields["file_path"];
    $file_name = $picture_rs->fields["save_file_name"];

    $full_path = $file_path . $file_name;
    $chk_path = INC_PATH . $full_path;

    if (is_file($chk_path) === false) {
        $full_path = NO_IMAGE;
    }

    $return["pic"] = $full_path; 

    $param = array();
    $param["table"] = "typset_format";
    $param["col"] = "name ,affil ,subpaper ,wid_size ,vert_size";
    $param["where"]["typset_format_seqno"] = $rs->fields["typset_format_seqno"];

    $format_rs = $dao->selectData($conn, $param);

    $return["typset_name"] = $format_rs->fields["name"];
    $return["affil"] = $format_rs->fields["affil"];
    $return["subpaper"] = $format_rs->fields["subpaper"];
    $return["size"] = $format_rs->fields["wid_size"] . "*" . $format_rs->fields["vert_size"];

} else {
    $param = array();
    $param["table"] = "brochure_typset_detail";
    $param["col"] = "typset_format_seqno 
        ,print_amt ,print_amt_unit ,regi_date 
        ,honggak_yn ,empl_seqno ,memo ,brochure_typset_seqno";
    $param["where"]["typset_num"] = $typset_num;

    $rs = $dao->selectData($conn, $param);

    $return["print_amt"] = $rs->fields["print_amt"];
    $return["print_amt_unit"] = $rs->fields["print_amt_unit"];
    $return["regi_date"] = $rs->fields["regi_date"];
    if ($rs->fields["honggak_yn"] == "Y") {
        $return["honggak_yn"] = "홍각기";
    } else {
        $return["honggak_yn"] = "돈땡"; 
    }
    $return["memo"] = $rs->fields["memo"];

    $param = array();
    $param["table"] = "empl";
    $param["col"] = "name";
    $param["where"]["empl_seqno"] = $rs->fields["empl_seqno"];

    $empl_rs = $dao->selectData($conn, $param);

    $return["empl_name"] = $empl_rs->fields["name"];

    $param = array();
    $param["table"] = "brochure_typset_file";
    $param["col"] = "file_path ,save_file_name ,origin_file_name";
    $param["where"]["sheet_typset_seqno"] = $rs->fields["sheet_typset_seqno"];

    $file_rs = $dao->selectData($conn, $param);

    $return["file_path"] = $file_rs->fields["file_path"];
    $return["save_file_name"] = $file_rs->fields["save_file_name"];
    $return["origin_file_name"] = $file_rs->fields["origin_file_name"];

    $param = array();
    $param["table"] = "typset_format";
    $param["col"] = "name ,affil ,subpaper ,wid_size ,vert_size";
    $param["where"]["typset_format_seqno"] = $rs->fields["typset_format_seqno"];

    $format_rs = $dao->selectData($conn, $param);

    $return["typset_name"] = $format_rs->fields["name"];
    $return["affil"] = $format_rs->fields["affil"];
    $return["subpaper"] = $format_rs->fields["subpaper"];
    $return["size"] = $format_rs->fields["wid_size"] . "*" . $format_rs->fields["vert_size"];
}

echo typsetInfo($return);
$conn->close();
?>
