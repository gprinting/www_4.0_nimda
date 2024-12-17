#! /usr/local/bin/php -f
<?php

include_once(dirname(__FILE__) . '/ConnectionPool.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$check = 1;

// txt파일 경로
$file_dir = dirname(__FILE__) . '/pri_csv/compl_txt/';

//$txts = scandir($file_dir); // 해당 디렉토리 내 모든 파일명을 가져옴
//$num  = count($txts);       // 루프타기전에 갯수 세기

//$file_name = "dp_ver2_proid_ev_master_kd.txt";
$file_name = "dp_ver2_proid_bl_small.txt";
$num  = 3;

// insert query bulk용(14)
$bulk_data = "('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s'),";

$conn->StartTrans();

// 루프돌면서 데이터 처리
// 3을 $num으로 바꿔줄 것
for ($i = 2; $i < $num; $i++) {
    //$file_name = $txts[$i];

    $fp = fopen($file_dir . $file_name, 'r');

    while(!feof($fp)) {
        $txt_data = json_decode(fgets($fp), true);
        foreach($txt_data as $key=>$val) {

            $key_param = explode("|", $key); 
            
            $conn->debug = 1;
            // #1. 파라미터 세팅
            $sel_param = array();
            $sel_param["cate_sortcode"]     = $key_param[0];
            $sel_param["cate_paper_mpcode"] = $key_param[1];
            $sel_param["cate_stan_mpcode"]  = $key_param[2]; 
            $sel_param["cate_print_mpcode"] = $key_param[3]; 

            // #2. 기존 가격 SELECT
            /*
            $query  = "  SELECT /* 기존가격 SELECT QUERY ";
            $query .= "\n       A.ply_price_seqno ";
            $query .= "\n  FROM ply_price A ";
            $query .= "\n WHERE A.cate_sortcode = '%s' ";
            $query .= "\n   AND A.cate_paper_mpcode = '%s' ";
            $query .= "\n   AND A.cate_stan_mpcode = '%s' ";
            $query .= "\n   AND A.cate_beforeside_print_mpcode = '%s' ";

            $query  = sprintf($query, $sel_param["cate_sortcode"]
                                    , $sel_param["cate_paper_mpcode"]
                                    , $sel_param["cate_stan_mpcode"]
                                    , $sel_param["cate_print_mpcode"]);

            $rs     = $conn->Execute($query);
            $fields = $rs->fields;
            $ply_price_seqno = $fields["ply_price_seqno"]; // 기존 가격 seqno
            */

            // #3. 기존 가격 DELETE
            $query  = "  DELETE /* 기존가격 DELETE QUERY*/ ";
            $query .= "\n  FROM ply_price ";
            $query .= "\n WHERE cate_sortcode = '%s'";
            $query .= "\n   AND cate_paper_mpcode = '%s' ";
            $query .= "\n   AND cate_stan_mpcode = '%s' ";
            $query .= "\n   AND cate_beforeside_print_mpcode = '%s' ";

            $query  = sprintf($query, $sel_param["cate_sortcode"]
                                    , $sel_param["cate_paper_mpcode"]
                                    , $sel_param["cate_stan_mpcode"]
                                    , $sel_param["cate_print_mpcode"]);

            $del_rs = $conn->Execute($query);

            if (!$del_rs) {
                $check = 0; // 0 : DELETE 실패
                $conn->FailTrans();
                $conn->RollbackTrans();
            }           
            $ins_bulk_data = ""; 

            foreach($val as $sub_key=>$sub_val) {
                $pri_val = $key . "|" . $sub_key . "|" . $sub_val;
                $pri_param = explode("|", $pri_val); 

                // #4. INSERT VALUE값 생성
                $ins_cate_sortcode                    = $pri_param[0]; // #D1
                $ins_cate_paper_mpcode                = $pri_param[1]; // #D2
                $ins_cate_stan_mpcode                 = $pri_param[2]; // #D3
                $ins_cate_beforeside_print_mpcode     = $pri_param[3]; // #D4
                $ins_amt                              = $pri_param[4]; // #D5
                $ins_basic_price                      = $pri_param[5]; // #D6
                $ins_cate_beforeside_add_print_mpcode = "0";           // #D7 
                $ins_cate_aftside_print_mpcode        = "0";           // #D8
                $ins_cate_aftside_add_print_mpcode    = "0";           // #D9 
                $ins_page                             = "2";           // #D10 
                $ins_page_dvs                         = "표지";        // #D11
                $ins_rate                             = "0";           // #D12
                $ins_aplc_price                       = "0";           // #D13
                $ins_new_price                        = $pri_param[5]; // #D14

                $ins_bulk_data .= sprintf($bulk_data, $ins_cate_sortcode     // #D1
                                                    , $ins_cate_paper_mpcode // #D2
                                                    , $ins_cate_stan_mpcode  // #D3
                                                    , $ins_cate_beforeside_print_mpcode // #D4      
                                                    , $ins_amt               // #D5      
                                                    , $ins_basic_price       // #D6      
                                                    , $ins_cate_beforeside_add_print_mpcode // #D7
                                                    , $ins_cate_aftside_print_mpcode // #D8
                                                    , $ins_cate_aftside_add_print_mpcode // #D9
                                                    , $ins_page              // #D10
                                                    , $ins_page_dvs          // #D11
                                                    , $ins_rate              // #D12
                                                    , $ins_aplc_price        // #D13
                                                    , $ins_new_price);       // #D14


            }

            $fin_bulk_data = substr($ins_bulk_data, 0, -1);

            // 5. 새 가격 INSERT
            $query  = "INSERT INTO"; 
            $query .= "\n       ply_price (";
            $query .= "\n        cate_sortcode ";
            $query .= "\n       ,cate_paper_mpcode ";
            $query .= "\n       ,cate_stan_mpcode ";
            $query .= "\n       ,cate_beforeside_print_mpcode ";
            $query .= "\n       ,amt ";
            $query .= "\n       ,basic_price ";
            $query .= "\n       ,cate_beforeside_add_print_mpcode ";
            $query .= "\n       ,cate_aftside_print_mpcode ";
            $query .= "\n       ,cate_aftside_add_print_mpcode ";
            $query .= "\n       ,page ";
            $query .= "\n       ,page_dvs ";
            $query .= "\n       ,rate ";
            $query .= "\n       ,aplc_price ";
            $query .= "\n       ,new_price ";
            $query .= "\n       ) VALUES %s";
            
            $query  = sprintf($query, $fin_bulk_data);
            
            $ins_rs = $conn->Execute($query);
            
            if (!$ins_rs) {
                $check = 2; // 2 : INSERT 실패
                $conn->FailTrans();
                $conn->RollBackTrans();
            }
        }
    }
    
    fclose($fp);
}


echo $check;
$conn->CompleteTrans();
$conn->Close();

?>
