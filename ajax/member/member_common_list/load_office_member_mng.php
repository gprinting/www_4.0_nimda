<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/Template.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/member/member_mng/MemberCommonListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$template = new Template();
$dao = new MemberCommonListDAO();

$el = $fb->form("el");
$sell_site = $fb->form("sell_site");
$depar_code = $fb->form("depar_code");

// 미분류 직원(기본값) 정보 검색
$param = array();
$param["table"] = "empl";
$param["col"] = "name, empl_seqno";
$param["where"]["cpn_admin_seqno"] = $sell_site;
$param["where"]["empl_id"] = "temp";

$temp_empl = $dao->selectData($conn, $param);

$arr = [];
$arr["flag"] = "N";
$arr["def"] = "";
$arr["dvs"] = "name";
$arr["val"] = "empl_seqno";
$temp_empl = makeSelectOptionHtml($temp_empl, $arr);

if ($el == "gene") {

    $param = array();
    $param["table"] = "empl";
    $param["col"] = "empl_seqno, name";
    $param["where"]["depar_code"] = $depar_code;
    $param["where"]["cpn_admin_seqno"] = $sell_site;

    $empl_rs = $dao->selectData($conn, $param);

    $arr = [];
    $arr["flag"] = "N";
    $arr["def"] = "";
    $arr["dvs"] = "name";
    $arr["val"] = "empl_seqno";

    $tel_empl = makeSelectOptionHtml($empl_rs, $arr);

    $param["where"]["oper_sys"] = "IBM";

    $empl_rs = $dao->selectData($conn, $param);

    $arr = [];
    $arr["flag"] = "N";
    $arr["def"] = "";
    $arr["dvs"] = "name";
    $arr["val"] = "empl_seqno";

    $ibm_empl = makeSelectOptionHtml($empl_rs, $arr);

    $param = array();
    $param["table"] = "empl";
    $param["col"] = "empl_seqno, name";
    $param["where"]["depar_code"] = $depar_code;
    $param["where"]["cpn_admin_seqno"] = $sell_site;
    $param["where"]["oper_sys"] = "MAC";

    $empl_rs = $dao->selectData($conn, $param);

    $arr = [];
    $arr["flag"] = "N";
    $arr["def"] = "";
    $arr["dvs"] = "name";
    $arr["val"] = "empl_seqno";

    $mac_empl = makeSelectOptionHtml($empl_rs, $arr);

} else {

    $param = array();
    $param["table"] = "empl";
    $param["col"] = "empl_seqno, name";
    $param["where"]["depar_code"] = $depar_code;
    $param["where"]["cpn_admin_seqno"] = $sell_site;

    $empl_rs = $dao->selectData($conn, $param);

    $arr = [];
    $arr["flag"] = "N";
    $arr["def"] = "";
    $arr["dvs"] = "name";
    $arr["val"] = "empl_seqno";

    $tel_empl = makeSelectOptionHtml($empl_rs, $arr);

    $param["where"]["oper_sys"] = "IBM";

    $empl_rs = $dao->selectData($conn, $param);

    $arr = [];
    $arr["flag"] = "N";
    $arr["def"] = "";
    $arr["dvs"] = "name";
    $arr["val"] = "empl_seqno";

    $ibm_empl = makeSelectOptionHtml($empl_rs, $arr);

    $param = array();
    $param["table"] = "empl";
    $param["col"] = "empl_seqno, name";
    $param["where"]["depar_code"] = $depar_code;
    $param["where"]["cpn_admin_seqno"] = $sell_site;
    $param["where"]["oper_sys"] = "MAC";

    $empl_rs = $dao->selectData($conn, $param);

    $arr = [];
    $arr["flag"] = "N";
    $arr["def"] = "";
    $arr["dvs"] = "name";
    $arr["val"] = "empl_seqno";

    $mac_empl = makeSelectOptionHtml($empl_rs, $arr);
}

echo $tel_empl . "♪" . $ibm_empl . "♪" . $mac_empl . "♪" . $temp_empl;
$conn->close();
?>
