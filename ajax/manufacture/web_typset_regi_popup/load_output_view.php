<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/typset_mng/TypsetListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetListDAO();

$typset_num = $fb->form("typset_num");

$param = array();
$param["table"] = "output_op";
$param["col"] = "name, affil, size, amt, amt_unit, memo, board, extnl_brand_seqno";
$param["where"]["typset_num"] = $typset_num;

$sel_rs = $dao->selectData($conn, $param);

$name = $sel_rs->fields["name"];
$affil = $sel_rs->fields["affil"];
$size = $sel_rs->fields["size"];
$amt = $sel_rs->fields["amt"];
$amt_unit = $sel_rs->fields["amt_unit"];
$memo = $sel_rs->fields["memo"];
$board = $sel_rs->fields["board"];
$extnl_brand_seqno = $sel_rs->fields["extnl_brand_seqno"];

$param = array();
$param["table"] = "extnl_brand";
$param["col"] = "extnl_etprs_seqno";
$param["where"]["extnl_brand_seqno"] = $extnl_brand_seqno;

$extnl_etprs_seqno = $dao->selectData($conn, $param)->fields["extnl_etprs_seqno"];

$param = array();
$param["table"] = "extnl_etprs";
$param["col"] = "manu_name";
$param["where"]["extnl_etprs_seqno"] = $extnl_etprs_seqno;

$manu_name = $dao->selectData($conn, $param)->fields["manu_name"];

$size_arr = explode("*", $size);

$param = array();
$param["table"] = "produce_process_flow";
$param["col"] = "output_yn";
$param["where"]["typset_num"] = $typset_num;

$output_yn = $dao->selectData($conn, $param)->fields["print_yn"];

$param = array();
if ($output_yn == "N") {
    $param["output_y"] = "";
    $param["output_n"] = "checked";
} else {
    $param["output_y"] = "checked";
    $param["output_n"] = "";
} 

$param["typset_num"] = $typset_num;
$param["name"] = $name;
$param["affil"] = $affil;
$param["wid_size"] = $size_arr[0];
$param["vert_size"] = $size_arr[1];
$param["amt"] = $amt;
$param["amt_unit"] = $amt_unit;
$param["memo"] = $memo;
$param["board"] = $board;
$param["extnl_brand_seqno"] = $extnl_brand_seqno;
$param["manu_name"] = $manu_name;

echo getOutputView($param);
$conn->close();
?>
