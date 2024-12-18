<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/calcul_mng/tab/PurTabListDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$fb = new FormBean();
$dao = new PurTabListDAO();

$pur_tab_seqno = $fb->form("pur_tab_seqno");

$param = array();
$param["pur_tab_seqno"] = $pur_tab_seqno;

$rs = $dao->selectPurDetail($conn, $param);

$pur_tab_seqno = '';
$write_date = '';
$item = '';
$supply_price = '';
$vat = '';
$manu_name = '';
$crn = '';
$repre_name = '';
$addr = '';
$bc = '';
$tob = '';
$extnl_etprs_seqno = '';

while($rs && !$rs->EOF) {
    $pur_tab_seqno = $rs->fields['pur_tab_seqno'];
    $write_date = $rs->fields['write_date'];
    $item = $rs->fields['item'];
    $supply_price = $rs->fields['supply_price'];
    $vat = $rs->fields['vat'];
    $manu_name = $rs->fields['manu_name'];
    $crn = $rs->fields['crn'];
    $repre_name = $rs->fields['repre_name'];
    $addr = $rs->fields['addr'];
    $bc = $rs->fields['bc'];
    $extnl_etprs_seqno = $rs->fields['extnl_etprs_seqno'];
    $tob = $rs->fields['tob'];

    $rs->MoveNext();
}


echo $write_date . "♪♭@"  . $item . "♪♭@" . number_format($supply_price) . "♪♭@" .
    number_format($vat) . "♪♭@" .
    $manu_name . "♪♭@" .
    $crn . "♪♭@" .
    $repre_name . "♪♭@" .
    $addr . "♪♭@" .
    $bc . "♪♭@" .
    $extnl_etprs_seqno . "♪♭@" .
    $tob . "♪♭@" .
    $pur_tab_seqno;

$conn->Close();
?>
