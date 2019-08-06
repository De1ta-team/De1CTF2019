from pyfinite import ffield
F = ffield.FField(24)

def pad(plain):
    pad_length = 6 - len(plain) % 6
    return plain + chr(pad_length) * pad_length

def padhex(plain):
    pad_length = 2 - len(plain)
    return pad_length*'0'+plain

def Mul(q1,q2):
    return F.Multiply(q1,q2)

def fbox(x):
    return Mul(Mul(x,x),x)

def dec_block(cipher,keys):
    ll = cipher[:3]
    rr = cipher[3:]
    l = ''
    r = ''
    for i in range(3):
        l+=padhex(hex(ll[i])[2:])
        r+=padhex(hex(rr[i])[2:])
    l = int(l,16)
    r = int(r,16)
    for i in range(6):
        l , r = r , l ^ fbox(keys[5-i] ^ r)
    l , r = r, l
    l = hex(l).lstrip('0x')
    r = hex(r).lstrip('0x')
    l = (6-len(l))*'0'+l
    r = (6-len(r))*'0'+r
    return l + r

def decrypt(cipher, keys):
    c = ''
    for i in range(0,len(cipher),2):
        c += chr(int(cipher[i:i+2],16))
    plain = ''
    for i in range(0 , len(c) , 6):
        plain += dec_block(list(map(ord, c[i:i+6])), keys)
    return plain


enc_flag = 'c9864e4883a69e7beeafe99db753958e02e59f655759addad91aac0d86568f193466017b'
keys = [7631223, 7430519, 3618355, 6449200, 3684730, 3371897]
flag = decrypt(enc_flag, keys)
flag = bytes.fromhex(flag).decode('utf-8')
print(flag)