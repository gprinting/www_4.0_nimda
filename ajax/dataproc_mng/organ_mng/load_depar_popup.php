<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/dataproc_mng/organ_mng/OrganMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();


$fb = new FormBean();
$organDAO = new OrganMngDAO();

//부서 일련번호
$depar_admin_seqno = $fb->form("depar_admin_seq");
//회사 일련번호
$cpn_admin_seqno = $fb->form("cpn_admin_seq");

$d_param = array();
$high_depar_code = "";
//부서 수정일때
if ($depar_admin_seqno) {

    $d_param["dis_sel"] = "disabled=\"disabled\"";

    //부서 관리
    $param = array();
    $param["table"] = "depar_admin";
    $param["col"] = "depar_name, high_depar_code, 
        depar_admin_seqno, cpn_admin_seqno";
    $param["where"]["depar_code"] =  $depar_admin_seqno;
    $param["where"]["cpn_admin_seqno"] =  $cpn_admin_seqno;
    $rs = $organDAO->selectData($conn, $param);
    $high_depar_code = $rs->fields["high_depar_code"];

}

//판매채널 선택
$param = array();
$param["table"] = "cpn_admin";
$param["col"] = "sell_site, cpn_admin_seqno";
$sell_rs = $organDAO->selectData($conn, $param);
$select_sell_site = "";
//수정 모드 일때
if ($cpn_admin_seqno) {

    $select_sell_site = $cpn_admin_seqno;

}

$sell_site_html = makeSelectedOptionHtml($sell_rs, $select_sell_site, 
                                        "sell_site", "cpn_admin_seqno");
//상위 부서 코드
$param = array();
$param["table"] = "depar_admin";
$param["col"] = "depar_name, depar_code";
$param["where"]["level"] = "1";
//수정 모드 일때
if ($cpn_admin_seqno) {
    $param["where"]["cpn_admin_seqno"] = $cpn_admin_seqno;
}

$high_rs = $organDAO->selectData($conn, $param);
$high_depar_html = makeSelectedOptionHtml($high_rs, $high_depar_code, 
                                        "depar_name", "depar_code");

//파라미터 셋팅
$d_param["depar_name"] = $fb->form("depar_name");
$d_param["depar_code"] = $rs->fields["depar_code"];
$d_param["sell_site_html"] =  $sell_site_html;
$d_param["high_depar_html"] = $high_depar_html;

$html = getDeparAdminHtml($d_param);

echo $html;

$conn->close();
?>
