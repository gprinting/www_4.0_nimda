<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/common_config.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/mkt/mkt_mng/EventMngDAO.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/file/FileAttachDAO.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_price_mng/PrdtPriceListDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$conn->StartTrans();

$fb = new FormBean();
$eventDAO = new EventMngDAO();
$fileDAO = new FileAttachDAO();
$prdtDAO = new PrdtPriceListDAO();
$check = 1;

$cate_bot = $fb->form("cate_bot");

//종이 맵핑코드
$param = array();
$param["table"] = "cate_paper";
$param["col"] = "mpcode";
$param["where"]["cate_sortcode"] = $cate_bot;
$param["where"]["name"] = $fb->form("paper_name");
$param["where"]["dvs"] = $fb->form("paper_dvs");
$param["where"]["basisweight"] = $fb->form("paper_basisweight");

$paper_rs = $eventDAO->selectData($conn, $param);
$paper_mpcode = $paper_rs->fields["mpcode"];

$param = array();
$param["cate_sortcode"] = $cate_bot;
$param["tmpt"]          = $fb->form("print_tmpt");

//인쇄 맵핑코드
$print_rs = $prdtDAO->selectCatePrintMpcode($conn, $param);
$print_mpcode = $print_rs->fields["mpcode"];

$stan_mpcode = $fb->form("stan_mpcode");

$param = array();
$param["table"] = "nowadays_busy_event";
//이벤트명
$param["col"]["name"] = $fb->form("event_name");
//진열 여부
$param["col"]["dsply_yn"] = $fb->form("dsply_yn");
//판매 사이트 일련번호
$param["col"]["cpn_admin_seqno"] = $fb->form("sell_site");
//카테고리 분류코드
$param["col"]["cate_sortcode"] = $cate_bot;
//수량
$param["col"]["amt"] = $fb->form("amt");
//수량 단위
$param["col"]["amt_unit"] = $fb->form("amt_unit");
//할인 금액
$param["col"]["sale_price"] = $fb->form("sale_price");
//계산 금액
$param["col"]["sum_price"] = $fb->form("sum_price");
//종이 맵핑코드
$param["col"]["cate_paper_mpcode"] = $paper_mpcode;
//인쇄 맵핑코드
$param["col"]["cate_print_mpcode"] = $print_mpcode;
//규격 맵핑코드
$param["col"]["cate_stan_mpcode"] = $stan_mpcode;

//요즘바빠요 이벤트 수정
if ($fb->form("nowadays_seqno")) {

    $param["prk"] = "nowadays_busy_event_seqno";
    $param["prkVal"] = $fb->form("nowadays_seqno");

    $result = $eventDAO->updateData($conn, $param);

    if (!$result) {

        $check = 0;
    }

    if ($fb->form("upload_file")) {

        //요즘바빠요 파일 삭제
        $param = array();
        $param["table"] = "nowadays_busy_file";
        $param["prk"] = "nowadays_busy_event_seqno";
        $param["prkVal"] = $fb->form("nowadays_seqno");

        $result = $eventDAO->deleteData($conn, $param);
        if (!$result) {

            $check = 0;
        }

        //파일 업로드 경로
        $param =  array();
        $param["file_path"] = SITE_DEFAULT_NOWADAYS_FILE;
        $param["tmp_name"] = $_FILES["upload_btn"]["tmp_name"];
        $param["origin_file_name"] = $_FILES["upload_btn"]["name"];

        //파일을 업로드 한 후 저장된 경로를 리턴한다.
        $result= $fileDAO->upLoadFile($param);

        //요즘바빠요 파일 추가
        $param = array();
        $param["table"] = "nowadays_busy_file";
        $param["col"]["nowadays_busy_event_seqno"] = $fb->form("nowadays_seqno");
        //요즘바빠요 파일
        $param["col"]["origin_file_name"] = $_FILES["upload_btn"]["name"];
        $param["col"]["save_file_name"] = $result["save_file_name"];
        $param["col"]["file_path"] = $result["file_path"];

        //요즘바빠요 파일 입력
        $result = $eventDAO->insertData($conn,$param);

        if (!$result) {
            $check = 0;
        }
    }

//요즘바빠요 파일 추가
} else {

    $result = $eventDAO->insertData($conn, $param);
    if (!$result) {
        $check = 0;
    }
    //요즘바빠요 이벤트 일련번호
    $nowadays_seqno = $conn->insert_ID();

    if ($fb->form("upload_file")) {

        //파일 업로드 경로
        $param =  array();
        $param["file_path"] = SITE_DEFAULT_NOWADAYS_FILE;
        $param["tmp_name"] = $_FILES["upload_btn"]["tmp_name"];
        $param["origin_file_name"] = $_FILES["upload_btn"]["name"];

        //파일을 업로드 한 후 저장된 경로를 리턴한다.
        $result= $fileDAO->upLoadFile($param);

        $param = array();
        $param["table"] = "nowadays_busy_file";
        $param["col"]["origin_file_name"] = $_FILES["upload_btn"]["name"];
        $param["col"]["save_file_name"] = $result["save_file_name"];
        $param["col"]["file_path"] = $result["file_path"];
        $param["col"]["nowadays_busy_event_seqno"] = $nowadays_seqno;

        $result = $eventDAO->insertData($conn, $param);

        if (!$result) {

            $check = 0;
        }
    }
}

echo $check;

$conn->CompleteTrans();
$conn->Close();
?>
