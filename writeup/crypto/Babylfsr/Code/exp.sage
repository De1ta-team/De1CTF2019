import hashlib

def pad(x):
    pad_length = 8 - len(x)
    return '0'*pad_length+x

rr = '001010010111101000001101101111010000001111011001101111011000100001100011111000010001100101110110011000001100111010111110000000111011000110111110001110111000010100110010011111100011010111101101101001110000010111011110010110010011101101010010100101011111011001111010000000001011000011000100000101111010001100000011010011010111001010010101101000110011001110111010000011010101111011110100011110011010000001100100101000010110100100100011001000101010001100000010000100111001110110101000000101011100000001100010'

for pad_bit in range(2**8):
    r = rr+pad(bin(pad_bit)[2:])
    index = 0
    a = [0]*512
    for i in r:
        if i=='1':
            a[index]=1
        index += 1

    res = []
    for i in range(256):
        for j in range(256):
            if a[i+j]==1:
                res.append(1)
            else:
                res.append(0)
    sn = []
    for i in range(256):
        if a[256+i]==1:
            sn.append(1)
        else:
            sn.append(0)

    MS = MatrixSpace(GF(2),256,256)
    MSS = MatrixSpace(GF(2),1,256)
    A = MS(res)
    s = MSS(sn)
    try:
        inv = A.inverse()
    except ZeroDivisionError as e:
        continue
    mask = s*inv


    cm = []
    for i in range(255):
        cm.append(0)
    cm.append(mask[0][0])
    index = 0
    for i in range(255):
        for j in range(256):
            if j==index:
                cm.append(1)
            else:
                if j==255:
                    cm.append(mask[0][1+i])
                else:
                    cm.append(0)
        index +=1
    c = MS(cm)
    try:
        c_inv = c.inverse()
    except ZeroDivisionError as e:
        continue
    res = ''
    for i in range(256):
        t = a[:256]
        t = MSS(t)
        r = (t*c_inv)[0][0]
        a.insert(0,r)
        res = str(r) + res
    FLAG ="de1ctf{"+hashlib.sha256(hex(int(res,2))[2:].rstrip('L')).hexdigest()+"}"
    if FLAG[7:11]=='1224':
        print FLAG



