[中文](./README_zh.md) [English](./README.md)

## Solution
You can look at this [website](https://ctf-wiki.github.io/ctf-wiki/crypto/streamcipher/fsr/lfsr/) to know the basic principle of lfsr and find the solution under the `BM algorithm` section that using sequence of length 2n to recover mask and key. In this challenge we know the sequence of length (2n - 8 bits). So we just need to brute 8 bits (using FLAG[7:11]=='1224' to check) to get the mask, get the key and finally get the flag. Just a math game!

## Code

1. Code/exp.sage(exp script, also you can use B-M algorithm to recover mask)

2. Code/task.py(Generate sequence using KEY and MASK)

3. Code/output(Save the output of task.py)

4. Code/secret.py(Save the MASK,KEY and FLAG)
