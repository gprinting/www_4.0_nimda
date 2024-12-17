<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/basic_mng/cate_mng/CateListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$cateListDAO = new CateListDAO();

$conn->StartTrans();

$cate_name = $fb->form("cate_name");
$cate_level = $fb->form("cate_level");
$high_sortcode = $fb->form("high_sortcode");

$param = array();
$param["table"] = "cate";
$param["col"] = "cate_seqno";
$param["where"]["cate_name"] = $cate_name;
$param["where"]["cate_level"] = $cate_level;
$param["where"]["high_sortcode"] = $high_sortcode;

$cate_rs = $cateListDAO->selectData($conn, $param);

//중복된 값이 없으면 
if ($cate_rs->EOF == 1) {

    $param = array();

    //카테고리 대분류일때
    if ($cate_level == 1) {

        $param["tot_name"] = $cate_name;

        //카테고리 중분류일때
    } else if ($cate_level == 2) {

        $param["high_sortcode"] = $high_sortcode;

        //카테고리 대분류 이름을 가져옴
        $name_sort = $cateListDAO->selectCateName($conn, $param);
        $name_sort = explode("♪", $name_sort);

        $param["tot_name"] = $name_sort[0] . "|" . $cate_name;

        //카테고리 소분류일때
    } else if ($cate_level == 3) {

        $param["high_sortcode"] = $high_sortcode;

        //카테고리 중분류 이름을 가져옴
        $name_sort = $cateListDAO->selectCateName($conn, $param);
        $name_sort = explode("♪", $name_sort);
        $mid_name = $name_sort[0];

        $param = array();
        $param["high_sortcode"] = $name_sort[1];

        //카테고리 소분류 이름을 가져옴
        $name_sort = $cateListDAO->selectCateName($conn, $param);
        $name_sort = explode("♪", $name_sort);

        $param["tot_name"] = $name_sort[0] . "|" . $mid_name . "|" . $cate_name;
    }

    $param["cate_level"] = $cate_level;
    $param["cate_name"] = $cate_name;
    $param["high_sortcode"] = $high_sortcode; 
    $param["flattyp_yn"] = "Y";

    //카테고리 Insert
    $rs = $cateListDAO->insertCate($conn, $param); 
}

if ($rs === FALSE) {
    echo false;
} else { 
    echo true;
}

$conn->CompleteTrans();
$conn->close();
?>
