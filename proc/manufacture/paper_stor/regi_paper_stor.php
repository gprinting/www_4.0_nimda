<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/print_mng/PaperStorDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PaperStorDAO();
$check = 1;

$conn->StartTrans();

$state_arr = $fb->session("state_arr");
$state = $state_arr["종이입고완료"];

$param = array();
$param["table"] = "paper_op";
$param["col"]["state"] = $state;
$param["col"]["stor_date"] = date("Y-m-d H:i:s");
$param["col"]["warehouser"] = $fb->session("name");
$param["prk"] = "paper_op_seqno";
$param["prkVal"] = $fb->form("paper_op_seqno");

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

$param = array();
$param["paper_op_seqno"] = $fb->form("paper_op_seqno");

$sel_rs = $dao->selectPaperOpView($conn, $param);

//R->장 = 장수 * 500 * 자리수(절수)
//장->R = 장수 / 자리수(절수) / 500

$amt = $sel_rs->fields["amt"];
$subpaper = $sel_rs->fields["subpaper"];
if ($sel_rs->fields["amt_unit"] != "R") {
    if ($subpaper == "별" || !$subpaper) {
        $subpaper = 1;
    }
    $amt = $sel_rs->fields["amt"] / $subpaper / 500;
}

$paper_name = $sel_rs->fields["name"];
$paper_dvs = $sel_rs->fields["dvs"];
$paper_color = $sel_rs->fields["color"];
$paper_basisweight = $sel_rs->fields["basisweight"];
$manu = $sel_rs->fields["manu_name"];

$param = array();
$param["paper_name"] = $paper_name;
$param["paper_dvs"] = $paper_dvs;
$param["paper_color"] = $paper_color;
$param["paper_basisweight"] = $paper_basisweight;
$param["manu"] = $manu;
$last_amt = $dao->selectLastStockAmt($conn, $param)->fields["stock_amt"];

$param = array();
$param["table"] = "manu_paper_stock_detail";
$param["col"]["regi_date"] = date("Y-m-d H:i:s");
$param["col"]["stor_yn"] = "Y";
$param["col"]["paper_name"] = $paper_name;
$param["col"]["paper_dvs"] = $paper_dvs;
$param["col"]["paper_color"] = $paper_color;
$param["col"]["paper_basisweight"] = $paper_basisweight;
$param["col"]["manu"] = $manu;
$param["col"]["stor_amt"] = $amt;
$param["col"]["typset_num"] = $sel_rs->fields["typset_num"];
$param["col"]["stock_amt"] = $last_amt + $amt;

$rs = $dao->insertData($conn, $param);

if (!$rs) {
    $check = 0;
}

$param = array();
$param["table"] = "manu_paper_stock_day";
$param["col"] = "manu_paper_stock_day_seqno, stor_amt, use_amt, stock_amt"; 
$param["where"]["manu"] = $manu;
$param["where"]["paper_name"] = $paper_name;
$param["where"]["paper_dvs"] = $paper_dvs;
$param["where"]["paper_color"] = $paper_color;
$param["where"]["paper_basisweight"] = $paper_basisweight;
$param["blike"]["regi_date"] = date("Y-m-d");

$sel_rs = $dao->selectData($conn, $param);

$manu_paper_stock_day_seqno = $sel_rs->fields["manu_paper_stock_day_seqno"];
$stor_amt = $sel_rs->fields["stor_amt"];
$use_amt = $sel_rs->fields["use_amt"];
$stock_amt = $sel_rs->fields["stock_amt"];

$param = array();
$param["table"] = "manu_paper_stock_day";
$param["col"]["regi_date"] = date("Y-m-d H:i:s");
$param["col"]["manu"] = $manu;
$param["col"]["paper_name"] = $paper_name;
$param["col"]["paper_dvs"] = $paper_dvs;
$param["col"]["paper_color"] = $paper_color;
$param["col"]["paper_basisweight"] = $paper_basisweight;

if ($manu_paper_stock_day_seqno) {
    $param["col"]["stor_amt"] = $stor_amt + $amt;
    $param["col"]["stock_amt"] = $stock_amt + $amt;
    $param["prk"] = "manu_paper_stock_day_seqno";
    $param["prkVal"] = $manu_paper_stock_day_seqno;

    $rs = $dao->updateData($conn, $param);
} else {

    $param["col"]["stor_amt"] = $amt;
    $param["col"]["stock_amt"] = $last_amt + $amt;

    $rs = $dao->insertData($conn, $param);
}

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->close();
echo $check;
?>
