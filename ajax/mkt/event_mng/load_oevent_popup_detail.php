<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/ErpCommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/mkt/mkt_mng/EventMngDAO.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_price_mng/PrdtPriceListDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$eventDAO = new EventMngDAO();
$prdtDAO = new PrdtPriceListDAO();
$util = new ErpCommonUtil();

//등록 or 수정
$oevent_seqno = $fb->form("oevent_seqno");

//오특이 이벤트 파일
$param = array();
$param["table"] = "oevent_file";
$param["col"] = "origin_file_name, file_path, oevent_file_seqno";
$param["where"]["oevent_event_seqno"] = $oevent_seqno;

$f_result = $eventDAO->selectData($conn, $param);

if ($f_result) {

    $param = array();
    $param["file_name"] = $f_result->fields["origin_file_name"];
    $param["file_seqno"] = $f_result->fields["oevent_file_seqno"];

    if ($param["file_name"]) {
        $param["event_func"] = "delOeventFile";
        $file_html = getFileHtml($param);
    }
}

$param = array();
$param["oevent_seqno"]  = $oevent_seqno;

//오특이 정보 가져오기
$result = $eventDAO->selectOeventInfoList($conn, $param);

$param = array();
$param["oevent_event_seqno"] = $oevent_seqno;
$param["file_html"] = $file_html;
$param["event_name"] = $result->fields["name"];
$param["oevent_seqno"]  = $oevent_seqno;

$dsply_yn = $result->fields["dsply_yn"];

//진열중일때
if ($dsply_yn == "Y") {

    $param["dsply_y"] = "checked";
    $param["dsply_n"] = "";

    //미진열일때
} else {

    $param["dsply_n"] = "checked";
    $param["dsply_y"] = "";

}

//이벤트 일자
$param["oevent_date"] = $result->fields["event_date"];

//이벤트 시작 시간
$start_hour_info = $result->fields["start_hour"];

$start_hour_info = substr($start_hour_info, 11);
$start_hour_info = explode(":", $start_hour_info);
$start_hour = $start_hour_info[0];
$start_min = $start_hour_info[1];

//이벤트 종료 시간
$end_hour_info = $result->fields["end_hour"];

$end_hour_info = substr($end_hour_info, 11);
$end_hour_info = explode(":", $end_hour_info);
$end_hour = $end_hour_info[0];
$end_min = $end_hour_info[1];

//카테고리 소분류
$bot_sortcode = $result->fields["cate_sortcode"];

//카테고리 대분류
$top_sortcode = substr($bot_sortcode, 0,3);
$t_result = $eventDAO->selectFlatCateList($conn);
$top_html = 
    makeSelectedOptionHtml($t_result, $top_sortcode, "cate_name", "sortcode");
$param["cate_top"] = $top_html;

//카테고리 중분류
$mid_sortcode = substr($bot_sortcode, 0,6);
$m_result = $eventDAO->selectMktCateList($conn, $top_sortcode);
$mid_html = 
    makeSelectedOptionHtml($m_result, $mid_sortcode, "cate_name", "sortcode");
$param["cate_mid"] = $mid_html;

//카테고리 소분류
$b_result = $eventDAO->selectMktCateList($conn, $mid_sortcode);
$bot_html = 
    makeSelectedOptionHtml($b_result, $bot_sortcode, "cate_name", "sortcode");
$param["cate_bot"] = $bot_html;

//종이명 셀렉트 박스
$p_param = array();
$p_param["cate_sortcode"] = $bot_sortcode;

//종이명
$paper_name = $result->fields["paper_name"];

$p_result = $prdtDAO->selectCatePaperInfo($conn, "NAME", $p_param);
$name_html = makeSelectedOptionHtml($p_result, $paper_name, "name", "name");
$param["paper_name"] =  $name_html;

$p_result = "";
//종이 구분
$paper_dvs = $result->fields["paper_dvs"];
$p_param["paper_name"] = $paper_name;
$p_result = $prdtDAO->selectCatePaperInfo($conn, "DVS", $p_param);
$dvs_html = 
    makeSelectedOptionHtml($p_result, $paper_dvs, "dvs", "dvs");
$param["paper_dvs"] = $dvs_html;

$p_result = "";
//종이 색상
$paper_color = $result->fields["paper_color"];
$p_result = $prdtDAO->selectCatePaperInfo($conn, "COLOR", $p_param);
$color_html = 
    makeSelectedOptionHtml($p_result, $paper_color, "color", "color");
$param["paper_color"] =  $color_html;

$p_result = "";
//종이 평량
$weight = $result->fields["paper_basisweight"];
$p_result = $prdtDAO->selectCatePaperInfo($conn, "BASISWEIGHT", $p_param);
$basisweight_html = 
    makeSelectedOptionHtml($p_result, $weight, "basisweight", "basisweight");
$param["paper_basisweight"] =  $basisweight_html;


//카테고리 규격
$p_param = array();
$p_result = "";
$p_param["cate_sortcode"] = $bot_sortcode;
$cate_stan_mpcode = $result->fields["cate_stan_mpcode"];
$p_result = $eventDAO->selectMktCateSize($conn, $p_param);
$size_html = 
    makeSelectedOptionHtml($p_result, $cate_stan_mpcode, "name", "mpcode");
$param["output_size"] =  $size_html;

//인쇄명
$p_result = "";
$print_name = $result->fields["print_name"];
$p_result = $eventDAO->selectMktPrintTmpt($conn, $p_param);
$print_html = 
    makeSelectedOptionHtml($p_result, $print_name, "name", "name");
$param["print_tmpt"] =  $print_html;

//수량

//* 판매채널에 해당하는 합판/계산형 가격테이블 검색

//판매채널 일련번호
$cpn_seqno = $result->fields["cpn_admin_seqno"];
$p_param = array();
$p_result = "";
$table_name = $prdtDAO->selectPriceTableName($conn, intval("N"), $cpn_seqno);

$p_param["cate_sortcode"] = $bot_sortcode;
$p_param["paper_mpcode"] = $result->fields["cate_paper_mpcode"];
$p_param["print_mpcode"] = $result->fields["cate_print_mpcode"];
$p_param["stan_mpcode"] = $cate_stan_mpcode;
$amt = $result->fields["amt"];
$p_result = $prdtDAO->selectCatePriceList($conn, $table_name, $p_param);
$amt_html = makeSelectedOptionHtml($p_result, $amt, "amt", "amt");
$param["amt"] = $amt_html;
$param["amt_unit"] = $result->fields["amt_unit"];

//계산 가격
$param["sum_price"] = $result->fields["sum_price"];

//할인 가격
$param["sale_price"] = $result->fields["sale_price"];

//기본 가격
$param["basic_price"] = $param["sum_price"] + $param["sale_price"];

$select_box_val = $start_hour . "♪♡♭" . $start_min . "♪♡♭" . 
$end_hour . "♪♡♭" .  $end_min . "♪♡♭" . $cpn_seqno;

//판매채널 콤보박스 셋팅
$param["sell_site"] = $eventDAO->selectSellSite($conn);
//시간 콤보박스 셋팅
$param["hour_list"] = makeOptionTimeHtml(0,23);
//분 콤보박스 셋팅
$param["min_list"] = makeOptionTimeHtml(0,59);

$html = getOeventDetailView($param);

echo $html . "♪♥♭" .  $select_box_val;

$conn->close();
?>
