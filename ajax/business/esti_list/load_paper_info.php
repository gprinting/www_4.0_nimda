<?php
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/esti_mng/EstiListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new EstiListDAO();

$dvs = $fb->form("dvs");

$param = [];
switch($dvs) {
    case "name" :
        $param["sort"] = $fb->form("sort");
        break;
    case "info" :
        $param["sort"] = $fb->form("sort");
        $param["name"] = $fb->form("name");
        break;
}

$rs = $dao->selectPrdtPaper($conn, $param, $dvs);

$html = '';
while ($rs && !$rs->EOF) {
    $fields = $rs->fields;

    switch($dvs) {
        case "name" :
            $html .= option($fields[$dvs], $fields[$dvs]);
            break;
        case "info" :
            $info = sprintf("%s %s %s%s (%s계열)", $fields["dvs"]
                                                 , $fields["color"]
                                                 , $fields["basisweight"]
                                                 , $fields["basisweight_unit"]
                                                 , $fields["affil"]);

            $html .= option($info, $fields["mpcode"]);
            break;
    }

    $rs->MoveNext();
}

echo $html;

$conn->Close();
?>
