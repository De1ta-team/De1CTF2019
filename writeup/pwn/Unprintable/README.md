[中文](./README_zh.md) [English](./README.md)

[attachment.zip](./attachment.zip)

This question is actually a variant of the printable from pwnable.tw

In theory, you can print out the stack address, as long as you predict the last three bits of the stack.

The first is to hijack the control flow, there is an leaving address from ld.so on the stack

The dl_fini function is executed when exit, and there is a interesting piece in it.

```
<_dl_fini+819>: call QWORD PTR [r12+rdx*8]
```

Rdx is fixed to 0, r12 is from the following code snippet

```
<_dl_fini+777>: mov r12, QWORD PTR [rax+0x8]
<_dl_fini+781>: mov rax, QWORD PTR [rbx+0x120]
<_dl_fini+788>: add r12, QWORD PTR [rbx]
```

Rbx points to the address of the ld.so that remains on the stack, so we can control the value of \[rbx]

R12 defaults to fini_array. By controlling rbx, we can let r12 point to bss, that is, we can hijack the control flow.

But after hijacking the flow of control?

We can jump back to the main function

```
.text:00000000004007A3 mov edx, 1000h ; nbytes
.text:00000000004007A8 mov esi, offset buf ; buf
.text:00000000004007AD mov edi, 0 ; fd
.text:00000000004007B2 call read
```

Read the content again to the bss section, then printf out

If you are careful, you can find that the 23rd parameter on the stack just points to the return address of printf now, that is, we can jump back to 0x4007A3 after printf, that is, infinite loop printf

With an infinite loop printf, it's done like a regular printf

At this time, we have any arbitrary writes, we can write the contents of the printf return address on the stack, write the address of a bss section, and then use the pop rsp gadget to rop.

There is also a small pit, which is that printf can't write values ​​with %hn after more than 0x2000 bytes, so it has to be brute force to the appropriate stack address, but the probability is quite high.

With rop? We still can't leak, this time we can use a magical gadget

```
.text:00000000004006E8 adc [rbp+48h], edx
```
Rbp and edx are all controllable, just in the bss section there are stdin, stdout, sterr these values, pointing to libc

So we can use this gadget to change stderr to one_gadget and then use __libc_csu_init
```
Call qword ptr [r12+rbx*8]
```
You can get the shell

After the get shell is quite simple, use the redirect to get the flag

```
Cat flag >&0
```
