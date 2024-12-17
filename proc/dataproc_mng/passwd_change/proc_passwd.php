<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/common/sess_common.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/common/NimdaCommonDAO.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new NimdaCommonDAO();
$fb = new FormBean();
$check=1;

$pw = $dao->selectPassword($conn, $fb->form("pw"));

$param = array();
$param["table"] = "empl";
$param["col"] = "passwd";
$param["where"]["empl_seqno"] = $fb->session("empl_seqno");
$result = $dao->selectData($conn, $param);
if (!$result) {
    $check = 0;
}

$pw_hash = $result->fields["passwd"];

//이전 비밀번호와 비교 후 맞을때
if ($pw === $pw_hash) {

    $new_pw = $fb->form("new_pw");
    $new_pw_verify = $fb->form("new_pw_verify");

    //새로운 비밀번호와 비밀번호 확인이 같을때
    if ($new_pw === $new_pw_verify) {

        $new_pw = $dao->parameterEscape($conn, $new_pw);

        $query  = "\n UPDATE empl";
        $query .= "\n    SET passwd = PASSWORD(%s)";
        $query .= "\n  WHERE empl_seqno = '%s'";
        $query  = sprintf($query, $new_pw, $fb->session("empl_seqno"));

        $result = $conn->Execute($query);

        //비밀번호 변경에 실패했을때
        if (!$result) {

            $check = 0;
        }

    //새로운 비밀번호와 비밀번호 확인이 다를때
    } else {

        $check = 3;
    }

//이전 비밀번호와 맞지 않을때
} else {

    $check = 2;

}

echo $check;
?>
