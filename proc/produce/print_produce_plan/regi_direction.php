<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/common_config.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/produce_plan/PrintProducePlanDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$conn->StartTrans();

$fb = new FormBean();
$dao = new PrintProducePlanDAO();
$check = "등록에 성공하였습니다.";

$manu = array();
$manu = $fb->form("selManu");
$seoul = array();
$seoul = $fb->form("seoul");
$region = array();
$region = $fb->form("region");

$selParam = array();
$selParam["date"] = $fb->form("date_from");
$selRs = $dao->selectDirection($conn, $selParam);
$manuArr = array();
$seqArr = array();

while ($selRs && !$selRs->EOF) {

    // 기존에 등록되어 있는 데이터가 존재하지 않으면 삭제
    if (!in_array($selRs->fields["extnl_etprs_seqno"],$manu)) {
        $param = array();
        $param["print_produce_sch_seqno"] = $selRs->fields["print_produce_sch_seqno"];
        $dao->deleteDirection($conn, $param);
    }
   
    $seqArr[$selRs->fields["extnl_etprs_seqno"]] = $selRs->fields["print_produce_sch_seqno"];
    array_push($manuArr, $selRs->fields["extnl_etprs_seqno"]);
    $selRs->moveNext();
}

//인쇄생산계획 지시등록
for ($i=0; $i<count($manu); $i++) {
    $param = array();
    $param["extnl_etprs_seqno"] = $manu[$i];
    $param["seoul_directions"] = $seoul[$i];
    $param["region_directions"] = $region[$i];
    $param["tot_directions"] = $seoul[$i] + $region[$i];
 
    if (in_array($manu[$i], $manuArr)) {
        $param["print_produce_sch_seqno"] = $seqArr[$manu[$i]];
        $rs = $dao->updateDirection($conn, $param);
    } else {
        $rs = $dao->insertDirection($conn, $param);
    }
    
    if (!$rs) {
        $check = "등록에 실패하였습니다.";
        break;
    }
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
