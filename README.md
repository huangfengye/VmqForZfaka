# VmqForZfaka
## 安装
 + 按照https://github.com/zlkbdotnet/zfaka  要求安装zfaka，建议使用页面中提示的宝塔教程进行安装，省时省力

 + 安装最新版V免签PHP

 + 将文件夹 tmdpayalipay和tmdpaywx  解压到网站目录application/library/Pay文件夹中，此时在Pay文件夹中会多出一个文件夹，名字分别为：tmdpayalipay 、tmdpaywx

 + 将文件夹 tmdpayalipay 中的tmdpayalipay.php打开，编辑（private $apiHost="https://xxxxxx.cn/createOrder ";）这段代码 为你自己的V免签服务端地址

 + 将文件夹 tmdpaywx     中的tmdpaywx.php    打开，编辑（private $apiHost="https://xxxxxx.cn/createOrder ";） 这段代码 为你自己的V免签服务端地址

 + 将文件  tmdpayalipay.html 、tmdpaywx.html解压到网站目录application\modules\Admin\views\payment\tpl文件夹中  

 + 修改数据库，在faka数据库中运行下面的sql语句，建议使用宝塔环境的phpmyadmin软件进行修改，省时省力
```
INSERT INTO `t_payment` (`payment`, `payname`, `payimage`, `alias`, `sign_type`, `app_id`, `app_secret`, `ali_public_key`, `rsa_private_key`, `configure3`, `configure4`, `overtime`, `active`) VALUES
('V免签支付宝', '支付宝', '/res/images/pay/alipay.jpg', 'tmdpayalipay', 'MD5', '', '', '', '', '', '', 300, 0),
('V免签微信', '微信', '/res/images/pay/weixin.jpg', 'tmdpaywx', 'MD5', '', '', '', '', '', '', 300, 0);
```
 + 登录zfaka的后台，依次点击：设置中心->配置中心，在第三页修改参数“weburl”的值为你自己的网站域名url（必须修改，否则无法回调）

 + 依次点击：设置中心->支付设置，修改编辑“V免签支付宝”和“V免签微信”这两个支付渠道，将V免签后台中的通信密钥填入进去，将V免签网址填入支付网关中，选中激活状态，点击确认修改

 + 按照zfaka的后台逻辑，自行添加商品，自行测试


## 环境
  + 以上步骤在 宝塔 + php-7.1 + nginx + mysql-10.2 + zfaka-1.4.1+Vmqphp-1.81中测试通过


## 赞助
如果您有经济条件，您可以赞助本项目的开发（下方收款码），如果您不想赞助，也请您点击上面的Star给一个星星，也是对我莫大的认同，感谢各位的支持。

![微信赞助](https://puu.sh/DF0jt/ded5938c8c.jpg)![支付宝赞助](https://puu.sh/DEYmS/32f8237fd8.jpg)

## 感谢
- https://github.com/huangfengye  集成此接口
- 感谢https://github.com/szvone/  提供的免签方案
- 感谢https://github.com/zlkbdotnet/zfaka  提供的发卡方案
