<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/calcul_mng/cashbook/CashbookRegiDAO.inc");
$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$check = 1;
$cashbookDAO = new CashbookRegiDAO();
$conn->StartTrans();

//증빙일자
$evid_date = $fb->form("evid_date");
$price = str_replace(',','',$fb->form("price"));

//마감일자 확인
$result = $cashbookDAO->selectCloseDate($conn, $param);
if (!$result) $check = 0;
$close_date = $result->fields["date"];

//마감일자가 증빙일자보다 크면
if ($close_date >= $evid_date) {

    echo "3";
    exit;
}

//지출수입 구분
$dvs = $fb->form("dvs");
//계정이 총매출일때 조정(선입금) 처리
if ($fb->form("acc_subject_detail")) {

    $a_param = array();
    $a_param["acc_detail_seqno"] = $fb->form("acc_subject_detail"); 

    $result = $cashbookDAO->selectAccName($conn, $a_param);
    if (!$result) $check = 0;

    $detail_name = $result->fields["name"];

    //조정테이블과 함께 처리되어야할 계정
    if ($detail_name == "즉시수금" || $detail_name == "선입금환불") {

        $a_param = array();

        //회원 예치금 검색
        $result = $cashbookDAO->selectMemberPrepay($conn, $a_param);
        if (!$result) $check = 0;
        $prepay_price = $result->fields["prepay_price"];

        if ($detail_name == "즉시수금") {

            $a_param = array();
            $a_param["prepay_price"] = $price + $prepay_price;


        } else if ($detail_name == "선입금환불") {

            $a_param = array();
            $a_param["prepay_price"] = $price - $prepay_price;

        }

        /*
        //회원 예치금 수정
        $result = $cashbookDAO->updateMemberPrepay($conn, $a_param);
        if (!$result) $check = 0;
        */
        //조정 테이블 입력
        $a_param = array();
        $a_param["table"] = "adjust";
        $a_param["col"]["cont"] = $fb->form("sumup");
        $a_param["col"]["deal_date"] = $fb->form("evid_date");
        $a_param["col"]["regi_date"] = date("Y-m-d H:i:s", time());
        $a_param["col"]["price"] = $price;
        $a_param["col"]["empl_seqno"] = $_SESSION["empl_seqno"];

        if ($detail_name == "즉시수금") {

            if ($fb->form("path") == "현금") {

                $a_param["col"]["input_dvs"] = "충전";
                $a_param["col"]["input_dvs_detail"] = "방문현금";
                $a_param["col"]["cont"] = "방문 - 현금으로 선입금 충전";

            } else if ($fb->form("path") == "카드") {

                $a_param["col"]["input_dvs"] = "충전";
                $a_param["col"]["input_dvs_detail"] = "방문카드";
                $a_param["col"]["cont"] = "방문 - 카드{" . $fb->form("path_detail");
                $a_param["col"]["cont"] .= "}로 선입금 충전";

            }

        } else if ($detail_name == "선입금환불") {

            $a_param["col"]["input_dvs"] = "차감";
            $a_param["col"]["input_dvs_detail"] = "금액환불";
            $a_param["col"]["cont"] = "선입금 환불 - {" . $fb->form("path_detail");
            $a_param["col"]["cont"] .= "}에서 출금";

        }

        $result = $cashbookDAO->insertData($conn, $a_param);
        if (!$result) $check = 0;

    }
}

//금전출납부
$param = array();
$param["table"] = "cashbook";
//회사 관리 일련번호
$param["col"]["cpn_admin_seqno"] = $fb->form("sell_site");
//이체 지출 구분
$param["col"]["dvs"] = $fb->form("dvs");
//적요
$param["col"]["sumup"] = $fb->form("sumup");
//입출금경로
$param["col"]["depo_withdraw_path"] = $fb->form("path");
//입출금경로상세
$param["col"]["depo_withdraw_path_detail"] = $fb->form("path_detail");
//증빙일자
$param["col"]["evid_date"] = $fb->form("evid_date");
//금액부분 초기화
$param["col"]["income_price"] = NULL;
$param["col"]["expen_price"] = NULL;
$param["col"]["trsf_income_price"] = NULL;
$param["col"]["trsf_expen_price"] = NULL;
//금액
$param["col"][$fb->form("dvs") . "_price"] = $price;

//제조사 일련번호
$param["col"]["extnl_etprs_seqno"] = $fb->form("etprs_seqno");
//카드사
$param["col"]["card_cpn"] = $fb->form("card_cpn");
//카드번호
$param["col"]["card_num"] = $fb->form("card_num");
//할부월수
if ($fb->form("mip_mon") == "") {

    $param["col"]["mip_mon"] = NULL;

} else {

    $param["col"]["mip_mon"] = $fb->form("mip_mon");

}

//승인번호
$param["col"]["aprvl_num"] = $fb->form("aprvl_num");
//승인일수
if ($fb->form("aprvl_date") == "") {

    $param["col"]["aprvl_date"] = NULL;

} else {

    $param["col"]["aprvl_date"] = $fb->form("aprvl_date");
}

//계정 상세 일련번호
if ($fb->form("acc_subject_detail")) {

    $param["col"]["acc_detail_seqno"] = $fb->form("acc_subject_detail");

} else {

    $param["col"]["acc_detail_seqno"] = NULL;

}

if ($fb->form("cashbook_seqno")) {

    $param["prk"] = "cashbook_seqno";
    $param["prkVal"] = $fb->form("cashbook_seqno");

    $result = $cashbookDAO->updateData($conn, $param);
    if (!$result) $check = 0;

} else {

    //등록일자
    $param["col"]["regi_date"] = date("Y-m-d H:i:s", time());

    $result = $cashbookDAO->insertData($conn, $param);
    if (!$result) $check = 0;

}

echo $check;

$conn->CompleteTrans();
$conn->close();
?>
