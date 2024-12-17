<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/dataproc_mng/organ_mng/OrganMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$conn->StartTrans();

$fb = new FormBean();
$organDAO = new OrganMngDAO();

$check = 1;

//부서명
$depar_name = $fb->form("depar_name");
//상위 부서 코드
$high_depar_code = $fb->form("high_depar_code");
//판매채널
$cpn_admin_seqno = $fb->form("sell_site");

//같은 부서가 있는지 검색
$param = array();
$param["table"] = "depar_admin";
$param["col"] = "depar_code";
$param["where"]["high_depar_code"] = $high_depar_code;
$param["where"]["depar_name"] = $depar_name;
$param["where"]["cpn_admin_seqno"] = $cpn_admin_seqno;

$result = $organDAO->selectData($conn, $param);
if (!$result) $check = 0;
$admin_seqno = $result->fields["depar_code"];
//수정 및 추가 할 데이터 셋팅
$param = array();
$param["table"] = "depar_admin";
$param["col"]["depar_name"] = $depar_name;
$param["col"]["cpn_admin_seqno"] = $cpn_admin_seqno;
$param["col"]["level"] = "2";

//부서 관리 수정
if ($fb->form("depar_code")) {

    //아무것도 수정하지 않고 저장버튼 클릭시
    if ($admin_seqno == $fb->form("depar_code")) {
        echo "1";
        exit;
    //이미 있는 부서일때
    } else if ($result->recordCount() > 0) {
        echo "3";
        exit;
    }

    $param["prk"] = "depar_code";
    $param["prkVal"] = $fb->form("depar_code");

    $result = $organDAO->updateData($conn, $param);
    if (!$result) $check = 0;

//부서 추가
} else {

    //부서 코드 생성
    $c_param = array();
    $c_param["high_depar_code"] = $high_depar_code;
    $c_param["cpn_admin_seqno"] = $cpn_admin_seqno;

    $depar_code = $organDAO->getDeparCode($conn, $c_param);
    $param["col"]["depar_code"] = $depar_code;
    $param["col"]["high_depar_code"] = $high_depar_code;

    //이미 있는 부서일때
    if ($result->recordCount() > 0) {
        echo "3";
        exit;
    }

    $result = $organDAO->insertData($conn, $param);
    if (!$result) $check = 0;

}

echo $check;

$conn->CompleteTrans();
$conn->close();
?>
