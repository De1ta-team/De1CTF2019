# python 3
from numpy import *
def GetReverse(a,n):
	x = 0
	y = 1
	q = n
	p = a
	z = q // p
	while(1 != p and 1 != q):
		t = p
		p = q % p
		q = t

		t = y
		y = x - y * z
		x = t
		z = q // p
	y = y % n
	if(y < 0):
		y += n
	return y
def CheckReverse(a,n):
	p = 2
	while(p*p<n):
		if(a%p == n%p and 0 == a%p):
			return False
		p+=1
	return True
def MatI(m,n):
	rank = len(m)
	mm = [[0 for i in range(0,rank)] for j in range(0,rank)]
	A = [[0 for i in range(0,2*rank)] for j in range(0,rank)]
	T = [0 for i in range(0,2*rank)]
	for i in range(0, rank):
		for j in range(0, rank*2):
			if(j < rank):
				A[i][j] = m[i,j]
			else:
				if(rank == j-i):
					A[i][j] = 1
				else:
					A[i][j] = 0
	for j in range(0, rank):
		for i in range(j, rank):
			if(CheckReverse(A[i][j], n)):
				a_1 = GetReverse(A[i][j], n)
				for k in range(0, rank*2):
					A[i][k] *= a_1
					A[i][k] %= n
					T[k] = A[i][k]
					A[i][k] = A[j][k]
					A[j][k] = T[k]
				break
			if(rank-1 == i):
				return False
		for i in range(0, rank):
			if(i != j):
				t = A[i][j]
				for k in range(0, rank*2):
					A[i][k] -= t * A[j][k]
					A[i][k] %= n
					if(A[i][k]<0):
						A[i][k] += n
	for i in range(0, rank):
		for j in range(0, rank):
			mm[i][j] = A[i][j+rank]
	return mat(mm)


key = list(b'Almost heaven west virginia, blue ridge mountains')
cipher = [  214,  77,  45, 133, 119, 151,  96,  98,  43, 136, 
  134, 202, 114, 151, 235, 137, 152, 243, 120,  38, 
  131,  41,  94,  39,  67, 251, 184,  23, 124, 206, 
   58, 115, 207, 251, 199, 156,  96, 175, 156, 200, 
  117, 205,  55, 123,  59, 155,  78, 195, 218, 216, 
  206, 113,  43,  48, 104,  70,  11, 255,  60, 241, 
  241,  69, 196, 208, 196, 255,  81, 241, 136,  81];
mK = mat(key).reshape(7,7)
#plain = list(b'de1ctf{7h3n_f4r3_u_w3ll_5w337_cr4g13_HILL_wh3r3_0f3n_71m35_1_v3_r0v3d}')
#print(plain)
#mP = mat(plain).reshape(10,7)
#mC = mP*mK%256
#print(mC.reshape(1,70).tolist()[0])
mC = mat(cipher).reshape(10,7)
mKI = MatI(mK,256)
mP = mC*mKI%256
print("".join(list(map(chr,mP.reshape(1,70).tolist()[0]))))