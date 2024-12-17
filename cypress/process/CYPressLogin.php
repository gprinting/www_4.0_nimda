<?
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-07-04
 * Time: 오전 10:07
 */

 /***********************************************************************************
 *** 기본 인클루드
 ***********************************************************************************/
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/cypress_init.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/common_lib/cypress_com.inc");
include_once(INC_PATH . "/common_lib/cypress_file.inc");
include_once(INC_PATH . '/com/dprinting/CypressDAO.inc');

/***********************************************************************************
 *** 클래스 선언
 ***********************************************************************************/

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$fb = new FormBean();
$dao = new CypressDAO();

$logpath = _WPATH . "_CYPressLogin";

 /***********************************************************************************
 *** 리퀘스트
 ***********************************************************************************/
//id, passwd
$param = array();
foreach($fb->fb as $key=>$value)
{
    $param[$key] = $value;
    CLS_File::FileWrite($logpath, "\n" . date("H:i:s")." 파라미터 ". $key . " : " . $param[$key] . "\n", "a+");
}

 /***********************************************************************************
 *** 회원 로그인 정보 체크
 ***********************************************************************************/

$rs = $dao->checkLoginInfo($conn, $param);

if($rs != false) {
    $nRes = "0";
    $nName = $rs;
} else {
    $nRes = "12&아이디와 비밀번호를 확인해주세요.";
}

/***********************************************************************************
 *** DB 컨넥션 종료
 ***********************************************************************************/



$conn->close();


/***********************************************************************************
*** 출력
***********************************************************************************/

echo "Res=".$nRes."\n";

if ($nRes == "0") {
	echo "[Account]\n";
	echo "Name=".$nName."\n";
}

?>
