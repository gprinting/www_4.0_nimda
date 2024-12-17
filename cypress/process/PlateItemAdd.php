<?
 /***********************************************************************************
 *** 프로 젝트 : CyPress
 *** 개발 영역 : 프리셋
 *** 개  발  자 : 김성진
 *** 개발 날짜 : 2016.09.20
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
$dao = new CypressDAO();
$fb = new FormBean();
$CCFile = new CLS_File;


/***********************************************************************************
 *** 리퀘스트
 ***********************************************************************************/

$param = array();
foreach($fb->fb as $key=>$value)
{
    $param[$key] = $value;
    CLS_File::FileWrite(_WPATH, "\n" . date("H:i:s")." 파라미터 ". $key . " : " . $param[$key] . "\n", "a+");
}

$param['from_state'] = '2120';
$param['to_state'] = '2130';

for($i = 1; $i <= $param['ONS']; $i++) {
    $param['order_detail_file_num'] = $dao->selectOrderDetailFileNum($conn, $param['ON' . $i]);
    $param['typset_num'] = substr($param['PENO'],0, strlen($param['PENO']) - 3);
    $param["idx"] = $i;

    $dao->updateState($conn, $param);
}


/***********************************************************************************
 *** DB 컨넥션 종료
 ***********************************************************************************/

$conn->close();


/***********************************************************************************
 *** 출력
 ***********************************************************************************/

echo "Res=0\n";

?>