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

$dvs = $fb["dvs"];

$name = $fb["name"];
$depth1 = $fb["depth1"];
$depth2 = $fb["depth2"];
$depth3 = $fb["depth3"];
$cate_sortcode = $fb["cs"];

$param = [
     "after_name" => $name
    ,"depth1" => $depth1
    ,"depth2" => $depth2
    ,"depth3" => $depth3
    ,"cate_sortcode" => $cate_sortcode
];

$field = $dvs;
if ($dvs === "depth3") {
    $field .= ", mpcode";
} else {
    $field = "DISTINCT " . $field;
}

$rs = $dao->selectAfterInfo($conn, $param, $field);

$html = '';
while ($rs && !$rs->EOF) {
    $fields = $rs->fields;

    $val = $rs->fields[$dvs];
    $mpcode = $rs->fields["mpcode"];

    if (empty($mpcode)) {
        $mpcode = $val;
    }

    $html .= option($val, $mpcode);

    $rs->MoveNext();
}

echo $html;
