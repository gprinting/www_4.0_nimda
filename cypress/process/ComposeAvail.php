<?
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-07-04
 * Time: 오전 9:44
 */

 /***********************************************************************************
 *** 기본 인클루드
 ***********************************************************************************/
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/cypress_init.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/dprinting/CypressDAO.inc');
include_once(INC_PATH . "/common_lib/cypress_com.inc");
include_once(INC_PATH . "/common_lib/cypress_file.inc");

/***********************************************************************************
 *** 클래스 선언
 ***********************************************************************************/

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$fb = new FormBean();
$dao = new CypressDAO();
$logpath = _WPATH . "_ComposeAvail";

/***********************************************************************************
*** 리퀘스트
***********************************************************************************/

$param = array();
foreach($fb->fb as $key=>$value)
{
    $param[$key] = $value;
    CLS_File::FileWrite($logpath, "\n" . date("H:i:s")." 파라미터 ". $key . " : " . $param[$key] . "\n", "a+");
}


/***********************************************************************************
*** 결과
***********************************************************************************/

$isAvailable = $dao->isComposeAvail($conn, $param);

/***********************************************************************************
*** DB 컨넥션 종료
***********************************************************************************/

$conn->close();


/***********************************************************************************
*** 출력
***********************************************************************************/

if($isAvailable) {
    echo "Res=0\n";
} else {
    echo "Res=12&조판불가능한 상품이 있습니다.";
}
?>