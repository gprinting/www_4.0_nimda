<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_price_mng/CalculPriceListDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new CalculPriceListDAO();
$util = new CommonUtil();

$type = $fb->form("type");

$param = array();

$ret = "{";

//$conn->debug = 1;
if ($type === "SORT") {
    /*
     * 종이 분류 셀렉트 박스 변경시
     * 파라미터에 종이 분류 추가
     */

    $html = array();
    
    $param["sort"] = $fb->form("paper_sort");

    $html["name"] =
        $dao->selectPrdtPaperInfoHtml($conn, "NAME", $param);
    $html["dvs"] =
        $dao->selectPrdtPaperInfoHtml($conn, "DVS", $param);
    $html["color"] =
        $dao->selectPrdtPaperInfoHtml($conn, "COLOR", $param);
    $html["basisweight"] =
        $dao->selectPrdtPaperInfoHtml($conn, "BASISWEIGHT", $param);

    $html = $util->convJsonStr($html);

    $ret .= " \"name\"        : \"%s\",";
    $ret .= " \"dvs\"         : \"%s\",";
    $ret .= " \"color\"       : \"%s\",";
    $ret .= " \"basisweight\" : \"%s\"";
    $ret .= "}";

    $ret  = sprintf($ret, $html["name"]
                        , $html["dvs"]
                        , $html["color"]
                        , $html["basisweight"]);
} else if ($type === "NAME") {
    /*
     * 종이명 셀렉트 박스 변경시
     * 파라미터에 분류, 종이명 추가
     */

    $html = array();
    
    $param["sort"] = $fb->form("paper_sort");
    $param["name"] = $fb->form("paper_name");

    $html["dvs"] =
        $dao->selectPrdtPaperInfoHtml($conn, "DVS", $param);
    $html["color"] =
        $dao->selectPrdtPaperInfoHtml($conn, "COLOR", $param);
    $html["basisweight"] =
        $dao->selectPrdtPaperInfoHtml($conn, "BASISWEIGHT", $param);

    $html = $util->convJsonStr($html);

    $ret .= " \"dvs\"         : \"%s\",";
    $ret .= " \"color\"       : \"%s\",";
    $ret .= " \"basisweight\" : \"%s\"";
    $ret .= "}";

    $ret  = sprintf($ret, $html["dvs"]
                        , $html["color"]
                        , $html["basisweight"]);

} else if ($type === "DVS") {
    /*
     * 구분 셀렉트 박스 변경시
     * 파라미터에 분류 종이명, 구분 추가
     */

    $html = array();
    
    $param["name"] = $fb->form("paper_name");
    $param["dvs"]  = $fb->form("paper_dvs");

    $html["color"] =
        $dao->selectPrdtPaperInfoHtml($conn, "COLOR", $param);
    $html["basisweight"] =
        $dao->selectPrdtPaperInfoHtml($conn, "BASISWEIGHT", $param);

    $html = $util->convJsonStr($html);

    $ret .= " \"color\"       : \"%s\",";
    $ret .= " \"basisweight\" : \"%s\"";
    $ret .= "}";

    $ret  = sprintf($ret, $html["color"]
                        , $html["basisweight"]);

} else if ($type === "COLOR") {
    /*
     * 색상 셀렉트 박스 변경시
     * 파라미터에 종이명, 구분, 색상 추가
     */

    $html = array();
    
    $param["name"]  = $fb->form("paper_name");
    $param["dvs"]   = $fb->form("paper_dvs");
    $param["color"] = $fb->form("paper_color");

    $html["basisweight"] =
        $dao->selectPrdtPaperInfoHtml($conn, "BASISWEIGHT", $param);

    $html = $util->convJsonStr($html);

    $ret .= " \"basisweight\" : \"%s\"";
    $ret .= "}";

    $ret  = sprintf($ret, $html["basisweight"]);
}

echo $ret;

$conn->Close();
?>
