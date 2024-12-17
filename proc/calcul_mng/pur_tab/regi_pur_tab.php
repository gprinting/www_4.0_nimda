<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/calcul_mng/tab/PurTabListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PurTabListDAO();
$check = 1;
$conn->StartTrans();

$pur_tab_seqno = $fb->form("pur_tab_seqno");
$cpn_admin_seqno = $fb->form("cpn_admin_seqno");
$extnl_etprs_seqno = $fb->form("extnl_etprs_seqno");
$pur_cpn = $fb->form("manu_name");
$crn = $fb->form("crn");
$repre_name = $fb->form("repre_name");
$addr = $fb->form("addr");
$bc = $fb->form("bc");
$tob = $fb->form("tob");
$write_date = $fb->form("write_date");
$item = $fb->form("item");
$supply_price = str_replace(',','', $fb->form("supply_price"));
$vat = str_replace(',','',$fb->form("vat"));
$tot_price = $supply_price + $vat;
$year = substr($fb->form("write_date"), 0,4);
$mon = substr($fb->form("write_date"), 5,2);

if($pur_tab_seqno == "") {
//매입계산서 등록
    $param = array();
    $param["table"] = "pur_tab";
    $param["col"]["cpn_admin_seqno"] = $fb->form("cpn_admin_seqno");
    $param["col"]["extnl_etprs_seqno"] = $fb->form("extnl_etprs_seqno");
    $param["col"]["pur_cpn"] = $fb->form("manu_name");
    $param["col"]["crn"] = $fb->form("crn");
    $param["col"]["repre_name"] = $fb->form("repre_name");
    $param["col"]["addr"] = $fb->form("addr");
    $param["col"]["bc"] = $fb->form("bc");
    $param["col"]["tob"] = $fb->form("tob");
    $param["col"]["write_date"] = $fb->form("write_date");
    $param["col"]["item"] = $fb->form("item");
    $param["col"]["supply_price"] = str_replace(',', '', $fb->form("supply_price"));
    $param["col"]["vat"] = str_replace(',', '', $fb->form("vat"));
    $param["col"]["tot_price"] = $fb->form("supply_price") + $fb->form("vat");
    $param["col"]["year"] = substr($fb->form("write_date"), 0, 4);
    $param["col"]["mon"] = substr($fb->form("write_date"), 5, 2);

    $rs = $dao->insertData($conn, $param);
    if (!$rs) {
        $check = 0;
    }
} else  {
    $param = array();
    $param['pur_tab_seqno'] = $pur_tab_seqno;
    $param['cpn_admin_seqno'] = $cpn_admin_seqno;
    $param['extnl_etprs_seqno'] = $extnl_etprs_seqno;
    $param['pur_cpn'] = $pur_cpn;
    $param['crn'] = $crn;
    $param['repre_name'] = $repre_name;
    $param['addr'] = $addr;
    $param['bc'] = $bc;
    $param['tob'] = $tob;
    $param['write_date'] = $write_date;
    $param['item'] = $item;
    $param['supply_price'] = $supply_price;
    $param['vat'] = $vat;
    $param['tot_price'] = $tot_price;
    $param['year'] = $year;
    $param['mon'] = $mon;
    $rs = $dao->updatePurDetail($conn, $param);
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
