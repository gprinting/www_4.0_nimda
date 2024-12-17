<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/mkt/mkt_mng/GradeMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$gradeDAO = new GradeMngDAO();

$conn->StartTrans();
$check = 1;

//등급 산정 정보 저장
$param = array();
$param["table"] = "mon_member_grade_set";

//매달 산정할 월 셋팅
for ($i = 1; $i < 13; $i++) {

    if ($fb->form("mon" . $i)) {

        $param["col"]["m" . $i] = "Y";

    } else {

        $param["col"]["m" . $i] = "N";

    }
}

$param["col"]["day"] = $fb->form("set_day");
$param["col"]["mon_member_grade_set_seqno"] = "1";

$result = $gradeDAO->replaceData($conn, $param);

if (!$result) $check = 0;

$param = array();
$param["table"] = "member_grade_policy";
$param["col"] = "member_grade_policy_seqno";

$m_result = $gradeDAO->selectData($conn, $param);

while ($m_result && !$m_result->EOF) {

    $seq = $m_result->fields["member_grade_policy_seqno"];

    //등급별 정책 정보 저장
    $param = array();
    $param["table"] = "member_grade_policy";
    $param["col"]["grade_name"] = $fb->form("grade_name" . $seq);
    $param["col"]["sales_start_price"] = $fb->form("start_price" . $seq);
    $param["col"]["sales_end_price"] = $fb->form("end_price" . $seq);
    $param["col"]["sales_sale_rate"] = $fb->form("sale_rate" . $seq);
    $param["col"]["sales_give_point"] = $fb->form("give_point" . $seq);
    $param["col"]["grade_dscr"] = $fb->form("dscr" . $seq);

    $param["prk"] = "member_grade_policy_seqno";
    $param["prkVal"] = $fb->form("grade_set" . $seq);

    $result = $gradeDAO->updateData($conn, $param);

    if (!$result) $check = 0;

    $m_result->moveNext();

}

if ($check == 1) {
    
    echo "1";

} else {

    echo "2";
}

$conn->CompleteTrans();
$conn->close();
?>
