[中文](./README_zh.md) [English](./README.md)

## Signal VM

Entering handlers by catching every exception, thereby achieving a vm.

This challenge references a challenge of qiang wang cup 2018 named obf. 

I can't find the official write up, so I just upload the challenge here.

### Program flow

At first, it fork a child process. The child process will step into many code which will throw many exception. The parent process debug child process and catch these exception.

The difference between Signal VM and Signal VM Δ is that, the first challenge modify the memory of parent process, the child process just transfer code to parent process.

In the second challenge, by using PTRACE_PEEKTEXT and PTRACE_POKETEXT, the parent process will modify the memory of child process.

So that, when we debug the parent process, in the first challenge, we could watch the memory of parent process. We can understand the instruction by watching how the register and memory change. That may be a easy way to solve a VM challenge.

But in the second challenge, due to the fact that the child process has been debugged by father process, so that we can't attach on it, and we can't watch the memory of child process easily.(Maybe there is an easy way I don't know, please tell me)Therefore, it increases the difficulty of analyze the instruction. So we should be focus on how the program parse instruction.

### Instructions

The instruction has three parts: opcode, type of operand, operand.

In addition to breakpoint, I add three other exceptions.

```
signal    | machine code | handler
-------------------------------------------
SIGILL    | 06           | mov, lea ...
SIGTRAP   | CC           | add, sub, mul div ...
SIGSEGV   | 00 00        | jcc
SIGFPE    | 30 C0 F6 F8  | cmp
```

The byte after the opcode is used to tell us the  type of operand(except jcc, because the operand of jcc is always immediate).

The high 4 bits represent first operand, and the low 4 bits represent the second:

```
0  register
1  immediate
2  address
```

The address must be pointed by register. The first operand cannot be immediate. The immediate must be 32 bits long.

After that is the operand. It should match with the operand type. The register use 1 byte and the immediate use 4 bytes. 0-9 represent R0-R9.

### Algorithm

The algorithm of two challenge is not very hard.

The first challenge use hill cipher to encrypt.

The second challenge reference https://projecteuler.net/problem=67 . Differently, we need to calculate the max sum of all path, and the max path together is the flag. The program calculate it by calculate every path. It's impossible to try every path as there are 2^99 altogether, so we must find out a efficient algorithm to solve it.

 We could select the bigger number of each two adjacent number at the bottom, and add it to upper floor. So when we calculate all floors, we can get the max sum. And when we compare the two number, we should write down the location so we can get the path.

In order to avoid multiple solutions, I modify the data that the number adjacent to the maximum cannot be equal to it.

### Source code

vm1.c and vm2.c are the source code. As I'm a rookie, the code may be poor. Please don't care it.

hill.c and triangle.c are the source code of the algorithm.

assembly1.txt and assembly2.txt are the assembly of two challenges. I just translate it from x86 assembly.

simulate1.py and simulate2.py will parse the instruction and simulate execution, and then assemble them into bytecode1 and bytecode2.

solve1.py and solve2.py are the reference scripts.



### Summary

If you have any question, please contact me by telegram: [@Apeng7364](https://t.me/Apeng7364)

