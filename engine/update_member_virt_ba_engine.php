#! /usr/local/bin/php -f
<?php

include_once(dirname(__FILE__) . '/common/ConnectionPool.php');
include_once(dirname(__FILE__) . '/dao/EngineDAO.php');
include_once(dirname(__FILE__) . '/common/EngineCommon.php');
include_once(dirname(__FILE__) . '/EngineCommonFunc.php');

// check whether today is the first day of month or not
$today = intval(date('d'));
$date  = date("Y-m-d");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$engineDAO = new EngineDAO();
$util = new EngineCommon();
$commonFunc = new EngineCommonFunc();

$base_path = dirname(__FILE__);

$log_path = "";
$log_str = "[%s] %s %s %s %s %s\r\n";

$fp = null;

// when the first day of the month
// do update and exit
if ($today == '1') {
    
    $chk = 1;

    if (!$util->checkLogDir($base_path, $date)) {
        if ($fp !== NULL) {
            fclose($fp);
        }

        if ($util->makeLogDir($base_path, $date)) {
            $date = explode('-', $date);
            $log_path = sprintf("%s/log/%s/%s/%s/update_virt_ba_engine_log.log", $base_path
                                                                , $date[0]
                                                                , $date[1]
                                                                , $date[2]);
            $fp = fopen($log_path, "w+");

            if (!$fp) goto FATAL_ERR;

            $log = sprintf($log_str, "LOG"
                                   , date("Y-m-d G:i:s")
                                   , "mkdir"
                                   , "SUCCESS"
                                   , ""
                                   , "");
            fwrite($fp, $log);
        } else {
            goto FATAL_ERR;
        }
    } else if (!$fp) {
        $date = explode('-', $date);
        $log_path = sprintf("%s/log/%s/%s/%s/update_virt_ba_engine_log.log", $base_path
                                                            , $date[0]
                                                            , $date[1]
                                                            , $date[2]);
        $fp = fopen($log_path, "w+");

        if (!$fp) goto FATAL_ERR;

            $log = sprintf($log_str, "LOG"
                                   , date("Y-m-d G:i:s")
                                   , "Log File Open"
                                   , "SUCCESS"
                                   , ""
                                   , "");
            fwrite($fp, $log);
    }

    //$conn->debug = 1;

    $query  = "SELECT /* [engine] 가상계좌 변경내역정보 query */ ";  
    $query .= "       A.member_seqno ";  
    $query .= "      ,A.bank_before ";  
    $query .= "      ,A.bank_aft ";  
    $query .= "      ,A.depo_name ";  
    $query .= "  FROM virt_ba_change_history AS A ";  
    $query .= " WHERE A.prog_state = '%s' ";  
    $query .= "   AND A.cancel_yn  = '%s' ";  

    $query  = sprintf($query, '진행중'
                            , 'N');

    $rs     = $conn->Execute($query);

    if ($rs->EOF) {

        goto END;
    }

    while ($rs && !$rs->EOF) {

        $conn->StartTrans();

        $fields = $rs->fields;

        $member_seqno = $fields["member_seqno"];
        $bank_before  = $fields["bank_before"];
        $bank_after   = $fields["bank_aft"];
        $depo_name    = $fields["depo_name"];

        $info_arr = array();
        $info_arr["member_seqno"] = $member_seqno;
        $info_arr["bank_before"]  = $bank_before;
        $info_arr["bank_after"]   = $bank_after;

        // TODO _test need to be erased
        $query  = "UPDATE /* [engine] 구 가상계좌정보 update query */ ";
        $query .= "       virt_ba_admin ";
        //$query .= "       virt_ba_admin "; // TODO need to be activated
        $query .= "   SET member_seqno = NULL ";
        $query .= "      ,depo_name    = NULL ";
        $query .= "      ,use_yn       = 'N' ";
        $query .= " WHERE member_seqno = '%s' ";

        $query  = sprintf($query, $member_seqno);

        $upd_rs = $conn->Execute($query);
        
        if (!$upd_rs) {
            $chk = 0;
            $msg = "기존계좌 초기화실패";
            $info_arr["prog_state"] = "변경실패";

            $log = sprintf($log_str, $member_seqno
                                   , date("Y-m-d G:i:s")
                                   , $bank_before
                                   , $bank_aft
                                   , $chk
                                   , $msg);
            fwrite($fp, $log);

            $conn->FailTrans();
            $conn->RollbackTrans();
            $conn->CompleteTrans();
            updateChanges($conn, $info_arr, $fp, $log, $log_str); 
            $rs->MoveNext();
            continue;
        }

        $query  = "SELECT /* [engine] 회원 가상계좌정보 query */ ";
        $query .= "       A.virt_ba_admin_seqno ";
        $query .= "  FROM virt_ba_admin AS A "; // TODO test need to be erased
        //$query .= "  FROM virt_ba_admin AS A "; // TODO need to be activated
        $query .= " WHERE A.bank_name = '%s' ";
        $query .= "   AND A.use_yn    = '%s' ";
        $query .= " LIMIT 1 ";

        $query  = sprintf($query, $bank_after
                                , 'N');
        
        $sel_rs = $conn->Execute($query);

        $sel_fields = $sel_rs->fields;
        $new_vc_seq = $sel_fields["virt_ba_admin_seqno"];

        if (!$new_vc_seq) {
            $chk = 2;
            $msg = "변경실패(잔여계좌없음)";
            $info_arr["prog_state"] = "변경실패(잔여계좌없음)";

            $log = sprintf($log_str, $member_seqno
                                   , date("Y-m-d G:i:s")
                                   , $bank_before
                                   , $bank_aft
                                   , $chk
                                   , $msg);
            fwrite($fp, $log);        

            $conn->FailTrans();
            $conn->RollbackTrans();
            $conn->CompleteTrans();
            updateChanges($conn, $info_arr, $fp, $log, $log_str); 
            $rs->MoveNext();
            continue;
        }

        // update new virtual account
        $query  = "UPDATE /* [engine] 새 가상계좌정보 update query */ ";
        $query .= "       virt_ba_admin "; // TODO test need to be erased
        //$query .= "       virt_ba_admin "; // TODO this need to be activated
        $query .= "   SET member_seqno = '%s' ";
        $query .= "      ,depo_name    = '%s' ";
        $query .= "      ,use_yn       = '%s' ";
        $query .= " WHERE virt_ba_admin_seqno = '%s' ";

        $query  = sprintf($query, $member_seqno
                                , $depo_name
                                , 'Y'
                                , $new_vc_seq);

        $upd_new_rs = $conn->Execute($query);

        if (!$upd_new_rs) {
            $chk = 3;
            $msg = "신규 계좌 매칭 및 수정 실패";
            $info_arr["prog_state"] = "변경실패";

            $log = sprintf($log_str, $member_seqno
                                   , date("Y-m-d G:i:s")
                                   , $bank_before
                                   , $bank_aft
                                   , $chk
                                   , $msg);
            fwrite($fp, $log);        

            $conn->FailTrans();
            $conn->RollbackTrans();
            $conn->CompleteTrans();
            updateChanges($conn, $info_arr, $fp, $log, $log_str); 
            $rs->MoveNext();
            continue;        
        }

        $info_arr["prog_state"] = "변경성공";
        updateChanges($conn, $info_arr, $fp, $log, $log_str); 

        // 단계 : 
        // 성공 시..
        // 변경체크 > 기존초기화 > 신규활성화 > 변경체크업뎃
        // 실패 시..
        // 롤백 후 (변경체크업뎃) > 로그파일 생성

        $conn->CompleteTrans();
        $rs->MoveNext();

    }

    goto END;

// no update and exit
} else {

    goto END;
}

FATAL_ERR:
    fclose($fp);
    $err_fp = fopen(dirname(__FILE__) . "/[fatal_err]update_member_virt_ba_engine.log", "w+");
    fwrite($err_fp, date("Y-m-d") . "[ERR] Log Dir OR Log File Make Fail");
    fclose($err_fp);

END : 
    $conn->Close();
    exit;

/*************************** function area ***********************/
function updateChanges($conn, $info_arr, $fp, $log, $log_str) {

        $conn->StartTrans();

        // update changes
        $query  = "UPDATE /* [engine] 가상계좌 변경내역정보 update query */ ";
        $query .= "       virt_ba_change_history ";
        $query .= "   SET prog_state = '%s' ";
        $query .= "      ,progday = now() ";
        $query .= " WHERE member_seqno = '%s' ";
        $query .= "   AND prog_state = '%s' ";

        $query  = sprintf($query, $info_arr["prog_state"]
                                , $info_arr["member_seqno"]
                                , '진행중');

        $upd_change_rs = $conn->Execute($query);

        if (!$upd_change_rs) {
            $chk = 4;
            $msg = "변경정보 업데이트 실패";

            $log = sprintf($log_str, $info_arr["member_seqno"]
                                   , date("Y-m-d G:i:s")
                                   , $info_arr["bank_before"]
                                   , $info_arr["bank_after"]
                                   , $chk
                                   , $msg);
            fwrite($fp, $log);        

            $conn->FailTrans();
            $conn->RollbackTrans();
        }

        $conn->CompleteTrans();

};

/************************** function end *************************/
?>
