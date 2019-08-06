[中文](./README_zh.md) [English](./README.md)

## Solution
This challenge refers to [NSUCRYPTO 2017 TwinPeaks](https://nsucrypto.nsu.ru/archive/2017/round/2/task/2/#data). And here is the [official solution](https://nsucrypto.nsu.ru/archive/2017/problems_solution) .I think this challenge is vert interesting so I tried to make it in our competition. I changed the  $F(a,b,c,d) = (a+c+S(c+d),a+b+d+S(c+d),a+c+d,b+d+S(c+d))$ to $F(a,b,c,d) = (a+b+S(a+c),a+b+d+S(a+c),a+c+d,c+d+S(a+c))$. So if you follow the official solution, you should change the $f(a,b,c,d)$ and $finv(a,b,c,d)$ to be $f(a,b,c,d) = (a+c,b+c+d,b+d,a+b+c)$ and $finv(a,b,c,d) = (a+b+c,a+d,b+c,a+c+d)$. The office solution need 18 requests, and I give you 20 requests so that you may find different way to solve this chanllenge just like [this](https://bomotodo.wordpress.com/2017/10/31/nsucrypto-2017/).

But in fact the official solution didn't say the steps clearly. I only use 7 requests to solve this challenge. And this is my solution:
`(Note : In the whole wp '+' means 'xor' )`
1. We assume
   1. $F(a,b,c,d) = (a+b+S(a+c),a+b+d+S(a+c),a+c+d,c+d+S(a+c))$
   2. $G(a,b,c,d) = (b+S(a),c,d,a)$
   3. $f(a,b,c,d) = (a+c,b+c+d,b+d,a+b+c)$
   4. $finv(a,b,c,d) = (a+b+c,a+d,b+c,a+c+d)$
2. If $F$ transfer the message $(a,b,c,d)$ to $(a',b',c',d')$, then $G$ transfer the message $f(a,b,c,d)$ to $f(a',b',c',d')$
3. And we assume the input of 6 rounds $G$ are $(x_1,x_2,x_3,x_4)$, the output are $(y_1,y_2,y_3,y_4)$, we can get the follow relations:
   1. $y_1 = x_3 + S(x_2 + S(x_1)) + S(y_4)$
   2. $y_2 = x_4 + S(x_3 + S(x_2 + S(x_1)))$
   3. $y_3 = x_1 + S(y_2)$
   4. $y_4 = x_2 + S(x_1) + S(y_3)$

So we can using Choose plaintext attack like this:

`(Note : We should using finv function to deal with input and using f function to deal with output in every requesting)`
1. Firstly we assume the secret is $(sx_1,sx_2,sx_3,sx_4)$ and we can get the output enc_secret $(sy_1,sy_2,sy_3,sy_4)$. 
2. Then, We randomly choose input $(ax_1,ax_2,ax_3,ax_4)$ and send them, then we get the output $(ay_1,ay_2,ay_3,ay_4)$, using `1 requesting`. We can get the relationship $S(ay_2)=ay_3+ax_1$
3. After that, We construct $(ay_2,ay_2+ax_1+ay_3,ax_1+ay_3+sy_2,sy_3)$, so the output $y_2 = x_4 + S(x_3 + S(x_2 + S(x_1))) = sy_3 + S(ax_1+ay_3+sy_2 + S(ay_2+ax_1+ay_3 + S(ay_2))) = sy_3 + S(ax_1+ay_3+sy_2 + S(ay_2)) = sy_3 + S(sy_2) = sx_1$, so we get the first part of secret $sx_1$ using `1 requesting`
4. Using step 3 we can get any $S(X)$ we want. Just using this payload: $(ay_2,ay_2+ax_1+ay_3,ax_1+ay_3+X,sy_3)$. Assume the output is $(y_1,y_2,y_3,y_4)$, then $S(X) = y_2 + sy_3$
5. So we using `2 requesting` to get $S(sx_1)$ and $S(sy_3)$, we can get the second part of secret $sx_2 = sy_4 + S(sx_1) + S(sy_3)$
6. And we using `2 requesting` to get $S(sx_2 + S(sx_1))$ and $S(sy_4)$, we can get the third part of secret $sx_3 = sy_1 + S(sx_2 + S(sx_1)) + S(sy_4)$ 
7. Finally we using `1 requesting` to get $S(sx_3 + S(sx_2 + S(sx_1)))$, we can get the fourth part of secret $sx_4 = sy_2 + S(sx_3 + S(sx_2 + S(sx_1)))$
8. Now we get the whole secret $(sx_1,sx_2,sx_3,sx_4)$ using `7 requesting`. And we can sned the secret to the server to get the flag



## Code

1. Code/exp.py(The exp scripts)

2. Code/task.py(Server. I don't give you the Sbox, you can use any Sbox you want, that doesn't matter)

3. Code/FLAG.py(Flag you want to know)

