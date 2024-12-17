#! /usr/local/bin/php
<?php
/**
 * @file request_vire_ba_num.php
 *
 * @brief 회원 결제내역에 입금내역 추가하면서 선입금액 수정
 *
 * @detail
 * [은행코드]
 * 기업은행   : 003
 * 국민은행   : 004
 * 농협중앙회 : 011
 * 우리은행   : 020
 * SC제일은행 : 023
 * 신한은행   : 026
 * 부산은행   : 032
 * 우체국     : 071
 * 하나은행   : 081
 *
 * [처리종류]
 * 일반     : 10
 * 고정     : 20
 * 고정갱신 : 22
 *
 * 고정식 계좌를 발급받을 때 상품금액에 0원을 넣어야 입금액 제한이 없음
 */
include_once(dirname(__FILE__) . '/ConnectionPool.php');
include_once(dirname(__FILE__) . '/CommonDAO.php');
include_once('/home/sitemgr/inc/define/nimda/order_mng_define.inc');

$opt_arr = getopt("b:c:");

if (count($opt_arr) < 2) {
    echo "Usage : ./request_vire_ba_num.php -b[은행명] -c[계좌수]\n";
    exit;
}

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$bank_name = $opt_arr['b'];
$count = intval($opt_arr['c']);
$bank_code_arr = [
     "기업은행"   => "003"
    ,"국민은행"   => "004"
    ,"농협중앙회" => "011"
    ,"우리은행"   => "020"
    ,"SC제일은행"   => "023"
    ,"신한은행"   => "088"
    ,"신한은행"   => "026"
    ,"부산은행"   => "032"
    ,"우체국"     => "071"
    ,"하나은행"   => "081"
];
$url = "http://172.16.33.207/webpay_direct_vacnt/web/easypay_request_engine.php";

$post_data = [
     "EP_mall_nm"         => ""          // <!-- 가맹점명-->
    //,"EP_mall_id"         => "T0009314"  // <!-- mid--> 실제 : 05528819 / 테스트 : T0009314
    ,"EP_mall_id"         => "T0011065"  // <!-- mid--> 실제 : 05528819 / 테스트 : T0011065
    ,"EP_currency"        => "00"        // <!-- 통화코드 // 00 : 원화-->
    ,"EP_return_url"      => ""          // <!-- 가맹점 CALLBACK URL // -->
    ,"EP_ci_url"          => ""          // <!-- CI LOGO URL // -->
    ,"EP_lang_flag"       => ""          // <!-- 언어 // -->
    ,"EP_charset"         => "UTF-8"     // <!-- 가맹점 CharSet // -->
    ,"EP_user_type"       => ""          //  <!-- 사용자구분 // -->
    ,"EP_user_id"         => "goodprinting"  //  <!-- 가맹점 고객ID // -->
    ,"EP_memb_user_no"    => ""          //  <!-- 가맹점 고객일련번호 // -->
    ,"EP_user_nm"         => "굿프린팅"  //  <!-- 가맹점 고객명 // -->
    ,"EP_user_mail"       => "webmaster@gprinting.co.kr" //  <!-- 가맹점 고객 E-mail // -->
    ,"EP_user_phone1"     => "02-2260-9000" //  <!-- 가맹점 고객 연락처1 // -->
    ,"EP_user_phone2"     => ""          //  <!-- 가맹점 고객 연락처2 // -->
    ,"EP_user_addr"       => "서울시 중구 필동로 8길 50-9 우리빌딩"          //  <!-- 가맹점 고객 주소 // -->
    ,"EP_user_define1"    => ""          //  <!-- 가맹점 필드1 // -->
    ,"EP_user_define2"    => ""          //  <!-- 가맹점 필드2 // -->
    ,"EP_user_define3"    => ""          //  <!-- 가맹점 필드3 // -->
    ,"EP_user_define4"    => ""          //  <!-- 가맹점 필드4 // -->
    ,"EP_user_define5"    => ""          //  <!-- 가맹점 필드5 // -->
    ,"EP_user_define6"    => ""          //  <!-- 가맹점 필드6 // -->
    ,"EP_product_type"    => ""          //  <!-- 상품정보구분 // -->
    ,"EP_product_expr"    => ""          //  <!-- 서비스 기간 // (YYYYMMDD) -->
    ,"EP_tr_cd"           => "00101000"  //   <!-- 거래구분(수정불가) -->
    ,"EP_tot_amt"         => "0"          //   <!-- 결제총금액 -->
    ,"EP_currency"        => "00"        //   <!-- 통화코드 : 00(원), 01(달러)-->
    ,"EP_escrow_yn"       => "N"         //   <!-- 에스크로여부(수정불가) -->
    ,"EP_complex_yn"      => "N"         //   <!-- 복합결제여부(수정불가) -->
    ,"EP_vacct_amt"       => "0"          //   <!-- 무통장입금 결제금액 -->
    ,"EP_expire_date"     => "29991231" //   <!-- 무통장입금 입금만료일(YYYYMMDD) -->
    ,"EP_expire_time"     => "235959"   //   <!-- 무통장입금 입금만료시간(HHMMSS) -->
    ,"EP_cash_yn"         => "0"         //   <!-- 현금영수증발행여부 -->
    ,"EP_cash_issue_type" => ""          //   <!-- 현금영수증발행용도 -->
    ,"EP_cash_auth_type"  => ""          //   <!-- 인증구분 -->
    ,"EP_cash_auth_value" => ""          //   <!-- 인증번호 -->
    ,"EP_bank_cd"         => $bank_code_arr[$bank_name] //   <!-- 은행종류-->
    ,"EP_vacct_txtype"    => "20"        //   <!-- 발급종류-->
    ,"EP_vacct_account"   => ""
    ,"EP_order_no"        => ""          //   <!-- 주문번호--> acc_[은행코드]_[번호]
    ,"EP_product_nm"      => ""          //   <!-- 상품명--> [은행명]_고정계좌채번
    ,"EP_product_amt"     => "0"         //   <!-- 상품금액-->
    ,"bank_name"          => $bank_name
];

$order_no   = "acc_%s_%s";
$product_nc = "%s_고정계좌채번";

$ch  = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
curl_setopt($ch, CURLOPT_VERBOSE, true);

for ($i = 0; $i < $count; $i++) {
    $post_data["EP_order_no"] = sprintf($order_no, $bank_code, uniqid());
    $post_data["EP_product_nm"] = sprintf($product_nc, $bank_name);

    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    $data = curl_exec($ch);

    var_dump($data);
}

curl_close($ch);
$conn->Close();
