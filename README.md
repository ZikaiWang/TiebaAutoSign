# TiebaAutoSign
## 百度贴吧自动签到0.2(基于PHP)    

[百度贴吧自动签到](https://zikai.wang/)    
    
## 简易版(tiebaAutoSign.php) 
#### 运行相关        
此程序基于PHP，以及需要开启php.ini中的cURL功能    
如未开启请如下操作    
    
手工开启    
0. 确认有php解释器以及有 php_curl.dll，没有先安装    
1. 打开 php.ini 文件    
2. 找到 ;extension=php_curl.dll 这行    
3. 将其修改为 extension=php_curl.dll (删除开头的 ';' 符号)    

Ubuntu自动开启
1. 控制台输入`sudo apt-get install php-curl`    
    
#### 前置工作      
1. 克隆此项目 `sudo git clone https://github.com/ZikaiWang/TiebaAutoSign.git`    
2. 修改 tiebaAutoSign.php 文件, 把要签到的百度账号的`buss`写入第三行指定的位置，具体操作请看注释    
3. 运行程序，进行签到。如果关注的吧比较多可能会慢一些 `php tiebaAutoSign.php`    
    
    
#### 设置定时运行
1. 运行 `sudo crontab -e` (如果没有先安装)
2. 按 `i` 键 (一般是用vi打开，i用于进入编辑模式)
3. 一直按下键直到到达末尾，添加如下行`30 3 * * * php /root/TiebaAutoSign/tiebaAutoSign.php`, /root/TiebaAutoSign/tiebaAutoSign.php 为tiebaAutoSign.php所在位置，请按实际情况修改。建议使用绝对路径以免奇怪的问题。这行代码的意思是天凌晨3点30运行一次，以及建议错开北京时间 11:45pm - 2:00am 的高峰期
4. 按`ESC`键退出编辑模式
5. 输入`:wq`, 保存编辑并退出

## 网页版(web文件夹内)
1. source里是bootstrap5, 可以替换
2. 请根据pass.php里的指引完成数据库的设置
