<?php
/**
 * File: tmdpaywx.php
 * Functionality: TMDpay -微信扫码支付
 * Author: 黄枫叶
 * Date: 2019-05-13
 */
namespace Pay\tmdpaywx;
use \Pay\notify;


class tmdpaywx
{
	private $paymethod ="tmdpaywx";
	
	//处理请求
	public function pay($payconfig,$params)
	{
		try
		{
	    $payGateWay= $payconfig['configure3'].'/createOrder';
	    $payIdGateWay= $payconfig['configure3'].'/getOrderPayId';
            $payId =$params['orderid'];
            $type  =1;//微信
            $price =(float)$params['money'];
            $param =$params['productname'];
			$key   =$payconfig['app_secret'];
			$isHtml=0;
            $return_url = $params['weburl']. "/query/auto/{$params['orderid']}.html";  //同步地址
            $notify_url = $params['weburl'] . '/product/notify/?paymethod=' . $this->paymethod;  //支付成功后回调地址
            $sign  = md5($payId . $param . $type . $price . $key);
            
            
            $payIdConfig = array(
                'pay_id'=>$payId,
            );
            
            $chPayId = curl_init(); //使用curl请求
            curl_setopt($chPayId, CURLOPT_URL,  $payIdGateWay);
            curl_setopt($chPayId, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($chPayId, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($chPayId, CURLOPT_POSTFIELDS, $payIdConfig);
            $payId_json = curl_exec($chPayId);
            curl_close($chPayId);

            $payId_data = json_decode($payId_json,true);
            
            if(is_array($payId_data))
			{
				if($payId_data['code']<1)
				{
				// 	return array('code'=>1002,'msg'=>$payId_data['msg'],'data'=>'');
				//获取失败说明之前没有订单，创建新订单
				    	$config =array(
                        'payId'=>$payId,
                        'type'=>$type,
                        'price'=>$price,
                        'sign'=>$sign,
        				"param" =>$param,
        				"isHtml"=>$isHtml,
        				"return_url"=>$return_url,
        				'notifyUrl' => $notify_url,
                    );
        
        			$ch = curl_init(); //使用curl请求
                    curl_setopt($ch, CURLOPT_URL,  $payGateWay);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $config);
                    $tmdpay_json = curl_exec($ch);
                    curl_close($ch);
        
                    $tmdpay_data = json_decode($tmdpay_json,true);
        			if(is_array($tmdpay_data))
        			{
        				if($tmdpay_data['code']<1)
        				{
        					return array('code'=>1002,'msg'=>$tmdpay_data['msg'],'data'=>'');
        				}else
        				{
                            $qr = $tmdpay_data['data']['payUrl'];                   
        					$money = isset($tmdpay_data['data']['reallyPrice'])?$tmdpay_data['data']['reallyPrice']:$params['money'];
        					//计算关闭时间
                             $closetime =300-(time()-$tmdpay_data['data']['date']);
                            
        					$result = array('type'=>0,'subjump'=>0,'subjumpurl'=>$tmdpay_data['data']['payUrl'],'paymethod'=>$this->paymethod,'qr' => $params['qrserver'] . urlencode($tmdpay_data['data']['payUrl']),'payname'=>$payconfig['payname'],'overtime'=>$closetime,'money'=>$money);
        					return array('code'=>1,'msg'=>'success','data'=>$result);
        				}
        			}else
        			{
        				return array('code'=>1001,'msg'=>"支付接口请求失败",'data'=>'');
        			}
				}else
				{
				    if ($payId_data['data']['state'] == 0){
    				    $qr = $payId_data['data']['payUrl'];                   
    					$money = isset($payId_data['data']['reallyPrice'])?$payId_data['data']['reallyPrice']:$params['money'];
    					//计算关闭时间
                        $closetime =300-(time()-$payId_data['data']['date']);
                        
    					$result = array('type'=>0,'subjump'=>0,'subjumpurl'=>$payId_data['data']['payUrl'],'paymethod'=>$this->paymethod,'qr' => $params['qrserver'] . urlencode($payId_data['data']['payUrl']),'payname'=>$payconfig['payname'],'overtime'=>$closetime,'money'=>$money);
    					return array('code'=>1,'msg'=>'success','data'=>$result); 
				    }else
				    {
				        return array('code'=>1003,'msg'=>"订单超时",'data'=>'');
				    }
                  
				}
			}else
			{
				return array('code'=>1001,'msg'=>"支付接口请求失败",'data'=>'');
			}

		} 
		catch (\Exception $e) 
		{
			return array('code'=>1000,'msg'=>$e->getMessage(),'data'=>'');
		}
	}
	
	//处理返回
	public function notify($payconfig)
	{
        ini_set("error_reporting","E_ALL & ~E_NOTICE");
		$key=$payconfig['app_secret'];
		$payId = $_GET['payId'];//商户订单号
        $param = $_GET['param'];//创建订单的时候传入的参数
        $type = $_GET['type'];//支付方式 ：微信支付为1 支付宝支付为2
        $price = $_GET['price'];//订单金额
        $reallyPrice = $_GET['reallyPrice'];//实际支付金额
        $sign = $_GET['sign'];

        $temp_sign = md5($payId . $param . $type . $price . $reallyPrice . $key);

        if ($temp_sign !== $sign) { //不合法的数据 KEY密钥为你的密钥
            return 'error|Notify: auth fail';
        } else { //合法的数据
            //业务处理
			$config = array('paymethod' => $this->paymethod, 'tradeid' => $param, 'paymoney' => $reallyPrice, 'orderid'=>$payId );
            $notify = new \Pay\notify();
            $data = $notify->run($config);
            if ($data['code'] > 1) {
                return 'error|Notify: ' . $data['msg'];
            } else {
                return 'success';
            }
        }
	}
	
	
	private function _curlPost($url,$params){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT,300); //设置超时
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;	
	}
	
	private function _signParams($params,$secret){
		$sign = $signstr = "";
		if(!empty($params)){
			ksort($params);
			reset($params);
			
			foreach ($params AS $key => $val) {
				if ($key == 'sign') continue;
				if ($signstr != '') {
					$signstr .= "&";
				}
				$signstr .= "$key=$val";
			}
			$sign = md5($signstr.$secret);
		}
		return $sign;
	}	
	
}
