
HOST = "45.32.100.6"
PORT = 10036

from pwn import *
import string
import hashlib
import os

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

payload = []
for i in range(35):
    r = os.urandom(3)+'\x00\x00\x01'
    payload.append(r.encode('hex'))



r = remote(HOST, PORT) 

sl = lambda s : r.sendline(s)
rl = lambda  : r.recvline()
sd = lambda s : r.send(s)
rc = lambda n=4096 : r.recv(n)
ru = lambda s : r.recvuntil(s)

data = ru("Give me XXXX:")
suffix = re.findall(r"XXXX\+([^\)]+)",data)[0]
h = re.findall(r"== ([0-9a-f]+)",data)[0]
p = solve_PoW(suffix, h)
sl(p)
  
result = []

for i in payload:
    d = ru('Tell me the plaintext(hex): ')
    sl(i)
    ru('The result is : ')
    res = rl()
    res = res[:-1]
    res = res[:12]
    result.append(res)
print payload
print result
ru('The enc_flag is : ')
enc_flag = rl()
r.close()
data = []
for i in range(35):
    data.append((int(payload[i][:6],16),int(result[i][:6],16),int(result[i][6:],16)))
print data
print enc_flag[:-1]