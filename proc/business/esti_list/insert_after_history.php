<?php
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/esti_mng/EstiListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new EstiListDAO();

$fb = $fb->getForm();

$param = [
    "esti_seqno" => $fb["esti_seqno"]
    ,"esti_detail_dvs_num" => $fb["detail_dvs_num"]
    ,"mpcode"     => $fb["mpcode"]
    ,"after_name" => $fb["name"]
    ,"depth1"     => $fb["depth1"]
    ,"depth2"     => $fb["depth2"]
    ,"depth3"     => $fb["depth3"]
];

if (empty($dao->selectDupEstiAfterHistory($conn, $param))) {
    $ret = $dao->insertEstiAfterHistory($conn, $param);

    $conn->Close();

    if ($ret === false) {
        echo "-1";
        exit;
    }

    echo "1";
} else {
    echo "2";
}


