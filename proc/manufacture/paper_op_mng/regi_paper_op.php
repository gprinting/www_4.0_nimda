<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/item_mng/PaperOpMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PaperOpMngDAO();
$check = 1;

$paper_op_seqno = $fb->form("paper_op_seqno");
$paper_op_seqnos = explode(",", $paper_op_seqno);

$param = array();
$param["table"] = "paper_op";
$param["col"] = "MAX(op_degree) AS op_degree";
$param["blike"]["op_date"] = date("Y-m-d");

$op_degree = $dao->selectData($conn, $param)->fields["op_degree"];

if (!$op_degree || $op_degree == 0) {
    $op_degree = 1;
} else {
    $op_degree = (int)$op_degree + 1;
}

$conn->StartTrans();

$state_arr = $fb->session("state_arr");
$state = $state_arr["종이발주대기"];
$newState = $state_arr["종이발주완료"];

for ($i=0;$i<count($paper_op_seqnos);$i++) {
     
    $param = array();
    $param["table"] = "paper_op";
    $param["col"] = "state";
    $param["where"]["paper_op_seqno"] = $paper_op_seqnos[$i];

    if ($dao->selectData($conn, $param)->fields["state"] == $state) {
        $param = array();
        $param["table"] = "paper_op";
        $param["col"]["state"] = $newState;
        $param["col"]["op_date"] = date("Y-m-d H:i:s");
        //$param["col"]["orderer"] = $fb->session("name");
        $param["col"]["op_degree"] = $op_degree;
        $param["prk"] = "paper_op_seqno";
        $param["prkVal"] = $paper_op_seqnos[$i]; 
        
        $rs = $dao->updateData($conn, $param);

        if(!$rs) {
            $check = 0;
            break;
        }
    }
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
