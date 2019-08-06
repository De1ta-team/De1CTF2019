import os,random,sys,string
from hashlib import sha256
import SocketServer
import signal
from FLAG import flag

def pad(x):
    pad_length = 8-len(x)
    return pad_length*'0'+x

def hex2list(x):
    res = []
    for i in range(0,32,8):
        r = x[i:i+8]
        res.append(int(r,16))
    return res

def list2hex(x):
    res = ''
    for i in range(4):
        res += pad(hex(x[i])[2:])
    return res

# Sbox is a secret permutation from F(2^32) to itself
def Sbox(plain):
    '''
        Obscured
    '''


def encrypt(msg):
    msg = hex2list(msg)
    A , B , C , D = msg[0],msg[1],msg[2],msg[3]
    for _ in range(6):
        S = Sbox(A^C)
        A , B , C , D = A ^ B ^ S , A ^ B ^ D ^ S , A ^ C ^ D , C ^ D ^ S 
    return list2hex([A,B,C,D])


class Task(SocketServer.BaseRequestHandler):
    def proof_of_work(self):
        random.seed(os.urandom(8))
        proof = ''.join([random.choice(string.ascii_letters+string.digits) for _ in range(20)])
        digest = sha256(proof).hexdigest()
        self.request.send("sha256(XXXX+%s) == %s\n" % (proof[4:],digest))
        self.request.send('Give me XXXX:')
        x = self.request.recv(10)
        x = x.strip()
        if len(x) != 4 or sha256(x+proof[4:]).hexdigest() != digest: 
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
        except:
            res = ''
        return res.strip('\n')

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
        secret = os.urandom(16).encode('hex')
        s = encrypt(secret)
        self.dosend('Welcome to the Crypto System.\n')
        self.dosend('You can encrypt any plain you want and if you tell me the secret I will give you the flag.\n')    
        self.dosend('The enc_secret is: '+s+'\n')
        for _ in range(20):
            self.dosend("Tell me the plaintext(hex): ")
            pt = self.recvhex(33)
            if pt=='':
                break
            if len(pt)!=32:
                self.dosend('The length must be 32!!!\n')
                continue
            ct = encrypt(pt)
            self.dosend("The result is: %s\n" % ct)
        self.dosend('Tell me the secret and I will give you the flag.\n')
        self.dosend('Secret(hex):\n')
        sc = self.recvhex(33)
        if sc==secret:
            self.dosend('Wow! How smart you are! Here is your flag:\n')
            self.dosend(flag)
        else:
            self.dosend('Sorry you are wrong!\n')
        self.request.close()


class ForkedServer(SocketServer.ForkingTCPServer, SocketServer.TCPServer):
    pass


if __name__ == "__main__":
    HOST, PORT = '0.0.0.0', 8003
    server = ForkedServer((HOST, PORT), Task)
    server.allow_reuse_address = True
    server.serve_forever()
