import hashlib
from secret import KEY,FLAG,MASK

assert(FLAG=="de1ctf{"+hashlib.sha256(hex(KEY)[2:].rstrip('L')).hexdigest()+"}")
assert(FLAG[7:11]=='1224')

LENGTH = 256

assert(KEY.bit_length()==LENGTH)
assert(MASK.bit_length()==LENGTH)

def pad(m):
    pad_length = 8 - len(m)
    return pad_length*'0'+m

class lfsr():
    def __init__(self, init, mask, length):
        self.init = init
        self.mask = mask
        self.lengthmask = 2**(length+1)-1

    def next(self):
        nextdata = (self.init << 1) & self.lengthmask 
        i = self.init & self.mask & self.lengthmask 
        output = 0
        while i != 0:
            output ^= (i & 1)
            i = i >> 1
        nextdata ^= output
        self.init = nextdata
        return output


if __name__=="__main__":
    l = lfsr(KEY,MASK,LENGTH)
    r = ''
    for i in range(63):
        b = 0
        for j in range(8):
            b = (b<<1)+l.next()
        r += pad(bin(b)[2:])
    with open('output','w') as f:
        f.write(r)