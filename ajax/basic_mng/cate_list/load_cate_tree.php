<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/basic_mng/cate_mng/CateListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$cateListDAO = new CateListDAO();

//1level 카테고리 검색
$param = array();
$param["table"] = "cate";
$param["col"] = "cate_name ,sortcode";
$param["where"]["cate_level"] = 1;
$param["group"] = "sortcode";
$param["order"] = "sortcode ASC";

//1level 호출
$rs = $cateListDAO->selectData($conn, $param);

//1level 배열 생성
$one_level = array();

while ($rs && !$rs->EOF) {
    $sortcode = $rs->fields["sortcode"];
    $cate_name = $rs->fields["cate_name"];
    $one_level[$sortcode] = $cate_name;
    $rs->moveNext();
}

//2level 카테고리 검색
$param = array();
$param["table"] = "cate";
$param["col"] = "cate_name ,sortcode ,high_sortcode";
$param["where"]["cate_level"] = 2;
$param["group"] = "sortcode";
$param["order"] = "sortcode ASC";

//2level 호출
$rs = $cateListDAO->selectData($conn, $param);

//2level 배열 생성
$two_level = array();

while ($rs && !$rs->EOF) {
    $high_sortcode = $rs->fields["high_sortcode"];
    $sortcode = $rs->fields["sortcode"];
    $cate_name = $rs->fields["cate_name"];
    $two_level[$high_sortcode][$sortcode] = $cate_name;
    $rs->moveNext();
}

//3level 카테고리 검색
$param = array();
$param["table"] = "cate";
$param["col"] = "cate_name ,sortcode ,high_sortcode";
$param["where"]["cate_level"] = 3;
$param["group"] = "sortcode";
$param["order"] = "sortcode ASC";

//3level 호출
$rs = $cateListDAO->selectData($conn, $param);

//3level 배열 생성
$thr_level = array();

while ($rs && !$rs->EOF) {
    $high_sortcode = $rs->fields["high_sortcode"];
    $sortcode = $rs->fields["sortcode"];
    $cate_name = $rs->fields["cate_name"];
    $thr_level[$high_sortcode][$sortcode] = $cate_name;
    $rs->moveNext();
}

$cateTreeFunc = array();
$cateTreeFunc[1] = "oneLevelTreeClick";
$cateTreeFunc[2] = "twoLevelTreeClick";
$cateTreeFunc[3] = "thrLevelTreeClick";

//카테고리 tab1 트리 생성
$cate_tree =  getCateTopTree($one_level, $two_level, $thr_level, 
                             $cateTreeFunc); 

echo $cate_tree;
$conn->close();
?>
