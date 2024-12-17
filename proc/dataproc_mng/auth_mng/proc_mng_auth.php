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

//$conn->debug = 1;

//직원 일련번호
$empl_seqno = $fb->form("select_empl_seqno");

$fb = $fb->getForm();

$param = array();
$param["table"] = "auth_admin_page";

//데이터를 삭제함
$param["prk"] = "empl_seqno";
$param["prkVal"] = $empl_seqno;
$result = $organDAO->deleteData($conn, $param);
if (!$result) $check = 0;


foreach($fb as $key => $val) {

    if ($key != "select_empl_seqno" && $key != "all_chk"){

        $page = explode("-", $key);
        $page_url = "/" . $page[0] . "/" . $page[1] . ".html";
        $auth_yn = $val;

        //관리자 권한 셋팅
        $param = array();
        $param["table"] = "auth_admin_page";
        $param["col"]["page_url"] = $page_url;
        $param["col"]["auth_yn"] = $auth_yn;
        $param["col"]["empl_seqno"] = $empl_seqno;
        $result = $organDAO->insertData($conn, $param);
        if (!$result) $check = 0;

    }

}

echo $check;

$conn->CompleteTrans();
$conn->close();
?>
