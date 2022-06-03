# TiebaAutoSign
## 百度贴吧自动签到0.1(基于PHP)    

### 基于linux时
#### 运行相关    
此程序基于PHP，以及需要开启php.ini中的cURL功能    
如未开启请如下操作    
    
0. 确认有php解释器以及有 php_curl.dll，没有先安装    
1. 打开 php.ini 文件    
2. 找到 ;extension=php_curl.dll 这行    
3. 将其修改为 extension=php_curl.dll (删除开头的 ';' 符号)    
    
    
#### 前置工作      
1. 克隆此项目 `sudo git clone https://github.com/ZikaiWang/TiebaAutoSign.git`    
2. 修改 tiebaAutoSign.php 文件, 把要签到的百度账号的`buss`写入第三行的    
3. 运行程序，进行签到。如果关注的吧比较多可能会慢一些 `php tiebaAutoSign.php`    
    
    
#### 设置定时运行
1. 运行 `sudo crontab -e` (如果没有先安装)
2. 按 `i` 键 (一般是用vi打开，i用于进入编辑模式)
3. 一直按下键直到到达末尾，添加如下行`0 */24 * * * php /root/TiebaAutoSign/tiebaAutoSign.php`, /root/TiebaAutoSign/tiebaAutoSign.php 为tiebaAutoSign.php所在位置，请按实际情况修改。建议使用绝对路径以免奇怪的问题。这行代码的意思是每24小时运行一次，以及建议错开北京时间 11:45pm - 2:00am 的高峰期
4. 按`ESC`键退出编辑模式
5. 输入`:wq`, 保存编辑并退出