<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/common_define/common_info.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdc_prdt_mng/TypsetMngDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetMngDAO();

$affil_arr = AFFIL;
$subpaper_arr = SUBPAPER;
$print_purp_arr = PRINT_PURP;

$param = array();
$param["affil"]      = '';
$param["subpaper"]   = '';
$param["print_purp"] = '';

foreach ($affil_arr as $val) {
    $param["affil"] .= option($val, $val);
}
foreach ($subpaper_arr as $val) {
    $param["subpaper"] .= option($val, $val);
}
foreach ($print_purp_arr as $val) {
    $param["print_purp"] .= option($val, $val);
}

$param["add_yn"] = "Y";
$param["cate_top"] = $dao->selectCateList($conn);
$param["cate_mid"] = "\n<option value=\"\">중분류(전체))</option>";
$param["cate_bot"] = "\n<option value=\"\">소분류(전체)</option>";

echo getPrdcTypsetView($param);
$conn->close();
?>
