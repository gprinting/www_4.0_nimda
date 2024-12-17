#! /usr/local/bin/php
<?php
/**
 * @file day_sales_stats_engine.php
 *
 * @brief 일별 집계 엔진
 * @comment member_pay_history의 데이터를 가져와 싹 더함으로 일별 집계를 잡는다
 *          집계 일시는 오전 1시이다.
 */
//*************** 프로세스 종료시 처리부분
declare(ticks=1);

function termProc() {
    echo "Kill PROCESS\n";
    @unlink(dirname(__FILE__) . "/temp/day_sales_stats_engine.pid");
    exit;
}
pcntl_signal(SIGINT , "termProc");
pcntl_signal(SIGTERM, "termProc");
//*************** 프로세스 종료시 처리부분

//*************** 프로세스 중복실행 방지부분
if (is_file(dirname(__FILE__) . "/temp/day_sales_stats_engine.pid") === true) {
    echo "process is running!\r\n";
    exit;
}

// TODO 엔진 수정 끝나면 이부분 주석 해제해야함
$pid_fd = fopen(dirname(__FILE__) . "/temp/day_sales_stats_engine.pid", 'w');
fwrite($pid_fd, getmypid());
fclose($pid_fd);
//*************** 프로세스 중복실행 방지부분

include_once(dirname(__FILE__) . '/common/ConnectionPool.php');
include_once(dirname(__FILE__) . '/dao/EngineDAO.php');
include_once(dirname(__FILE__) . '/common/EngineCommon.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$util = new EngineCommon();
$dao  = new EngineDAO();

//$conn->debug = 1;

$now_date = date('Y-m-d'); // 현재일

$date = $now_date; // 로그용 날짜

$fd = null;
$log_str = '';

$base_path = dirname(__FILE__);
// 로그 생성
if (!$util->checkLogDir($base_path, $date)) {
    if ($fd !== NULL) {
        fclose($fd);
    }

    if ($util->makeLogDir($base_path, $date)) {
        $date = explode('-', $date);
        $log_path = sprintf("%s/log/%s/%s/%s/day_sales_stats_engine_log.log", $base_path
                                                                        , $date[0]
                                                                        , $date[1]
                                                                        , $date[2]);
        $fd = fopen($log_path, 'a');
        chown($log_path, "sitemgr");
        chgrp($log_path, "dpgrp");

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
    $log_path = sprintf("%s/log/%s/%s/%s/day_sales_stats_engine_log.log", $base_path
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

$date_exp = explode('-', $now_date);
$prev_day = intval($date_exp[2]) - 1;

if ($prev_day < 10) {
    $prev_day = "0" . $prev_day;
}

// #1. 이전일(진행일시가 현재일 오전 1시이므로 이전날짜 주문을 집계함)지정,  여기선 시간 계산은 따로 하지 않음
$prev_date = $date_exp[0] . "-" . $date_exp[1] . "-" . $prev_day;

// #2. 전일 데이터 검색 : member_seqno
$param = [];
$param["deal_date"] = $prev_date;
$rs = $dao->selectPayHistoryMemberSeqno($conn, $param);

if (!$rs || $rs->EOF) {
    echo "NO DATA!";
    $log_str .= "[NO_DATA]";
}

while ($rs && !$rs->EOF) {

    // #2-1. 검색한 member_seqno를 가지고 해당 일련번호 지불내역 불러옴
    $sub_arr = [];
    $sub_arr["member_seqno"] = $rs->fields["member_seqno"];
    $sub_arr["deal_date"]    = $prev_date;

    $sub_rs = $dao->selectPayHistoryByMemberSeqno($conn, $sub_arr);

    // #2-2. 가져온 결과값들끼리 합산
    $sum_sell_price      = 0;
    $sum_sale_price      = 0;
    $sum_pay_price       = 0;
    $sum_card_pay_price  = 0;
    $sum_depo_price      = 0;
    $sum_card_depo_price = 0;

    $cnt = 0;
    while ($sub_rs && !$sub_rs->EOF) {
        $fields = $sub_rs->fields;
        $sum_sell_price      += intval($fields["sell_price"]);
        $sum_sale_price      += intval($fields["sale_price"]);
        $sum_pay_price       += intval($fields["pay_price"]);
        $sum_card_pay_price  += intval($fields["card_pay_price"]);
        $sum_depo_price      += intval($fields["depo_price"]);
        $sum_card_depo_price += intval($fields["card_depo_price"]);

        // #2-3. 합산한 결과물로 day_sales_stats에 입력
        if ($sum_sell_price == 0 && $sum_sale_price == 0 &&
                $sum_pay_price == 0 && $sum_card_pay_price == 0 &&
                $sum_depo_price == 0 && $sum_card_depo_price == 0) {
            // 이날은 데이터가 없는 날임
            echo "NO DATA!";
            $log_str .= "[NO DATA]";
        } else {
            $sum_arr = [];
            $sum_arr["sales_price"]          = $sum_sell_price;
            $sum_arr["sale_price"]           = $sum_sale_price;
            $sum_arr["net_sales_price"]      = $sum_pay_price;
            $sum_arr["card_net_sales_price"] = $sum_card_pay_price;
            $sum_arr["depo_price"]           = $sum_depo_price;
            $sum_arr["card_depo_price"]      = $sum_card_depo_price;
            $sum_arr["member_seqno"]         = $sub_arr["member_seqno"];
            $sum_arr["input_date"]           = $prev_date;
            $ins_sum_rs = $dao->updateDaySalesStats($conn, $sum_arr);

            $log_str .= "[" . $sub_arr["member_seqno"] . "]" .  $sum_sell_price . "|";
            $log_str .= $sum_sale_price . "|" . $sum_pay_price . "|";
            $log_str .= $sum_card_pay_price . "|" . $sum_depo_price . "|";
            $log_str .= $sum_card_depo_price . "|" . $sub_arr["member_seqno"] . "|";
            $log_str .= $prev_date . "|\n";

            unset($sum_arr);
        }
        $sub_rs->MoveNext();

    }

    unset($sub_arr);

    $rs->MoveNext();
}

echo $log_str;
// #3. 루프가 끝나면 종료
goto END;

FATAL_ERR:
    $err_fd = fopen(dirname(__FILE__) . "/[fatal_err]day_sales_stats_engine.log", "w+");
    fwrite($err_fd, date("Y-m-d") . "[ERR] Log Dir OR Log File Make Fail");
    fclose($err_fd);

END : 
    fwrite($fd, $log_str);
    fclose($fd);
    $conn->Close();
    exit;



?>
