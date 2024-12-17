<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/TypsetListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetListDAO();
$check = 1;

$conn->StartTrans();

$paper_op_seqno = $fb->form("paper_op_seqno");
$paper_op_seqnos = explode("&", $paper_op_seqno);

for ($i=0;$i<count($paper_op_seqnos);$i++) {
    $tmp = explode("=", $paper_op_seqnos[$i]);
    
    if ($dao->selectOpState($conn, $tmp[1]) != "520") {
        $param = array();
        $param["paper_op_seqno"] = $tmp[1]; 
        
        $rs = $dao->updateOpState($conn, $param);

        if (!$rs) {
            $check = 0;
        }
    }
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
