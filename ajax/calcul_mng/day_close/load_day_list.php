<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/calcul_mng/settle/DayCloseDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$fb = new FormBean();
$dayDAO = new DayCloseDAO();

$year = $fb->form("year");
$mon = $fb->form("mon");
$sell_site = $fb->form("sell_site");

$param = array();
$param["year"] = $year;
$param["mon"] = $mon;
$param["sell_site"] = $sell_site;

$rs = $dayDAO->selectLastCloseDay($conn, $param); 
$close_day = substr($rs->fields["close_date"], 8, 2);
if (!$close_day) {
    $param = array();
    $param["mon"] = $mon-1;
    $param["year"] = $year;
    $param["sell_site"] = $sell_site;

    if ($mon == "01") {
        $param["year"] = $year-1;
        $param["mon"] = 12;
    }

    if (strlen($param["mon"]) == "1") {
        $param["mon"] = "0" . $param["mon"];
    }

    $day = 1;
    while(checkdate($param["mon"], $day, $param["year"])) {
        $day++;
    }
    $day = $day-1;

    $rs = $dayDAO->selectLastCloseDay($conn, $param); 
    $close_day = substr($rs->fields["close_date"], 8, 2);

    if ($day == $close_day) {
        $close_day = "0";

    } else {
        $close_day = "";
    }
} 

$day = 1;
while(checkdate($mon, $day, $year)) {
    $day++;
}

$day = $day-1;

$disabled = "";
if ($close_day == $day) {

    $param = array();
    $param["mon"] = $mon + 1;
    $param["year"] = $year;
    $param["sell_site"] = $sell_site;

    if (strlen($param["mon"]) == "1") {
        $param["mon"] = "0" . $param["mon"];
    }

    $rs = $dayDAO->selectLastCloseDay($conn, $param);

    if ($rs->fields["close_date"]) {
        $disabled = "disabled=\"disabled\"";
    }
} 

$param = array();
$param["year"] = $year;
$param["mon"] = $mon;
$param["day"] = $day;
$param["close_day"] = $close_day;
$param["sell_site"] = $sell_site;
$param["disabled"] = $disabled;

$list = makeDayListHtml($param);

echo $list;
?>
