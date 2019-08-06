[中文](./README_zh.md) [English](./README.md)

## 解题思路
你可以在[CTF-WIKI](https://ctf-wiki.github.io/ctf-wiki/crypto/streamcipher/fsr/lfsr/)的这个部分找到有关lfsr的基本知识。并且在`BM algorithm`下面提到可以用2n的序列恢复mask和key的方法。在这个挑战中我们知道的序列的长度只有(2n-8bits)，但是我们可以通过约束条件`FLAG[7:11]=='1224'`去爆破剩下的8bits。然后恢复mask，恢复key，最终得到明文

## 代码

1. Code/exp.sage(解题脚本,当然你也可以使用B-M算法恢复mask)

2. Code/task.py(使用KEY和MASK生成序列的脚本)

3. Code/output(task.py的输出)

4. Code/secret.py(包含MASK,KEY和FLAG)
