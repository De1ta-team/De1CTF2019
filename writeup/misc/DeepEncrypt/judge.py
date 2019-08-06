import numpy as np
import os
flag = np.loadtxt("flag.txt")
# true_flag = "de1ctf{xxx_xxx_xxx}"
true_flag = open("true_flag.txt", 'r').read()
threshold = 0.2


def mse(true, predict):
    loss = np.average(np.abs(true - predict))
    print(loss)
    return loss


def judge(predict):
    if mse(flag, predict) < threshold:
        print(true_flag)
    else:
        print("You can't fool me")


if __name__ == "__main__":
    inp = input("Input your flag_dec result:")
    inp = np.asarray(inp.split(' '), dtype=float)
    judge(inp)
