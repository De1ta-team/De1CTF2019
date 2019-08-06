[中文](./WP_zh.md) [English](./WP.md)

## 解题思路

这是一道有关[插值攻击](https://en.wikipedia.org/wiki/Interpolation_attack)的题目. Purε是一种Feistel网络的加密，在1997年被Knudsen在论文[Jakobsen T， Knudsen L. The interpolation attack on block cipher\[C\]. FSE 1997， LNCS 1267](https://www.researchgate.net/publication/225190352_The_interpolation_attack_on_block_ciphers)中提出。它只需6轮便足以抵抗差分密码攻击和线性密码攻击。但是与之相对的，它能被插值攻击的方法攻击。

在Purε加密中Fbox = $x^3$，它的次数十分地低.我们假设加密的轮数是t，输入的明文是$(X，C)$. 其中$C$是一个常数。

1. 在1轮加密之后，输出可以表示成该形式：$(C，X \bigoplus C^*)$， 其中$C^*$是另一个常数。
2. 在2轮加密之后，输出可以表示成该形式：$(X \bigoplus C^*，X^3 \bigoplus g_1(x))$. 其中$deg(g_1(X)) \leq 3^1-1$. 
3. ...
4. 在t轮加密之后，输出可以表示成该形式：$(X^{3^{t-2}} \bigoplus g_{t-2}(X)，X^{3^{t-1}} \bigoplus g_{t-1}(X))$. 其中$deg(g_i(X))\leq3^i-1$. 

那么如果$3^{i-2} \leq 2^{24}-1$， $l_i(X)=X^{3^{i-2}} \bigoplus g_{i-2}(X)$就是一个多项式函数。其中$deg(l_i(X))\leq3^{i-2}$. 

我们可以使用以下步骤去攻击Purε:
1. 随机选取$3^{t-3}+2$个明文$P_i$，明文可以被表示为$(X，C)$，$C$是一个常数。然后我们可以加密这些明文并得到密文$C_i=(C_L^{(i)}，C_R^{(i)})$($1\leq i \leq 3^{t-2}+2$)
2. 随机选取最后一轮的密钥 $k^*$， 计算 $D_i = C_L^{(i)} \bigoplus (C_R^{(i)} \bigoplus k^*)^3$
3. 利用$(P_1，D_1)$，$(P_2，D_2)$，...，$(P_{3^{t-3}+2}，D_{3^{t-3}+2})$和拉格朗日插值法计算$h(X)$使得当$1\leq i \leq3^{t-3}+2$时， $h(P_i)=D_i$
4. 检验$deg(h(X))=3^{t-3}$是否成立。如果不成立则$k^*$肯定是错误的。我们可以重新回到步骤2。如果成立则$k^*$就是最后一轮的key.
5. 我们可以通过以上的方法恢复3第3到t轮的密钥。然后我们可以用$C$和$X$去爆破第1，2轮的密钥。这是很简单的。

在这个挑战中加密轮数t=6，所以我们只需要$3^3+2=29$对明密文对。为了减小解题的复杂度。我将Purε改为了在$GF(2^{24})$上运算并且使得key的取值在小写字母和数字之中。所以你可以使用python或sage脚本去解决它。这也是为什么这道题叫`Mini Purε`.

但是后来看了解出来题目的队伍的WP才发现我在接收数据的时候写错为`recv(1024)`,所以导致出现了非预期解。非常感谢AAA在WP中提出这个错误，真的十分抱歉出现这样的问题:( 。

最后我要特别感谢[*CTF 2019](https://ctftime.org/event/778)以及其中的题目notfeal。我参加了*CTF 2019并解决了这个挑战。它是一个有关使用[差分密码攻击](http://theamazingking.com/crypto-feal.php)来攻击feal的题目。但是它修改了部分feal的实现，所以又和feal不太相同。从解决这个挑战的过程中我学习到了许多分组加密攻击的方法。同时我也发现网络上并没有太多有关插值攻击的资料。所以我创造了这个挑战希望大家可以从中学到有关插值攻击的方法，并且可以自己动手试一试。

希望你可以喜欢这个挑战。我也会保留环境一段时间的。如果你想要尝试，就来攻击它吧！:)


## 代码

1. Code/exp.py(用于从服务器获取数据的脚本。其中输出的data是exp.sage的输入，enc_flag是decrypt.py的输入)

2. Code/exp.sage(使用插值攻击得到密钥。其中输出的keys是decrypt.py的输入)

3. Code/decrypt.py(解密flag)

4. Code/task.py(服务器脚本)

5. Code/FLAG.py(Flag)
