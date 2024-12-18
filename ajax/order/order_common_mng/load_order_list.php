<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/ErpCommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/order_mng/OrderCommonMngDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/pageLib.inc');

/*******************************************************************************
                                 함수 영역
 ******************************************************************************/
/**
 * @brief 주문 상태에 따란 코드값 반환
 *
 * @param $status      = 상태값
 * @param $status_proc = 상태진행값
 *
 * @return 상태진행 코드값 배열
 */
function getOrderStatus($status, $status_proc, $state_arr) {
    if ($status === '') {
        return '';
    }

    if ($status_proc !== '') {
        return $status_proc;
    }

    $proc_arr = $state_arr[$status];
    $temp_arr = array();
    $i = 0;
    if($proc_arr != null) {

        foreach ($proc_arr as $status => $code) {
            $temp_arr[$i++] = $code;
        }
    }

    return $temp_arr;
}

/**
 * @brief 회원별 검색일 때는 판매채널을 제외한 조건
 * 팀 별 검색일 때는 모든 검색조건 중 하나라도 넘어온 경우에는
 * 검색조건 쿼리, 빈 조건일 경우에는 대용량 쿼리로 구분지어서
 * 실행하는 함수
 *
 * @param $conn  = connection identifier
 * @param $dao   = 쿼리를 수행할 dao객체
 * @param $util  = 유틸 객체
 * @param $param = 검색조건 파라미터
 *
 * @return 주문리스트 html
 */
function getOrderListHtml($conn, $dao, $util, $param) {
    $ret = "";
    $flag = false;

    $list_size = $param["list_size"];
    $page      = $param["page"];

    // 이하 검색조건 값이 하나라도 있는경우 무조건
    // 일반검색 쿼리로 실행하기 위한 조건문들
    if ($dao->blankParameterCheck($param ,"sell_site")) {
        $flag = true;
    }
    if ($dao->blankParameterCheck($param ,"search_keyword")) {
        $flag = true;
    }
    if ($dao->blankParameterCheck($param ,"order_state")) {
        $flag = true;
    }
    if ($dao->blankParameterCheck($param ,"start_date")) {
        $flag = true;
    }
    if ($dao->blankParameterCheck($param ,"end_date")) {
        $flag = true;
    }

    if ($flag === true) {
        // 페이징 계산
        $param["limit_block"] = $list_size * ($page - 1);

        $ret = $dao->selectOrderListCondHtml($conn, $param);
    } else {
        // seqno 범위 계산
        $last_seqno = $dao->selectLastOrderSeqno($conn);

        $seqno_range = $util->calcSeqnoRange($last_seqno,
                                             $list_size,
                                             $page);
        if ($seqno_range === false) {
            return false;
        }
        $param["start_seqno"] = $seqno_range["start"];
        $param["end_seqno"]   = $seqno_range["end"];
        $param["start_date"]  = "0000-00-00 00:00:00";

        $ret = $dao->selectOrderListHtml($conn, $param);
    }

    return $ret;
}

/*******************************************************************************
                                 함수 영역 종료
 ******************************************************************************/
$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OrderCommonMngDAO();
$util = new ErpCommonUtil();

$page       = intval($fb->form("page"));
$list_size  = intval($fb->form("list_size"));

$sell_site     = $fb->form("sell_site");
$cate_sortcode = $fb->form("sortcode");

$from     = $fb->form("start_date");
$to       = $fb->form("end_date");

$search_dvs = $fb->form("search_dvs");
$search_keyword = $fb->form("search_keyword");

$pay_way = $fb->form("pay_way");
if($pay_way == "전체") $pay_way = "";
$order_state = $fb->form("order_state");

$param = array();
$param["sell_site"]     = $sell_site;
$param["sortcode"] = $cate_sortcode;
$param["search_dvs"]  = $search_dvs;
$param["search_keyword"]    = $search_keyword;
$param["start_date"]          = $from;
$param["end_date"]            = $to;
$param["pay_way"]         = $pay_way;
$param["order_state"]         = $order_state;

$param["page"]      = $page;
$param["list_size"] = $list_size;


$param["dvs"] = "COUNT";
$count_rs = $dao->selectOrderListCond($conn, $param);

$param["dvs"] = "TOTAL";
$total_rs = $dao->selectOrderListCond($conn, $param);

$t_sum = (int)$total_rs->fields["sell_sum"] +
         (int)$total_rs->fields["after_sum"] +
         (int)$total_rs->fields["opt_sum"];
$t_sum = number_format($t_sum);

$param["dvs"] = "";
$html = getOrderListHtml($conn, $dao, $util, $param);

if ($html === false) {
    goto NOT_INFO;
}

$cnt = $count_rs->fields["cnt"];

if ($cnt == "") {
    $cnt = 0;
}
//블록 갯수
$scrnum = 5; 

$paging = mkDotAjaxFncPage($cnt, $page, $scrnum, $list_size, "submit_search", $list_size);

//차후 부하 걸릴 시 삭제 해야 될 소스
$total = "";

$html .= "♪" . $total . "♪" . $paging;

$conn->Close();
echo $html;
exit;

NOT_INFO:
    $conn->Close();
    echo "<tr><td colspan=\"15\">검색된 내용이 없습니다.</td></tr>";
    echo "♪" . $total;
    exit;
?>
