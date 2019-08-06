[中文](./WP_zh.md) [English](./WP.md)

## 解题思路
这个挑战是根据[NSUCRYPTO 2017 TwinPeaks](https://nsucrypto.nsu.ru/archive/2017/round/2/task/2/#data)来制作的。这是官网的解题思路[official solution](https://nsucrypto.nsu.ru/archive/2017/problems_solution)。 我觉得这个挑战很有意思所以我将它实现并放在了这次比赛中。和官方不同的是我将$F(a,b,c,d) = (a+c+S(c+d),a+b+d+S(c+d),a+c+d,b+d+S(c+d))$ 修改为了 $F(a,b,c,d) = (a+b+S(a+c),a+b+d+S(a+c),a+c+d,c+d+S(a+c))$。 如果你按照官方的解题思路，你只需要将 $f(a,b,c,d)$ 和 $finv(a,b,c,d)$ 变为 $f(a,b,c,d) = (a+c,b+c+d,b+d,a+b+c)$ 和 $finv(a,b,c,d) = (a+b+c,a+d,b+c,a+c+d)$即可。官方的解题思路需要加密18次明文，所以我给了稍微宽裕的20次加密的机会，这也是为了让大家有更多的空间去操作，找出不同的解题方法。比如这个[解法](https://bomotodo.wordpress.com/2017/10/31/nsucrypto-2017/)就是个不错的解法。

但是当我按照官方的解题思路进行解题的时候我发现他有的地方说的有点不那么清楚，并且我只使用了7次加密的机会就解决了这个问题。所以我写下了我的解题思路：
`(注意 : 在本文中所有的 '+' 都代表 'xor' )`
1. 我们首先假设以下关系式：
   1. $F(a,b,c,d) = (a+b+S(a+c),a+b+d+S(a+c),a+c+d,c+d+S(a+c))$
   2. $G(a,b,c,d) = (b+S(a),c,d,a)$
   3. $f(a,b,c,d) = (a+c,b+c+d,b+d,a+b+c)$
   4. $finv(a,b,c,d) = (a+b+c,a+d,b+c,a+c+d)$
2. 如果 $F$ 是将 $(a,b,c,d)$ 加密为 $(a',b',c',d')$, 那么 $G$ 就是将 $f(a,b,c,d)$ 加密为 $f(a',b',c',d')$
3. 我们假设6轮$G$函数的输入是 $(x_1,x_2,x_3,x_4)$, 那么输出就是$(y_1,y_2,y_3,y_4)$,我们可以找到以下关系:
   1. $y_1 = x_3 + S(x_2 + S(x_1)) + S(y_4)$
   2. $y_2 = x_4 + S(x_3 + S(x_2 + S(x_1)))$
   3. $y_3 = x_1 + S(y_2)$
   4. $y_4 = x_2 + S(x_1) + S(y_3)$

所以我们可以使用选择明文攻击并进行下面的操作：

`(注意： 所有的输入都要经过finv函数，所有的输出都要经过f函数)`
1. 首先我们假设secret是$(sx_1,sx_2,sx_3,sx_4)$ ，我们得到的enc_secret是$(sy_1,sy_2,sy_3,sy_4)$
2. 然后我们随机地选择一组输入$(ax_1,ax_2,ax_3,ax_4)$传给服务器，我们可以用`一次加密`的机会得到输出$(ay_1,ay_2,ay_3,ay_4)$。其中有如下关系$S(ay_2)=ay_3+ax_1$
3. 之后我们构造$(ay_2,ay_2+ax_1+ay_3,ax_1+ay_3+sy_2,sy_3)$, 那么我们可以推出输出是$y_2 = x_4 + S(x_3 + S(x_2 + S(x_1))) = sy_3 + S(ax_1+ay_3+sy_2 + S(ay_2+ax_1+ay_3 + S(ay_2))) = sy_3 + S(ax_1+ay_3+sy_2 + S(ay_2)) = sy_3 + S(sy_2) = sx_1$。我们可以用`一次加密`的机会得到secret的第一部分$sx_1$
4. 使用步骤3我们可以得到我们想要的任何$S(X)$的值。我们只需要这样构造输入即可: $(ay_2,ay_2+ax_1+ay_3,ax_1+ay_3+X,sy_3)$.如果输出是$(y_1,y_2,y_3,y_4)$, 那么$S(X) = y_2 + sy_3$
5. 所以我们可以使用`两次加密`的机会得到$S(sx_1)$和$S(sy_3)$的值,然后我们可以得到secret的第二部分$sx_2 = sy_4 + S(sx_1) + S(sy_3)$
6. 然后我们可以使用`两次加密`的机会得到$S(sx_2 + S(sx_1))$和$S(sy_4)$的值得到secret的第三部分$sx_3 = sy_1 + S(sx_2 + S(sx_1)) + S(sy_4)$ 
7. 最后我们可以使用`两次加密`的机会得到$S(sx_3 + S(sx_2 + S(sx_1)))$的值得到secret的第四部分 $sx_4 = sy_2 + S(sx_3 + S(sx_2 + S(sx_1)))$
8. 现在我们使用`七次加密`的机会得到了整个secret的值$(sx_1,sx_2,sx_3,sx_4)$。我们可以将secret传给服务器得到flag



## 代码

1. Code/exp.py(解题脚本)

2. Code/task.py(服务器脚本。我没有给Sbox，你可以自己选择你喜欢的Sbox放进去，这并没有什么影响)

3. Code/FLAG.py(Flag)

