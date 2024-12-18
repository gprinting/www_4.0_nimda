<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/mkt/mkt_mng/EventMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$fb = new FormBean();
$eventDAO = new EventMngDAO();
$prdt_list = "";

if ($fb->form("overto_seqno")) {
    
    //골라담기 상품 리스트 그리기
    $param = array();
    $param["overto_seqno"] = $fb->form("overto_seqno");
    $result = $eventDAO->selectOvertoDetailList($conn, $param);
    $prdt_list = makeOvertoDetailList($result);

    //골라담기 상품 그룹 정보
    $param = array();
    $param["table"] = "overto_event";
    $param["col"] = "name, use_yn, tot_order_price, 
                     sale_rate, cpn_admin_seqno";
    $param["where"]["overto_event_seqno"] = $fb->form("overto_seqno");
    $result = $eventDAO->selectData($conn, $param);

    $param = array();
    //이벤트 이름
    $param["event_name"] = $result->fields["name"];
    $use_yn = $result->fields["use_yn"];

    //사용 여부
    if ($use_yn == "Y") {

        $param["use_y"] = "checked=\"checked\"";
        $param["use_n"] = "";

    } else {

        $param["use_n"] = "checked=\"checked\"";
        $param["use_y"] = "";

    }

    //전체 사용 금액
    $param["tot_order_price"] = $result->fields["tot_order_price"];
    //할인 요율
    $param["sale_rate"] = $result->fields["sale_rate"];
    $sell_site = $result->fields["cpn_admin_seqno"];

    //대표이미지
    $fparam = array();
    $fparam["table"] = "overto_repre_file";
    $fparam["col"] = "origin_file_name, overto_repre_file_seqno";
    $fparam["where"]["overto_event_seqno"] = $fb->form("overto_seqno");

    $f_result = $eventDAO->selectData($conn, $fparam);

    //파일이 있을때
    if ($f_result) {
        $f_param = array();
        $f_param["file_name"] = $f_result->fields["origin_file_name"];
        $f_param["file_seqno"] = $f_result->fields["overto_repre_file_seqno"];

        if ($f_param["file_name"]) {
            //삭제할 함수 이름
            $f_param["event_func"] = "delOvertoRepreFile";
            $file_html = getFileHtml($f_param);
            $param["main_file_html"] = $file_html;
        }
    }

} else {

    $param = array();
    $param["dis_btn"] = "disabled=\"disabled\"";
    $param["use_n"] = "checked=\"checked\"";
    $sell_site = "";

}

if (!$prdt_list) {
    $prdt_list = "<tr><td colspan='6'>검색된 내용이 없습니다.</td></tr>";
}

$param["prdt_list"] = $prdt_list;

//판매채널 콤보박스 셋팅
$param["sell_site"] = $eventDAO->selectSellSite($conn);
//카테고리 대분류 콤보박스 셋팅
$result = $eventDAO->selectFlatCateList($conn);
$arr = [];
$arr["flag"] = "Y";
$arr["def"] = "대분류";
$arr["dvs"] = "cate_name";
$arr["val"] = "sortcode";
$param["cate_top"] = makeSelectOptionHtml($result, $arr);

$html = getOvertoView($param);

echo $html . "♪♥♭" . $sell_site;

$conn->close();
?>
