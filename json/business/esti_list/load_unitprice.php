<?php
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/esti_mng/EstiListDAO.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new EstiListDAO();
$util = new CommonUtil();

$session = $fb->getSession();
$fb = $fb->getForm();

$param_typ = $fb["typ"];
$dvs = $fb["dvs"];
$typ_arr = ["cover", "inner1", "inner2","inner3"];

$ret = [];
foreach ($typ_arr as $typ) {
    $mpcode_arr = $fb[$typ];

    if (!empty($mpcode_arr)) {
        $paper_mpcode = $mpcode_arr["paper"];
        $output_mpcode = $mpcode_arr["output"];
        $print_mpcode = $mpcode_arr["print"];

        $print_r_count = $mpcode_arr["print_r_count"];

        $param = [
            "prdt_paper_mpcode" => $paper_mpcode
            ,"prdt_output_info_mpcode" => $output_mpcode
            ,"prdt_print_info_mpcode" => $print_mpcode
            ,"amt" => $print_r_count
        ];

        $paper_price  = $dao->selectPrdtPaperPrice($conn, $param);
        $output_price = $dao->selectPrdtOutputPrice($conn, $param);
        $print_price  = $dao->selectPrdtPrintPrice($conn, $param);

        $ret = [];

        switch($param_typ) {
            case "all" :
                $ret[$typ] = [
                    "paper" => $paper_price
                    ,"output" => $output_price
                    ,"print" => $print_price
                ];

                break;
            default :
                $ret[$typ] = [
                    $dvs => ${$dvs . "_price"}
                ];
        }
    }
}

echo json_encode($ret);
