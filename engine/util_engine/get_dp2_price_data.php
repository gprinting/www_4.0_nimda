#! /usr/local/bin/php -f
<?php

include_once(dirname(__FILE__) . '/ConnectionPool.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

//$conn->debug = 1;

// csv파일 경로
$file_dir  = dirname(__FILE__) . '/pri_csv/';
$file_dir .= "dp_ver2_proid_";
//$file_dir .= "dp_ver2_proid_etc_memo.csv";

$file_name_arr = ["bl_small.csv"];
/*
$file_name_arr = ["ad_holder.csv"    , "ad_poster.csv"
                 ,"ad_stan.csv"      , "bl.csv" 
                 ,"bl_small.csv"     , "etc_door.csv"
                 ,"etc_lottery.csv"  , "etc_memo.csv"
                 ,"etc_menu"         , "etc_multiple.csv"
                 ,"ev_bi.csv"        , "etc_multiple_mono.csv"
                 ,"ev_jc.csv"        , "ev_master_ca.csv"
                 ,"ev_master_kd.csv" , "ev_master_le.csv"
                 ,"ev_master_ne.csv" , "ev_master_of.csv"
                 ,"ev_master_pa.csv" , "ev_master_sq.csv"
                 ,"ev_master_ss.csv" , "ev_master_ti.csv"
                 ,"ev_master_wi.csv" , "ev_md.csv"
                 ,"ev_mv.csv"        , "ev_sm.csv"
                 ,"ev_tk.csv"        , "gb_mt.csv"
                 ,"gb_sq.csv"        , "mg_opener.csv"
                 ,"mg_paper.csv"     , "mg_sticker.csv"
                 ,"nc_cd.csv"        , "nc_ct.csv"
                 ,"nc_dn.csv"        , "nc_hc.csv"
                 ,"nc_hq.csv"        , "nc_ic.csv"
                 ,"nc_nc.csv"        , "st_cl.csv"
                 ,"st_sp.csv"        , "st_thomson_cr.csv"
                 ,"st_thomson_el.csv", "st_thomson_en.csv"
                 ,"st_thomson_hc.csv", "st_thomson_ht.csv"
                 ,"st_thomson_re.csv", "st_thomson_sq.csv"
                 ,"st_thomson_vc.csv"];
                 */

$ch  = curl_init();
$url = "http://172.16.33.253/shop_sun/modules/OrderPrice.php";

curl_setopt($ch, CURLOPT_URL, $url);
//curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36");
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 

foreach ($file_name_arr as $file) {
    $target_file = fopen($file_dir . $file, 'r');

    $i = 0;
    $flag = true;
    $csv_arr = array();
    while ($line = fgetcsv($target_file, 10000, ',', '"')) {
       
        if ($flag == true) {
            $flag = false;
            continue;
        } 
        
        $csv_arr[] = $line; 
    
    }

    $num = count($csv_arr); // csv_arr의 총개수
    
    $info_arr   = array(); // 정리용 배열
    $postfields = array(); // 소켓통신용 배열
    $err_arr    = array(); // 에러상황일 경우 배열
    
    $j = 0;
    for ($i = 0; $i < $num; $i++) {
        $amt_arr    = array(); // 수량 배열
        
        $prdt_name  = $csv_arr[$i][0]; // 제품정보
        $paper_name = $csv_arr[$i][1]; // 종이정보
        $size       = $csv_arr[$i][2]; // 사이즈
        $print      = $csv_arr[$i][3]; // 도수
        $pro_id     = $csv_arr[$i][4]; // Pro_id
   
        $conn->debug = 1;
        // 1. 카테고리 분류코드 검색
        $query  = "SELECT ";
        $query .= "       A.sortcode ";
        $query .= "      ,A.cate_name ";
        $query .= "  FROM cate A ";
        $query .= " WHERE A.cate_name = '%s' ";
        $query .= "   AND A.cate_level = '3' ";
        
        $query  = sprintf($query, $prdt_name);
        $rs     = $conn->Execute($query);
        
        $fields = $rs->fields;
        $cate_sortcode = $fields["sortcode"]; // #1.cate_sortcode
        $cate_name     = $fields["cate_name"];// #1-1.cate_name
    
        // 종이 값 세분화
        $paper_name_arr = explode(" ", $paper_name); 

        $paper_arr_num  = count($paper_name_arr);
    
        // 2. 종이 분류코드 검색
        if ($paper_arr_num == "3") {

            $query  = "SELECT ";
            $query .= "       A.mpcode ";
            $query .= "  FROM cate_paper A ";
            $query .= " WHERE A.name  = '%s' ";
            $query .= "   AND A.color = '%s' ";
            $query .= "   AND A.basisweight   = '%s' ";
            $query .= "   AND A.cate_sortcode = '%s' ";
        
            $query  = sprintf($query, $paper_name_arr[0]
                                    , $paper_name_arr[1]
                                    , $paper_name_arr[2]
                                    , $cate_sortcode);

        } else if ($paper_arr_num == "4") {

            $query  = "SELECT ";
            $query .= "       A.mpcode ";
            $query .= "  FROM cate_paper A ";
            $query .= " WHERE A.name  = '%s' ";
            $query .= "   AND A.dvs   = '%s' ";
            $query .= "   AND A.color = '%s' ";
            $query .= "   AND A.basisweight   = '%s' ";
            $query .= "   AND A.cate_sortcode = '%s' ";
        
            $query  = sprintf($query, $paper_name_arr[0]
                                    , $paper_name_arr[1]
                                    , $paper_name_arr[2]
                                    , $paper_name_arr[3]
                                    , $cate_sortcode);

        }
    
        $rs     = $conn->Execute($query);
        $fields = $rs->fields;
        $cate_paper_mpcode = $fields["mpcode"]; // #2.cate_paper_mpcode

        if (empty($cate_paper_mpcode) === true) {
            $note = "종이 안 맞음";
            $err_arr[] = $cate_name . "," . $cate_sortcode . "," 
                       . 0 . "," . 0 . "," 
                       . 0 . "," . 0 . "," 
                       . 0 . "," . $note;

        }
    
        // 3. 사이즈 분류코드 검색
        $query  = "SELECT ";
        $query .= "       A.mpcode ";
        $query .= "  FROM cate_stan A ";
        $query .= "      ,prdt_stan B ";
        $query .= " WHERE A.prdt_stan_seqno = B.prdt_stan_seqno ";
        $query .= "   AND A.cate_sortcode = '%s' ";
        $query .= "   AND B.name like ('%s%%') ";
        
        $query  = sprintf($query, $cate_sortcode
                                , $size);
    
        $rs     = $conn->Execute($query);
        $fields = $rs->fields;
        $cate_stan_mpcode = $fields["mpcode"]; // #3.cate_stan_mpcode
    
        // 4. 도수 분류코드 검색 
        $query  = "SELECT ";
        $query .= "       A.mpcode ";
        $query .= "  FROM cate_print A ";
        $query .= "      ,prdt_print B ";
        $query .= " WHERE A.prdt_print_seqno = B.prdt_print_seqno ";
        $query .= "   AND A.cate_sortcode = '%s' ";
        $query .= "   AND B.name = '%s' ";
        
        $query  = sprintf($query, $cate_sortcode
                                , $print);
    
        $rs     = $conn->Execute($query);
        $fields = $rs->fields;
        $cate_print_mpcode = $fields["mpcode"]; // #4.cate_print_mpcode
    
        // 5. amt 검색
        $query  = "SELECT ";
        $query .= "       A.amt ";
        $query .= "  FROM ply_price A ";
        $query .= " WHERE A.cate_sortcode = '%s' ";
        $query .= "   AND A.cate_paper_mpcode = '%s' ";
        $query .= "   AND A.cate_stan_mpcode = '%s' ";
        $query .= "   AND A.cate_beforeside_print_mpcode = '%s' ";
        
        $query  = sprintf($query, $cate_sortcode
                                , $cate_paper_mpcode
                                , $cate_stan_mpcode
                                , $cate_print_mpcode);
    
        $rs     = $conn->Execute($query);

        if ($rs->EOF) {
            $price = 0;
            $note  = "3.0수량없음";

            $err_arr[] = $cate_name . "," . $cate_sortcode . "," 
                       . $cate_paper_mpcode . "," . $cate_stan_mpcode . "," 
                       . $cate_print_mpcode . "," . $amt_arr[$j][$h] . "," 
                       . $price . "," . $note;
            continue; 
        }

        while ($rs && !$rs->EOF) {
            $fields  = $rs->fields;
            $amt_arr[] = $fields["amt"]; // #5.amt
    
            $rs->MoveNext();
        }
    
        $amt_num = count($amt_arr);
    
        // 통신하는 부분
        for ($h = 0; $h < $amt_num; $h++) {
            $amt = $amt_arr[$h];

            $postfields["Pro_id"]  = $pro_id;
            $postfields["Pro_qty"] = $amt;
            $postfields["Pro_Num"] = "1";
            $postfields["After_p"] = "0";
            
            $dp2_price = get_content($ch, $postfields);
            
            echo "sc : " . $cate_sortcode . " pid : " . $pro_id . " qty : " . $amt . " pri : " . $dp2_price . "\n";
    
            if (empty($dp2_price) === true) {
                $price = 0;
                $note  = "2.0없음";
    
                $err_arr[] = $cate_name . "," . $cate_sortcode . "," 
                           . $cate_paper_mpcode . "," . $cate_stan_mpcode . "," 
                           . $cate_print_mpcode . "," . $amt . "," 
                           . $price . "," . $note;
                
                continue; 
            }
    
            // 배열에 삽입
            $info_arr_key = $cate_sortcode . "|" . $cate_paper_mpcode . "|"
                          . $cate_stan_mpcode . "|" . $cate_print_mpcode;
            $info_arr[$info_arr_key][$amt] = $dp2_price;
        }
        sleep(1);
    
        $j++;
    }
    
    if (!empty($err_arr)) {
        makeDataToCsv($err_arr, $file);
    }

    $txt_name = explode('.', $file)[0];
    $txt_name = $txt_name . ".txt";

    $json_dat = json_encode($info_arr);

    echo "!!!!!!!!!!!!!!!!";
    print_r($json_dat);
    echo "@@@@@@@@@@@@@@@@";

    $txt_file = fopen($file_dir . $txt_name, 'w') or die("txt파일 오픈 에러");

    fwrite($txt_file, $json_dat);  
    
    fclose($txt_file);
    fclose($target_file);
}

curl_close($ch);
exit;

/**************************** 함수 영역 *******************************/
// 소켓 통신 함수
function get_content($ch, $post_data) {
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

    $data = curl_exec($ch);
    if (curl_error($ch)) {
        exit('CURL_ERROR('.curl_errno( $ch ) .')'.
                    curl_error($ch));
    }

    $data = explode('/', $data)[0];
    
    return $data;
}

/**
 * @brief csv 파일로 떨궈주는 함수
 */
function makeDataToCsv($err_arr, $file) {
     
    $path  = dirname(__FILE__) . '/pri_csv/';
    $name  = $file . "_" . uniqid() . ".csv";

    $fd = fopen($path . $name, 'w');

    if (!$fd) {
        echo "파일생성실패"; 
        exit;
    }

    // csv 헤드부분
    $csv_form = "%s,%s,%s,%s,%s,%s,%s,%s\r\n";
    $csv_head = sprintf($csv_form, iconv("UTF-8", "EUC-KR", "카테고리명")
                                 , iconv("UTF-8", "EUC-KR", "카테고리 분류코드")
                                 , iconv("UTF-8", "EUC-KR", "종이 분류코드")
                                 , iconv("UTF-8", "EUC-KR", "사이즈 분류코드")
                                 , iconv("UTF-8", "EUC-KR", "도수 분류코드")
                                 , iconv("UTF-8", "EUC-KR", "수량")
                                 , iconv("UTF-8", "EUC-KR", "가격")
                                 , iconv("UTF-8", "EUC-KR", "비고"));
   
    fwrite($fd, $csv_head); 

    // csv 본문부분
    $num = count($err_arr);
    for ($i = 0; $i < $num; $i++) {
        $csv_body = $err_arr[$i] . "\r\n";
        $csv_body = iconv("UTF-8", "EUC-KR", $csv_body);
        fwrite($fd, $csv_body);
    }
}

?>
