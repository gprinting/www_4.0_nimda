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
$param["seqno"] = $fb->form("seqno");
$rs = $dao->selectBrochureTypsetDirectionsView($conn, $param);

$ret  = "{";
$ret .= " \"typset_num\"          : \"%s\",";
$ret .= " \"typset_name\"         : \"%s\",";
$ret .= " \"depar_name\"          : \"%s\",";
$ret .= " \"empl_name\"           : \"%s\",";
$ret .= " \"wid_size\"            : \"%s\",";
$ret .= " \"vert_size\"           : \"%s\",";
$ret .= " \"affil\"               : \"%s\",";
$ret .= " \"subpaper\"            : \"%s\",";
$ret .= " \"beforeside_tmpt\"     : \"%s\",";
$ret .= " \"beforeside_spc_tmpt\" : \"%s\",";
$ret .= " \"aftside_tmpt\"        : \"%s\",";
$ret .= " \"aftside_spc_tmpt\"    : \"%s\",";
$ret .= " \"honggak_yn\"          : \"%s\",";
$ret .= " \"after_list\"          : \"%s\",";
$ret .= " \"opt_list\"            : \"%s\",";
$ret .= " \"print_amt\"           : \"%s\",";
$ret .= " \"print_amt_unit\"      : \"%s\",";
$ret .= " \"dlvrboard\"           : \"%s\",";
$ret .= " \"memo\"                : \"%s\",";
$ret .= " \"op_typ\"              : \"%s\",";
$ret .= " \"op_typ_detail\"       : \"%s\",";
$ret .= " \"typset_format_seqno\" : \"%s\"";
$ret .= "}";

$param = array();
$param["table"] = "depar_admin";
$param["col"] = "depar_name";
$param["where"]["depar_code"] = $rs->fields["depar_code"];

$sel_rs = $dao->selectData($conn, $param);

echo sprintf($ret, $rs->fields["typset_num"]
        , $rs->fields["typset_name"]
        , $sel_rs->fields["depar_name"]
        , $rs->fields["empl_name"]
        , $rs->fields["wid_size"]
        , $rs->fields["vert_size"]
        , $rs->fields["affil"]
        , $rs->fields["subpaper"]
        , $rs->fields["beforeside_tmpt"]
        , $rs->fields["beforeside_spc_tmpt"]
        , $rs->fields["aftside_tmpt"]
        , $rs->fields["aftside_spc_tmpt"]
        , $rs->fields["honggak_yn"]
        , $rs->fields["after_list"]
        , $rs->fields["opt_list"]
        , $rs->fields["print_amt"]
        , $rs->fields["print_amt_unit"]
        , $rs->fields["dlvrboard"]
        , $util->convJsonStr($rs->fields["memo"])
        , $rs->fields["op_typ"]
        , $rs->fields["op_typ_detail"]
        , $rs->fields["typset_format_seqno"]);

$conn->close();
?>
