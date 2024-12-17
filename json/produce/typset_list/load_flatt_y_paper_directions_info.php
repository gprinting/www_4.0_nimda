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

$param = array();
$param["paper_op_seqno"] = $fb->form("paper_op_seqno");
$rs = $dao->selectPaperDirectionsView($conn, $param);

$ret  = "{";
$ret .= " \"paper_op_seqno\"    : \"%s\",";
$ret .= " \"name\"              : \"%s\",";
$ret .= " \"dvs\"               : \"%s\",";
$ret .= " \"color\"             : \"%s\",";
$ret .= " \"basisweight\"       : \"%s\",";
$ret .= " \"manu_name\"         : \"%s\",";
$ret .= " \"op_affil\"          : \"%s\",";
$ret .= " \"op_wid_size\"       : \"%s\",";
$ret .= " \"op_vert_size\"      : \"%s\",";
$ret .= " \"stor_subpaper\"     : \"%s\",";
$ret .= " \"stor_wid_size\"     : \"%s\",";
$ret .= " \"stor_vert_size\"    : \"%s\",";
$ret .= " \"grain\"             : \"%s\",";
$ret .= " \"amt\"               : \"%s\",";
$ret .= " \"amt_unit\"          : \"%s\",";
$ret .= " \"memo\"              : \"%s\",";
$ret .= " \"typ\"               : \"%s\",";
$ret .= " \"typ_detail\"        : \"%s\",";
$ret .= " \"extnl_brand_seqno\" : \"%s\",";
$ret .= " \"paper_seqno\"       : \"%s\"";
$ret .= "}";

$param = array();
$param["table"] = "paper";
$param["col"] = "paper_seqno";
$param["where"]["search_check"] = $rs->fields["name"] . "|" . $rs->fields["dvs"] . "|" . $rs->fields["color"] . "|" . $rs->fields["basisweight"];
$param["where"]["extnl_brand_seqno"] = $rs->fields["extnl_brand_seqno"];

$sel_rs = $dao->selectData($conn, $param);

$op_size = explode("*", $rs->fields["op_size"]);
$stor_size = explode("*", $rs->fields["stor_size"]);

echo sprintf($ret, $rs->fields["paper_op_seqno"]
        , $rs->fields["name"]
        , $rs->fields["dvs"]
        , $rs->fields["color"]
        , $rs->fields["basisweight"]
        , $rs->fields["manu_name"]
        , $rs->fields["op_affil"]
        , $op_size[0]
        , $op_size[1]
        , $rs->fields["stor_subpaper"]
        , $stor_size[0]
        , $stor_size[1]
        , $rs->fields["grain"]
        , $rs->fields["amt"]
        , $rs->fields["amt_unit"]
        , $util->convJsonStr($rs->fields["memo"])
        , $rs->fields["typ"]
        , $rs->fields["typ_detail"]
        , $rs->fields["extnl_brand_seqno"]
        , $sel_rs->fields["paper_seqno"]);
$conn->close();
?>
