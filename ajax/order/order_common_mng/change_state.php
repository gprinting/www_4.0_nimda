<?
define(INC_PATH, $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . '/com/dprinting/MoamoaDAO.inc');
include_once(INC_PATH . "/com/nexmotion/job/front/common/FrontCommonDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new MoamoaDAO();
$fb = new FormBean();
$new_dao = new FrontCommonDAO();


$session = $fb->getSession();
$param = [];
$param['empl_id'] = $session["id"];
$param['state'] = $fb->form("state");

$ordernums = explode("|", $fb->form("ordernums"));
$cancelled = [];
foreach($ordernums as $ordernum) {
    $param['ordernum'] = $ordernum;

    $rs = $dao->select_30OrderNum($conn, $param);
    $OPI_Date = $rs["OPI_Date"];
    $OPI_Seq = $rs["OPI_Seq"];
    $OPI_Inserted = $rs["OPI_Inserted"];
    $count = $rs["count"];
    $cate_sortcode = $rs["cate_sortcode"];
    $cate_paper_mpcode = $rs['cate_paper_mpcode'];
    $order_mng = $rs["order_mng"];
    $order_state = $rs["order_state"];

    if($param['state'] == "1180" && $order_state == "2120") {
        $str = "ordernum=" . $ordernum;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://211.206.147.196:7777/delete_product");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $str);

        $headers = array();
        $response = curl_exec($ch);
        //$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response != "1") {
            array_push($cancelled, $ordernum . " - 조판중");
            continue;
        }
    }

    if($param['state'] == "1180" && $order_state == "1320" && $order_mng != $param['empl_id'] && $order_mng != "") {
        array_push($cancelled, $ordernum . " - 배정된 주문건");
        continue;
    }

//외주로 보낼품목
    if(startsWith($cate_sortcode, "002")
        || ($cate_sortcode == "003003001" && $cate_paper_mpcode != "198") // 카드명함
        || $cate_sortcode == "003007002" || $cate_sortcode == "003007003"
        || $cate_sortcode == "008002001" || $cate_sortcode == "008002002" || $cate_sortcode == "008002003" // 자석
        || $cate_sortcode == "003012001" || $cate_sortcode == "003003002" || $cate_sortcode == "003010001" || $cate_sortcode == "003011001" || $cate_sortcode == "003002003"
        || $cate_sortcode == "006002001" || $cate_sortcode == "006002002" || $cate_sortcode == "006002003" // 마스터봉투
        || $cate_sortcode == "006002004" || $cate_sortcode == "006002005" || $cate_sortcode == "006002006" // 마스터봉투
        || $cate_sortcode == "006002007" || $cate_sortcode == "006002008" || $cate_sortcode == "006002009" || $cate_sortcode == "006002010" // 마스터봉투
        || $cate_sortcode == "007001001" || $cate_sortcode == "007001002" || $cate_sortcode == "007001003"  // 마스터NCR
        || $cate_sortcode == "007002001" // 모조양식지
        || $cate_sortcode == "009001001" || $cate_sortcode == "009001002"
        || ($cate_sortcode == "003002001" && ($cate_paper_mpcode == "1004" || $cate_paper_mpcode == "1005" || $cate_paper_mpcode == "1006" || $cate_paper_mpcode == "1007" || $cate_paper_mpcode == "1008" ||  $cate_paper_mpcode == "1009" || $cate_paper_mpcode == "1010")) // 수입지(엑스트라계열)
        //VIP명함
    ) {

    } else {
        if($param['state'] == "1180" && ((int)$order_state > 2120 || (int)$order_state < 1320)) {
            array_push($cancelled, $ordernum . " - 조판완료");
            continue;
        }
    }

    $dao->updateProductStatecode($conn, $param);
    $dao->insertStateHistory($conn, $param);


    if($OPI_Date != "" && $param['state'] == "1180" && $param['empl_id'] != "migration") {
        //&& (strpos($param['ordernum'],"EV") !== false || $cate_sortcode == "001002001")) {
        $param["OPI_Date"] = $OPI_Date;
        $param["OPI_Seq"] = $OPI_Seq;
        $dao->delete_30OrderNum($conn, $param);
        $post_data = array();
        $post_data["mode"] = "Deliv_End_Direct_99";
        $post_data["Or_Number_99"] = "DP-" . $OPI_Date . "-" . $OPI_Seq . "-" . $count;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://30.gprinting.co.kr/ISAF/Libs/php/doquery40.php");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

        $headers = array();
        $response = curl_exec($ch);
        //$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    }


    if($param['state'] == "1180") {
        $param1['order_common_seqno'] =  $dao->selectOrderCommonSeqnoByOrderNum($conn, $ordernum);
        $param1['empl_id'] = $param['empl_id'];
        $param1["kind"] = "주문취소";
        $param1["before"] = "";
        $param1["after"] = "";
        $dao->insertOrderInfoHistory($conn, $param1);

        // 카드결제 취소시 승인취소
        $param2 = array();
        $param2["order_seqno"] = $param1['order_common_seqno'];

        $order_result = $dao->selectOrderInfo($conn, $param2);
        $pay_way = "";
        $pay_price = 0;
        $delivery_price = 0;
        $cno = "";
        $dlvr_dvs = "";
        $order_num = "";
        $exist_together_dlvr = "N";
        $checked_bun = false;

        while($order_result && !$order_result->EOF) {
            $member_seqno = $order_result->fields['member_seqno'];
            $pay_way = $order_result->fields['pay_way'];
            $cno = $order_result->fields['deal_num'];
            $dlvr_dvs = $order_result->fields['dlvr_dvs'];
            $bun_dlvr_order_num = $order_result->fields['bun_dlvr_order_num'];
            $order_num = $order_result->fields['order_num'];


            if($order_result->fields['use_point_price'] != 0  && $i == 0){

                $param['add_minus_check'] = "add";
                $param['send_points'] = $order_result->fields['use_point_price'];
                $param['add_minus_reason'] = "주문취소 포인트 환불";
                $param['order_num'] = $order_result->fields["order_num"];

                $re_id = $new_dao->selectMemberInfo($conn,$order_result->fields['member_seqno'] );

                $param['mb_id_point'] = $re_id['id'];

                $rs = $new_dao->selectMemberInfoPoint($conn, $param);
                $result = $new_dao->updatePoint2($conn, $param, $rs, $new_dao);
                $i++;
            
            }

            // 묶음존재 여부
            if($dlvr_dvs == "namecard") {
                if($order_result->fields['dvs'] != "배송비")
                    $pay_price += $order_result->fields['sell_price'];

                //함께배송인 제품이 있는지 확인
                if($checked_bun == false) {
                    $tmp_param = array();
                    $tmp_param["bun_dlvr_order_num"] = $bun_dlvr_order_num;
                    $tmp_param["member_seqno"] = $member_seqno;
                    $tmp_param["order_num"] = $order_num;
                    $cnt = $dao->selectCountBunDelivery($conn,$tmp_param);
                    if($cnt >= 1) {
                        $exist_together_dlvr = "Y";
                        $dao->updateDeliveryFeeToAnotherRow($conn,$tmp_param);
                    }
                    if($cnt == 0) {
                        // 묶음배송중 상품 한개만 남아있는경우 배송비조회하여 취소금액에 추가
                        $delivery_price = $dao->selectBunDeliveryPrice($conn,$tmp_param);
                        $pay_price += $delivery_price;
                    }
                    $checked_bun = true;
                }
            } else {
                $pay_price += $order_result->fields['sell_price'];
                $exist_together_dlvr = "N";
            }

            $order_result->MoveNext();
        }
        if($pay_way == "카드") {
            //취소로직
            $url = "https://pgapi.easypay.co.kr/api/trades/revise";

            $fb = new FormBean();

            $headers = array( "content-type: application/json" );

            $mall_id = '05562982';
            $secret_key = "easypay!wJ8YFOFW"; // 암복호화키

            $ch = $fb->form("ordernums");
            $ch = substr($ch, 0,2);
        
            if($ch == "DP") {
                $mall_id = "05574480";
                $secret_key = "easypay!84NaUZh4"; // 암복호화키
            } 



            $id = uniqid();
            $hash_val = hash_hmac( 'sha256', $cno . "|" . $id, $secret_key, false);

            $post_data = array(
                "mallId"         => $mall_id,
                "shopTransactionId"         => $id,
                "pgCno"         => $cno,
                "reviseTypeCode"         => $exist_together_dlvr == "N" ? "32" : "32",// // 32 - 부분취소, 40 - 즉시취소
                "amount"    => $pay_price, // 부분취소금액
                "clientIp"         => "211.110.168.85", // 요청자IP
                "clientId"         => $session["id"], // 요청자ID
                "msgAuthValue"         => $hash_val,
                "cancelReqDate"         => date('Ymd')
            );

            $ch  = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));

            $data = curl_exec($ch);
            curl_close($ch);
            $decoded = json_decode($data);
        }
    }

    if($param['state'] == "1325") {
        $dao->updateReupload($conn, $param);

        $record_param = array();
        $record_param["state"] = "1325";
        $record_param["empl_id"] = $session["id"];;
        $record_param["kind"] = "상태변경";
        $record_param["before"] = "";
        $record_param["after"] = "파일에러로 상태변경";
        $record_param["order_common_seqno"] = $dao->selectOrderCommonSeqnoByOrderNum($conn, $ordernum);
        $dao->insertOrderInfoHistory($conn, $record_param);
    }
}

echo json_encode($cancelled);


function startsWith($haystack, $needle){
    return strncmp($haystack, $needle, strlen($needle)) === 0;
}
?>