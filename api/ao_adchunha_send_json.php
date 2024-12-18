<?php
/**
 * 광고천하 데이터 전송용 json 생성
 */

define(INC_PATH, $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/api/AoAdchunhaDAO.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/PasswordEncrypt.inc");
include_once(INC_PATH . "/define/nimda/api_define.inc");
include_once($_SERVER["DOCUMENT_ROOT"] . "/application/libraries/JWT.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/application/helpers/labelhelper.php");
use \Firebase\JWT\JWT;

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new AoAdchunhaDAO();

$data = $_GET["data"] ?? $_POST["data"];
$data = json_decode($data, true);

$token         = $data["token"];
$order_num_arr = $data["order_num_arr"];
$dlvr_way      = $data["dlvr_way"];

$d_name    = $data["d_name"];
$d_tel     = $data["d_tel"];
$d_hp      = $data["d_hp"];
$d_zipcode = $data["d_zipcode"];
$d_addr1   = $data["d_addr1"];
$d_addr2   = $data["d_addr2"];

/*
if (empty($token) || count($order_num_arr) === 0 || empty($dlvr_way)) {
    $err_json['result']['code'] = '0003';
    $err_json['result']['value'] = 'adchunha api error';

    echo json_encode($err_json);
    exit;
}

$decoded = JWT::decode($token, labelhelper::$KEY, ['HS256']);
*/

$err_json = [];

// 선택한 배송방법에 따라서 발송자 정보 변경
$f_name = '';
$f_tel  = '';
$f_addr = '';
if ($dlvr_way === "택배") {
    $f_name = "(주)굿프린팅";
    $f_tel  = "02-2260-9000";
    $f_addr = "서울 중구 필동2가 84-12 (주)굿프린팅";
}

// !****************************************************************************
// !****************************************************************************
// !****************************************************************************
// 
// 단가 api 나오면 붙여야됨
// 
// !****************************************************************************
// !****************************************************************************
// !****************************************************************************
$price = "10000";

$order_num_str = '';

echo "<pre>";

$i = 0;

$item = '';
$item_arr = [];
$param = [];
foreach ($order_num_arr as $order_num) {
    $order_num_str .= $order_num . '|';

    unset($param);
    $param["order_num"] = $order_num;
    $order_rs = $dao->selectAoOrderInfo($conn, $param);

    unset($param);
    $param["order_detail_seqno"] = $order_rs["order_detail_seqno"];
    $file_rs = $dao->selectAoOrderFileInfo($conn, $param);
    $file_url  = FILE_URL . "?seqno=" . $file_rs["order_detail_count_file_seqno"];
    $file_url .= "&hash=" . password_hash($file_rs["save_file_name"], PASSWORD_DEFAULT);

    unset($param);
    $param["mpcode"] = $order_rs["cate_paper_mpcode"];
    $paper_info = $dao->selectCatePaper($conn, $param)->fields;
    $opt1  = $paper_info["name"];
    $opt1 .= intval($paper_info["basisweight"]) > 0
                ? '(' . $paper_info["color"] . $paper_info["basisweight"] . ')' : '';

    $param["cate_sortcode"] = $order_rs["cate_sortcode"];
    $param["mpcode"] = $order_rs["cate_beforeside_print_mpcode"];
    $opt2 = $dao->selectCatePrintTmpt($conn, $param)->fields["name"];

    unset($param);
    $param["order_common_seqno"] = $order_rs["order_common_seqno"];
    $after_rs = $dao->selectAoAfterInfo($conn, $param);
    $opt_rs   = $dao->selectAoOptInfo($conn, $param);

    $opt_arr = getOpt3to7($after_rs, $opt_rs, $order_rs["amt"]);

    $item_arr[] = [
         "item_code"  => ITEM_CODE[$order_rs["cate_sortcode"]] //#24-1 Y 4자리 코드
        ,"item_name3" => ITEM_NAME[$order_rs["cate_sortcode"]] //#24-2 Y 간편주문명칭
        ,"size1"      => intval($order_rs["cut_size_wid"])     //#24-3 Y 가로 mm
        ,"size2"      => intval($order_rs["cut_size_vert"])    //#24-4 Y 세로 mm
        ,"qnt"        => intval($order_rs["amt"])              //#24-5 Y 수량
        ,"amount"     => intval($price)                        //#24-6 Y 금액
        ,"filename"   => $file_url                             //#24-7 Y 원본파일경로
        ,"subject"    => $order_rs["title"]                    //#24-8 N 주문명
        ,"opt1"       => $opt1                                 //#24-9 Y 원단옵션
        ,"opt2"       => $opt2                                 //#24-10 Y 잉크옵션
        ,"opt3"       => $opt_arr["opt3"]                      //#24-11 N 코팅옵션
        ,"opt4"       => $opt_arr["opt4"]                      //#24-12 Y 후가공옵션
        ,"opt5"       => $opt_arr["opt5"]                      //#24-13 N 추가후가공옵션
        ,"opt6"       => $opt_arr["opt6"]                      //#24-14 N 합지옵션
        ,"opt7"       => $opt_arr["opt7"]                      //#24-15 N 추가물품옵션
        ,"opt8"       => $opt_arr["opt8"]                      //#24-16 N 포장방법
    ];

    /*
    if ($i === 2) {
        break;
    }

    $i++;
    */
}

$order_num_str = substr($order_num_str, 0, -1);

$arr = [
     "userId"       => ID               //#1 Y 로그인 아이디
    ,"userPw"       => PW               //#2 Y 로그인 비밀번호
    ,"userKey"      => KEY              //#3 Y 전송키
    ,"a_type"       => '9'              //#4 Y 1:무통장, 9:미수금
    ,"order_idx"    => $order_num_str   //#5 Y 고객사주문번호

    ,"name"         => ORDERER["name"]  //#6 Y 주문자명
    ,"tel"          => ORDERER["tel"]   //#7 Y 주문자 전화번호
    ,"hp"           => ORDERER["hp"]    //#8 Y 주문자 휴대폰
    ,"hp2"          => ORDERER["hp2"]   //#9 Y 시안확인 휴대폰
    ,"email"        => ORDERER["email"] //#10 N 주문자 이메일

    ,"f_name"       => $f_name          //#11 N 발송자 이름
    ,"f_tel"        => $f_tel           //#12 N 발송자 전화번호
    ,"f_addr"       => $f_addr          //#13 N 발송자 주소

    ,"d_name"       => $d_name          //#14 N 받는사람 이름
    ,"d_tel"        => $d_tel           //#15 N 받는사람 전화번호
    ,"d_hp"         => $d_hp            //#16 N 받는사람 휴대폰
    ,"d_zipcode"    => $d_zipcode       //#17 N 받는사람 우편번호(5자리)
    ,"d_addr1"      => $d_addr1         //#18 N 받는사람 주소 1
    ,"d_addr2"      => $d_addr2         //#19 N 받는사람 주소 2
    ,"d_type_admin" => $dlvr_way        //#20 Y 배송방법(502:택배, 504:퀵, 507:화물)
    ,"d_fee"        => '3'              //#21 Y 배송비 구분(0:미정, 2:착불, 3:신용, 4:신용(청구))
    ,"d_loc2"       => ''               //#22 N 화물지점(경동, 대신화물)
    ,"d_time"       => ''               //#23 N 납품시간(6~23)

    ,"items"        => $item_arr
];

print_r($arr);

$json = json_encode(["orders" => [$arr]],
                    JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, URL);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$response = json_decode($response, true);

$conn->debug = 1;
foreach ($response["response"] as $res) {
    $state = $res["state"];
    $msg   = $res["massage"];

    if ($state !== "0000") {
        $err_json['result']['code'] = $state;
        $err_json['result']['value'] = $msg;

        echo json_encode($err_json, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        exit;
    }

    $outsrc  = $res["o_code"];
    $own_arr = explode('|', $res["order_idx"]);

    unset($param);
    $param["outsource_order_num"] = $outsrc;

    foreach ($own_arr as $own) {
        $param["owncompany_order_num"] = $own;

        if (!$dao->insertOutsourceOrderNumMp($conn, $param)) {
            $err_json['result']['code'] = '0004';
            $err_json['result']['value'] = 'order num insert fail';

            echo json_encode($err_json);
            exit;
        }
    }
}

$conn->Close();
exit;

/******************************************************************************
 ******************************************************************************/

/**
 * @brief 후공정, 옵션값으로 opt3~7 까지 값 생성
 *
 * @detail 카테고리별 opt 명세
 *   * 공통
 *      - opt1 : 소재(=종이)
 *      - opt2 : 소재(=도수)
 *   * 추가
 *      - opt3 : 코팅(페트배너, 미니배너)
 *      - opt4 : 기본후가공
 *      - opt5 : 추가후가공
 *      - opt6 : 고리(현수막, 가로등배너)
 *      - opt7 : 추가물품
 *      - opt7 : 포장방법
 *
 * @param $after_rs = 후공정 검색결과
 * @param $opt_rs   = 옵션 검색결과
 *
 * @return opt3~7
 */
function getOpt3to7($after_rs, $opt_rs, $amt) {
    $opt3 = '';
    $opt4 = '';
    $opt5 = '';
    $opt6 = '';
    $opt7 = '';
    $opt8 = '';

    $makeInfo = function($add, ...$str_arr) {
        $ret = '';

        foreach ($str_arr as $str) {
            if ($str === '-') {
                continue;
            }

            $ret .= $str . '_';
        }

        $ret  = substr($ret, 0, -1);

        if (!empty($add)) {
            $ret .= '/' . $add;
        }

        return $ret;
    };

    while ($after_rs && !$after_rs->EOF) {
        $fields = $after_rs->fields;

        $name   = $fields["after_name"];
        $depth1 = $fields["depth1"];
        $depth2 = $fields["depth2"];
        $depth3 = $fields["depth3"];

        $depth1 = str_replace(' ', '', $depth1);
        $depth2 = str_replace(' ', '', $depth2);
        $depth3 = str_replace(' ', '', $depth3);

        $detail = $fields["detail"];
        $detail = str_replace('_', '', $detail);

        $basic_yn = $fields["basic_yn"];

        if ($name === "미싱" || $name === "추가미싱") {
            if ($depth2 !== '-') {
                $name = $depth1;
                $depth1 = $depth2;
                $depth2 = '-';
            } else if ($depth1 === "나무끈" || $depth1 === "대나무끈") {
                $name = $depth1;
                $depth1 = '-';
            } else {
                $name = "미싱";
            }
        }

        if ($name === "재단") {
            $name = $depth1;
            $detail = $depth2;

            $depth1 = '-';
            $depth2 = '-';
        }

        if ($name === "족자") {
            $name = $depth1;

            $depth1 = '-';
        }

        if ($name === "쿨코팅") {
            $opt3 = sprintf("%s(%s)", $name, $depth1);
        } else if ($name === "고리") {
            $name = $depth1;
            $depth1 = '-';

            if (strpos($name, "사방") !== false) {
                $detail = '';
            } else {
                $detail = str_replace('/', ',', $detail);
            }

            $opt6 = $makeInfo($detail, $name, $depth1, $depth2, $depth3);
        } else if ($basic_yn === 'Y' && empty($opt4)) {
            // 기본후공정은 하나만 들어감
            $detail = str_replace('/', '', $detail);

            $opt4 = $makeInfo($detail, $name, $depth1, $depth2, $depth3);
        } else {
            if (!empty($opt5)) {
                $opt5 .= ' ';
            }
            $detail = str_replace('/', '', $detail);

            $opt5 .= $makeInfo($detail, $name, $depth1, $depth2, $depth3);
        }

        $after_rs->MoveNext();
    }
    unset($after_rs);

    while ($opt_rs && !$opt_rs->EOF) {
        $fields = $opt_rs->fields;

        $name   = $fields["opt_name"];
        $depth1 = $fields["depth1"];
        $depth2 = $fields["depth2"];
        $depth3 = $fields["depth3"];

        $detail = $fields["detail"];
        $detail = str_replace('/', '', $detail);
        $detail = str_replace('_', '', $detail);

        if ($name === "포장") {
            if ($depth1 === "등신대박스포장") {
                $detail = $amt . '개';
            }

            $opt8 .= $makeInfo($detail, $depth1);
        } else {
            if (!empty($opt7)) {
                $opt7 .= ' ';
            }

            if ($name === "추가물품") {
                if ($depth1 === "로프") {
                    // 3mm로프/3m/1개
                    $name = $depth2 . $depth1;
                    $detail = $depth3 . '/' . $detail;
                } else if ($depth1 === "큐방") {
                    $name = $depth1;
                }

                $depth1 = '-';
                $depth2 = '-';
                $depth3 = '-';
            }

            $opt7 .= $makeInfo($detail, $name, $depth1, $depth2, $depth3);
        }


        $opt_rs->MoveNext();
    }
    unset($opt_rs);

    return [
         "opt3" => $opt3
        ,"opt4" => $opt4
        ,"opt5" => $opt5
        ,"opt6" => $opt6
        ,"opt7" => $opt7
        ,"opt8" => $opt8
    ];
}
