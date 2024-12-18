<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/mkt/mkt_mng/GradeMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$gradeDAO = new GradeMngDAO();

$check = 1;

//회원 등급 정책 리스트 가져오기
$param = Array();
$param["table"] = "member_grade_policy";
$param["col"] = "grade_name, grade_dscr, sales_start_price,
                 sales_end_price, sales_sale_rate, grade, 
                 sales_give_point, member_grade_policy_seqno";

$result = $gradeDAO->selectData($conn, $param);


//등급 list
$grade_list = makeGradeMngList($result);


//산정 월 가져오기
$param = Array();
$param["table"] = "mon_member_grade_set";
$param["col"] = "m1, m2, m3, m4, m5, m6, m7, m8, m9, m10
                , m11, m12, day";

$result = $gradeDAO->selectData($conn, $param);

$mon1 = "";
//1월이 산정월에 해당될때
if ($result->fields["m1"] == "Y") $mon1 = "checked";
$mon2 = "";
//2월이 산정월에 해당될때
if ($result->fields["m2"] == "Y") $mon2 = "checked";
$mon3 = "";
//3월이 산정월에 해당될때
if ($result->fields["m3"] == "Y") $mon3 = "checked";
$mon4 = "";
//4월이 산정월에 해당될때
if ($result->fields["m4"] == "Y") $mon4 = "checked";
$mon5 = "";
//5월이 산정월에 해당될때
if ($result->fields["m5"] == "Y") $mon5 = "checked";
$mon6 = "";
//6월이 산정월에 해당될때
if ($result->fields["m6"] == "Y") $mon6 = "checked";
$mon7 = "";
//7월이 산정월에 해당될때
if ($result->fields["m7"] == "Y") $mon7 = "checked";
$mon8 = "";
//8월이 산정월에 해당될때
if ($result->fields["m8"] == "Y") $mon8 = "checked";
$mon9 = "";
//9월이 산정월에 해당될때
if ($result->fields["m9"] == "Y") $mon9 = "checked";
$mon10 = "";
//10월이 산정월에 해당될때
if ($result->fields["m10"] == "Y") $mon10 = "checked";
$mon11 = "";
//11월이 산정월에 해당될때
if ($result->fields["m11"] == "Y") $mon11 = "checked";
$mon12 = "";
//12월이 산정월에 해당될때
if ($result->fields["m12"] == "Y") $mon12 = "checked";

$select_day = $result->fields["day"];

echo $mon1 . "♥♪@" . $mon2 . "♥♪@" . $mon3 . "♥♪@" . $mon4 . "♥♪@" .  $mon5 .
     "♥♪@"  . $mon6 . "♥♪@" . $mon7 . "♥♪@" . $mon8 . "♥♪@" . $mon9 . "♥♪@" .
     $mon10 . "♥♪@" . $mon11 . "♥♪@" . $mon12 . "♥♪@" . $select_day . "♥♪@" .
     $grade_list;

$conn->close();
?>
