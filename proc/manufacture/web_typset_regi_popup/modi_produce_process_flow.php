<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/typset_mng/TypsetListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetListDAO();
$util = new CommonUtil();
$check = 1;

$typset_num = $fb->form("typset_num");
$dvs = $fb->form("dvs");
$val = $fb->form("val");
$state = "";

$conn->StartTrans();

//상태는 출력준비이지만, 조판지시서리스트는 조판완료
$param = array();
$param["table"] = "produce_process_flow";

if ($dvs == "paper") {
    $param["col"]["paper_yn"] = $val;
} else if ($dvs == "output") {
    $param["col"]["output_yn"] = $val;
    $state = $util->status2statusCode("출력준비");
} else if ($dvs == "print") {
    $param["col"]["print_yn"] = $val;
    $state = $util->status2statusCode("인쇄준비");
}
$param["prk"] = "typset_num";
$param["prkVal"] = $typset_num;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

//기초 데이터 생성
if ($val == "Y") {
 
    $param = array();
    $param["table"] = $dvs . "_op";
    $param["col"] = "COUNT(*) AS cnt";
    $param["where"]["typset_num"] = $typset_num;

    $rs = $dao->selectData($conn, $param);

    if ($rs->fields["cnt"] == 0) {

        $param = array();
        $param["table"] = $dvs . "_op";
        $param["col"]["typset_num"] = $typset_num;
        $param["col"]["state"] = $state;
       
        $rs = $dao->insertData($conn, $param);

        if (!$rs) {
            $check = 0;
        }
    }

//기초 데이터 삭제
} else {
    $param = array();
    $param["table"] = $dvs . "_op";
    $param["prk"] = "typset_num";
    $param["prkVal"] = $typset_num;

    $rs = $dao->deleteData($conn, $param);

    if (!$rs) {
        $check = 0;
    }
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
