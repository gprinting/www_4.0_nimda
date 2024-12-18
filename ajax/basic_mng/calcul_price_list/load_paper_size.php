<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/common_define/common_info.inc');

$fb = new FormBean();

$type_46  = $fb->form("46");
$type_guk = $fb->form("GUK");
$type_spc = $fb->form("SPC");

$ret = "<option value=\"\">전체</option>";

$option_form = "<option value=\"%s*%s\">%s*%s</option>";

if ($type_46 === "true") {
    $ret .= sprintf($option_form, TYPE_46_SIZE[1]["WID"]
                                , TYPE_46_SIZE[1]["VERT"]
                                , TYPE_46_SIZE[1]["WID"]
                                , TYPE_46_SIZE[1]["VERT"]);
}
if ($type_guk === "true") {
    $ret .= sprintf($option_form, TYPE_GUK_SIZE[1]["WID"]
                                , TYPE_GUK_SIZE[1]["VERT"]
                                , TYPE_GUK_SIZE[1]["WID"]
                                , TYPE_GUK_SIZE[1]["VERT"]);
}
if ($type_spc === "true") {
    $size_arr = TYPE_SPC_SIZE;
    $size_arr_count = count($size_arr);

    for ($i = 0; $i < $size_arr_count; $i++) {
        $ret .= sprintf($option_form, $size_arr[$i]["WID"]
                                    , $size_arr[$i]["VERT"]
                                    , $size_arr[$i]["WID"]
                                    , $size_arr[$i]["VERT"]);
    }
}

echo $ret;
?>
