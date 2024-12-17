<?
class CommonDAO {

    function selectCateName($conn, $param) {
        $query  = "\n SELECT sortcode";
        $query .= "\n   FROM cate";
        $query .= "\n  WHERE cate_name = '%s'";
        if (!empty($param["high_sortcode"])) {
            $query .= "\n    AND high_sortcode = '";
            $query .= $param["high_sortcode"] . "'";
        }
        if (!empty($param["cate_level"])) {
            $query .= "\n    AND cate_level = '";
            $query .= $param["cate_level"] . "'";
        }

        $query  = sprintf($query, $param["cate_name"]);

        return $conn->Execute($query)->fields["sortcode"];
    }

    function selectCateMid($conn, $cate_name) {
        $query  = "\n SELECT high_sortcode";
        $query .= "\n   FROM cate";
        $query .= "\n  WHERE cate_name = '%s'";
        $query .= "\n    AND cate_level = '3'";

        $query  = sprintf($query, $cate_name);

        return $conn->Execute($query)->fields["high_sortcode"];
    }

    function selectCateBot($conn, $sortcode) {
        $query  = "\n SELECT sortcode";
        $query .= "\n   FROM cate";
        $query .= "\n  WHERE high_sortcode = '%s'";

        $query  = sprintf($query, $sortcode);

        return $conn->Execute($query);
    }

    function selectPrdtPrintInfo($conn, $cate_sortcode) {
        $query  = "\n SELECT  print_name";
        $query .= "\n        ,purp_dvs";
        $query .= "\n   FROM  prdt_print_info";
        $query .= "\n  WHERE  cate_sortcode = '%s'";

        $query  = sprintf($query, $cate_sortcode);

        return $conn->Execute($query);
    }

    function selectPrdtPrintSeqno($conn, $param) {
        $query  = "\n SELECT  A.prdt_print_seqno";
        $query .= "\n   FROM  prdt_print AS A";
        $query .= "\n        ,prdt_print_info AS B";
        $query .= "\n  WHERE  A.sort = '%s'";
        $query .= "\n    AND  A.name = '%s'";
        $query .= "\n    AND  A.side_dvs = '%s'";
        $query .= "\n    AND  B.cate_sortcode = '%s'";
        $query .= "\n    AND  A.print_name = B.print_name";
        $query .= "\n    AND  A.purp_dvs = B.purp_dvs";

        $query  = sprintf($query, $param["sort"]
                                , $param["name"]
                                , $param["side_dvs"]
                                , $param["sortcode_m"]);

        return $conn->Execute($query)->fields["prdt_print_seqno"];
    }

    function selectPrdtStanSeqno($conn, $param) {
        $query  = "\n SELECT prdt_stan_seqno";
        $query .= "\n   FROM prdt_stan";
        $query .= "\n  WHERE sort  = '%s'";
        $query .= "\n    AND typ   = '%s'";
        $query .= "\n    AND name  = '%s'";
        $query .= "\n    AND affil = '%s'";

        $query  = sprintf($query, $param["sort"]
                                , $param["typ"]
                                , $param["name"]
                                , $param["affil"]);

        return $conn->Execute($query)->fields["prdt_stan_seqno"];
    }

    function selectExtnlEtprsSeqno($conn, $param) {
        $query  = "\n SELECT extnl_etprs_seqno";
        $query .= "\n   FROM extnl_etprs";
        $query .= "\n  WHERE manu_name = '%s'";
        $query .= "\n    AND cpn_name  = '%s'";
        $query .= "\n    AND pur_prdt  = '%s'";

        $query  = sprintf($query, $param["sort"]
                                , $param["name"]
                                , $param["name"]);

        return $conn->Execute($query)->fields["extnl_etrps_seqno"];
    }

    function selectOrderCommonLastNum($conn) {
        date_default_timezone_set("Asia/Seoul");
        $today = date("Y-m-d");

        $query  = "\n   SELECT order_num";
        $query .= "\n     FROM order_common";
        $query .= "\n    WHERE '%s 00:00:00' <= order_regi_date";
        $query .= "\n      AND order_regi_date <= '%s 23:59:59'";
        $query .= "\n ORDER BY order_common_seqno DESC";
        $query .= "\n    LIMIT 1";

        $query  = sprintf($query, $today, $today);

        $rs = $conn->Execute($query);

        if ($rs->EOF) {
            $last_num = 1;
        } else {
            $last_num = intval(substr($rs->fields["order_num"], 11)) + 1;
        }

        return $last_num;
    }

    function selectOrderCommon($conn, $order_num) {
        $query  = "\n SELECT  group_seqno";
        $query .= "\n        ,order_num";
        $query .= "\n        ,order_state";
        $query .= "\n        ,oper_sys";
        $query .= "\n        ,cust_memo";
        $query .= "\n        ,grade_sale_price";
        $query .= "\n        ,event_price";
        $query .= "\n        ,use_point_price";
        $query .= "\n        ,sell_price";
        $query .= "\n        ,pay_price";
        $query .= "\n        ,order_regi_date";
        $query .= "\n        ,member_seqno";
        $query .= "\n        ,mono_yn";
        $query .= "\n        ,claim_yn";
        $query .= "\n        ,order_detail";
        $query .= "\n        ,title";
        $query .= "\n        ,expec_weight";
        $query .= "\n        ,bun_group";
        $query .= "\n        ,depo_finish_date";
        $query .= "\n        ,cpn_admin_seqno";
        $query .= "\n        ,del_yn";
        $query .= "\n        ,point_use_yn";
        $query .= "\n        ,owncompany_img_num";
        $query .= "\n        ,pay_way";
        $query .= "\n        ,cate_sortcode";
        $query .= "\n        ,opt_use_yn";
        $query .= "\n        ,prdt_basic_info";
        $query .= "\n        ,prdt_add_info";
        $query .= "\n        ,prdt_price_info";
        $query .= "\n        ,bun_yn";
        $query .= "\n        ,prdt_pay_info";
        $query .= "\n        ,add_after_price";
        $query .= "\n        ,add_opt_price";
        $query .= "\n        ,receipt_dvs";
        $query .= "\n        ,order_mng";
        $query .= "\n        ,file_upload_dvs";
        $query .= "\n        ,amt";
        $query .= "\n        ,amt_unit_dvs";
        $query .= "\n        ,count";
        $query .= "\n        ,order_common_seqno";
        $query .= "\n   FROM  order_common";
        $query .= "\n  WHERE  order_num = '%s'";

        $query = sprintf($query, $order_num);

        return $conn->Execute($query)->fields;
    }

    function insertOrderCommon($conn, $param, $last_num) {
        $query  = "\n INSERT INTO order_common (";
        $query .= "\n      group_seqno";
        $query .= "\n     ,order_num";
        $query .= "\n     ,order_state";
        $query .= "\n     ,oper_sys";
        $query .= "\n     ,cust_memo";
        $query .= "\n     ,grade_sale_price";
        $query .= "\n     ,event_price";
        $query .= "\n     ,use_point_price";
        $query .= "\n     ,sell_price";
        $query .= "\n     ,pay_price";
        $query .= "\n     ,order_regi_date";
        $query .= "\n     ,member_seqno";
        $query .= "\n     ,mono_yn";
        $query .= "\n     ,claim_yn";
        $query .= "\n     ,order_detail";
        $query .= "\n     ,title";
        $query .= "\n     ,expec_weight";
        $query .= "\n     ,bun_group";
        $query .= "\n     ,depo_finish_date";
        $query .= "\n     ,cpn_admin_seqno";
        $query .= "\n     ,del_yn";
        $query .= "\n     ,point_use_yn";
        $query .= "\n     ,owncompany_img_num";
        $query .= "\n     ,pay_way";
        $query .= "\n     ,cate_sortcode";
        $query .= "\n     ,opt_use_yn";
        $query .= "\n     ,prdt_basic_info";
        $query .= "\n     ,prdt_add_info";
        $query .= "\n     ,prdt_price_info";
        $query .= "\n     ,bun_yn";
        $query .= "\n     ,prdt_pay_info";
        $query .= "\n     ,add_after_price";
        $query .= "\n     ,add_opt_price";
        $query .= "\n     ,receipt_dvs";
        $query .= "\n     ,order_mng";
        $query .= "\n     ,file_upload_dvs";
        $query .= "\n     ,amt";
        $query .= "\n     ,amt_unit_dvs";
        $query .= "\n     ,count";
        $query .= "\n ) VALUES (";
        $query .= "\n      %s";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'1000'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n )";

        $order_num_form = substr($param["order_num"], 0, -5);
        $order_num = $order_num_form . str_pad($last_num, 5, '0', STR_PAD_LEFT);

        $query = sprintf($query, $param["group_seqno"] == null ? "null" : "'" . $param["group_seqno"] . "'"
                               , $order_num
                               //, $fields["order_state"]
                               , $param["oper_sys"]
                               , $param["cust_memo"]
                               , $param["grade_sale_price"]
                               , $param["event_price"]
                               , $param["use_point_price"]
                               , $param["sell_price"]
                               , $param["pay_price"]
                               , $param["order_regi_date"]
                               , $param["member_seqno"]
                               , $param["mono_yn"]
                               , $param["claim_yn"]
                               , $param["order_detail"]
                               , $param["title"]
                               , $param["expec_weight"]
                               , $param["bun_group"]
                               , $param["depo_finish_date"]
                               , $param["cpn_admin_seqno"]
                               , $param["del_yn"]
                               , $param["point_use_yn"]
                               , $param["owncompany_img_num"]
                               , $param["pay_way"]
                               , $param["cate_sortcode"]
                               , $param["opt_use_yn"]
                               , $param["prdt_basic_info"]
                               , $param["prdt_add_info"]
                               , $param["prdt_price_info"]
                               , $param["bun_yn"]
                               , $param["prdt_pay_info"]
                               , $param["add_after_price"]
                               , $param["add_opt_price"]
                               , $param["receipt_dvs"]
                               , $param["order_mng"]
                               , $param["file_upload_dvs"]
                               , $param["amt"]
                               , $param["amt_unit_dvs"]
                               , $param["count"]);

        return $conn->Execute($query);
    }

    function selectOrderDetail($conn, $order_common_seqno) {
        $query  = "\n SELECT  order_detail_seqno";
        $query .= "\n        ,order_common_seqno";
        $query .= "\n        ,order_detail_dvs_num";
        $query .= "\n        ,state";
        $query .= "\n        ,typ";
        $query .= "\n        ,page_amt";
        $query .= "\n        ,cate_paper_mpcode";
        $query .= "\n        ,spc_dscr";
        $query .= "\n        ,work_size_wid";
        $query .= "\n        ,work_size_vert";
        $query .= "\n        ,cut_size_wid";
        $query .= "\n        ,cut_size_vert";
        $query .= "\n        ,tomson_size_wid";
        $query .= "\n        ,tomson_size_vert";
        $query .= "\n        ,cate_beforeside_print_mpcode";
        $query .= "\n        ,cate_beforeside_add_print_mpcode";
        $query .= "\n        ,cate_aftside_print_mpcode";
        $query .= "\n        ,cate_aftside_add_print_mpcode";
        $query .= "\n        ,print_purp_dvs";
        $query .= "\n        ,sell_price";
        $query .= "\n        ,grade_sale_price";
        $query .= "\n        ,add_after_price";
        $query .= "\n        ,pay_price";
        $query .= "\n        ,del_yn";
        $query .= "\n        ,order_detail";
        $query .= "\n        ,mono_yn";
        $query .= "\n        ,stan_name";
        $query .= "\n        ,amt";
        $query .= "\n        ,count";
        $query .= "\n        ,expec_weight";
        $query .= "\n        ,amt_unit_dvs";
        $query .= "\n        ,after_use_yn";
        $query .= "\n        ,cate_sortcode";
        $query .= "\n        ,tot_tmpt";
        $query .= "\n        ,receipt_mng";
        $query .= "\n        ,print_tmpt_name";
        $query .= "\n        ,prdt_basic_info";
        $query .= "\n        ,prdt_add_info";
        $query .= "\n        ,receipt_memo";
        $query .= "\n        ,receipt_finish_date";
        $query .= "\n        ,side_dvs";
        $query .= "\n   FROM  order_detail";
        $query .= "\n  WHERE  order_common_seqno= '%s'";

        $query = sprintf($query, $order_common_seqno);

        return $conn->Execute($query)->fields;
    }

    function insertOrderDetail($conn, $param, $last_num) {
        $query  = "\n INSERT INTO order_detail (";
        $query .= "\n      order_common_seqno";
        $query .= "\n     ,order_detail_dvs_num";
        $query .= "\n     ,state";
        $query .= "\n     ,typ";
        $query .= "\n     ,page_amt";
        $query .= "\n     ,cate_paper_mpcode";
        $query .= "\n     ,spc_dscr";
        $query .= "\n     ,work_size_wid";
        $query .= "\n     ,work_size_vert";
        $query .= "\n     ,cut_size_wid"; //10
        $query .= "\n     ,cut_size_vert";
        $query .= "\n     ,tomson_size_wid";
        $query .= "\n     ,tomson_size_vert";
        $query .= "\n     ,cate_beforeside_print_mpcode";
        $query .= "\n     ,cate_beforeside_add_print_mpcode";
        $query .= "\n     ,cate_aftside_print_mpcode";
        $query .= "\n     ,cate_aftside_add_print_mpcode";
        $query .= "\n     ,print_purp_dvs";
        $query .= "\n     ,sell_price";
        $query .= "\n     ,grade_sale_price"; //20
        $query .= "\n     ,add_after_price";
        $query .= "\n     ,pay_price";
        $query .= "\n     ,del_yn";
        $query .= "\n     ,order_detail";
        $query .= "\n     ,mono_yn";
        $query .= "\n     ,stan_name";
        $query .= "\n     ,amt";
        $query .= "\n     ,count";
        $query .= "\n     ,expec_weight";
        $query .= "\n     ,amt_unit_dvs"; //30
        $query .= "\n     ,after_use_yn";
        $query .= "\n     ,cate_sortcode";
        $query .= "\n     ,tot_tmpt";
        $query .= "\n     ,receipt_mng";
        $query .= "\n     ,print_tmpt_name";
        $query .= "\n     ,prdt_basic_info";
        $query .= "\n     ,prdt_add_info";
        $query .= "\n     ,receipt_memo";
        $query .= "\n     ,receipt_finish_date";
        $query .= "\n     ,side_dvs"; //40
        $query .= "\n ) VALUES (";
        $query .= "\n      '%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'1000'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'"; //10
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'"; //20
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'"; //30
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'"; //40
        $query .= "\n )";

        $dvs_num_form = substr($param["order_detail_dvs_num"], 0, -7);
        $dvs_num = $dvs_num_form . str_pad($last_num, 5, '0', STR_PAD_LEFT) . "01";

        $query = sprintf($query, $param["order_common_seqno"]
                               , $dvs_num
                               , $param["typ"]
                               , $param["page_amt"]
                               , $param["cate_paper_mpcode"]
                               , $param["spc_dscr"]
                               , $param["work_size_wid"]
                               , $param["work_size_vert"]
                               , $param["cut_size_wid"]
                               , $param["cut_size_vert"]
                               , $param["tomson_size_wid"]
                               , $param["tomson_size_vert"]
                               , $param["cate_beforeside_print_mpcode"]
                               , $param["cate_beforeside_add_print_mpcode"]
                               , $param["cate_aftside_print_mpcode"]
                               , $param["cate_aftside_add_print_mpcode"]
                               , $param["print_purp_dvs"]
                               , $param["sell_price"]
                               , $param["grade_sale_price"]
                               , $param["add_after_price"]
                               , $param["pay_price"]
                               , $param["del_yn"]
                               , $param["order_detail"]
                               , $param["mono_yn"]
                               , $param["stan_name"]
                               , $param["amt"]
                               , $param["count"]
                               , $param["expec_weight"]
                               , $param["amt_unit_dvs"]
                               , $param["after_use_yn"]
                               , $param["cate_sortcode"]
                               , $param["tot_tmpt"]
                               , $param["receipt_mng"]
                               , $param["print_tmpt_name"]
                               , $param["prdt_basic_info"]
                               , $param["prdt_add_info"]
                               , $param["receipt_memo"]
                               , $param["receipt_finish_date"]
                               , $param["side_dvs"]
                               );

        $ret = $conn->Execute($query);

        return array("ret" => $ret,
                     "dvs_num" => $dvs_num);
    }

    function selectOrderDetailCountFile($conn, $order_detail_seqno) {
        $query  = "\n SELECT  order_detail_seqno";
        $query .= "\n        ,order_detail_count_file_seqno";
        $query .= "\n        ,order_detail_file_num";
        $query .= "\n        ,state";
        $query .= "\n        ,file_path";
        $query .= "\n        ,save_file_name";
        $query .= "\n        ,origin_file_name";
        $query .= "\n        ,size";
        $query .= "\n        ,print_file_path";
        $query .= "\n        ,print_file_name";
        $query .= "\n        ,tmp_file_path";
        $query .= "\n        ,tmp_file_name";
        $query .= "\n        ,seq";
        $query .= "\n   FROM  order_detail_count_file";
        $query .= "\n  WHERE  order_detail_seqno= '%s'";

        $query = sprintf($query, $order_detail_seqno);

        return $conn->Execute($query)->fields;
    }

    function selectOrderAfterHistory($conn, $order_detail_dvs_num) {
        $query  = "\n SELECT  order_common_seqno";
        $query .= "\n        ,after_name";
        $query .= "\n        ,depth1";
        $query .= "\n        ,depth2";
        $query .= "\n        ,depth3";
        $query .= "\n        ,price";
        $query .= "\n        ,basic_yn";
        $query .= "\n        ,seq";
        $query .= "\n        ,detail";
        $query .= "\n   FROM  order_after_history";
        $query .= "\n  WHERE  order_detail_dvs_num = '%s'";

        $query = sprintf($query, $order_detail_dvs_num);

        return $conn->Execute($query);
    }

    function insertOrderAfterHistory($conn, $param) {
        $query  = "\n INSERT INTO order_after_history (";
        $query .= "\n      order_common_seqno";
        $query .= "\n     ,order_detail_dvs_num";
        $query .= "\n     ,after_name";
        $query .= "\n     ,depth1";
        $query .= "\n     ,depth2";
        $query .= "\n     ,depth3";
        $query .= "\n     ,price";
        $query .= "\n     ,basic_yn";
        $query .= "\n     ,seq";
        $query .= "\n     ,detail";
        $query .= "\n ) VALUES (";
        $query .= "\n      '%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,%s";
        $query .= "\n     ,'%s'";
        $query .= "\n )";

        $query = sprintf($query, $param["order_common_seqno"]
                               , $param["order_detail_dvs_num"]
                               , $param["after_name"]
                               , $param["depth1"]
                               , $param["depth2"]
                               , $param["depth3"]
                               , $param["price"]
                               , $param["basic_yn"]
                               , $param["seq"] == null ? "null" : "'" . $param["seq"] . "'"
                               , $param["detail"]
                               );

        return $conn->Execute($query);
    }

    function selectOrderOptHistory($conn, $order_common_seqno) {
        $query  = "\n SELECT  order_common_seqno";
        $query .= "\n        ,opt_name";
        $query .= "\n        ,depth1";
        $query .= "\n        ,depth2";
        $query .= "\n        ,depth3";
        $query .= "\n        ,price";
        $query .= "\n        ,basic_yn";
        $query .= "\n        ,detail";
        $query .= "\n   FROM  order_opt_history";
        $query .= "\n  WHERE  order_common_seqno = '%s'";

        $query = sprintf($query, $order_common_seqno);

        return $conn->Execute($query);
    }

    function insertOrderOptHistory($conn, $param) {
        $query  = "\n INSERT INTO order_opt_history (";
        $query .= "\n      order_common_seqno";
        $query .= "\n     ,opt_name";
        $query .= "\n     ,depth1";
        $query .= "\n     ,depth2";
        $query .= "\n     ,depth3";
        $query .= "\n     ,price";
        $query .= "\n     ,basic_yn";
        $query .= "\n     ,detail";
        $query .= "\n ) VALUES (";
        $query .= "\n      '%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n )";

        $query = sprintf($query, $param["order_common_seqno"]
                               , $param["opt_name"]
                               , $param["depth1"]
                               , $param["depth2"]
                               , $param["depth3"]
                               , $param["price"]
                               , $param["basic_yn"]
                               , $param["detail"]
                               );

        return $conn->Execute($query);
    }

    function selectOrderDlvr($conn, $order_common_seqno) {
        $query  = "\n SELECT  tsrs_dvs";
        $query .= "\n        ,name";
        $query .= "\n        ,tel_num";
        $query .= "\n        ,cell_num";
        $query .= "\n        ,addr";
        $query .= "\n        ,zipcode";
        $query .= "\n        ,order_common_seqno";
        $query .= "\n        ,sms_yn";
        $query .= "\n        ,dlvr_way";
        $query .= "\n        ,dlvr_sum_way";
        $query .= "\n        ,dlvr_price";
        $query .= "\n        ,invo_num";
        $query .= "\n        ,invo_cpn";
        $query .= "\n        ,bun_dlvr_order_num";
        $query .= "\n        ,bun_group";
        $query .= "\n   FROM  order_dlvr";
        $query .= "\n  WHERE  order_common_seqno = '%s'";

        $query = sprintf($query, $order_common_seqno);

        return $conn->Execute($query);
    }

    function insertOrderDlvr($conn, $param) {
        $query  = "\n INSERT INTO order_dlvr (";
        $query .= "\n      tsrs_dvs";
        $query .= "\n     ,name";
        $query .= "\n     ,tel_num";
        $query .= "\n     ,cell_num";
        $query .= "\n     ,addr";
        $query .= "\n     ,zipcode";
        $query .= "\n     ,order_common_seqno";
        $query .= "\n     ,sms_yn";
        $query .= "\n     ,dlvr_way";
        $query .= "\n     ,dlvr_sum_way";
        $query .= "\n     ,dlvr_price";
        $query .= "\n     ,invo_num";
        $query .= "\n     ,invo_cpn";
        $query .= "\n     ,bun_dlvr_order_num";
        $query .= "\n     ,bun_group";
        $query .= "\n ) VALUES (";
        $query .= "\n      '%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n )";

        $query = sprintf($query, $param["tsrs_dvs"]
                               , $param["name"]
                               , $param["tel_num"]
                               , $param["cell_num"]
                               , $param["addr"]
                               , $param["zipcode"]
                               , $param["order_common_seqno"]
                               , $param["sms_yn"]
                               , $param["dlvr_way"]
                               , $param["dlvr_sum_way"]
                               , $param["dlvr_price"]
                               , $param["invo_num"]
                               , $param["invo_cpn"]
                               , $param["bun_dlvr_order_num"]
                               , $param["bun_group"]
                               );

        return $conn->Execute($query);
    }

    function selectAmtOrderDetailSheet($conn, $order_detail_count_file_seqno) {
        $query  = "\n SELECT  amt";
        $query .= "\n        ,sheet_typset_seqno";
        $query .= "\n        ,state";
        $query .= "\n   FROM  amt_order_detail_sheet";
        $query .= "\n  WHERE  order_detail_count_file_seqno = '%s'";

        $query = sprintf($query, $order_detail_count_file_seqno);

        return $conn->Execute($query)->fields;
    }

    function insertAmtOrderDetailSheet($conn, $param) {
        $query  = "\n INSERT INTO amt_order_detail_sheet (";
        $query .= "\n      amt";
        $query .= "\n     ,order_detail_count_file_seqno";
        $query .= "\n     ,sheet_typset_seqno";
        $query .= "\n     ,state";
        $query .= "\n ) VALUES (";
        $query .= "\n      '%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,%s";
        $query .= "\n     ,'%s'";
        $query .= "\n )";

        $query = sprintf($query, $param["amt"]
                               , $param["order_detail_count_file_seqno"]
                               , $param["sheet_typset_seqno"] == null ? "null" : "'" . $param["seq"] . "'"
                               , "1000"
                               );

        return $conn->Execute($query);
    }

    function selectOrderFile($conn, $order_common_seqno) {
        $query  = "\n SELECT  member_seqno";
        $query .= "\n        ,dvs";
        $query .= "\n        ,file_path";
        $query .= "\n        ,save_file_name";
        $query .= "\n        ,origin_file_name";
        $query .= "\n        ,size";
        $query .= "\n   FROM  order_file";
        $query .= "\n  WHERE  order_common_seqno = '%s'";

        $query = sprintf($query, $order_common_seqno);

        return $conn->Execute($query)->fields;
    }

    function insertOrderFile($conn, $param) {
        $query  = "\n INSERT INTO order_file (";
        $query .= "\n     member_seqno";
        $query .= "\n    ,dvs";
        $query .= "\n    ,file_path";
        $query .= "\n    ,save_file_name";
        $query .= "\n    ,origin_file_name";
        $query .= "\n    ,size";
        $query .= "\n    ,order_common_seqno";
        $query .= "\n ) VALUES (";
        $query .= "\n      '%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n )";

        $query = sprintf($query, $param["member_seqno"]
                               , $param["dvs"]
                               , $param["file_path"]
                               , $param["save_file_name"]
                               , $param["origin_file_name"]
                               , $param["size"]
                               , $param["order_common_seqno"]
                               );

        return $conn->Execute($query);
    }

    function insertEmplNum($conn, $param) {
        $query  = "\n INSERT INTO gprinting.empl (";
        $query .= "\n      empl_num";   // 사번
        $query .= "\n     ,admin_auth"; // 등급
        $query .= "\n     ,high_depar_code"; // 사업부서
        $query .= "\n     ,depar_code"; // 사업부서
        $query .= "\n     ,belong";     // 소속
        $query .= "\n     ,posi_code";  // 직급
        $query .= "\n     ,job";        // 직책
        $query .= "\n ) VALUES (";
        $query .= "\n      '%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n )";

        $query  = sprintf($query, $param["empl_num"]
                                , $param["admin_auth"]
                                , $param["high_depar_code"]
                                , $param["depar_code"]
                                , $param["belong"]
                                , $param["posi_code"]
                                , $param["job"]
                                );

        return $conn->Execute($query);
    }

    function insertEmplHumanInfo($conn, $param) {
        $query  = "\n INSERT INTO gprinting.empl_human_info (";
        $query .= "\n      enter_date";
        $query .= "\n     ,name";
        $query .= "\n     ,reginum";
        $query .= "\n     ,sex";
        $query .= "\n     ,zipcode";
        $query .= "\n     ,addr";
        $query .= "\n     ,empl_seqno";
        $query .= "\n ) VALUES (";
        $query .= "\n      '%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n )";

        $query  = sprintf($query, $param["enter_date"]
                                , $param["name"]
                                , $param["reginum"]
                                , $param["sex"]
                                , $param["zipcode"]
                                , $param["addr"]
                                , $param["empl_seqno"]
                                );

        return $conn->Execute($query);
    }

    function selectDeparAdmin($conn, $depar_line) {
        $query  = "\n   SELECT  A.depar_code";
        $query .= "\n          ,A.high_depar_code";
        $query .= "\n     FROM  gprinting.depar_admin AS A";
        $query .= "\n    WHERE  A.depar_name = '%s'";

        $query  = sprintf($query, $depar_line);

        $rs = $conn->Execute($query);

        return $rs->fields;
    }

    function selectPosiAdmin($conn, $posi_line) {
        $query  = "\n   SELECT  A.posi_code";
        $query .= "\n     FROM  gprinting.posi_admin AS A";
        $query .= "\n    WHERE  A.posi_name = '%s'";

        $query  = sprintf($query, $posi_line);

        $rs = $conn->Execute($query);

        return $rs->fields;
    }

}
?>
