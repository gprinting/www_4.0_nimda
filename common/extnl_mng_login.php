<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/job/nimda/common/NimdaCommonDAO.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new NimdaCommonDAO();
$fb = new FormBean();

$id = $fb->form("id");
$pw = $fb->form("pw");

$param = array();
$param["table"] = "extnl_etprs_member";
$param["col"] = "extnl_etprs_member_seqno, mng, id,
    access_code, tel_num, cell_num, mail, resp_task,
    job, extnl_etprs_seqno";
$param["where"]["id"] = $id;

$rs = $dao->selectData($conn, $param);

$access_code = $rs->fields["access_code"];

if ($pw != $access_code) {
    echo "false";
    exit;
}

$mng = $rs->fields["mng"];
$id = $rs->fields["id"];
$tel_num = $rs->fields["tel_num"];
$cell_num = $rs->fields["cell_num"];
$mail = $rs->fields["mail"];
$resp_task = $rs->fields["resp_task"];
$job = $rs->fields["job"];
$extnl_etprs_seqno = $rs->fields["extnl_etprs_seqno"];
$extnl_etprs_member_seqno = $rs->fields["extnl_etprs_member_seqno"];
$login_date  = date("Y-m-d H:i:s",time());

unset($rs);

$param = array();
$param["table"] = "extnl_etprs";
$param["col"] = "pur_prdt, manu_name";
$param["where"]["extnl_etprs_seqno"] = $extnl_etprs_seqno;

$rs = $dao->selectData($conn, $param);
$pur_prdt = $rs->fields["pur_prdt"];
$manu_name = $rs->fields["manu_name"];

$fb->addSession("name", $mng);
$fb->addSession("id", $id);
$fb->addSession("tel_num", $tel_num);
$fb->addSession("cell_num", $cell_num);
$fb->addSession("mail", $mail);
$fb->addSession("resp_task", $resp_task);
$fb->addSession("job", $job);
$fb->addSession("extnl_etprs_seqno", $extnl_etprs_seqno);
$fb->addSession("manu_name", $manu_name);
$fb->addSession("extnl_etprs_member_seqno", $extnl_etprs_member_seqno);
$fb->addSession("pur_prdt", $pur_prdt);
$fb->addSession("login_date", $login_date);

if ($pur_prdt == "종이") {
    $url = "";
/* 출력, 인쇄, 후공정은 일단 사내직원
} else if ($pur_prdt == "출력") {
    $url = "/manufacture/outsourcing_output_list.html";
} else if ($pur_prdt == "인쇄") {
    $url = "/manufacture/outsourcing_print_list.html";
} else if ($pur_prdt == "후공정") {
    $url = "/manufacture/outsourcing_after_list.html";
*/
} else if ($pur_prdt == "카드명함") {
    $url = "/manufacture/outsourcing_list.html";
} else if ($pur_prdt == "자석") {
    $url = "/manufacture/outsourcing_list.html";
} else if ($pur_prdt == "메뉴판") {
    $url = "/manufacture/outsourcing_list.html";
} else if ($pur_prdt == "마스터") {
    $url = "/manufacture/outsourcing_list.html";
} else if ($pur_prdt == "그린백") {
    $url = "/manufacture/outsourcing_list.html";
} else {
    $url = "";
}

echo "true" . "♪" . $url;
?>
