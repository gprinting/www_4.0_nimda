<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/item_mng/PaperStockMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PaperStockMngDAO();
$check = 1;

$conn->StartTrans();

$manu = $fb->form("manu");
$paper_name = $fb->form("name");
$paper_dvs = $fb->form("dvs");
$paper_color = $fb->form("color");
$paper_basisweight = $fb->form("basisweight");
$stor_yn = $fb->form("stor_yn");
$amt = $fb->form("amt");

$param = array();
$param["paper_name"] = $paper_name;
$param["paper_dvs"] = $paper_dvs;
$param["paper_color"] = $paper_color;
$param["paper_basisweight"] = $paper_basisweight;
$param["manu"] = $manu;
$last_amt = $dao->selectLastStockAmt($conn, $param)->fields["stock_amt"];

$fields = "";
$stock_amt = "";
if ($stor_yn == "Y") {
    $fields = "stor_amt";
    $stock_amt = $last_amt + $amt;
} else {
    $fields = "use_amt";
    $stock_amt = $last_amt - $amt;
}

$param = array();
$param["table"] = "manu_paper_stock_detail";
$param["col"]["regi_date"] = date("Y-m-d H:i:s");
$param["col"]["stor_yn"] = $stor_yn;
$param["col"]["paper_name"] = $paper_name;
$param["col"]["paper_dvs"] = $paper_dvs;
$param["col"]["paper_color"] = $paper_color;
$param["col"]["paper_basisweight"] = $paper_basisweight;
$param["col"]["manu"] = $manu;
$param["col"][$fields] = $amt;
$param["col"]["stock_amt"] = $stock_amt;

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
