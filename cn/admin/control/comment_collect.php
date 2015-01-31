<?php
//评论采集
defined('InShopNC') or exit('Access Invalid!');

class comment_collectControl extends SystemControl
{
    public function __construct()
    {
        parent::__construct();
        
    }
    
    public function commentOp()
    {
        $goods_id = intval($_REQUEST['goods_id']);
        $store_id = intval($_REQUEST['store_id']);
        if (empty($_REQUEST['goods_id'])) {
            showMessage($lang['goods_edit_goods_null']);
        }
        
        Tpl::output('goods_id', $goods_id);
        Tpl::showpage('comment');
        
    }
    
    
    public function comment_previewOp()
    {
        $goods_id              = intval($_REQUEST['goods_id']);
        $taourl                = $_POST['taourl'];
        $keywords              = $_POST['keyword'];
        $max_num               = $_POST['maxNum'];
        $GetNum                = $_POST['GetNum'];
        $_SESSION['TimeSplit'] = $_POST['TimeSplit'];
        if (empty($taourl)) {
            showMessage("天猫商品地址不能为空");
        }
        if (stristr($taourl, ".taobao.com")) {
            $GetGoods_ID    = $this->GetGoodsID($taourl);
            $Getselerdid_tb = $this->Getselerdid_tb("http://a.m.taobao.com/i$GetGoods_ID.htm?v=1");
            $reviews_url    = "http://rate.taobao.com/detail_rate.htm?userNumId=$Getselerdid_tb&auctionNumId=$GetGoods_ID&showContent=1&currentPage=1&ismore=0&siteID=7";
        } else {
            $GetGoods_ID = $this->GetGoodsID($taourl);
            $selerdid    = $this->Getselerdid($taourl);
            $reviews_url = "http://rate.tmall.com/list_detail_rate.htm?itemId=$GetGoods_ID&spuId=0&sellerId=$selerdid&order=1";
        }
        $page = $GetNum;
        
        for ($i = 1; $i <= $page; $i++) {
            $pageContents = '';
            $reviews_url  = str_replace('currentPage', '', $reviews_url);
            $reviews_url  = $reviews_url . "&currentPage=$i";
            $pageContents = file_get_contents($reviews_url);
            $pageContents = iconv('GB2312', 'UTF-8', $pageContents);
            preg_match_all('/,\"rateContent\"\:\"(.*?)\",\"/i', $pageContents, $match1);
            preg_match_all('/displayUserNick\"\:\"(.*?)\",\"/i', $pageContents, $match2);
            preg_match_all('/rateDate\"\:\"(.*?)\",\"/i', $pageContents, $match3);
            $comment_list[] = $match1[1];
            $user_list[]    = $match2[1];
            $dateList[]     = $match3[1];
        }
        
        
        $comment_list_temp = array();
        $user_list_temp    = array();
        $dateList_temp     = array();
        foreach ($comment_list as $key => $val) {
            foreach ($val as $k => $v) {
                $comment_list_temp[$user_list[$key][$k]] = $v;
                $dateList_temp[$user_list[$key][$k]]     = $dateList[$key][$k];
            }
        }
        $comment_list = $comment_list_temp;
        $dateList     = $dateList_temp;
        $comments     = array();
        $i            = 0;
        foreach ($comment_list as $key => $val) {
            if ($i >= $max_num) {
                continue;
            }
            if (!empty($keywords)) {
                if (strpos($val, $keywords) == false) {
                    continue;
                }
            }
            $comments[$key]['comment_type'] = 0;
            $comments[$key]['id_value']     = $goods_id;
            $comments[$key]['email']        = '';
            $comments[$key]['user_name']    = $key;
            $comments[$key]['content']      = $val;
            $rank                           = mt_rand(4, 5);
            $comments[$key]['comment_rank'] = $rank;
            if (isset($dateList[$key])) {
                $time = strtotime(str_replace('.', '-', $dateList[$key]));
            } else {
                $time = gmtime();
            }
            $time = $time - 87591;
            
            $comments[$key]['add_time'] = $time;
            
            $comments[$key]['status']      = 1;
            $comments[$key]['parent_id']   = 0;
            $comments[$key]['user_id']     = 0;
            $comments[$key]['goods_name']  = "$goods_name";
            $comments[$key]['format_time'] = $time;
            $comments[$key]['id']          = $i;
            $i++;
        }
        $arrdata['result'] = 'success';
        $arrdata['data']   = $comments;
        $arrdata['count']  = count($comments);
        if ($arrdata['result'] == 'error') {
            exit($arrdata['data']);
        }
        if ($arrdata['result'] == 'success') {
            
            
            $_SESSION['comment_arrdata'] = $arrdata;
            Tpl::output('comment_list', $arrdata['data']);
        }
        Tpl::output('store_id', $store_id);
        Tpl::output('goods_id', $goods_id);
        Tpl::output('goods_name', $goods_name);
        Tpl::output('c_count', $arrdata['count']);
        Tpl::output('c_goods', '<a href="../goods.php?id=' . $goods_id . '" target="_blank" >查看商品</a>');
        Tpl::output('source_url', $arrdata['source_url']);
        Tpl::output('c_manage', "<a href='comment_manage.php?act=list' target='_blank' >进入评论后台管理</a>");
        Tpl::showpage('comment_preview');
        
        
    }
    
    public function comment_batch_importOp()
    {
        $ids     = isset($_POST['checkboxes']) ? $_POST['checkboxes'] : array(); //是否全选
        $arrdata = $_SESSION['comment_arrdata'];
        if ($arrdata['result'] == 'error') {
            exit($arrdata['data']);
        }
	
        if ($arrdata['result'] == 'success') {
			
	
			
            $array_name   = array();
            $model_member = Model();
            $names        = $model_member->field('member_name')->table('member')->select();
		 
            for ($i = 0; $i < count($names); $i++) {
                $array_name[] = $names[$i]['member_name'];
            }
            
            $order_snnew = Model();
            $order_snnew = $order_snnew->table('goods')->where(array(
                'goods_id' => $_POST['goods_id']
            ))->find();
            
            $geval_goodsname  = $order_snnew['goods_name'];
            $geval_goodsprice = $order_snnew['goods_price'];
            $geval_storeid    = $order_snnew['store_id'];
			$geval_commentnum   = $order_snnew['evaluation_count'];
			$geval_salenum    = $order_snnew['goods_salenum'];
            $bian             = 0;
            
            foreach ($arrdata['data'] as $comment) {
						//判断评价是否存在
			$geval_check=Model();
			$geval_check      = $geval_check->table('evaluate_goods')->where(array(
                        'geval_frommembername' => $_POST['user_name'][$comment['id']], 'geval_content' => $_POST['content'][$comment['id']]
                    ))->find();
				if ($geval_check )  showMessage('评价已存在','index.php?act=evaluate&op=index'); 				
                if (in_array($comment['id'], $ids)) { //选中的做循环入库
                    $bian++;
                    
                    date_default_timezone_set("PRC");
                    
                    
                    //添加会员信息
                    $upmember_arr                = array();
                    $upmember_arr['member_name'] = $_POST['user_name'][$comment['id']];
				
                    if (in_array($_POST['user_name'][$comment['id']], $array_name)) {
                        $upmember_arr['member_name'] =$_POST['user_name'][$comment['id']] . "_" . time(); //会员姓名修改                             
                    }
                    
                    $upmember_arr['member_credit'] = rand(1, 555);
                    $upmember_arr['member_state']  = "0"; //关闭会员所有状态
                    $upmember_arr['is_buy']        = "0";
                    $upmember_arr['is_allowtalk']  = "0";
                    $model_upmember                = Model();
                    $model_upmember->table('member')->insert($upmember_arr);
                    
                    $member_id = $model_upmember->table('member')->field('member_id')->where(array(
                        'member_name' =>$upmember_arr['member_name']
                    ))->find();
                    $memberstore_id = $model_upmember->table('member')->field('member_id')->where(array(
                        'store_id' => $geval_storeid
                    ))->find();
                    
                    //销售订单表
					$model_uporder=Model();
                    $uporder_arr                      = array();
                    $uporder_arr['order_sn']          = $_POST['goods_id'].$bian;

                    $uporder_arr['store_id']          = $geval_storeid;
                    $uporder_arr['buyer_id']          = $member_id['member_id'];
                    $uporder_arr['buyer_name']        = $upmember_arr['member_name'];
                    $uporder_arr['add_time']          = $_POST['add_time'][$comment['id']] - (mt_rand(3,$_POST['buy_time']) * 24 * 3600); //随机3-X天评价
                    $uporder_arr['evaluation_state'] = "0";
                    $uporder_arr['order_state']       = "40";
                    $model_uporder->table('order')->insert($uporder_arr);
                    //销售订单商品表				
                    $neworder_id                     = $model_upmember->table('order')->field('order_id')->where(array(
                        'order_sn' => $_POST['goods_id'] . $bian
                    ))->find();
                    $upordergoods_arr                = array();
                    $upordergoods_arr['order_id']    = $neworder_id['order_id'];
                    $upordergoods_arr['goods_id']    = $_POST['goods_id'];
                   
					 $gailv=mt_rand(1, $_POST['buy_num']*10);
                     if($gailv<= $_POST['buy_num']*8) $upordergoods_arr['goods_num']=1;        //购买1件为80%概率
                     $upordergoods_arr['goods_price'] = $geval_goodsprice*$upordergoods_arr['goods_num'];
                   
                    $model_uporder->table('order_goods')->insert($upordergoods_arr);
                    //评价信息
					
					
					
                    $upgeval_arr                     = array();
                    
                    $upgeval_arr['geval_orderid']        = $neworder_id['order_id'];
                    $upgeval_arr['geval_orderno']        = $_POST['goods_id'] . $bian; //刷的评价订单号为商品编号加变量。
                    $upgeval_arr['geval_goodsid']        = $_POST['goods_id']; //商品id
                    $upgeval_arr['geval_goodsname']      = $geval_goodsname;
                    $upgeval_arr['geval_goodsprice']     = $geval_goodsprice;
                    $upgeval_arr['geval_scores']         = $_POST['comment_rank'][$comment['id']]; //好评分数
                    $upgeval_arr['geval_content']        = $_POST['content'][$comment['id']]; //评价内容	                    
                    $upgeval_arr['geval_addtime']        = $_POST['add_time'][$comment['id']]; //评价时间
                    $upgeval_arr['geval_storeid']        = $geval_storeid; //店铺ID
                    $upgeval_arr['geval_frommemberid']   = $member_id['member_id'];
                    $upgeval_arr['geval_frommembername'] = $upmember_arr['member_name'];
                    $upgeval_arr['geval_state']          = "0";
                    $upgeval_arr['geval_remark']         = "蓝色计划"; //刷信用备注
                     
                    $model_upgeval                       = Model();
					
                    $model_upgeval->table('evaluate_goods')->insert($upgeval_arr);
					
					
					//店铺评分
					
					               $upgeval_arr                     = array();
                    
                    $upgeval_arr['seval_orderid']        = $neworder_id['order_id'];
                    $upgeval_arr['seval_orderno']        = $_POST['goods_id'] . $bian; //刷的评价订单号为商品编号加变量。                       
                    $upgeval_arr['seval_addtime']        = $_POST['add_time'][$comment['id']]; //评价时间
                    $upgeval_arr['seval_storeid']        = $geval_storeid; //店铺ID
                    $upgeval_arr['seval_memberid']   = $member_id['member_id'];
                    $upgeval_arr['seval_membername'] = $upmember_arr['member_name'];
                    $upgeval_arr['seval_desccredit']          = $_POST['comment_rank'][$comment['id']];  
					$upgeval_arr['seval_servicecredit'] = $_POST['comment_rank'][$comment['id']] ;
                    $upgeval_arr['seval_deliverycredit']          =$_POST['comment_rank'][$comment['id']] ;                     
                    $model_upgeval                       = Model();
					
                    $model_upgeval->table('evaluate_store')->insert($upgeval_arr);
					
					
					
                     //增加店铺评分  这里刷信用所以默认为5
					 $store_gev=Model();
					  $store_gev= $store_gev->table('store')->where(array(
                       'store_id' =>$geval_storeid))->find();
					   if ($store_gev['store_servicecredit']=="0") {$store_gev['store_servicecredit']=4.8;}
						if ($store_gev['store_servicecredit']=="0") {$store_gev['store_servicecredit']=4.8;} 
						if ($store_gev['store_servicecredit']=="0") {$store_gev['store_servicecredit']=4.8;}
					 $uporder = array();
					 $uporder['store_desccredit']= sprintf("%.2f",($store_gev['store_desccredit']*9+5)/10);
					 $uporder['store_servicecredit']= sprintf("%.2f",($store_gev['store_servicecredit']*9+5)/10);
					 $uporder['store_deliverycredit']= sprintf("%.2f",($store_gev['store_deliverycredit']*9+5)/10);
			     	 $uporder['store_sales']=$store_gev['store_sales']+1;		
                     $model_uporder->table('store')->where(array('store_id'=>$geval_storeid))->update($uporder);
                     $pingjiashu++;
                }
				
				//增加商品评分及销售
				
				     $model_upgoods=Model();
					 $upgoods = array();
                    $upgoods['evaluation_count']=$geval_commentnum+$pingjiashu;
			        $upgoods['goods_salenum']=$geval_salenum +$pingjiashu;
                    $model_upgoods->table('goods')->where(array('goods_id'=>$_POST['goods_id']))->update($upgoods);
            }
        }
        unset($_SESSION['comment_arrdata']);

      
		  showMessage('导入成功','index.php?act=evaluate&op=index');
    }
	
	
    function get_order_sn()
    {
        mt_srand((double) microtime() * 1000000);
        return date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }
    
    function check_user($user_name)
    {
        $sql = "SELECT user_id FROM " . $GLOBALS['ecs']->table("users") . " WHERE user_name='$user_name'";
        $row = $GLOBALS['db']->getOne($sql);
        if ($row > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    private function GetGoodsID($Url)
    {
        $b = (explode("&", $Url));
        foreach ($b as $v) {
            if (stristr($v, "id=")) {
                $str = $v . ">";
                preg_match("/id=(.*)>/", $str, $c);
                $reslt = $c[1];
                return $reslt;
                break;
            }
        }
    }
    private function Getselerdid_tb($Url)
    {
        $tb_content = file_get_contents($Url);
        preg_match("/shop-15-15.png(.*)>进入店铺</a>/", $tb_content, $c);
        preg_match("/http://(.*).m.taobao.com/", $c[0], $a);
        $tb_content = file_get_contents($a[0]);
        preg_match("/name=\"suid\" value=\"(.*)\"/>/", $tb_content, $d);
        $aa = (explode("\"/>", $d[1]));
        return $aa[0];
    }
    private function Getselerdid($Url)
    {
        $tmall_content = file_get_contents($Url);
        preg_match("/sellerId:\"(.*)\",shopId:/", $tmall_content, $c);
        return $c[1];
    }
    
    
}

?>