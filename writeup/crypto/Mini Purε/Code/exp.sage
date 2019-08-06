import itertools
import string
data = [(1054957, 7140871, 15226469), (9593188, 9289076, 10323090), (3116469, 10940913, 1105645), (7649691, 8630486, 1444601), (10770446, 375445, 13762205), (3936479, 3016057, 897561), (2887518, 12474278, 685593), (14752170, 14155165, 14957512), (16140779, 7293217, 14375677), (2648484, 15467463, 4994588), (3812455, 954818, 12687778), (14353362, 351927, 9791054), (15163586, 12116018, 10753409), (435269, 9087032, 14991898), (5605020, 13485987, 7100688), (7279933, 12303214, 4087767), (14660697, 5065609, 13175787), (3224283, 3482175, 7422896), (1256715, 7672743, 13803567), (9982340, 12691464, 9793430), (6340955, 5109615, 1146062), (15142889, 11931352, 691164), (15702400, 1405425, 8043069), (5986140, 6122492, 3444367), (14309648, 8999094, 10405785), (15639889, 14468330, 13048492), (2792064, 3901480, 16005764), (16415937, 4284782, 10318328), (14360255, 14265602, 14644365), (3831905, 15824814, 11462617), (5337828, 2949460, 1077430), (11156340, 5498368, 13813036), (16351807, 3655410, 15436396), (3263646, 9308153, 12094400), (4320493, 14864124, 10372046)]
deg = 3**3
num = deg + 3

plainLeft = []
plainRight = 1
cipherRight = []
cipherLeft = []

for i in range(num):
    plainLeft.append(data[i][0])
    cipherLeft.append(data[i][1])
    cipherRight.append(data[i][2])

F = GF(2**24)
R = F['x']

def getD(cl,cr,k):
    f = cr^^k
    f = F.fetch_int(f)
    f = F(f**3).integer_representation()
    d = cl^^f
    return d

keys = []
keyList = string.ascii_lowercase + string.digits

# Round 6 - 3
for round in range(4):
    print '[+] Round ' +str(6-round)
    for key in itertools.product(*[keyList]*3):
        key = int(''.join(key).encode('hex'),16)
        points = []
        for i in range(num):
            x = F.fetch_int(plainLeft[i])
            d = getD(cipherLeft[i],cipherRight[i],key)
            d = F.fetch_int(d)
            points.append((x,d))
        l = R.lagrange_polynomial(points)
        if l.degree()==deg:
            print '[+] Found key '+str(6-round) + ':' + str(key)
            for j in range(num):
                cipherLeft[j] = cipherRight[j]
                cipherRight[j] = points[j][1].integer_representation()
            keys.insert(0,key)
            deg /= 3
            num = deg + 3
            break    

# Round 2
points = []
num = 3
flag = 0
print '[+] Round 2'
for key in itertools.product(*[keyList]*3):
    key = int(''.join(key).encode('hex'),16)
    for i in range(num):
        d = getD(cipherLeft[i],cipherRight[i],key)
        points.append(d)
        if d!=plainRight:
            break
        if i==(num-1):
            print '[+] Found key 2:' + str(key)
            for j in range(num):
                cipherLeft[j] = cipherRight[j]
                cipherRight[j] = points[j]
            keys.insert(0,key)
            flag = 1
            break
    if flag == 1:
        break

# Round 1
flag = 0
print '[+] Round 6'
for key in itertools.product(*[keyList]*3):
    key = int(''.join(key).encode('hex'),16)
    for i in range(num):
        d = getD(cipherLeft[i],plainRight,key)
        if d!=plainLeft[i]:
            break
        if i==(num-1):
            print '[+] Found key 1:' + str(key)
            keys.insert(0,key)
            flag = 1
    if flag == 1:
        break
print("[+] Find all keys:")
print(keys)