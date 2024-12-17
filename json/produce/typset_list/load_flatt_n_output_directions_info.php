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
$param["table"] = "brochure_typset";
$param["col"] = "typset_num";
$param["where"]["brochure_typset_seqno"] = $fb->form("seqno");

$rs = $dao->selectData($conn, $param);

$typset_num = $rs->fields["typset_num"];

$param = array();
$param["typset_num"] = $typset_num;
$rs = $dao->selectOutputDirectionsView($conn, $param);

$ret  = "{";
$ret .= " \"output_name\"  : \"%s\",";
$ret .= " \"affil\"        : \"%s\",";
$ret .= " \"amt\"          : \"%s\",";
$ret .= " \"amt_unit\"     : \"%s\",";
$ret .= " \"manu_name\"    : \"%s\",";
$ret .= " \"typ\"          : \"%s\",";
$ret .= " \"typ_detail\"   : \"%s\",";
$ret .= " \"size\"         : \"%s\",";
$ret .= " \"board\"        : \"%s\",";
$ret .= " \"memo\"         : \"%s\",";
$ret .= " \"output_yn\"    : \"%s\",";
$ret .= " \"brand_seqno\"  : \"%s\",";
$ret .= " \"output_seqno\" : \"%s\"";
$ret .= "}";

$param = array();
$param["table"] = "output";
$param["col"] = "output_seqno";
$param["where"]["search_check"] = $rs->fields["output_name"] . "|" . $rs->fields["board"] . "|" . $rs->fields["size"];

$sel_rs = $dao->selectData($conn, $param);

$param = array();
$param["table"] = "produce_process_flow";
$param["col"] = "output_yn";
$param["where"]["typset_num"] = $typset_num;

$pro_rs = $dao->selectData($conn, $param);

echo sprintf($ret, $rs->fields["output_name"]
        , $rs->fields["affil"]
        , $rs->fields["amt"]
        , $rs->fields["amt_unit"]
        , $rs->fields["manu_name"]
        , $rs->fields["typ"]
        , $rs->fields["typ_detail"]
        , $rs->fields["size"]
        , $rs->fields["board"]
        , $util->convJsonStr($rs->fields["memo"])
        , $pro_rs->fields["output_yn"]
        , $rs->fields["extnl_brand_seqno"]
        , $sel_rs->fields["output_seqno"]);
$conn->close();
?>
