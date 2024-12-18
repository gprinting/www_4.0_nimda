<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/dataproc_mng/bulletin_mng/BulletinMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$bulletinDAO = new BulletinMngDAO();

//팝업 일련번호
$popup_seqno = $fb->form("popup_seq");

//팝업 설정
$param = array();
$param["popup_seqno"] =  $popup_seqno;
$result = $bulletinDAO->selectPopupList($conn, $param);

//html data 셋팅
$param = array();
//팝업 제목
$param["name"] = $result->fields["name"];
//게시 시작 날짜
$param["start_date"] = $result->fields["post_start_date"];
//게시 종료 날짜
$param["end_date"] = $result->fields["post_end_date"];

//시작시간
if ($result->fields["start_hour"]) {

    $start_hour = substr($result->fields["start_hour"], 0, 2);
    $start_min = substr($result->fields["start_hour"], 3, 2);

} else {

    $start_hour = "00";
    $start_min = "00";

}

//종료시간
if ($result->fields["end_hour"]) {

    $end_hour = substr($result->fields["end_hour"], 0, 2);
    $end_min = substr($result->fields["end_hour"], 3, 2);

} else {

    $end_hour = "23";
    $end_min = "59";

}

//시간 콤보박스 셋팅
$param["hour_list"] = makeOptionTimeHtml(0,23);
//분 콤보박스 셋팅
$param["min_list"] = makeOptionTimeHtml(0,59);

//팝업 가로 사이즈
$param["wid_size"] = $result->fields["wid_size"];
//팝업 세로 사이즈
$param["vert_size"] = $result->fields["vert_size"];

//파일 이름
$param["file_name"] = $result->fields["origin_file_name"];
//파일이 있으면 삭제버튼 숨김
if (!$param["file_name"]) $param["hide_btn"] = "style=display:none";
//파일 html
$param["file_html"] = $file_html;

//Link URL
$param["url_addr"] = $result->fields["url_addr"];

//사용 여부
$param["use_y"] = "";
$param["use_n"] = "";
if ($result->fields["use_yn"] == "Y") {

    $param["use_y"] = "checked=\"checked\"";

} else {

    $param["use_n"] = "checked=\"checked\"";

}

//타겟 여부
$param["target_y"] = "";
$param["target_n"] = "";
if ($result->fields["target_yn"]) {

    if ($result->fields["target_yn"] == "Y") {

        $param["target_y"] = "selected=\"selected\"";

    } else {

        $param["target_n"] = "selected=\"selected\"";

    }
}
//팝업 관리 일련번호
$param["popup_seqno"] = $popup_seqno;

$html = getPopupSetHtml($param);

$select_box_val = $start_hour . "♪♡♭" . $start_min . "♪♡♭" . 
                  $end_hour . "♪♡♭" .  $end_min;

echo $html . "♪♥♭" . $select_box_val;

$conn->close();
?>
