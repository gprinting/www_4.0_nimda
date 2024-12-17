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
$param["table"] = "sheet_typset";
$param["col"] = "typset_num";
$param["where"]["sheet_typset_seqno"] = $fb->form("seqno");

$rs = $dao->selectData($conn, $param);

$typset_num = $rs->fields["typset_num"];

$param = array();
$param["typset_num"] = $typset_num;
$rs = $dao->selectPrintDirectionsView($conn, $param);

$ret  = "{";
$ret .= " \"print_name\"          : \"%s\",";
$ret .= " \"affil\"               : \"%s\",";
$ret .= " \"amt\"                 : \"%s\",";
$ret .= " \"amt_unit\"            : \"%s\",";
$ret .= " \"manu_name\"           : \"%s\",";
$ret .= " \"typ\"                 : \"%s\",";
$ret .= " \"typ_detail\"          : \"%s\",";
$ret .= " \"size\"                : \"%s\",";
$ret .= " \"beforeside_tmpt\"     : \"%s\",";
$ret .= " \"beforeside_spc_tmpt\" : \"%s\",";
$ret .= " \"aftside_tmpt\"        : \"%s\",";
$ret .= " \"aftside_spc_tmpt\"    : \"%s\",";
$ret .= " \"tot_tmpt\"            : \"%s\",";
$ret .= " \"memo\"                : \"%s\",";
$ret .= " \"print_yn\"            : \"%s\",";
$ret .= " \"brand_seqno\"         : \"%s\",";
$ret .= " \"print_seqno\"         : \"%s\"";
$ret .= "}";

$param = array();
$param["table"] = "print";
$param["col"] = "print_seqno";
$param["where"]["search_check"] = $rs->fields["print_name"] . "|" . 
                                  $rs->fields["affil"] . "|" . 
                                  $rs->fields["wid_size"] . "*" . 
                                  $rs->fields["vert_size"];

$sel_rs = $dao->selectData($conn, $param);

$param = array();
$param["table"] = "produce_process_flow";
$param["col"] = "print_yn";
$param["where"]["typset_num"] = $typset_num;

$pro_rs = $dao->selectData($conn, $param);

echo sprintf($ret, $rs->fields["print_name"]
        , $rs->fields["affil"]
        , $rs->fields["amt"]
        , $rs->fields["amt_unit"]
        , $rs->fields["manu_name"]
        , $rs->fields["typ"]
        , $rs->fields["typ_detail"]
        , $rs->fields["size"]
        , $rs->fields["beforeside_tmpt"]
        , $rs->fields["beforeside_spc_tmpt"]
        , $rs->fields["aftside_tmpt"]
        , $rs->fields["aftside_spc_tmpt"]
        , $rs->fields["tot_tmpt"]
        , $util->convJsonStr($rs->fields["memo"])
        , $pro_rs->fields["print_yn"]
        , $rs->fields["extnl_brand_seqno"]
        , $sel_rs->fields["print_seqno"]);
$conn->close();
?>
