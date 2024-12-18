<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdc_prdt_mng/TypsetMngDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetMngDAO();

//조판 일련번호
$typset_seqno = $fb->form("typset_seqno");

$param = array();
$param["table"] = "typset_format";
$param["col"] = "affil, subpaper, wid_size, paper, worker_id,
                 vert_size, dscr, preset_name, preset_cate,
                 purp, regi_date, honggak_yn";
$param["where"]["typset_format_seqno"] = $typset_seqno;

$result = $dao->selectData($conn, $param);

$param = array();
$honggak_yn = $result->fields["honggak_yn"];
if ($honggak_yn == "Y") {
    $param["honggak_y"] = "checked";
    $param["honggak_n"] = "";
} else {
    $param["honggak_y"] = "";
    $param["honggak_n"] = "checked";
}
$param["preset_name"] = $result->fields["preset_name"];
$param["regi_date"] = $result->fields["regi_date"];
$param["preset_cate"] = $result->fields["preset_cate"];
$param["paper"] = $result->fields["paper"];
$param["worker_id"] = $result->fields["worker_id"];
$param["affil"] = $result->fields["affil"];
$param["subpaper"] = $result->fields["subpaper"];
$param["wid_size"] = $result->fields["wid_size"];
$param["vert_size"] = $result->fields["vert_size"];
$param["dscr"] = $result->fields["dscr"];
$param["cate_sortcode"] = $result->fields["cate_sortcode"];
$param["purp"] = $result->fields["purp"];

$html = getPrdcTypsetView($param);

echo $html;
$conn->close();
?>
