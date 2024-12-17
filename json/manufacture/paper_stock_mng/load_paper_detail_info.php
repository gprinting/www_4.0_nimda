<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/item_mng/PaperStockMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PaperStockMngDAO();

$param = array();
$param["table"] = "manu_paper_stock_detail";
$param["col"] = "paper_name, paper_dvs, paper_color, paper_basisweight, manu, regi_date";
$param["where"]["manu_paper_stock_detail_seqno"] = $fb->form("seqno");

$sel_rs = $dao->selectData($conn, $param);
$paper_name = $sel_rs->fields["paper_name"];
$paper_dvs = $sel_rs->fields["paper_dvs"];
$paper_color = $sel_rs->fields["paper_color"];
$paper_basisweight = $sel_rs->fields["paper_basisweight"];
$manu = $sel_rs->fields["manu"];
$regi_date = $sel_rs->fields["regi_date"];

$param = array();
$param["table"] = "manu_paper_stock_day";
$param["col"] = "SUM(stock_amt) AS stock_amt, SUM(use_amt) AS use_amt, SUM(stor_amt) AS stor_amt";
$param["where"]["manu"] = $manu;
$param["where"]["paper_name"] = $paper_name;
$param["where"]["paper_dvs"] = $paper_dvs;
$param["where"]["paper_color"] = $paper_color;
$param["where"]["paper_basisweight"] = $paper_basisweight;
$param["blike"]["regi_date"] = date("Y-m-d", strtotime($regi_date));

$day_rs = $dao->selectData($conn, $param);

$day_use = $day_rs->fields["use_amt"] / $day_rs->fields["stor_amt"] * 100;
$day_stock = $day_rs->fields["stock_amt"] / $day_rs->fields["stor_amt"] * 100;

$param = array();
$param["table"] = "manu_paper_stock_day";
$param["col"] = "SUM(stock_amt) AS stock_amt, SUM(use_amt) AS use_amt, SUM(stor_amt) AS stor_amt";
$param["where"]["manu"] = $manu;
$param["where"]["paper_name"] = $paper_name;
$param["where"]["paper_dvs"] = $paper_dvs;
$param["where"]["paper_color"] = $paper_color;
$param["where"]["paper_basisweight"] = $paper_basisweight;
$param["blike"]["regi_date"] = date("Y-m", strtotime($regi_date));

$month_rs = $dao->selectData($conn, $param);

$month_use = $month_rs->fields["use_amt"] / $month_rs->fields["stor_amt"] * 100;
$month_stock = $month_rs->fields["stock_amt"] / $month_rs->fields["stor_amt"] * 100;

$ret  = "{";
$ret .= "\"day\":[{";
$ret .= "\"name\" : \"재고량\",";
$ret .= "\"y\"    : %d";
$ret .= "}, {";
$ret .= "\"name\" : \"사용량\",";
$ret .= "\"y\"    : %d";
$ret .= "}],"; 
$ret .= "";
$ret .= "\"month\": [{";
$ret .= "\"name\" : \"재고량\",";
$ret .= "\"y\"    : %d";
$ret .= "}, {";
$ret .= "\"name\" : \"사용량\",";
$ret .= "\"y\"    : %d";
$ret .= "}]}";

echo sprintf($ret, $day_stock
        , $day_use
        , $month_stock
        , $month_use);

$conn->close();
?>
