<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/TypsetListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetListDAO();
$util = new CommonUtil();

$after_op_seqno = $fb->form("after_op_seqno");

$param = array();
$param["after_op_seqno"] = $after_op_seqno;
$rs = $dao->selectAfterDirectionsView($conn, $param);

$after_tmp = explode("-", $rs->fields["after_name"]);

$ret  = "{";
$ret .= " \"name\"              : \"%s\",";
$ret .= " \"depth1\"            : \"%s\",";
$ret .= " \"depth2\"            : \"%s\",";
$ret .= " \"depth3\"            : \"%s\",";
$ret .= " \"manu_name\"         : \"%s\",";
$ret .= " \"extnl_brand_seqno\" : \"%s\",";
$ret .= " \"amt\"               : \"%s\",";
$ret .= " \"amt_unit\"          : \"%s\",";
$ret .= " \"memo\"              : \"%s\",";
$ret .= " \"op_typ\"            : \"%s\",";
$ret .= " \"op_typ_detail\"     : \"%s\",";
$ret .= " \"after_seqno\"       : \"%s\",";
$ret .= " \"after_op_seqno\"    : \"%s\"";
$ret .= "}";

$search_check = "";
if ($after_tmp[0]) {
    $search_check .= $after_tmp[0] . "|";
    $after_name = $after_tmp[0];
} else {
    $search_check .= "-|";
    $after_name = "-";
}
if ($after_tmp[1]) {
    $search_check .= $after_tmp[1] . "|";
    $depth1 = $after_tmp[1];
} else {
    $search_check .= "-|";
    $depth1 = "-";
}
if ($after_tmp[2]) {
    $search_check .= $after_tmp[2] . "|";
    $depth2 = $after_tmp[2];
} else {
    $search_check .= "-|";
    $depth2 = "-";
}
if ($after_tmp[3]) {
    $search_check .= $after_tmp[3];
    $depth3 = $after_tmp[3];
} else {
    $search_check .= "-";
    $depth3 = "-";
}

$param = array();
$param["table"] = "after";
$param["col"] = "after_seqno";
$param["where"]["search_check"] = $search_check;

$sel_rs = $dao->selectData($conn, $param);

$param = array();
$param["table"] = "after_op_work_file";
$param["col"] = "origin_file_name";
$param["where"]["after_op_seqno"] = $after_op_seqno;

$file_rs = $dao->selectData($conn, $param);

echo sprintf($ret, $after_name
        , $depth1
        , $depth2
        , $depth3
        , $rs->fields["manu_name"]
        , $rs->fields["extnl_brand_seqno"]
        , $rs->fields["amt"]
        , $rs->fields["amt_unit"]
        , $util->convJsonStr($rs->fields["memo"])
        , $rs->fields["op_typ"]
        , $rs->fields["op_typ_detail"]
        , $sel_rs->fields["after_seqno"]
        , $after_op_seqno);

$conn->close();
?>
