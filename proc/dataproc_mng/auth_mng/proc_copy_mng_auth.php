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

//직원 A 일련번호
$a_empl_seqno = $fb->form("a_mng");
$b_empl_seqno = $fb->form("b_mng");

$param = array();
$param["table"] = "auth_admin_page";
$param["col"] = "page_url, auth_yn";
$param["where"]["empl_seqno"] = $a_empl_seqno;

$result = $organDAO->selectData($conn, $param);
if (!$result) $check = 0;

while ($result && !$result->EOF) {

    $page_url = $result->fields["page_url"];
    $auth_yn = $result->fields["auth_yn"];


    //권한유무 검사
    $param = array();
    $param["table"] = "auth_admin_page";
    $param["col"] = "auth_admin_page_seqno";
    $param["where"]["page_url"] = $page_url;
    $param["where"]["empl_seqno"] = $b_empl_seqno;

    $b_result = $organDAO->selectData($conn, $param);
    if (!$b_result) $check = 0;
    $cnt = $b_result->recordCount();

    //관리자 권한 셋팅
    $param = array();
    $param["table"] = "auth_admin_page";
    $param["col"]["page_url"] = $page_url;
    $param["col"]["auth_yn"] = $auth_yn;

    //없으면 insert
    if ($cnt == 0) {

        $param["col"]["empl_seqno"] = $b_empl_seqno;
        $q_result = $organDAO->insertData($conn, $param);

    //있으면 update
    } else {

        $param["prk"] = "auth_admin_page_seqno";
        $param["prkVal"] = $b_result->fields["auth_admin_page_seqno"];
        $q_result = $organDAO->updateData($conn, $param);

    }
    if (!$q_result) $check = 0;

    $result->moveNext();
}

echo $check;

$conn->CompleteTrans();
$conn->close();
?>
