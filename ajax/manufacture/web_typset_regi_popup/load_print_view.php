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

$param["table"] = "print_op";
$param["col"] = "beforeside_tmpt, beforeside_spc_tmpt,
    aftside_tmpt, aftside_spc_tmpt, tot_tmpt, amt, amt_unit, 
    name, affil, size, memo, extnl_brand_seqno";
$param["where"]["typset_num"] = $typset_num;

$sel_rs = $dao->selectData($conn, $param);

$extnl_brand_seqno = $sel_rs->fields["extnl_brand_seqno"];
$beforeside_tmpt = $sel_rs->fields["beforeside_tmpt"];
$beforeside_spc_tmpt = $sel_rs->fields["beforeside_spc_tmpt"];
$aftside_tmpt = $sel_rs->fields["aftside_tmpt"];
$aftside_spc_tmpt = $sel_rs->fields["aftside_spc_tmpt"];
$tot_tmpt = $sel_rs->fields["tot_tmpt"];
$amt = $sel_rs->fields["amt"];
$amt_unit = $sel_rs->fields["amt_unit"];
$name = $sel_rs->fields["name"];
$affil = $sel_rs->fields["affil"];
$size = $sel_rs->fields["size"];
$memo = $sel_rs->fields["memo"];

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
$param["col"] = "print_yn";
$param["where"]["typset_num"] = $typset_num;

$print_yn = $dao->selectData($conn, $param)->fields["print_yn"];

$param = array();
if ($print_yn == "N") {
    $param["print_y"] = "";
    $param["print_n"] = "checked";
} else {
    $param["print_y"] = "checked";
    $param["print_n"] = "";
} 

$param["typset_num"] = $typset_num;
$param["beforeside_tmpt"] = $beforeside_tmpt;
$param["beforeside_spc_tmpt"] = $beforeside_spc_tmpt;
$param["aftside_tmpt"] = $aftside_tmpt;
$param["aftside_spc_tmpt"] = $aftside_spc_tmpt;
$param["tot_tmpt"] = $tot_tmpt;
$param["name"] = $name;
$param["affil"] = $affil;
$param["wid_size"] = $size_arr[0];
$param["vert_size"] = $size_arr[1];
$param["amt"] = $amt;
$param["amt_unit"] = $amt_unit;
$param["memo"] = $memo;
$param["extnl_brand_seqno"] = $extnl_brand_seqno;
$param["manu_name"] = $manu_name;

echo getPrintView($param);
$conn->close();
?>
