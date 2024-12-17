<?php
/*
 * Copyright (c) Nexmotion, Inc.
 * All rights reserved.
 *
 * 날짜계산 관련 유틸 클래스
 *
 * 사용법 예시)
 * #1. 초기화
 * $util = new DateUtil();
 * $util->setData([
 *     'y' => "2017",
 *     'm' => "3",
 *     'd' => "20"
 * ]);
 * #2. 계산(y = 년 / m = 월 / d = 일)
 * $ret = $util->calcDate('y', -1); // 전년
 * $ret = $util->calcDate('y',  1); // 내년
 * #3. 문자열 반환
 * $str = $util->getDateString();
 *
 * #4. 특정 년월의 마지막 일 검색
 * $last_day = $util->getLastDay("2017", "5");
 *
 * #5. 클래스에 전달된 데이터의 마지막 일 검색
 * $last_day = $util->getLastDayByData();
 *
 * #6. 특정 일자의 주차 검색
 * $week_num = $util->getWeekNum("2017-06-20");
 *
 * 실사용 예시코드) 현재 월, 전년 동월, -1월, -2월, -3월
 * //------------------------------ 시작
 * $year  = date('Y');
 * $month = date('m');
 * // 현재
 * $date_param['d'] = "01";
 * $dateUtil->setData($date_param);
 * $cur_from = $dateUtil->getDateString();
 * // 작년동기
 * $dateUtil->calcDate('y', -1);
 * $last_year_from = $dateUtil->getDateString();
 * // -1월
 * $dateUtil->calcDate('m', -1);
 * $m1_from = $dateUtil->getDateString();
 * // -2월
 * $dateUtil->calcDate('m', -2);
 * $m2_from = $dateUtil->getDateString();
 * // -3월
 * $dateUtil->calcDate('m', -3);
 * $m3_from = $dateUtil->getDateString();
 * 
 * //------------------------------ 종료
 * // 현재
 * $date_param['y'] = $year;
 * $date_param['m'] = $month;
 * $date_param['d'] = $dateUtil->getLastDay($year, $month);
 * $dateUtil->setData($date_param);
 * $cur_to = $dateUtil->getDateString();
 * // 작년동기
 * $dateUtil->calcDate('y', -1);
 * $last_year_to = $dateUtil->getDateString();
 * // -1월
 * $dateUtil->calcDate('m', -1);
 * $m1_to = $dateUtil->getDateString();
 * // -2월
 * $dateUtil->calcDate('m', -2);
 * $m2_to = $dateUtil->getDateString();
 * // -3월
 * $dateUtil->calcDate('m', -3);
 * $m3_to = $dateUtil->getDateString();
 *
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/05/30 엄준현 생성
 * 2017/06/30 엄준현 추가(주차 구하는 함수추가)
 * 2018/02/20 엄준현 수정(일 더하는 함수 수정)
 *=============================================================================
 */
class DateUtil {
    private $y;
    private $m;
    private $d;

    private $calc_y;
    private $calc_m;
    private $calc_d;

    public function __construct(array $param = []) {
        if (!empty($param)) {
            $this->setData($param);
        }
    }

    /**
     * @brief 날짜를 계산하기 위한 데이터 세팅
     *
     * @param $param = 데이터 값
     * @detail [
     *     "y" => "년"
     *     "m" => "월"
     *     "d" => "일"
     * ]
     */
    public function setData(array $param) {
        $this->y = intval($param["y"]);
        $this->m = intval($param["m"]);
        $this->d = intval($param["d"]);

        $this->setCalcData($param);
    }

    /**
     * @brief 계산된 날짜데이터 저장
     *
     * @param $param = 데이터 값
     * @detail [
     *     "y" => "년"
     *     "m" => "월"
     *     "d" => "일"
     * ]
     */
    private function setCalcData(array $param) {
        $this->calc_y = intval($param["y"]);
        $this->calc_m = intval($param["m"]);
        $this->calc_d = intval($param["d"]);
    }

    /**
     * @brief 구분값에 따라서 날짜 계산
     *
     * @param $dvs = 구분값(y/m/d or Y/M/D)
     * @param $calc = 계산값
     *
     * @return array(
     *     'y' => 연
     *     'm' => 월
     *     'd' => 일
     * )
     */
    public function calcDate(string $dvs, $calc): array {
        $dvs = strtolower($dvs);
        $calc = intval($calc);

        $ret = null;

        switch ($dvs) {
            case 'y' :
                $ret = $this->calcYear($calc);
                break;
            case 'm' :
               $ret =  $this->calcMonth($calc);
                break;
            case 'd' :
                $ret = $this->calcDay($calc);
                break;
        }

        $this->setCalcData($ret);

        return $ret;
    }

    /**
     * @brief 년 계산
     *
     * @param $calc = 계산값
     *
     * @return array(
     *     'y' => 연
     *     'm' => 월
     *     'd' => 일
     * )
     */
    private function calcYear(int $calc): array {
        $y = $this->y;
        $m = $this->m;
        $d = $this->d;

        $y += $calc;

        return array(
            'y' => $y,
            'm' => $m,
            'd' => $d
        );
    }

    /**
     * @brief 월 계산
     *
     * @param $calc = 계산값
     *
     * @return array(
     *     'y' => 연
     *     'm' => 월
     *     'd' => 일
     * )
     */
    private function calcMonth(int $calc): array {
        $y = $this->y;
        $m = $this->m;
        $d = $this->d;

        $temp = $m + $calc;

        if ($temp <= 0) {
            // 1월에서 전월일 경우 1년 감소
            $y--;
            $m = 12;
        } else {
            $m = $temp;
        }

        $last_day = $this->getLastDay($y, $m);

        if ($d > $last_day) {
            // 당월보다 전월의 마지막 일이 작을 경우(3월 30일 -> 2월 28일)
            $d = $last_day;
        }

        return array(
            'y' => $y,
            'm' => $m,
            'd' => $d
        );
    }

    /**
     * @brief 일 계산
     *
     * @param $calc = 계산값
     *
     * @return array(
     *     'y' => 연
     *     'm' => 월
     *     'd' => 일
     * )
     */
    private function calcDay(int $calc): array {
        $y = $this->y;
        $m = $this->m;
        $d = $this->d;

        $last_day = $this->getLastDay($y, $m);

        // +1을 안하면 오늘을 제외한 일 수
        // 3/1 ~ 3/7 -> 오늘 포함하면 7일, 제외하면 6일
        $temp = $d + $calc + 1;

        if ($temp <= 0)  {
            $util = new DateUtil();
            $util->setData(array(
                'y' => $y,
                'm' => $m,
                'd' => $d
            ));
            $res = $util->calcDate('m', -1);

            $y = $res['y'];
            $m = $res['m'];

            $last_day = $this->getLastDay($y, $m);
            $d = $last_day + $temp;

        } else if ($last_day < $temp) {
            $util = new DateUtil();
            $util->setData(array(
                'y' => $y,
                'm' => $m,
                'd' => $d
            ));
            $res = $util->calcDate('m', 1);

            $y = $res['y'];
            $m = $res['m'];

            $d = $temp - $last_day;
        } else {
            $d = $temp;
        }

        return array(
            'y' => $y,
            'm' => $m,
            'd' => $d
        );
    }

    /**
     * @brief yyyy-mm-dd 형식 문자열을 잘라서 배열로 반환
     *
     * @return array(
     *     'y' => 연
     *     'm' => 월
     *     'd' => 일
     * )
     */
    private function getDateArr($date): array {
        $date_arr = explode('-', $date);

        $y = intval($date_arr[0]);
        $m = intval($date_arr[1]);
        $d = intval($date_arr[2]);

        return array(
            'y' => $y,
            'm' => $m,
            'd' => $d
        );
    }

    /**
     * @brief 현재 클래스에 저장된 연월의 말일 계산
     *
     * @return 말일
     */
    public function getLastDayByData(): int {
        //이번달 1일
        $temp = mktime(0, 0, 0, $this->m, 1, $this->y);

        return intval(date("t", $temp));
    }

    /**
     * @brief 특정 연월의 말일 계산
     *
     * @param $y = 계산할 년
     * @param $m = 계산할 월
     *
     * @return 말일
     */
    public function getLastDay($y, $m): int {
        $y = intval($y);
        $m = intval($m);

        //이번달 1일
        $temp = mktime(0, 0, 0, $m, 1, $y);

        return intval(date("t", $temp));
    }

    /**
     * @brief 현 클래스에 저장되어 있는 값 Y-M-D 문자열로 반환
     *
     * @return 문자열
     */
    public function getDateString($separator = '-'): string {
        $m = str_pad(strval($this->calc_m), 2, '0', STR_PAD_LEFT);
        $d = str_pad(strval($this->calc_d), 2, '0', STR_PAD_LEFT);

        return sprintf("%s%s%s%s%s", $this->calc_y
                                   , $separator
                                   , $m
                                   , $separator
                                   , $d);
    }

    /**
     * @brief 해당 일자에 해당하는 주차수 반환
     *
     * @param $date = 주차수를 반환할 일자(yyyy-mm-dd)
     *
     * @return 주차수
     */
    public function getWeekNum($date): int {
        $timestamp = strtotime($date);
        $w = date('w', mktime(0, 0, 0,
                              date('n', $timestamp),
                              1,
                              date('Y', $timestamp)));

        return intval(ceil(($w + date('j', $timestamp) - 1) / 7));
    }

    /**
     * @brief 해당 기간에 따른 주차수 배열 반환
     *
     * @param $from = 시작일자
     * @param $to   = 종료일자
     *
     * @return $ret["y-m"][주차수][] = 일자
     */
    public function makeFromToWeekNumArr($from, $to): array {
        $from_arr = $this->getDateArr($from);
        $to_arr   = $this->getDateArr($to);

        $from_y = $from_arr[0];
        $from_m = $from_arr[1];
        $from_d = $from_arr[2];

        $to_y = $to_arr[0];
        $to_m = $to_arr[1];
        $to_d = $to_arr[2];

        $is_start = true;

        $ret = [];

        for ($y = $from_y; $y <= $to_y; $y++) {
            for ($m = $from_m; $m <= $to_m; $m++) {
                $start_d = 1;
                $last_d  = $this->getLastDay($y, $m);

                // 시작일(1일부터 시작이 아니므로)
                if ($is_start) {
                    $start_d = $from_d;
                    $is_start = false;
                }
                // 종료일(연/월이 같을경우)
                if ($from_y === $to_y && $from_m === $to_m) {
                    $last_d = $to_d;
                }
                if ($m === $to_m) {
                    $last_d = $to_d;
                }

                for ($d = $start_d; $d <= $last_d; $d++) {
                    $w = $this->getWeekNum($y . '-' . $m . '-' . $d);

                    $ret[$y . '-' . $m][$w][] = $d;
                }
            }
        }

        return $ret;
    }

    /**
     * @brief 해당 기간에 토/일요일이 얼만큼 들어있는지 반환
     *
     * @detail 주말 추가시간
     *  (1) ceil((from - to) / 24)로 며칠인지를 구함
     *  (2) from의 시작요일을 구한다
     *  (3) from의 시작요일에 (1)의 값을 더한다
     *  (4) (3)의 값이 5 이하면 0개
     *      6이면 1개, 7이면 2개
     *      7초과면 (3) - 7의 값으로 다시 계산
     *
     *
     * @param $from = 시작일자
     * @param $to   = 종료일자
     *
     * @return 토/일요일 수
     */
    public function getWeekendCount($from, $to): int {
        $from_arr = $this->getDateArr($from);
        $to_arr   = $this->getDateArr($to);

        $from_y = $from_arr['y'];
        $from_m = $from_arr['m'];
        $from_d = $from_arr['d'];

        $to_y = $to_arr['y'];
        $to_m = $to_arr['m'];
        $to_d = $to_arr['d'];

        $from_stamp = mktime(0, 0, 0,
                             $from_m, $from_d, $from_y);
        $to_stamp = mktime(0, 0, 0,
                           $to_m, $to_d, $to_y);

        // (1)
        $term = ceil((($to_stamp - $from_stamp) / 3600) / 24);
        // (2)
        $from_day = date('N', $from_stamp);
        // (3)
        $day_sum = intval($from_day + $term);
        // (4)
        $ret = 0;
        $i = $day_sum;
        while ($i) {
            if ($i === 6) {
                $ret += 1;
                break;
            } else if ($i === 7) {
                $ret += 2;
                break;
            } else if ($i > 7) {
                $ret += 2;
                $i -= 7;
            } else {
                break;
            }
        } 

        return $ret;
    }
}
