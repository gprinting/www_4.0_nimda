<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/common_config.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/typset_mng/PrintProduceOrdDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PrintProduceOrdDAO();
$check = 1;

$manu = array();
$manu = $fb->form("selManu");
//$theday = array();
//$theday = $fb->form("theday");
$basic_seoul = array();
$basic_seoul = $fb->form("basic_seoul");
$basic_region = array();
$basic_region = $fb->form("basic_region");

$conn->StartTrans();

$selParam = array();
$selParam["date"] = $fb->form("date");
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

    if ($manu[$i]) {

        $param = array();
        $param["extnl_etprs_seqno"] = $manu[$i];
        //    $param["theday_directions"] = $theday[$i];
        $param["basic_seoul_directions"] = $basic_seoul[$i];
        $param["basic_region_directions"] = $basic_region[$i];
        $param["tot_directions"] = $theday[$i] + $basic_seoul[$i] + $basic_region[$i];

        if (in_array($manu[$i], $manuArr)) {
            $param["print_produce_sch_seqno"] = $seqArr[$manu[$i]];
            $rs = $dao->updateDirection($conn, $param);
        } else {
            $rs = $dao->insertDirection($conn, $param);
        }

        if (!$rs) {
            $check = 0;
            break;
        }
    }
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
