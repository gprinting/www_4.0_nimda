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

$cate_sortcode = $fb->form("cate_sortcode");
$grade = array();
$grade[1] = $fb->form("grade_1");
$grade[2] = $fb->form("grade_2");
$grade[3] = $fb->form("grade_3");
$grade[4] = $fb->form("grade_4");
$grade[5] = $fb->form("grade_5");
$grade[6] = $fb->form("grade_6");
$grade[7] = $fb->form("grade_7");
$grade[8] = $fb->form("grade_8");
$grade[9] = $fb->form("grade_9");
$grade[10] = $fb->form("grade_10");

for ($i = 1; $i <= 10; $i++) {

    $param = array();
    $param["table"] = "grade_sale_price";
    $param["col"] = "grade_sale_price_seqno";
    $param["where"]["cate_sortcode"] = $cate_sortcode;
    $param["where"]["grade"] = $i;

    $grade_rs = $cateListDAO->selectData($conn, $param);
 
    $param = array();
    $param["table"] = "grade_sale_price";
    $param["col"]["cate_sortcode"] = $cate_sortcode;
    $param["col"]["grade"] = $i;
    $param["col"]["rate"] = $grade[$i];

    if ($grade_rs->EOF == 1) {
        $rs = $cateListDAO->insertData($conn, $param);
    } else {
        $param["prk"] = "grade_sale_price_seqno";
        $param["prkVal"] = $grade_rs->fields["grade_sale_price_seqno"];
        $rs = $cateListDAO->updateData($conn, $param);
    }
}

if ($rs === FALSE) {
    echo false;
} else { 
    echo true;
}

$conn->CompleteTrans();
$conn->close();
?>
