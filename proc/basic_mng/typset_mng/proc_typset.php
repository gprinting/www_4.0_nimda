<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/common_config.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdc_prdt_mng/TypsetMngDAO.inc');
include_once(INC_PATH . "/com/nexmotion/job/nimda/file/FileAttachDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();

$typsetDAO = new TypsetMngDAO();
$fileDAO = new FileAttachDAO();
$check = 1;
$conn->StartTrans();

$param = array();
$param["table"] = "typset_format";
//조판명
$param["col"]["name"] = $fb->form("pop_typset_name");
//계열
$param["col"]["affil"] = $fb->form("affil");
//절수
$param["col"]["subpaper"] = $fb->form("subpaper");
//가로 사이즈
$param["col"]["wid_size"] = $fb->form("wid_size");
//세로 사이즈
$param["col"]["vert_size"] = $fb->form("vert_size");
//설명
$param["col"]["dscr"] = $fb->form("dscr");
//배송판
$param["col"]["dlvrboard"] = $fb->form("dlvrboard");
//용도
$param["col"]["purp"] = $fb->form("purp");
//홍각기여부
$param["col"]["honggak_yn"] = $fb->form("honggak_yn");
//카테고리 분류코드
$param["col"]["cate_sortcode"] = $fb->form("cate_bot");

//조판 추가
if ($fb->form("add_yn") == "Y") {

    //공정여부
    $param["col"]["process_yn"] = "N";

    $result = $typsetDAO->insertData($conn, $param);

    if (!$result) {
        $check = 0;
    }

    //조판 일련번호
    $typset_seqno = $conn->insert_ID();

    }

//조판 수정
} else {

    $param["prk"] = "typset_format_seqno";
    $param["prkVal"] = $fb->form("typset_seqno");

    $result = $typsetDAO->updateData($conn, $param);

}

if ($check == 1) {
    echo "1";

} else {
    echo "2";
}

$conn->CompleteTrans();
$conn->close();
?>

