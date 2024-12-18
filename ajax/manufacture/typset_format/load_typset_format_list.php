<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/typset_mng/TypsetFormatDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetFormatDAO();

$commonDAO = $dao;

$el = $fb->form("el");

//한페이지에 출력할 게시물 갯수
$list_num = $fb->form("listSize"); 

//리스트 보여주는 갯수 설정
if (!$fb->form("listSize")) {
    $list_num = 30;
}

//현재 페이지
$page = $fb->form("page");

// 페이지가 없으면 1 페이지
if (!$fb->form("page")) {
    $page = 1; 
}

//블록 갯수
$scrnum = 5; 
$s_num = $list_num * ($page-1);

//정렬
$sorting = $fb->form("sorting");
$sorting_type = $fb->form("sorting_type");

$param = array();
$param["s_num"] = $s_num;
$param["list_num"] = $list_num;
$param["search_txt"] = $fb->form("search_txt");;
$param["sorting"] = $sorting;
$param["sorting_type"] = $sorting_type;
$param["dvs"] = "SEQ";
$param["el"] = $el;

//종이탭
if ($el === "paper") {
    $rs = $dao->selectPaperList($conn, $param);
    $list = makePaperListHtml($rs, $param);

    $param["dvs"] = "COUNT";
    $count_rs = $dao->selectPaperList($conn, $param);

//출력탭
} else if ($el === "output") {

    $et_param = array();
    $et_param["table"] = "basic_produce_paper";
    $et_param["col"] = "extnl_etprs_seqno";
    $et_param["where"]["typset_format_seqno"] = $fb->form("typset_format_seqno");

    $extnl_etprs_seqno = $dao->selectData($conn, $et_param)->fields["extnl_etprs_seqno"];

    if ($extnl_etprs_seqno) {

        $va_param = array();
        $va_param["table"] = "extnl_etprs";
        $va_param["col"] = "pur_prdt";
        $va_param["where"]["extnl_etprs_seqno"] = $extnl_etprs_seqno;

        $pur_prdt = $dao->selectData($conn, $va_param)->fields["pur_prdt"];

        if ($pur_prdt == "출력") {
            $param["extnl_etprs_seqno"] = $extnl_etprs_seqno;
        }
    }

    $rs = $dao->selectOutputList($conn, $param);
    $list = makeOutputListHtml($rs, $param);

    $param["dvs"] = "COUNT";
    $count_rs = $dao->selectOutputList($conn, $param);

//인쇄탭
} else if ($el === "print") {

    $et_param = array();
    $et_param["table"] = "basic_produce_paper";
    $et_param["col"] = "extnl_etprs_seqno";
    $et_param["where"]["typset_format_seqno"] = $fb->form("typset_format_seqno");
$conn->debug = 1;
    $extnl_etprs_seqno = $dao->selectData($conn, $et_param)->fields["extnl_etprs_seqno"];

    if ($extnl_etprs_seqno) {

        $va_param = array();
        $va_param["table"] = "extnl_etprs";
        $va_param["col"] = "pur_prdt";
        $va_param["where"]["extnl_etprs_seqno"] = $extnl_etprs_seqno;

        $pur_prdt = $dao->selectData($conn, $va_param)->fields["pur_prdt"];

        if ($pur_prdt == "인쇄") {
            $param["extnl_etprs_seqno"] = $extnl_etprs_seqno;
        }
    }

    $et_param = array();
    $et_param["table"] = "basic_produce_output";
    $et_param["col"] = "extnl_etprs_seqno";
    $et_param["where"]["typset_format_seqno"] = $fb->form("typset_format_seqno");

    $extnl_etprs_seqno = $dao->selectData($conn, $et_param)->fields["extnl_etprs_seqno"];

    if ($extnl_etprs_seqno) {

        $va_param = array();
        $va_param["table"] = "extnl_etprs";
        $va_param["col"] = "pur_prdt";
        $va_param["where"]["extnl_etprs_seqno"] = $extnl_etprs_seqno;

        $pur_prdt = $dao->selectData($conn, $va_param)->fields["pur_prdt"];

        if ($pur_prdt == "인쇄") {
            $param["extnl_etprs_seqno"] = $extnl_etprs_seqno;
        }
    }

    $rs = $dao->selectPrintList($conn, $param);
    $list = makePrintListHtml($rs, $param);

    $param["dvs"] = "COUNT";
    $count_rs = $dao->selectPrintList($conn, $param);

//후공정탭
} else if ($el === "after") {

    /*
    $et_param = array();
    $et_param["table"] = "basic_produce_paper";
    $et_param["col"] = "extnl_etprs_seqno";
    $et_param["where"]["typset_format_seqno"] = $fb->form("typset_format_seqno");

    $extnl_etprs_seqno = $dao->selectData($conn, $et_param)->fields["extnl_etprs_seqno"];

    if ($extnl_etprs_seqno) {

        $va_param = array();
        $va_param["table"] = "extnl_etprs";
        $va_param["col"] = "pur_prdt";
        $va_param["where"]["extnl_etprs_seqno"] = $extnl_etprs_seqno;

        $pur_prdt = $dao->selectData($conn, $va_param)->fields["pur_prdt"];

        if ($pur_prdt == "후공정") {
            $param["extnl_etprs_seqno"] = $extnl_etprs_seqno;
        }
    }

    $et_param = array();
    $et_param["table"] = "basic_produce_output";
    $et_param["col"] = "extnl_etprs_seqno";
    $et_param["where"]["typset_format_seqno"] = $fb->form("typset_format_seqno");

    $extnl_etprs_seqno = $dao->selectData($conn, $et_param)->fields["extnl_etprs_seqno"];

    if ($extnl_etprs_seqno) {

        $va_param = array();
        $va_param["table"] = "extnl_etprs";
        $va_param["col"] = "pur_prdt";
        $va_param["where"]["extnl_etprs_seqno"] = $extnl_etprs_seqno;

        $pur_prdt = $dao->selectData($conn, $va_param)->fields["pur_prdt"];

        if ($pur_prdt == "후공정") {
            $param["extnl_etprs_seqno"] = $extnl_etprs_seqno;
        }
    }

    $et_param = array();
    $et_param["table"] = "basic_produce_print";
    $et_param["col"] = "extnl_etprs_seqno";
    $et_param["where"]["typset_format_seqno"] = $fb->form("typset_format_seqno");

    $extnl_etprs_seqno = $dao->selectData($conn, $et_param)->fields["extnl_etprs_seqno"];

    if ($extnl_etprs_seqno) {

        $va_param = array();
        $va_param["table"] = "extnl_etprs";
        $va_param["col"] = "pur_prdt";
        $va_param["where"]["extnl_etprs_seqno"] = $extnl_etprs_seqno;

        $pur_prdt = $dao->selectData($conn, $va_param)->fields["pur_prdt"];

        if ($pur_prdt == "후공정") {
            $param["extnl_etprs_seqno"] = $extnl_etprs_seqno;
        }
    }
    */

    $rs = $dao->selectAfterList($conn, $param);
    $list = makeAfterListHtml($rs, $param);

    $param["dvs"] = "COUNT";
    $count_rs = $dao->selectAfterList($conn, $param);
}

$rsCount = $count_rs->fields["cnt"];
$paging = mkDotAjaxPage($rsCount, $page, $scrnum, $list_num, "movePage", $el);

echo $list . "♪" . $paging;
$conn->Close();
?>
