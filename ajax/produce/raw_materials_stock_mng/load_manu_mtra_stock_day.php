<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/produce/raw_materials_stock_mng/RawMaterialStockMngDAO.inc');
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new RawMaterialStockMngDAO();

//한페이지에 출력할 게시물 갯수
$list_num = $fb->form("showPage"); 

//현재 페이지
$page = $fb->form("page");

//리스트 보여주는 갯수 설정
if (!$fb->form("showPage")) {
    $list_num = 30;
}

//블록 갯수
$scrnum = 5; 

// 페이지가 없으면 1 페이지
if (!$page) {
    $page = 1; 
}

$s_num = $list_num * ($page-1);

$param = array();
$param["s_num"] = $s_num;
$param["list_num"] = $list_num;
$param["regi_date"] = $fb->form("date");
$param["name"] = $fb->form("name");
$param["manu"] = $fb->form("manu");

$param["dvs"] = "SEQ";
$rs = $dao->selectMtraStockMngList($conn, $param);
$list = "";
while ($rs && !$rs->EOF) {

    $button = "<button type=\"button\" class=\"green btn_pu btn fix_height20 fix_width40\" onclick=\"searchDetail('%s', '%s', '%s');\">보기</button>";

    $button = sprintf($button, $rs->fields["manu"]
                    , $rs->fields["regi_date"]
                    , $rs->fields["name"]);


    $html = "<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>";

    $html = sprintf($html, $rs->fields["date"]
                     , $rs->fields["manu"]
                     , $rs->fields["name"]
                     , $rs->fields["stor_amt"]
                     , $rs->fields["use_amt"]
                     , $rs->fields["stock_amt"]
                     , $button); 
    $list .= $html;
    
    $rs->moveNext();
}
$param["dvs"] = "COUNT";
$count_rs = $dao->selectMtraStockMngList($conn, $param);
$rsCount = $count_rs->fields["cnt"];

$paging = mkDotAjaxPage($rsCount, $page, $scrnum, $list_num, "movePage");


echo $list . "♪" . $paging;
$conn->close();
?>
