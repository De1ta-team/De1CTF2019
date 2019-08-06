[中文](./WP_zh.md) [English](./WP.md)

## Solution

This is a challenge about [Interpolation attack](https://en.wikipedia.org/wiki/Interpolation_attack). Purε is a kind of Feistel cipher proposed by Knudsen in 1997 [Jakobsen T, Knudsen L. The interpolation attack on block cipher\[C\]. FSE 1997, LNCS 1267](https://www.researchgate.net/publication/225190352_The_interpolation_attack_on_block_ciphers). It can prevent differential cryptanalysis and linear cryptanalysis only need 6 rounds. But they use Interpolation attack to crack it. 

In Purε the Fbox = $x^3$ , the degree of this expression is too low. We assume the round number is t, the input plaintest is $(X,C)$. $C$ is a constant. 

1. After 1 round, the output is $(C,X \bigoplus C^*)$, $C^*$ is another constant. 
2. After 2 rounds, the output is  $(X \bigoplus C^*,X^3 \bigoplus g_1(x))$. The $deg(g_1(X)) \leq 3^1-1$. 
3. ...
4. After t rounds, the output is $(X^{3^{t-2}} \bigoplus g_{t-2}(X)，X^{3^{t-1}} \bigoplus g_{t-1}(X))$. The $deg(g_i(X))\leq3^i-1$. 



So if $3^{i-2} \leq 2^{24}-1$, the $l_i(X)=X^{3^{i-2}} \bigoplus g_{i-2}(X)$ is a polynomial function. The $deg(l_i(X))\leq3^{i-2}$. 

We can use following steps to attack Purε:
1. Randomly choose $3^{t-3}+2$ plaintexts $P_i$, which is just like $(X,C)$,$C$ is a fixed constant. And we can encrypt these plaintexts to get ciphertexts like $C_i=(C_L^{(i)},C_R^{(i)})$ where $1\leq i \leq 3^{t-2}+2$
2. Randomly choose the last round key $k^*$, calculate $D_i = C_L^{(i)} \bigoplus (C_R^{(i)} \bigoplus k^*)^3$
3. Use $(P_1,D_1)$,$(P_2,D_2)$,...,$(P_{3^{t-3}+2},D_{3^{t-3}+2})$ and Lagrangian interpolation to calculate $h(X)$ when $1\leq i \leq3^{t-3}+2$ , $h(P_i)=D_i$
4. Check if $deg(h(X))=3^{t-3}$ is established. If not, the $k^*$ must be wrong. We can go back to step 2. If it is right, we can say the $k^*$  is the last round key.
5. We can do this again and again to fix keys of round 3-t. And we can use $C$ and $X$ to brute keys of round 1-2. It is so easy. 

In our challenge the t=6, so we just need $3^3+2=29$ pairs of plaintexts and ciphertexts. In order to reduce the computational complexity. I change the Purε to work on $GF(2^{24})$ and limit the keylist in ascii_lowercase + digits. Python or sage scripts is enough to solve it. So I call it `Mini Purε`.

But after reading the wp of the teams who solved the challenge, I find that I made a mistake that I write `recv(1024)`, so there are unexpected solutions. Thanks to AAA for pointing out this mistake in WP. I'm very sorry about that :( 

Finally I want to thank the challenge notfeal in [*CTF 2019](https://ctftime.org/event/778). I joined *CTF 2019 and finally solved this challenge. It is a challenge of feal used [differential cryptanalysis](http://theamazingking.com/crypto-feal.php). But it changed part of internal structure of feal. From this challenge I learned a lot of ways to attack block cipher. And I find there is not too much information about interpolation attack on the Internet. So I tried to build this challenge that you can learn the interpolation attack and implement it by yourself. 

Hope you can enjoy this challenge. I will keep the environment for a while. So if you like it, attack it! :)


## Code

1. Code/exp.py(In order to get data from server. The output data is the input of exp.sage and the output enc_flag is the input of decrypt.py)

2. Code/exp.sage(Use Interpolation attack to get keys, The output keys is the input of decrypt.py)

3. Code/decrypt.py(Decrypt flag)

4. Code/task.py(Server)

5. Code/FLAG.py(Flag you want to know)
