<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/ErpCommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/mkt/mkt_mng/EventMngDAO.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/basic_mng/prdt_price_mng/PrdtPriceListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$eventDAO = new EventMngDAO();
$prdtDAO = new PrdtPriceListDAO();
$util = new ErpCommonUtil();

//골라담기 이벤트 상세 일련번호
$overto_detail_seqno = $fb->form("overto_detail_seqno");

//골라담기 이벤트 파일
$param = array();
$param["table"] = "overto_detail_file";
$param["col"] = "origin_file_name, overto_detail_file_seqno";
$param["where"]["overto_event_detail_seqno"] = $overto_detail_seqno;

$f_result = $eventDAO->selectData($conn, $param);

//파일이 있을때
if ($f_result) {

    $param = array();
    $param["file_name"] = $f_result->fields["origin_file_name"];
    $param["file_seqno"] = $f_result->fields["overto_detail_file_seqno"];

    if ($param["file_name"]) {

        //삭제할 함수 이름
        $param["event_func"] = "delOvertoDetailFile";
        $file_html = getFileHtml($param);

    }
}

$param = array();
$param["overto_seqno"]  = $fb->form("overto_seqno");
$param["overto_detail_seqno"]  = $overto_detail_seqno;

//골라담기 정보 가져오기
$result = $eventDAO->selectOvertoDetailList($conn, $param);

$param = array();
$param["overto_detail_seqno"] = $overto_detail_seqno;
$param["file_html"] = $file_html;

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

$html = getOvertoDetailView($param);

echo $html;

$conn->close();
?>
