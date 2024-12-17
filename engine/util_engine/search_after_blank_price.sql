SELECT  T1.mpcode
       ,T1.cate_sortcode
       ,T2.cate_name
       ,T1.after_name
       ,T1.depth1
       ,T1.depth2
       ,T1.depth3
       ,T1.basic_price
       ,T1.sell_rate
       ,T1.sell_aplc_price
       ,T1.sell_price
  FROM  cate AS T2
       ,(SELECT  a.after_name
                ,a.depth1
                ,a.depth2
                ,a.depth3
                ,b.cate_sortcode
                ,b.mpcode
                ,c.basic_price
                ,c.sell_rate
                ,c.sell_aplc_price
                ,c.sell_price
           FROM  prdt_after AS a
                ,cate_after AS b
           LEFT OUTER JOIN cate_after_price AS c
             ON b.mpcode = c.cate_after_mpcode
          WHERE a.prdt_after_seqno = b.prdt_after_seqno
            AND c.basic_price IS NULL
            AND a.after_name != '박'
            AND a.after_name != '형압') AS T1
 WHERE T1.cate_sortcode = T2.sortcode
 ORDER BY T1.cate_sortcode, T2.cate_name