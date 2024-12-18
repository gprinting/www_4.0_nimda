<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/claim_mng/ClaimListDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new ClaimListDAO();
$commonDAO = $dao;

//$conn->debug = 1;

//리스트 보여주는 갯수 설정
$list_num = 5;

//현재 페이지
$page = $fb->form("page");

// 페이지가 없으면 1 페이지
if (!$fb->form("page")) {
    $page = 1; 
}

//블록 갯수
$scrnum = 5; 
$s_num = $list_num * ($page-1);

$from_date = $fb->form("date_from");
$from_time = "";
$to_date = $fb->form("date_to");
$to_time = "";

if ($from_date) {
    $from_time = $fb->form("time_from");
    $from = $from_date . " " . $from_time . ":00:00";
}

if ($to_date) {
    $to_time = " " . $fb->form("time_to") + 1;
    $to =  $to_date . " " . $to_time . ":59:59";
}

$state = "";

if ($fb->form("status")) {
    $state = $fb->form("status");
}

$param = array();
$param["list_num"]     = $list_num;
$param["sell_site"]    = $fb->form("sell_site");
$param["member_seqno"] = $fb->form("member_seqno");
$param["depar_code"]   = $fb->form("depar_code");
$param["dvs"]          = "";
$param["claim_dvs"]    = $fb->form("claim_dvs");
$search_keyword        = $fb->form("search_keyword");
$search_dvs            = $fb->form("search_dvs");
$param["depar"]        = $fb->form("depar");
$param["empl"]         = $fb->form("empl");
$param["member_typ"]   = $fb->form("member_typ");
$param["member_grade"] = $fb->form("member_grade");
$param["s_num"]        = $s_num;
$param["from"]         = $from;
$param["to"]           = $to;
$param["state"]        = "";

//직원 팀 검색
$pre_rs = $dao->selectEmplTeam($conn, $param);
$empl_seqno = "";
while(!$pre_rs->EOF && $pre_rs) {
    $empl_seqno .= $pre_rs->fields["empl_seqno"];
    $empl_seqno .= ",";

    $pre_rs->MoveNext();
}
$empl_seqno = substr($empl_seqno, 0, -1);

// 빈값일경우 확실히 빈값을 넘김 
if ($empl_seqno == "") {
    $empl_seqno = "";
}

$param["empl_seqno"] = $empl_seqno;

//상태 일 때 
if ($search_dvs == "status" && !empty($search_keyword)) {
    $param["state"] = $search_keyword;
}
$rs = $dao->selectClaimListByCond($conn, $param, $s_num);
$list = makeClaimListHtml($rs, $param);

$result_cnt = '';
if (empty($page_dvs)) {
    $result_cnt = $dao->selectFoundRows($conn);
}

//$paging = mkDotAjaxPage($result_cnt, $page, $scrnum, $list_num, "movePage");

echo $list;
$conn->Close();
?>
