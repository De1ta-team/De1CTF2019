#!/usr/bin/env python3
# coding=utf-8
import socketserver
import os,random,sys,string
from hashlib import sha256,sha1
import binascii
from pyfinite import ffield
from FLAG import flag
import signal

F = ffield.FField(24)
flag = ''.join(['%02x' % b for b in flag.encode(encoding="utf-8")])

def pad(plain):
    pad_length = 6 - len(plain) % 6
    return plain + chr(pad_length) * pad_length

def padhex(plain):
    pad_length = 2 - len(plain)
    return pad_length*'0'+plain

def genkeys():
    keys=[]
    keyList = string.ascii_lowercase
    for _ in range(6):
        key = ''.join(random.sample(keyList,3))
        key_int = int(str(binascii.hexlify(key.encode()),'ascii'),16)
        keys.append(key_int)
    return keys

def Mul(q1,q2):
    return F.Multiply(q1,q2)

def fbox(x):
    return Mul(Mul(x,x),x)

def enc_block(plain,keys):
    ll = plain[:3]
    rr = plain[3:]
    l = ''
    r = ''
    for i in range(3):
        l+=padhex(hex(ll[i])[2:])
        r+=padhex(hex(rr[i])[2:])
    l = int(l,16)
    r = int(r,16)
    for i in range(6):
        l , r = r , l ^ fbox(keys[i] ^ r)
    l , r = r, l
    l = hex(l).lstrip('0x')
    r = hex(r).lstrip('0x')
    l = (6-len(l))*'0'+l
    r = (6-len(r))*'0'+r
    return l + r

def encrypt(plain, keys):
    p = ''
    for i in range(0,len(plain),2):
        p += chr(int(plain[i:i+2],16))
    plain = pad(p)
    cipher = ''
    for i in range(0 , len(plain) , 6):
        cipher += enc_block(list(map(ord, plain[i:i+6])), keys)
    return cipher


class Task(socketserver.BaseRequestHandler):
    def proof_of_work(self):
        random.seed(os.urandom(8))
        proof = ''.join([random.choice(string.ascii_letters+string.digits) for _ in range(20)])
        digest = sha256(proof.encode()).hexdigest()
        self.dosend(str.encode(("sha256(XXXX+%s) == %s\n" % (proof[4:],digest))))
        self.dosend(str.encode('Give me XXXX:'))
        x = self.request.recv(10)
        x = (x.strip()).decode("utf-8") 
        if len(x) != 4 or sha256((x+proof[4:]).encode()).hexdigest() != digest: 
            return False
        return True

    def recvhex(self, sz):
        try:
            r = sz
            res = ''
            while r>0:
                res += self.request.recv(r)
                if res.endswith('\n'):
                    r = 0
                else:
                    r = sz - len(res)
            res = res.strip()
            res = bytes.fromhex(res).decode('utf-8')
        except:
            res = ''
        return res

    def dosend(self, msg):
        try:
            self.request.sendall(msg)
        except:
            pass

    def handle(self):
        signal.alarm(500)
        if not self.proof_of_work():
            return
        signal.alarm(450)
        keys = genkeys()
        self.dosend(str.encode("Welcome to the Purε crypto system!!!\n"))
        self.dosend(str.encode("You can choose plaintext to encrypt and I will give you the result.\n"))
        for i in range(35):
            self.dosend(str.encode("Tell me the plaintext(hex): "))
            pt = self.request.recv(1024)
            pt = str(pt,'ascii').rstrip('\n')
            if pt=='':
                break
            ct = encrypt(pt, keys)
            self.dosend(str.encode('The result is : ' + ct + '\n'))
        enc_flag = encrypt(flag, keys)
        self.dosend(str.encode('The enc_flag is : ' + enc_flag + '\n'))
        self.dosend(str.encode("Bye~"))
        self.request.close()

class ThreadedServer(socketserver.ThreadingMixIn, socketserver.TCPServer):
    pass

if __name__ == "__main__":
    HOST, PORT = '0.0.0.0', 10036
    server = ThreadedServer((HOST, PORT), Task)
    server.allow_reuse_address = True
    server.serve_forever()