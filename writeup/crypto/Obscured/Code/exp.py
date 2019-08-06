HOST = "45.32.100.6"
PORT = 8003

from pwn import *
import string
import hashlib
import os

r = remote(HOST, PORT) 

sl = lambda s : r.sendline(s)
sd = lambda s : r.send(s)
rc = lambda n=4096 : r.recv(n)
ru = lambda s : r.recvuntil(s)

def solve_PoW(suffix, h):
    print "solving PoW......"
    charset = string.letters + string.digits

    for p1 in charset:
        for p2 in charset:
            for p3 in charset:
                for p4 in charset:
                    plaintext = p1 + p2+ p3+ p4 + suffix
                    m = hashlib.sha256()
                    m.update(plaintext)
                    if m.hexdigest() == h:
                        print "PoW solution has been found!"
                        return p1+p2+p3+p4 

def pad(x):
    pad_length = 8-len(x)
    return pad_length*'0'+x

def hex2list(x):
    res = []
    for i in range(0,32,8):
        rr = x[i:i+8]
        res.append(int(rr,16))
    return res

def list2hex(x):
    res = ''
    for i in range(4):
        res += pad(hex(x[i])[2:])
    return res

#f(a,b,c,d) = (a+c,b+c+d,b+d,a+c+b)
def f(x):
    a = x[0]^x[2]
    b = x[1]^x[2]^x[3]
    c = x[1]^x[3]
    d = x[0]^x[1]^x[2]
    return [a,b,c,d]

#f_inv(a,b,c,d) = (a+b+c,a+d,b+c,a+c+d)
def f_inv(x):
    a = x[0]^x[1]^x[2]
    b = x[0]^x[3]
    c = x[1]^x[2]
    d = x[0]^x[2]^x[3]
    return [a,b,c,d]

def GetEncSecret():
    r.recvuntil('The enc_secret is: ')
    res = r.recvuntil('\n')
    return res.strip('\n')

def GetEncMsg(msg):
    r.recvuntil('plaintext(hex): ')
    r.sendline(msg)
    r.recvuntil('The result is: ')
    res = r.recvuntil('\n')
    return res.strip('\n')

def GetFlag(msg):
    r.recvuntil('plaintext(hex): ')
    r.sendline('\n')
    r.recvuntil('Secret(hex):\n')
    r.sendline(msg)
    r.recvuntil('Wow! How smart you are! Here is your flag:\n')
    flag = r.recvuntil('}')
    return flag

data = r.recvuntil("Give me XXXX:")
suffix = re.findall(r"XXXX\+([^\)]+)",data)[0]
h = re.findall(r"== ([0-9a-f]+)",data)[0]
p = solve_PoW(suffix, h)
r.send(p)

target_res = GetEncSecret()
target_res = hex2list(target_res)
target_res = f(target_res)

msg = 'A'*32
msg = hex2list(msg)
msg = list2hex(f_inv(msg))

res = GetEncMsg(msg)
res = hex2list(res)
res = list2hex(f(res))
res = hex2list(res) 


y1 = target_res[0]
y2 = target_res[1]
y3 = target_res[2]
y4 = target_res[3]

msg = hex2list(msg) 
s = msg[0]^res[2]
w1 = res[1]
v1 = w1 ^ s
v = s ^ y2

# (ay_2,ay_2+ax_1+ay_3,ax_1+ay_3+sy_2,sy_3)
payload = [w1,v1,v,y3]
payload = list2hex(f_inv(payload))


res = GetEncMsg(payload)
res = list2hex(f(hex2list(res)))
x1 = int(res[8:16],16)

# Get S(x1)
vx1 = s ^ x1

payload_x1 = [w1,v1,vx1,y3]
payload_x1 = list2hex(f_inv(payload_x1))

rsx1 = GetEncMsg(payload_x1)
rsx1 = list2hex(f(hex2list(rsx1)))
sx1 = int(rsx1[8:16],16)^y3

# Get S(y3)
vy3 = s ^ y3

payload_y3 = [w1,v1,vy3,y3]
payload_y3 = list2hex(f_inv(payload_y3))

rsy3 = GetEncMsg(payload_y3)
rsy3 = list2hex(f(hex2list(rsy3)))
sy3 = int(rsy3[8:16],16)^y3

# Get x2
x2 = y4^sx1^sy3

# Get S(x2+S(x1))
vsx21 = s ^ sx1 ^ x2
payload_x21 = [w1,v1,vsx21,y3]
payload_x21 = list2hex(f_inv(payload_x21))

rsx21 = GetEncMsg(payload_x21)
rsx21 = list2hex(f(hex2list(rsx21)))
sx21 = int(rsx21[8:16],16)^y3

# Get S(y4)
vy4 = s ^ y4

payload_y4 = [w1,v1,vy4,y3]
payload_y4 = list2hex(f_inv(payload_y4))

rsy4 = GetEncMsg(payload_y4)
rsy4 = list2hex(f(hex2list(rsy4)))
sy4 = int(rsy4[8:16],16)^y3

# Get x3
x3 = y1^sx21^sy4

# Get S(x3+S(x2+S(x1)))
vsx321 = s ^ sx21 ^ x3
payload_x321 = [w1,v1,vsx321,y3]
payload_x321 = list2hex(f_inv(payload_x321))

rsx321 = GetEncMsg(payload_x321)
rsx321 = list2hex(f(hex2list(rsx321)))
sx321 = int(rsx321[8:16],16)^y3

# Get x4
x4 = y2^sx321

# Get secret
secret = [x1,x2,x3,x4]
secret = list2hex(f_inv(secret))
print secret

# Get flag
flag = GetFlag(secret)
print flag

r.close()




