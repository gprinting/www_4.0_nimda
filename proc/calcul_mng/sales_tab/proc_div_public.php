<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/calcul_mng/tab/SalesTabListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new SalesTabListDAO();
$check = 1;
$conn->StartTrans();
echo "<pre>";
$conn->debug=1;

$param = array();
$param["table"] = "public_admin";
$param["col"] = "member_seqno
                ,sales_price
                ,card_price
                ,public_dvs
                ,public_state
                ,public_year
                ,public_mon
                ,member_name
                ,supply_corp
                ,crn
                ,repre_name
                ,addr
                ,bc
                ,tob
                ,public_date
                ,item
                ,supply_price
                ,vat
                ,evid_dvs
                ,cashreceipt_num
                ,card_yn";
$param["where"]["public_admin_seqno"] = $fb->form("seqno");
$info_rs = $dao->selectData($conn, $param);
if (!$info_rs) {
    $check = 0;
}

$param = array();
$param["table"] = "public_admin";
$param["col"]["member_seqno"] = $info_rs->fields["member_seqno"];
$param["col"]["sales_price"] = $info_rs->fields["sales_price"];
$param["col"]["card_price"] = $info_rs->fields["card_price"];
$param["col"]["public_dvs"] = $info_rs->fields["public_dvs"];
$param["col"]["public_state"] = $info_rs->fields["public_state"];
$param["col"]["public_year"] = $info_rs->fields["public_year"];
$param["col"]["public_mon"] = $info_rs->fields["public_mon"];
$param["col"]["member_name"] = $info_rs->fields["member_name"];
$param["col"]["supply_corp"] = $info_rs->fields["supply_corp"];
$param["col"]["crn"] = $info_rs->fields["crn"];
$param["col"]["repre_name"] = $info_rs->fields["repre_name"];
$param["col"]["addr"] = $info_rs->fields["addr"];
$param["col"]["bc"] = $info_rs->fields["bc"];
$param["col"]["tob"] = $info_rs->fields["tob"];
$param["col"]["public_date"] = $info_rs->fields["public_date"];
$param["col"]["item"] = $info_rs->fields["item"];
$param["col"]["supply_price"] = $info_rs->fields["supply_price"];
$param["col"]["vat"] = $info_rs->fields["vat"];
$param["col"]["evid_dvs"] = $info_rs->fields["evid_dvs"];
$param["col"]["cashreceipt_num"] = $info_rs->fields["cashreceipt_num"];
$param["col"]["card_yn"] = $info_rs->fields["card_yn"];

$price_arr = explode(",", $fb->form("price"));

for ($i = 0; $i < count($price_arr); $i++) {

    $param["col"]["object_price"] = $price_arr[$i];
    $param["col"]["unissued_price"] = $price_arr[$i];

    $rs = $dao->insertData($conn, $param);

    if (!$rs) {
        $check = 0;
    }
}

$param = array();
$param["table"] = "public_admin";
$param["prk"] = "public_admin_seqno";
$param["prkVal"] = $fb->form("seqno");

$rs = $dao->deleteData($conn, $param);

if (!$rs) {
    $check = 0;
}



$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
