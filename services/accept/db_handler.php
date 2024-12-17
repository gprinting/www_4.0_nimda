<?php
/**
 * Created by PhpStorm.
 * User: dpdev01
 * Date: 2018-06-05
 * Time: 18:42
 */

class db_handler {

    public function connect() {
        $conn = mysqli_connect("211.110.168.85", "dpuser01", "gpdb2021");
        mysqli_select_db($conn, "gprinting");
        return $conn;
    }

    public function disconnect($conn) {
        mysqli_close($conn);
    }

    public function is_order_id_valid($conn, $order_id) {
        $query = "select order_common_seqno from order_common where order_num = '$order_id'";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);
        return ($row !== null ? true : false);
    }

    public function get_order_info_for_accept_branch($conn, $order_id) {
        $infos = array();

        $query = "select a.cate_sortcode, a.member_seqno, a.title, a.count, a.owncompany_img_num, b.cate_paper_mpcode, c.save_file_name, d.flattyp_yn
from order_common as a
inner join order_detail as b on a.order_common_seqno = b.order_common_seqno
inner join order_file as c on a.order_common_seqno = c.order_common_seqno
inner join cate as d on a.cate_sortcode = d.sortcode
where a.order_num = '$order_id'";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);
        if ($row !== null) {
            $infos["cate_code"] = $row["cate_sortcode"];
            $infos["customer_seq"] = $row["member_seqno"];
			$infos["order_title"] = $row["title"];
			$infos["item_count"] = $row["count"];
			$infos["dp_image"] = $row["owncompany_img_num"];
            $infos["paper_code"] = $row["cate_paper_mpcode"];
            $infos["file_name"] = $row["save_file_name"];
            $infos["flattyp_yn"] = $row["flattyp_yn"];

            $query = "select b.after_name
from order_common as a
left outer join order_after_history as b on a.order_common_seqno = b.order_common_seqno
where a.order_num = '$order_id' and b.basic_yn = 'N'";
            $result = mysqli_query($conn, $query);
            while ($row = mysqli_fetch_assoc($result)) {
                $infos["finish"][] = $row["after_name"];
            }
        }

        return $infos;
    }

    public function get_order_info_from_accept_id($conn, $accept_id) {
        $infos = array();

        $query = "select b.order_num, b.title, b.cate_sortcode, c.stan_name, b.count, c.work_size_wid, c.work_size_vert, c.cut_size_wid, c.cut_size_vert, c.side_dvs, e.cate_name, d.file_path, d.save_file_name
from accept_work as a
inner join order_common as b on a.order_id = b.order_num
inner join order_detail as c on b.order_common_seqno = c.order_common_seqno
inner join order_file as d on b.order_common_seqno = d.order_common_seqno
inner join cate as e on b.cate_sortcode = e.sortcode
where a.accept_id = '$accept_id'";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);
        if ($row !== null) {
            $infos["order_id"] = $row["order_num"];
            $infos["order_title"] = $row["title"];
            $infos["cate_code"] = $row["cate_sortcode"];
            $infos["cate_name"] = $row["cate_name"];
            $infos["item_count"] = $row["count"];
            $infos["bleed_width"] = $row["work_size_wid"];
            $infos["bleed_height"] = $row["work_size_vert"];
            $infos["trim_width"] = $row["cut_size_wid"];
            $infos["trim_height"] = $row["cut_size_vert"];
            $infos["stan_name"] = $row["stan_name"];
            $infos["side_dvs"] = $row["side_dvs"];
            $infos["file_path"] = $row["file_path"];
            $infos["file_name"] = $row["save_file_name"];
        }

        return $infos;
    }

    public function get_order_info_from_accept_id_for_qc($conn, $accept_id) {
        $infos = array();

        $query = "select b.order_num, b.title, b.cust_memo, c.file_path, c.save_file_name
from accept_work as a
inner join order_common as b on a.order_id = b.order_num
inner join order_file as c on b.order_common_seqno = c.order_common_seqno
where a.accept_id = '$accept_id'";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);
        if ($row !== null) {
            $infos["order_id"] = $row["order_num"];
            $infos["order_title"] = $row["title"];
            $infos["cust_memo"] = $row["cust_memo"];
            $infos["file_path"] = $row["file_path"];
            $infos["file_name"] = $row["save_file_name"];
        }

        return $infos;
    }

	public function get_order_infos($conn, $order_ids) {
		$orders = array();

		$order_ids_string = "";
		foreach ($order_ids as $order_id) {
			if (empty($order_ids_string) === true)
				$order_ids_string = "a.order_num in ('$order_id'";
			else
				$order_ids_string = $order_ids_string . ", '$order_id'";
		}
		$order_ids_string = $order_ids_string . ")";

		$query = "select a.order_num, a.title, a.count, b.sortcode, b.cate_name, c.id, c.member_name
from order_common as a
inner join cate as b on a.cate_sortcode = b.sortcode
inner join member as c on a.member_seqno = c.member_seqno
where $order_ids_string order by a.order_num asc";
		$result = mysqli_query($conn, $query);
		if ($result !== false) {
			while ($row = mysqli_fetch_assoc($result)) {
				$infos = array();
				$infos["order"]["id"] = $row["order_num"];
				$infos["order"]["name"] = $row["title"];
				$infos["category"]["id"] = $row["sortcode"];
				$infos["category"]["name"] = $row["cate_name"];
				$infos["customer"]["id"] = $row["id"];
				$infos["customer"]["name"] = $row["member_name"];
				$infos["item_count"] = $row["count"];

				$_order_id = $row["order_num"];
				$query_2 = "select b.opt_name
from order_common as a
left outer join order_opt_history as b on a.order_common_seqno = b.order_common_seqno
where a.order_num = '$_order_id' and b.basic_yn = 'N'";
				$result_2 = mysqli_query($conn, $query_2);
				while ($row_2 = mysqli_fetch_assoc($result_2)) {
					$infos["options"][] = $row_2["opt_name"];
				}

				$orders[] = $infos;
			}
		}

		return $orders;
	}

    public function get_order_detail($conn, $order_id) {
        $detail = array();

        $query = "select a.title, a.order_detail, a.order_regi_date, a.count, a.pay_price, a.cust_memo, a.owncompany_img_num, b.work_size_wid, b.work_size_vert, b.cut_size_wid, b.cut_size_vert, b.side_dvs, b.stan_name, b.tot_tmpt, c.file_path, c.save_file_name, d.sortcode, d.cate_name, e.id, e.member_name
from order_common as a
inner join order_detail as b on a.order_common_seqno = b.order_common_seqno
inner join order_file as c on a.order_common_seqno = c.order_common_seqno
inner join cate as d on a.cate_sortcode = d.sortcode
inner join member as e on a.member_seqno = e.member_seqno
where a.order_num = '$order_id'";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);
        if ($row !== null) {
            $detail["order"]["id"] = $order_id;
            $detail["order"]["name"] = $row["title"];
            $detail["order"]["desc"] = $row["order_detail"];
            $detail["order"]["ordered_date"] = $row["order_regi_date"];
            $detail["category"]["id"] = $row["sortcode"];
            $detail["category"]["name"] = $row["cate_name"];
            $detail["customer"]["id"] = $row["id"];
            $detail["customer"]["name"] = $row["member_name"];
            $detail["bleed_size"]["width"] = $row["work_size_wid"];
            $detail["bleed_size"]["height"] = $row["work_size_vert"];
            $detail["trim_size"]["width"] = $row["cut_size_wid"];
            $detail["trim_size"]["height"] = $row["cut_size_vert"];
            $detail["regularity"] = ($row["stan_name"] === "비규격" ? 0 : 1);
            $detail["side_count"] = ($row["side_dvs"] === "양면" ? 2 : 1);
			$detail["color_count"] = $row["tot_tmpt"];
            $detail["item_count"] = $row["count"];
            $detail["price"] = $row["pay_price"];
            $detail["memo"] = $row["cust_memo"];
            $detail["file_info"]["path"] = $row["file_path"] . $row["save_file_name"];
			$detail["file_info"]["dp_image"] = $row["owncompany_img_num"];

            $query = "select b.opt_name
from order_common as a
left outer join order_opt_history as b on a.order_common_seqno = b.order_common_seqno
where a.order_num = '$order_id' and b.basic_yn = 'N'";
            $result = mysqli_query($conn, $query);
            while ($row = mysqli_fetch_assoc($result)) {
                $detail["options"][] = $row["opt_name"];
            }

            $query = "select b.after_name, b.depth1, b.depth2, b.depth3, b.detail
from order_common as a
left outer join order_after_history as b on a.order_common_seqno = b.order_common_seqno
where a.order_num = '$order_id' and b.basic_yn = 'N'";
            $result = mysqli_query($conn, $query);
            while ($row = mysqli_fetch_assoc($result)) {
                $detail["finishes"][] = $row["after_name"] . "|" . $row["depth1"] . "|" . $row["depth2"] . "|" . $row["depth3"] . "|" . $row["detail"];
            }
        }

        return $detail;
    }

    public function get_order_details($conn, $order_ids) {
        $order_ids_string = "";
        foreach ($order_ids as $order_id) {
            if (empty($order_ids_string) === true)
                $order_ids_string = "order_id in ('$order_id''";
            else
                $order_ids_string = $order_ids_string . ", '$order_id''";
        }
        $order_ids_string = $order_ids_string . ")";

    }

    public function get_category_info_from_order_id($conn, $order_id) {
        $infos = array();

        $query = "select b.sortcode, b.cate_name
from order_common as a
inner join cate as b on a.cate_sortcode = b.sortcode
where a.order_num = '$order_id'";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);
        if ($row !== null) {
            $infos["cate_code"] = $row["sortcode"];
            $infos["cate_name"] = $row["cate_name"];
        }

        return $infos;
    }

    public function get_order_id_from_accept_id($conn, $accept_id) {
        $order_id = "";

        $query = "select order_id from accept_work where accept_id = '$accept_id'";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);
        if ($row !== null) {
            $order_id = $row["order_id"];
        }

        return $order_id;
    }

    public function get_order_id_from_order_seqno($conn, $order_seqno) {
        $order_id = "";

        $query = "select order_num from order_common where order_common_seqno = '$order_seqno'";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);
        if ($row !== null) {
            $order_id = $row["order_num"];
        }

        return $order_id;
    }

    public function get_order_count_in_one_file_group($conn, $order_id) {
        $order_count = 0;

        $query = "select count(order_num) as order_count from onefile_order_group where group_num = (select group_num from onefile_order_group where order_num = '$order_id')";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);
        if ($row !== null) {
            $order_count = (int) $row["order_count"];
        }

        return $order_count;
    }

    public function get_order_ids_in_one_file_group($conn, $order_id) {
        $order_ids = array();

        $query = "select group_num, order_num from onefile_order_group where group_num = (select group_num from onefile_order_group where order_num = '$order_id')";
        $result = mysqli_query($conn, $query);
        if ($result !== false) {
            while ($row = mysqli_fetch_assoc($result)) {
                $set = array();
                $set["group_id"] = $row["group_num"];
                $set["order_id"] = $row["order_num"];
                $order_ids[] = $set;
            }
        }

        return $order_ids;
    }

    public function get_order_seqs_for_one_file($conn, $order_seq) {
        $order_seqs = array();

        $query = "select order_common_seqno from order_file where origin_file_name = (select origin_file_name from order_file where order_common_seqno = '$order_seq')";
        $result = mysqli_query($conn, $query);
        if ($result !== false) {
            while ($row = mysqli_fetch_assoc($result)) {
                $order_seqs[] = $row["order_common_seqno"];
            }
        }

        return $order_seqs;
    }

	public function get_imp_ready_items_for_commercial($conn) {
		$items = array();

		$query = "select a.order_detail_file_num, a.order_detail_count_file_seqno, b.stan_name, b.side_dvs, b.print_tmpt_name, b.amt, b.amt_unit_dvs, b.cate_paper_mpcode, c.order_regi_date, d.sortcode, d.cate_name, f.member_seqno, f.member_name, e.name, e.color, e.basisweight
from order_detail_count_file as a
inner join order_detail as b on b.order_detail_seqno = a.order_detail_seqno
inner join order_common as c on c.order_common_seqno = b.order_common_seqno
inner join cate as d on d.sortcode = c.cate_sortcode
inner join cate_paper as e on e.mpcode = b.cate_paper_mpcode
inner join member as f on f.member_seqno = c.member_seqno
inner join amt_order_detail_sheet as g on g.order_detail_count_file_seqno = a.order_detail_count_file_seqno
where b.state = '2120' and g.sheet_typset_seqno is null and d.sortcode not like '003%' and d.sortcode not like '004%'";
		$result = mysqli_query($conn, $query);
		if ($result !== false) {
			while ($row = mysqli_fetch_assoc($result)) {
				$infos = array();
				$infos["item_id"] = $row["order_detail_file_num"];
				$infos["item_temp_no"] = $row["order_detail_count_file_seqno"];
				$infos["size_name"] = $row["stan_name"];
				$infos["side_dvs"] = $row["side_dvs"];
				$infos["color_name"] = $row["print_tmpt_name"];
				$infos["quantity"] = $row["amt"];
				$infos["quantity_unit"] = $row["amt_unit_dvs"];
				$infos["paper_code"] = $row["cate_paper_mpcode"];
				$infos["ordered_date"] = $row["order_regi_date"];
				$infos["cate_code"] = $row["sortcode"];
				$infos["cate_name"] = $row["cate_name"];
				$infos["customer_id"] = $row["member_seqno"];
				$infos["customer_name"] = $row["member_name"];
				$infos["paper_name"] = $row["name"];
				$infos["paper_color"] = $row["color"];
				$infos["paper_weight"] = $row["basisweight"];
				$items[] = $infos;
			}
		}

		$query = "select a.order_detail_file_num, b.stan_name, b.side_dvs, b.print_tmpt_name, b.amt, b.amt_unit_dvs, b.cate_paper_mpcode, c.order_regi_date, d.sortcode, d.cate_name, f.member_seqno, f.member_name, e.name, e.color, e.basisweight
from order_detail_count_file as a
inner join order_detail_brochure as b on b.order_detail_seqno = a.order_detail_seqno
inner join order_common as c on c.order_common_seqno = b.order_common_seqno
inner join cate as d on d.sortcode = c.cate_sortcode
inner join cate_paper as e on e.mpcode = b.cate_paper_mpcode
inner join member as f on f.member_seqno = c.member_seqno
inner join amt_order_detail_sheet as g on g.order_detail_count_file_seqno = a.order_detail_count_file_seqno
where b.state = '2120' and g.sheet_typset_seqno is null";
		$result = mysqli_query($conn, $query);
		if ($result !== false) {
			while ($row = mysqli_fetch_assoc($result)) {
				$infos = array();
				$infos["item_id"] = $row["order_detail_file_num"];
				$infos["size_name"] = $row["stan_name"];
				$infos["side_dvs"] = $row["side_dvs"];
				$infos["color_name"] = $row["print_tmpt_name"];
				$infos["quantity"] = $row["amt"];
				$infos["quantity_unit"] = $row["amt_unit_dvs"];
				$infos["paper_code"] = $row["cate_paper_mpcode"];
				$infos["ordered_date"] = $row["order_regi_date"];
				$infos["cate_code"] = $row["sortcode"];
				$infos["cate_name"] = $row["cate_name"];
				$infos["customer_id"] = $row["member_seqno"];
				$infos["customer_name"] = $row["member_name"];
				$infos["paper_name"] = $row["name"];
				$infos["paper_color"] = $row["color"];
				$infos["paper_weight"] = $row["basisweight"];
				$items[] = $infos;
			}
		}

		return $items;
	}

    public function get_customer_info($conn, $customer_seq) {
        $info = array();

        $query = "select id, member_name, manual_yn from member where member_seqno = '$customer_seq'";
        $result = mysqli_query($conn, $query);
        if ($result !== false) {
            while ($row = mysqli_fetch_assoc($result)) {
                $info["id"] = $row["id"];
                $info["name"] = $row["member_name"];
                $info["manual_yn"] = $row["manual_yn"];
            }
        }

        return $info;
    }

    public function get_accept_works_for_retouch($conn) {
        $works = array();

        $query = "select a.accept_id, a.accept_typ, b.order_num, b.order_detail, b.cate_sortcode, b.count, c.work_size_wid, c.work_size_vert, c.side_dvs, d.file_path, d.save_file_name, e.id, e.member_name
from accept_work as a
inner join order_common as b on a.order_id = b.order_num
inner join order_detail as c on b.order_common_seqno = c.order_common_seqno
inner join order_file as d on b.order_common_seqno = d.order_common_seqno
inner join member as e on b.member_seqno = e.member_seqno
where (a.accept_typ = '31' or a.accept_typ = '32') and a.accept_result is null";
        $result = mysqli_query($conn, $query);
        if ($result !== false) {
            while ($row = mysqli_fetch_assoc($result)) {
                $infos = array();
                $infos["accept_id"] = $row["accept_id"];
                $infos["accept_type"] = $row["accept_typ"];
                $infos["order_id"] = $row["order_num"];
                $infos["order_detail"] = $row["order_detail"];
                $infos["cate_code"] = $row["cate_sortcode"];
                $infos["customer_id"] = $row["id"];
                $infos["customer_name"] = $row["member_name"];
                $infos["bleed_width"] = $row["work_size_wid"];
                $infos["bleed_height"] = $row["work_size_vert"];
                $infos["side_dvs"] = $row["side_dvs"];
                $infos["item_count"] = $row["count"];
                $infos["file_path"] = $row["file_path"];
                $infos["file_name"] = $row["save_file_name"];
                $works[] = $infos;
            }
        }

        return $works;
    }

    public function get_accept_works_for_manual($conn, $worker_id, $accept_types, $limit_count) {
        $works = array();

        $accept_type_string = "";
        foreach ($accept_types as $accept_type) {
            if (empty($accept_type_string) === true)
                $accept_type_string = "a.accept_typ in ($accept_type";
            else
                $accept_type_string = $accept_type_string . ", $accept_type";
        }
        $accept_type_string = $accept_type_string . ")";

		$query = "";
		if ($limit_count === -1) {
			$query = "select a.accept_id, a.order_id, a.accept_typ, a.worker_id
from accept_work as a
inner join accept_item as b on b.accept_id = a.accept_id
where $accept_type_string and (a.worker_id is null or a.worker_id = '$worker_id') and a.accept_result is null and b.state in ('00', '11', '12') group by a.accept_id order by a.accept_id asc";
		} else {
			$query = "select a.accept_id, a.order_id, a.accept_typ, a.worker_id
from accept_work as a
inner join accept_item as b on b.accept_id = a.accept_id
where $accept_type_string and (a.worker_id is null or a.worker_id = '$worker_id') and a.accept_result is null and b.state in ('00', '11', '12') group by a.accept_id order by a.accept_id asc limit $limit_count";
		}
        $result = mysqli_query($conn, $query);
        if ($result !== false) {
            while ($row = mysqli_fetch_assoc($result)) {
                $infos = array();
                $infos["accept_id"] = $row["accept_id"];
                $infos["accept_type"] = $row["accept_typ"];
                $infos["order_id"] = $row["order_id"];
                $infos["worker_id"] = $row["worker_id"];
                $works[] = $infos;
            }
        }

        return $works;
    }

    public function get_ready_accept_works($conn, $order_ids, $worker_id, $accept_types) {
		$works = array();

        $order_ids_string = "";
        foreach ($order_ids as $order_id) {
            if (empty($order_ids_string) === true)
                $order_ids_string = "order_id in ('$order_id'";
            else
                $order_ids_string = $order_ids_string . ", '$order_id'";
        }
        $order_ids_string = $order_ids_string . ")";

        $accept_type_string = "";
        foreach ($accept_types as $accept_type) {
            if (empty($accept_type_string) === true)
                $accept_type_string = "accept_typ in ($accept_type";
            else
                $accept_type_string = $accept_type_string . ", $accept_type";
        }
        $accept_type_string = $accept_type_string . ")";

        $query = "select accept_id, order_id, accept_typ, worker_id
from accept_work
where $order_ids_string and $accept_type_string and (worker_id is null or worker_id = '$worker_id') and accept_result is null
group by order_id
order by accept_id asc";
		$result = mysqli_query($conn, $query);
		if ($result !== false) {
			while ($row = mysqli_fetch_assoc($result)) {
				$work = array();
				$work["accept_id"] = $row["accept_id"];
				$work["accept_type"] = $row["accept_typ"];
				$work["order_id"] = $row["order_id"];
				$work["worker_id"] = $row["worker_id"];
				$works[] = $work;
			}
		}

        return $works;
    }

    public function get_last_accept_work($conn, $order_id) {
        $infos = array();

        $query = "select accept_id, accept_typ, accept_result, accept_report from accept_work where order_id = '$order_id' order by accept_id desc limit 1";
        $result = mysqli_query($conn, $query);
        if ($result !== false) {
            if ($row = mysqli_fetch_assoc($result)) {
                $infos["accept_id"] = $row["accept_id"];
                $infos["accept_type"] = $row["accept_typ"];
                $infos["accept_result"] = $row["accept_result"];
                $infos["accept_report"] = $row["accept_report"];
            }
        }

        return $infos;
    }

    public function get_prev_accept_work($conn, $order_id) {
        $infos = array();

        $query = "select accept_id, accept_typ, accept_result, accept_report from accept_work where order_id = '$order_id' order by accept_id asc limit 2";
        $result = mysqli_query($conn, $query);
        if ($result !== false) {
            if ($row = mysqli_fetch_assoc($result)) {
                $infos["accept_id"] = $row["accept_id"];
                $infos["accept_type"] = $row["accept_typ"];
                $infos["accept_result"] = $row["accept_result"];
                $infos["accept_report"] = $row["accept_report"];
            }
        }

        return $infos;
    }

    public function get_accept_work($conn, $accept_id) {
        $infos = array();

        $query = "select order_id, accept_typ, accept_title, accept_memo, accept_result, accept_report from accept_work where accept_id = '$accept_id'";
        $result = mysqli_query($conn, $query);
        if ($result !== false) {
            if ($row = mysqli_fetch_assoc($result)) {
                $infos["order_id"] = $row["order_id"];
                $infos["accept_type"] = $row["accept_typ"];
				$infos["accept_title"] = $row["accept_title"];
				$infos["accept_memo"] = $row["accept_memo"];
                $infos["accept_result"] = $row["accept_result"];
                $infos["accept_report"] = $row["accept_report"];
            }
        }

        return $infos;
    }

    public function get_worker_id_for_accept($conn, $accept_id) {
        $worker_id = "";

        $query = "select worker_id from accept_work where accept_id = '$accept_id'";
        $result = mysqli_query($conn, $query);
        if ($result !== false) {
            if ($row = mysqli_fetch_assoc($result)) {
                $worker_id = $row["worker_id"];
            }
        }

        return $worker_id;
    }

	public function get_worker_info($conn, $worker_id) {
		$infos = array();

		$query = "select a.empl_id, b.name, a.depar_code
from empl as a
inner join empl_human_info as b on a.empl_seqno = b.empl_seqno
where a.empl_id = '$worker_id'";
		$result = mysqli_query($conn, $query);
		if ($result !== false) {
			if ($row = mysqli_fetch_assoc($result)) {
				$infos["worker_id"] = $row["empl_id"];
				$infos["worker_name"] = $row["name"];
				$infos["dept_code"] = $row["depar_code"];
			}
		}

		return $infos;
	}

    public function get_accept_work_detail($conn, $accept_id) {
        $infos = array();

        $query = "select a.order_id, a.accept_typ, a.accept_result, a.accept_report, a.worker_id, min(b.date) as started_date, max(b.date) as finished_date
from accept_work as a
left outer join accept_event as b on a.accept_id = b.accept_id
where a.accept_id = '$accept_id'";
        $result = mysqli_query($conn, $query);
        if ($result !== false) {
            if ($row = mysqli_fetch_assoc($result)) {
                $infos["order_id"] = $row["order_id"];
                $infos["accept_type"] = $row["accept_typ"];
                $infos["accept_result"] = $row["accept_result"];
                $infos["accept_report"] = $row["accept_report"];
                $infos["worker_id"] = $row["worker_id"];
                $infos["started_date"] = $row["started_date"];
                $infos["finished_date"] = $row["finished_date"];
            }
        }

        return $infos;
    }

    public function get_accept_type($conn, $accept_id) {
        $accept_type = "";

        $query = "select accept_typ from accept_work where accept_id = '$accept_id'";
        $result = mysqli_query($conn, $query);
        if ($result !== false) {
            if ($row = mysqli_fetch_assoc($result)) {
                $accept_type = $row["accept_typ"];
            }
        }

        return $accept_type;
    }

    public function get_imcomplete_accept_id($conn, $worker_id) {
        $query = "select accept_id from accept_work where worker_id = '$worker_id' and accept_typ = 1 and  order by accept_id desc limit 1";
        $result = mysqli_query($conn, $query);
        if ($result !== false) {
            if ($row = mysqli_fetch_assoc($result)) {
                array_push($infos, $row["accept_id"]);
                array_push($infos, $row["accept_typ"]);
                array_push($infos, $row["accept_result"]);
            }
        }
    }

    public function get_ready_accept_id_for_auto($conn) {
        $accept_id = "";

        $query = "select accept_id from accept_work where worker_id is null and (accept_typ = 11 or accept_typ = 12) order by accept_id asc limit 1";
        $result = mysqli_query($conn, $query);
            if ($row = mysqli_fetch_assoc($result)) {
                $accept_id = $row["accept_id"];
            }

        return $accept_id;
    }

    public function get_ready_accept_info_for_auto($conn) {
        $infos = array();

        $query = "select accept_id, accept_typ from accept_work where worker_id is null and (accept_typ = 11 or accept_typ = 12) order by accept_id asc limit 1";
        $result = mysqli_query($conn, $query);
        if ($row = mysqli_fetch_assoc($result)) {
            $infos["accept_id"] = $row["accept_id"];
            $infos["accept_type"] = $row["accept_typ"];
        }

        return $infos;
    }

    public function get_last_accept_id($conn) {
        $accept_id = "";

        $query = "select accept_id from accept_work order by accept_id desc limit 1";
        $result = mysqli_query($conn, $query);
        if ($result !== false) {
            if ($row = mysqli_fetch_assoc($result)) {
                $accept_id = $row["accept_id"];
            }
        }

        $now = new DateTime();
        $today = $now->format("ymd");
        if ($accept_id === "") {
            $accept_id = $today . "00000";
        } else if (substr($accept_id, 0, 6) !== $today) {
            $accept_id = $today . "00000";
        }

        return $accept_id;
    }

    public function get_accept_result($conn, $accept_id) {
        $accept_result = "";

        $query = "select accept_result from accept_work where accept_id = '$accept_id'";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);
        if ($row !== null) {
            $accept_result = $row["accept_result"];
        }

        return $accept_result;
    }

    public function create_accept_work($conn, $order_id, $accept_type, $order_title) {
        $accept_id = $this->get_last_accept_id($conn);
        $accept_id = strval(((int) $accept_id) + 1);

        $query = "insert into accept_work (accept_id, order_id, accept_typ, accept_title) values ('$accept_id', '$order_id', '$accept_type', '$order_title')";
        $result = mysqli_query($conn, $query);
        if ($result === false) {
            $accept_id = $this->get_last_accept_id($conn);
            $accept_id = strval(((int) $accept_id) + 1);

            $query = "insert into accept_work (accept_id, order_id, accept_typ, accept_title) values ('$accept_id', '$order_id', '$accept_type', '$order_title')";
            $result = mysqli_query($conn, $query);
            if ($result === false)
                return "";
        }

        return $accept_id;
    }

    public function create_accept_items($conn, $accept_id, $item_count, $status) {
        for ($i = 0; $i < $item_count; $i++) {
            $query = "insert into accept_item (accept_id, accept_index, state) values ('$accept_id', $i + 1, '$status')";
            mysqli_query($conn, $query);
        }
    }

    public function update_accept_items_with_status($conn, $accept_id, $status) {
        $accept_item_seqs = array();
        $query = "select seqno
from accept_item
where accept_id = '$accept_id'";
        $result = mysqli_query($conn, $query);
		while ($row = mysqli_fetch_assoc($result)) {
			$accept_item_seqs[] = $row["seqno"];
        }

        if (count($accept_item_seqs) === 0) {
			return false;
		} else {
			$query = "update accept_item
				set state = '$status'
				where accept_id = '$accept_id'";
			mysqli_query($conn, $query);

			return true;
		}
    }

    public function update_accept_item_with_status($conn, $accept_id, $accept_index, $status) {
		$accept_item_seq = "";
        $query = "select seqno
from accept_item
where accept_id = '$accept_id' and accept_index = '$accept_index'";
        $result = mysqli_query($conn, $query);
        if ($result !== false) {
            if ($row = mysqli_fetch_assoc($result)) {
				$accept_item_seq = $row["seqno"];
            }
        }

		if ($accept_item_seq === "") {
			$query = "insert into accept_item (accept_id, accept_index, state)
				value ('$accept_id', '$accept_index', '$status')";
		} else {
			$query = "update accept_item
				set state = '$status'
				where accept_id = '$accept_id' and accept_index = '$accept_index'";
		}
        mysqli_query($conn, $query);
    }

	public function update_accept_item_with_title($conn, $accept_id, $accept_index, $title) {
		$accept_item_seq = "";
		$query = "select seqno
from accept_item
where accept_id = '$accept_id' and accept_index = '$accept_index'";
		$result = mysqli_query($conn, $query);
		if ($result !== false) {
			if ($row = mysqli_fetch_assoc($result)) {
				$accept_item_seq = $row["seqno"];
			}
		}

		if ($accept_item_seq === "") {
			$query = "insert into accept_item (accept_id, accept_index, accept_title)
				value ('$accept_id', '$accept_index', '$title')";
		} else {
			$query = "update accept_item
				set accept_title = '$title'
				where accept_id = '$accept_id' and accept_index = '$accept_index'";
		}
		mysqli_query($conn, $query);
	}

	public function update_accept_item_with_memo($conn, $accept_id, $accept_index, $memo) {
		$accept_item_seq = "";
		$query = "select seqno
from accept_item
where accept_id = '$accept_id' and accept_index = '$accept_index'";
		$result = mysqli_query($conn, $query);
		if ($result !== false) {
			if ($row = mysqli_fetch_assoc($result)) {
				$accept_item_seq = $row["seqno"];
			}
		}

		if ($accept_item_seq === "") {
			$query = "insert into accept_item (accept_id, accept_index, accept_memo)
				value ('$accept_id', '$accept_index', '$memo')";
		} else {
			$query = "update accept_item
				set accept_memo = '$memo'
				where accept_id = '$accept_id' and accept_index = '$accept_index'";
		}
		mysqli_query($conn, $query);
	}

	public function get_accept_items($conn, $statuses) {
		$items = array();

		$accept_status_string = "";
		foreach ($statuses as $status) {
			if (empty($accept_status_string) === true)
				$accept_status_string = "a.state in ($status";
			else
				$accept_status_string = $accept_status_string . ", $status";
		}
		$accept_status_string = $accept_status_string . ")";

		$query = "select a.accept_id, a.accept_index, b.accept_typ, c.date, a.state
from accept_item as a
inner join accept_work as b on a.accept_id = b.accept_id
inner join accept_event as c on a.accept_id = c.accept_id and a.accept_index = c.accept_index
where $accept_status_string";
		$result = mysqli_query($conn, $query);
		while ($row = mysqli_fetch_assoc($result)) {
			$infos = array();
			$infos["accept_id"] = $row["accept_id"];
			$infos["accept_index"] = $row["accept_index"];
			$infos["accept_typ"] = $row["accept_typ"];
			$infos["started_date"] = $row["date"];
			$infos["status_code"] = $row["state"];
			$items[] = $infos;
		}

		return $items;
	}

    public function get_accept_items_in_prepress($conn) {
        $items = array();

        $query = "select a.accept_id, a.accept_index, b.accept_typ, d.cate_sortcode, c.date
from accept_item as a
inner join accept_work as b on a.accept_id = b.accept_id
inner join accept_event as c on a.accept_id = c.accept_id and a.accept_index = c.accept_index
inner join order_common as d on b.order_id = d.order_num
where a.state = '21' and c.typ = '31'";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $infos = array();
            $infos["accept_id"] = $row["accept_id"];
            $infos["accept_index"] = $row["accept_index"];
            $infos["accept_typ"] = $row["accept_typ"];
            $infos["cate_sortcode"] = $row["cate_sortcode"];
            $infos["date"] = $row["date"];
            $items[] = $infos;
        }

        return $items;
    }

    public function get_accept_items_with_prepress_done($conn) {
        $items = array();

        $query = "select a.accept_id, a.accept_index, b.accept_typ, c.cate_sortcode, c.count, e.id, e.member_name, d.side_dvs
from accept_item as a
inner join accept_work as b on a.accept_id = b.accept_id
inner join order_common as c on b.order_id = c.order_num
inner join order_detail as d on c.order_common_seqno = d.order_common_seqno
inner join member as e on c.member_seqno = e.member_seqno
where a.state = '22'";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $infos = array();
            $infos["accept_id"] = $row["accept_id"];
            $infos["accept_index"] = $row["accept_index"];
            $infos["accept_typ"] = $row["accept_typ"];
            $infos["cate_sortcode"] = $row["cate_sortcode"];
            $infos["customer_id"] = $row["id"];
            $infos["customer_name"] = $row["member_name"];
            $infos["side_dvs"] = $row["side_dvs"];
            $infos["item_count"] = $row["count"];
            // option info needed
            $items[] = $infos;
        }

        return $items;
    }

    public function get_accept_items_with_qc_ready($conn) {
        $items = array();

        $query = "select a.accept_id, a.accept_index, b.accept_typ, c.cate_sortcode, c.count, e.id, e.member_name, d.side_dvs
from accept_item as a
inner join accept_work as b on a.accept_id = b.accept_id
inner join order_common as c on b.order_id = c.order_num
inner join order_detail as d on c.order_common_seqno = d.order_common_seqno
inner join member as e on c.member_seqno = e.member_seqno
where a.state = '31' or a.state = '32'";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $infos = array();
            $infos["accept_id"] = $row["accept_id"];
            $infos["accept_index"] = $row["accept_index"];
            $infos["accept_typ"] = $row["accept_typ"];
            $infos["cate_sortcode"] = $row["cate_sortcode"];
            $infos["customer_id"] = $row["id"];
            $infos["customer_name"] = $row["member_name"];
            $infos["side_dvs"] = $row["side_dvs"];
            $infos["item_count"] = $row["count"];
            // option info needed
            $items[] = $infos;
        }

        return $items;
    }

    public function get_accept_status_list_for_accept_items($conn, $accept_id) {
        $status_list = array();

        $query = "select accept_index, state
from accept_item
where accept_id = '$accept_id'";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $infos = array();
            $infos["accept_index"] = $row["accept_index"];
            $infos["status"] = $row["state"];
            $status_list[] = $infos;
        }

        return $status_list;
    }

    public function update_accept_work_with_accept_result($conn, $accept_id, $accept_result, $accept_report) {
        $query = "update accept_work
				set accept_result = '$accept_result'
				where accept_id = '$accept_id'";
        mysqli_query($conn, $query);

        if ($accept_report !== "") {
            $query = "update accept_work
				set accept_report = '$accept_report'
				where accept_id = '$accept_id'";
            mysqli_query($conn, $query);
        }
    }

    public function update_accept_work_with_worker_id($conn, $accept_id, $worker_id) {
        $query = "update accept_work
				set worker_id = '$worker_id'
				where accept_id = '$accept_id'";
        if (mysqli_query($conn, $query) === false) {
            return false;
        }

        return true;
    }

	public function update_accept_work_with_title($conn, $accept_id, $title) {
		$query = "update accept_work
				set accept_title = '$title'
				where accept_id = '$accept_id'";
		if (mysqli_query($conn, $query) === false) {
			return false;
		}

		return true;
	}

	public function update_accept_work_with_memo($conn, $accept_id, $memo) {
		$query = "update accept_work
				set accept_memo = '$memo'
				where accept_id = '$accept_id'";
		if (mysqli_query($conn, $query) === false) {
			return false;
		}

		return true;
	}

    public function add_accept_event($conn, $accept_id, $accept_index, $type) {
        $query = "insert into accept_event (accept_id, accept_index, typ)
				values ('$accept_id', $accept_index, '$type')";
        if (mysqli_query($conn, $query) === false) {
            return false;
        }

        return true;
    }

	public function get_plates($conn, $type, $start_date, $end_date) {
		$plates = array();

		$query = "select typset_num, state, regi_date, (SELECT count(*) FROM amt_order_detail_sheet WHERE sheet_typset_seqno = sheet_typset_seqno) AS item_count
from sheet_typset
where typset_way = '$type' between '$start_date' and '$end_date 23:59:59'";
		$result = mysqli_query($conn, $query);
		mysqli_fetch_assoc($result);
		while ($row = mysqli_fetch_assoc($result)) {
			$infos = array();
			$infos["plate_id"] = $row["typset_num"];
			$infos["item_count"] = $row["item_count"];
			$infos["status_code"] = $row["state"];
			$infos["created_date"] = $row["regi_date"];
			$plates[] = $infos;
		}

		return $plates;
	}

	public function get_last_plate_id($conn, $imp_method) {
		$plate_id = "";
		if ($imp_method === "COMMERCIAL") {
			$date_like = substr(date("Ymd"), 2) . "_%";
			$query = "select typset_num
from sheet_typset
where typset_num like '$date_like' and typset_way = 'COMMERCIAL' order by sheet_typset_seqno asc limit 1";
			$result = mysqli_query($conn, $query);
			if ($result !== false) {
				if ($row = mysqli_fetch_assoc($result)) {
					$plate_id = $row["paper_op_seqno"];
				}
			}
		}

		return $plate_id;
	}

	public function add_plate($conn, $plate_id, $plate_size, $paper_name, $quantity, $front_side_color_count, $back_side_color_count, $imp_method, $plate_class, $plate_title, $print_house, $memo) {
		$query = "insert into sheet_typset (typset_num, size, paper_name, print_amt, print_amt_unit, prdt_page, prdt_page_dvs, beforeside_tmpt, aftside_tmpt, typset_way, dlvrboard, state, print_title, print_etprs, memo)
				values ('$plate_id', '$plate_size', '$paper_name', '$quantity', '장', '2', '낱장', $front_side_color_count, $back_side_color_count, '$imp_method', '$plate_class', '2120', '$plate_title', '$print_house', '$memo')";
		if (mysqli_query($conn, $query) === false) {
			return false;
		}

		return true;
	}

	public function update_plate_items_with_plate_id($conn, $plate_item_ids, $plate_id) {
		$plate_item_ids_string = "";
		foreach ($plate_item_ids as $plate_item_id) {
			if (empty($plate_item_ids_string) === true)
				$plate_item_ids_string = "order_detail_count_file_seqno in ($plate_item_id";
			else
				$plate_item_ids_string = $plate_item_ids_string . ", $plate_item_id";
		}
		$plate_item_ids_string = $plate_item_ids_string . ")";

		$plate_seqno = "";
		$query = "select sheet_typset_seqno
from sheet_typset
where typset_num = '$plate_id'";
		$result = mysqli_query($conn, $query);
		if ($result !== false) {
			if ($row = mysqli_fetch_assoc($result)) {
				$plate_seqno = $row["sheet_typset_seqno"];
			}
		}

		$query = "update amt_order_detail_sheet
		 set sheet_typset_seqno = $plate_seqno
		 where $plate_item_ids_string";
		if (mysqli_query($conn, $query) === false) {
			return false;
		}

		return true;
	}

	public function update_plate_items_with_status($conn, $plate_item_ids, $status) {
		$plate_item_ids_string = "";
		foreach ($plate_item_ids as $plate_item_id) {
			if (empty($plate_item_ids_string) === true)
				$plate_item_ids_string = "order_detail_count_file_seqno in ($plate_item_id";
			else
				$plate_item_ids_string = $plate_item_ids_string . ", $plate_item_id";
		}
		$plate_item_ids_string = $plate_item_ids_string . ")";

		$query = "update amt_order_detail_sheet
		 set state = '$status'
		 where $plate_item_ids_string";
		if (mysqli_query($conn, $query) === false) {
			return false;
		}

		return true;
	}

    public function get_all_papers($conn) {
        $papers = array();

        $query = "select distinct(concat(name, ' ', color, ' ', basisweight)) as paper from cate_paper";
        $result = mysqli_query($conn, $query);
        mysqli_fetch_assoc($result);
        while ($row = mysqli_fetch_assoc($result)) {
            $papers[] = $row["paper"];
        }

        return $papers;
    }

    public function get_paper_orders($conn, $start_date, $end_date) {
        $orders = array();

        $query = "select a.*, b.etprs_name
        from paper_op as a
        left outer join extnl_etprs as b on a.extnl_brand_seqno = b.extnl_etprs_seqno
        where a.regi_date between '$start_date' and '$end_date 23:59:59'";
        $result = mysqli_query($conn, $query);
		mysqli_fetch_assoc($result);
		while ($row = mysqli_fetch_assoc($result)) {
			$infos = array();
			$infos["paper_op_seqno"] = $row["paper_op_seqno"];
			$infos["etprs_name"] = $row["etprs_name"];
			$infos["name"] = $row["name"];
			$infos["color"] = $row["color"];
			$infos["basisweight"] = $row["basisweight"];
			$infos["op_size"] = $row["op_size"];
			$infos["stor_size"] = $row["stor_size"];
			$infos["grain"] = $row["grain"];
			$infos["amt"] = $row["amt"];
			$infos["warehouser"] = $row["warehouser"];
			$infos["memo"] = $row["memo"];
			$infos["op_degree"] = $row["op_degree"];
			$orders[] = $infos;
		}

        return $orders;
    }

    public function get_last_paper_order_id($conn) {
        $order_id = -1;

        $query = "select paper_op_seqno
        from paper_op order by paper_op_seqno desc limit 1";
        $result = mysqli_query($conn, $query);
        if ($result !== false) {
            if ($row = mysqli_fetch_assoc($result)) {
                $order_id = $row["paper_op_seqno"];
            }
        }

        return $order_id;
    }

    public function add_paper_order($conn, $paper_mill, $paper_info, $paper_size_1, $paper_size_2, $paper_grain, $quantity, $print_house, $sequence, $memo) {
        $paper_info_part = explode(" ", $paper_info);

        $query = "insert into paper_op (typset_num, extnl_brand_seqno, name, color, basisweight, op_size, stor_size, grain, amt, amt_unit, warehouser, op_degree, memo, op_date)
				values ('', (select extnl_etprs_seqno from extnl_etprs where etprs_name = '$paper_mill'), '$paper_info_part[0]', '$paper_info_part[1]', '$paper_info_part[2]', '$paper_size_1', '$paper_size_2', '$paper_grain', '$quantity', '장', '$print_house', '$sequence', '$memo', date('Y-m-d H:i:s'))";
        if (mysqli_query($conn, $query) === false) {
            return false;
        }

        return true;
    }

	public function update_paper_order_with_paper_mill($conn, $order_id, $paper_mill) {
		$query = "update paper_op
		 set extnl_brand_seqno = (select extnl_etprs_seqno from extnl_etprs where etprs_name = '$paper_mill')
		 where paper_op_seqno = '$order_id'";
		if (mysqli_query($conn, $query) === false) {
			return false;
		}

		return true;
	}

	public function update_paper_order_with_paper_info($conn, $order_id, $paper_info) {
		$paper_info_part = explode(" ", $paper_info);

		$query = "update paper_op
		 set name = '$paper_info_part[0]', color = '$paper_info_part[1]', basisweight = '$paper_info_part[2]'
		 where paper_op_seqno = '$order_id'";
		if (mysqli_query($conn, $query) === false) {
			return false;
		}

		return true;
	}

	public function update_paper_order_with_paper_size_1($conn, $order_id, $paper_size_1) {
		$query = "update paper_op
		 set op_size = '$paper_size_1'
		 where paper_op_seqno = '$order_id'";
		if (mysqli_query($conn, $query) === false) {
			return false;
		}

		return true;
	}

	public function update_paper_order_with_paper_size_2($conn, $order_id, $paper_size_2) {
		$query = "update paper_op
		 set stor_size = '$paper_size_2'
		 where paper_op_seqno = '$order_id'";
		if (mysqli_query($conn, $query) === false) {
			return false;
		}

		return true;
	}

	public function update_paper_order_with_paper_grain($conn, $order_id, $paper_grain) {
		$query = "update paper_op
		 set grain = '$paper_grain'
		 where paper_op_seqno = '$order_id'";
		if (mysqli_query($conn, $query) === false) {
			return false;
		}

		return true;
	}

	public function update_paper_order_with_quantity($conn, $order_id, $quantity) {
		$query = "update paper_op
		 set amt = '$quantity'
		 where paper_op_seqno = '$order_id'";
		if (mysqli_query($conn, $query) === false) {
			return false;
		}

		return true;
	}

	public function update_paper_order_with_print_house($conn, $order_id, $print_house) {
		$query = "update paper_op
		 set warehouser = '$print_house'
		 where paper_op_seqno = '$order_id'";
		if (mysqli_query($conn, $query) === false) {
			return false;
		}

		return true;
	}

	public function update_paper_order_with_sequence($conn, $order_id, $sequence) {
		$query = "update paper_op
		 set op_degree = '$sequence'
		 where paper_op_seqno = '$order_id'";
		if (mysqli_query($conn, $query) === false) {
			return false;
		}

		return true;
	}

	public function update_paper_order_with_memo($conn, $order_id, $memo) {
		$query = "update paper_op
		 set memo = '$memo'
		 where paper_op_seqno = '$order_id'";
		if (mysqli_query($conn, $query) === false) {
			return false;
		}

		return true;
	}

	public function update_paper_order_with_state($conn, $order_id) {
		$query = "update paper_op
		 set state = 'del'
		 where paper_op_seqno = '$order_id'";
		if (mysqli_query($conn, $query) === false) {
			return false;
		}

		return true;
	}

    public function test_add_order_to_group($conn, $order_seq, $group_num, $order_num) {
        $query = "insert into onefile_order_group (order_common_seqno, group_num, order_num)
				values ('$order_seq', '$group_num', '$order_num')";
        if (mysqli_query($conn, $query) === false) {
            return false;
        }

        return true;
    }

    public function test($conn) {
        $query = "select a.accept_id, a.order_id, c.save_file_name
from accept_work as a
inner join order_common as b on a.order_id = b.order_num
inner join order_file as c on b.order_common_seqno = c.order_common_seqno
where a.accept_typ = '21' and c.save_file_name like '%.sit'";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $aa = $row["accept_id"];

            $query = "update accept_work
				set accept_typ = '22'
				where accept_id = '$aa'";
            mysqli_query($conn, $query);
        }
    }

}

?>