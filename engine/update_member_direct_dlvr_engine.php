#! /usr/local/bin/php -f
<?php
/**
 * @file update_member_direct_dlvr_engine.php
 *
 * @brief 월배송 정보를 읽어 월배송을 유지 또는 해지 시키는 엔진이다.
 *
 * @detail 월배송 테이블 검색 -> 회원테이블 비교 -> 유지 또는 해지
 *
 *
 *
 */

//*************** 프로세스 종료시 처리부분
declare(ticks=1);

function termProc() {
    echo "Kill PROCESS\n";
    @unlink(dirname(__FILE__) . "/temp/update_member_direct_dlvr.pid");
    exit;
}
pcntl_signal(SIGINT , "termProc");
pcntl_signal(SIGTERM, "termProc");
//*************** 프로세스 종료시 처리부분

//*************** 프로세스 중복실행 방지부분
if (is_file(dirname(__FILE__) . "/temp/update_member_direct_dlvr.pid") === true) {
    echo "process is running!\r\n";
    exit;
}

// TODO 엔진 수정 끝나면 이부분 주석 해제해야함
$pid_fd = fopen(dirname(__FILE__) . "/temp/update_member_direct_dlvr.pid", 'w');
fwrite($pid_fd, getmypid());
fclose($pid_fd);
//*************** 프로세스 중복실행 방지부분

include_once(dirname(__FILE__) . '/common/ConnectionPool.php');
include_once(dirname(__FILE__) . '/dao/EngineDAO.php');
include_once(dirname(__FILE__) . '/common/EngineCommon.php');
include_once(dirname(__FILE__) . '/common/DateUtil.php');
include_once(dirname(__FILE__) . '/EngineCommonFunc.php');

// 가상계좌 업데이트와 비슷한 방식으로 진행
// direct_dlvr_req에 올라온 정보가 있는데, member와 정보를 비교해서 N 일 경우 Y로 활성화.
// 현재 날짜와 월배송 기간이 필요함. 비교해서 월배송 기간이 종료되었을 경우 N으로 비활성화.
// 이 엔진도 항상 돌고 있어야 한다.

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new EngineDAO();
$util = new EngineCommon();
$DateUtil = new DateUtil();

$base_path = dirname(__FILE__);
$err_line = 0;

//$conn->debug = 1;

$date = date("Y-m-d");

$fd = null;
$log_str = "[%s] %s %s %s\r\n";

if (!$util->checkLogDir($base_path, $date)) {
    if ($fd !== NULL) {
        fclose($fd);
    }

    if ($util->makeLogDir($base_path, $date)) {
        $date = explode('-', $date);
        $log_path = sprintf("%s/log/%s/%s/%s/update_member_direct_dlvr_engine_log.log", $base_path
                                                                        , $date[0]
                                                                        , $date[1]
                                                                        , $date[2]);
        $fd = fopen($log_path, 'a');

        if (!$fd) goto FATAL_ERR;

        $log = sprintf($log_str, "LOG"
                               , date("Y-m-d G:i:s")
                               , "mkdir"
                               , "SUCCESS");
        fwrite($fd, $log);
    } else {
        goto FATAL_ERR;
    }
} else if (!$fd) {
    $date = explode('-', $date);
    $log_path = sprintf("%s/log/%s/%s/%s/update_member_direct_dlvr_engine_log.log", $base_path
                                                                    , $date[0]
                                                                    , $date[1]
                                                                    , $date[2]);
    $fd = fopen($log_path, 'a');

    if (!$fd) goto FATAL_ERR;

        $log = sprintf($log_str, "LOG"
                               , date("Y-m-d G:i:s")
                               , "Log File Open"
                               , "SUCCESS");
        fwrite($fd, $log);
}

$rs = $dao->selectMemberDirectDlvrInfo($conn);
$today = $date[0] ."-". $date[1] ."-". $date[2];
$from  = $date[0] ."-". $date[1] ."-01"; // 해당 월의 첫 날

$fcsv = null;

if ($date[2] > "24") { // 25일에 폴더, csv 생성
    $nimda_path = "/home/sitemgr/nimda";
    $fold_name  = "dlvr_csv";

    if (!$util->checkNewDir($nimda_path, $fold_name, $today)) {
        if ($fcsv !== NULL) {
            fclose($fcsv);
        }
    
        if ($util->makeNewDir($nimda_path, $fold_name, $today)) {
            $csv_path = sprintf("%s/%s/%s/%s/%s/non_extend_list.csv" , $nimda_path
                                                                     , $fold_name
                                                                     , $date[0]
                                                                     , $date[1]
                                                                     , $date[2]); 
            $fcsv = fopen($csv_path, 'a');

            $csv_form = "%s,%s,%s,%s,%s,%s\r\n";
            $csv_head = sprintf($csv_form, iconv("UTF-8", "EUC-KR", "회원명")
                                         , iconv("UTF-8", "EUC-KR", "전화번호")
                                         , iconv("UTF-8", "EUC-KR", "핸드폰번호")
                                         , iconv("UTF-8", "EUC-KR", "마지막 주문일")
                                         , iconv("UTF-8", "EUC-KR", "회원구분")
                                         , iconv("UTF-8", "EUC-KR", "누적매출액"));
            
            fwrite($fcsv, $csv_head);

            if (!$fcsv) goto FATAL_ERR;

        } else {
            goto FATAL_ERR;  
        }
    } else if (!$fcsv) {
        $csv_path = sprintf("%s/%s/%s/%s/%s/non_extend_list.csv" , $nimda_path
                                                                 , $fold_name
                                                                 , $date[0]
                                                                 , $date[1]
                                                                 , $date[2]); 

        $fcsv = fopen($csv_path, 'a');
    }
}

while ($rs && !$rs->EOF) {
    $fields = $rs->fields;

    $member_seqno     = $fields["member_seqno"];
    $member_name      = $fields["member_name"];
    $tel_num          = $fields["tel_num"];
    $cell_num         = $fields["cell_num"];
    $final_order_date = $fields["final_order_date"];
    $member_dvs       = $fields["member_dvs"];
    $direct_dlvr_yn   = $fields["direct_dlvr_yn"];

    $param = array();
    $param["member_seqno"] = $member_seqno;
    $param["from"]         = $from;
    $param["to"]           = $today;

    // 이거
    $member_fields = $dao->selectMemberMonthlySales($conn, $param);

    $net_sales      = intval($member_fields["net"]);
    $card_net_sales = intval($member_fields["card_net"]);

    $sum_net_sales  = $net_sales + $card_net_sales;

    // 순매출이 0이거나 0보다 작으면 넘어감
    if ($sum_net_sales <= 0) {
        unset($param);
        $rs->MoveNext();
        // 이거
        continue;
    }

    $new_end_period = "";
    // 직배 사용 중일 경우(매월 25일 기준)
    if ($direct_dlvr_yn == "Y") {

        $dlvr_rs = $dao->selectMemberDirectDlvr($conn, $param);

        $dlvr_fields  = $dlvr_rs->fields;
        $start_period = $dlvr_fields["start_period"]; 
        $end_period   = $dlvr_fields["end_period"]; 

        // 월배송 자동연장 조건 충족 못함
        if ($sum_net_sales < 330000) {
            if ($date[2] > 24) { // 24로 변경 필요
                $member_arr = [];
                $member_arr[0] = $member_name;
                $member_arr[1] = $tel_num;
                $member_arr[2] = $cell_num;
                $member_arr[3] = $final_order_date;
                $member_arr[4] = $member_dvs;
                $member_arr[5] = $sum_net_sales;

                $num = count($member_arr);

                $csv_body = sprintf($csv_form, iconv("UTF-8", "EUC-KR", $member_arr[0]) 
                                             , iconv("UTF-8", "EUC-KR", $member_arr[1])
                                             , iconv("UTF-8", "EUC-KR", $member_arr[2])
                                             , iconv("UTF-8", "EUC-KR", $member_arr[3])
                                             , iconv("UTF-8", "EUC-KR", $member_arr[4])
                                             , iconv("UTF-8", "EUC-KR", $member_arr[5]));
                fwrite($fcsv, $csv_body);

                $log = sprintf($log_str, $member_seqno
                                       , date("Y-m-d G:i:s")
                                       , $sum_net_sales
                                       , "SUCCESS");
                fwrite($fd, $log);
                unset($member_arr);
            }

        // 월배송 자동연장 조건 충족 함
        } else if ($sum_net_sales >= 330000) {
            $now_year    = $date[0];
            // 이번 월 마지막 날짜 구함
            $tm_last_day = $DateUtil->getLastDay($now_year, $date[1]);

            if ($date[2] == $tm_last_day) {

                // 다음 월 마지막 날짜 구함
                $next_month = intval($date[1]) + 1;
                if ($next_month == "13") {
                    $next_month = 1;
                }
                $last_day       = $DateUtil->getLastDay($now_year, $next_month);
                $new_end_period = $date[0] ."-". $next_month ."-". $last_day;

                $upd_param = [];
                $upd_param["member_seqno"] = $member_seqno;
                $upd_param["end_period"]   = $new_end_period;

                // 기간 업데이트 시켜줌
                $upd_rs = $dao->updateMemberDirectDlvrPeriod($conn, $upd_param);
            
                if (!$upd_rs) {
                    $log = sprintf($log_str, $member_seqno
                                           , date("Y-m-d G:i:s")
                                           , $sum_net_sales . "," . $end_period
                                           , "FAILED");
                    fwrite($fd, $log);
                    unset($upd_param);
                    continue;
                }
                $log = sprintf($log_str, $member_seqno
                                       , date("Y-m-d G:i:s")
                                       , $sum_net_sales . "," . $end_period
                                       , "SUCCESS");
                fwrite($fd, $log);
                unset($upd_param);
            }
            
        }
    } 

    // 오늘 날짜가 시작일 / 종료일 사이일 경우
    // 직배여부가 N으로 되어있으면 Y로 변경시켜준다.
    if (!empty($new_end_period)) {
        $end_period = $new_end_period; 
    }
    
    if ($today >= $start_period && $today <= $end_period) {
        if (!empty($rs)) {
            if ($direct_dlvr_yn == "N" || $direct_dlvr_yn == null) {
                $param["direct_dlvr_yn"] = "Y";
                $update_rs = $dao->updateMemberDirectDlvrYn($conn, $param);
                if (!$update_rs) {
                    $log_str .= "[" . $member_seqno . "] 직배송 업데이트 실패 to-> [Y] ";
                }
            }
        }
    // 윗경우와 반대로, 오늘날짜가 시작일 / 종료일과 연관이
    // 없을 경우, 직배여부를 N으로 변경해준다.
    } else {
        if (!empty($rs)) {
            if ($direct_dlvr_yn == "Y") {
                $param["direct_dlvr_yn"] = "N";
                $update_rs = $dao->updateMemberDirectDlvrYn($conn, $param);
                if (!$update_rs) {
                    $log_str .= "[" . $member_seqno . "] 직배송 업데이트 실패 to-> [N] ";
                }
            }
        }
    }

    unset($param);

    $rs->MoveNext();
}

if ($date[2] > 24) {
    fclose($fcsv);
}

goto END;

FATAL_ERR:
    $err_fd = fopen(dirname(__FILE__) . "/[fatal_err]update_member_direct_dlvr_engine.log", "w+");
    fwrite($err_fd, date("Y-m-d") . "[ERR] Log Dir OR Log File Make Fail");
    fclose($err_fd);

END : 
    fwrite($fd, $log_str);
    fclose($fd);
    $conn->Close();
    exit;



?>
