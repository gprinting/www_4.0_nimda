<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/calcul_mng/tab/SalesTabListDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/excel/PHPExcel/IOFactory.inc');
include_once(INC_PATH . '/define/nimda/excel_define.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new SalesTabListDAO();

$sell_site = $fb->form("sell_site");
$member_dvs = $fb->form("member_dvs");
$year = $fb->form("year");
$mon = $fb->form("mon");
$seqno = $dao->arr2paramStr($conn, explode(',', $fb->form("seqno")));

$param = array();
$param["sell_site"] = $sell_site;
$param["member_dvs"] = $member_dvs;
$param["year"] = $year;
$param["mon"] = $mon;

if (empty($seqno) === true) {
    $param["tab_public"] = "미발행";
} else {
    $param["seqno"] = $seqno;
}

if ($fb->form("corp_name")) {
    $param["corp_name"] = $fb->form("corp_name");
}

$rs = $dao->selectCashreceiptListSeqno($conn, $param);

if ($rs->EOF) {
    echo 0;
    exit;
} 


$param = array();
$param["table"] = "public_admin";
$param["col"]["tab_public"] = "발행";
$param["col"]["public_state"] = "완료";
$param["prk"] = "public_admin_seqno";

$conn->StartTrans();
while ($rs && !$rs->EOF) {
    $param["prkVal"] = $rs->fields["public_admin_seqno"];
    $rs2 = $dao->updateData($conn, $param);
    if (!$rs2) {
        echo 0;
        exit;
    }
    $rs->MoveNext();
}
$conn->CompleteTrans();

$rs->MoveFirst();

// 검색결과가 존재할 경우 csv 파일 생성 후 xlsx 파일로 컨버팅
$file_name = "cashreceipt";
$csv_form = "%s,%s,%s,1,11,,,,,%s,%s,%s,%s,%s,0,40400,10800,%s\r\n";

$fd = fopen(DOWNLOAD_PATH . '/' . $file_name .".csv", 'w');

$ret = fwrite($fd, "년도,월,일,매입매출구분(1-매출/2-매입),과세유형,불공제사유,신용카드거래처코드,신용카드사명,신용카드(가맹점)번호,거래처명,사업자(주민)번호,공급가액,부가세,품명,전자세금(1전자),기본계정,상대계정,현금영수증승인번호\r\n");

if ($ret  === false) {
    echo 0;
    exit;
}

while (!$rs->EOF) {
    $fields = $rs->fields;

    $req_date = explode('-', explode(' ', $fields["req_date"])[0]);

    $ret = fwrite($fd, sprintf($csv_form, $req_date[0]
                                        , $req_date[1]
                                        , $req_date[2]
                                        , $fields["corp_name"]
                                        , $fields["crn"]
                                        , $fields["supply_price"]
                                        , $fields["vat"]
                                        , $fields["print_title"]
                                        , $fields["cashreceipt_num"]));

    $rs->MoveNext();
}


$csv = PHPExcel_IOFactory::load(DOWNLOAD_PATH . '/' . $file_name . ".csv");
$writer= PHPExcel_IOFactory::createWriter($csv, 'Excel2007');
$writer->save(DOWNLOAD_PATH . '/' . $file_name . ".xlsx");

fclose($fd);
$conn->Close();
echo $file_name;
?>
