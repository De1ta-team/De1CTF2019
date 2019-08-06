[中文](./README_zh.md) [English](./README.md)

[附件](./attachment.zip)

这题其实是pwnable tw上printable一道题的变种

理论上可以不用打印栈地址出来，只要预测栈后三位就可以了

首先是劫持控制流，栈上面残留了一个ld.so的地址

在exit的时候会执行dl_fini函数，里面有一段比较有趣的片段

```
<_dl_fini+819>:	call   QWORD PTR [r12+rdx*8]
```

rdx固定为0，r12来自下面的代码片段

```
<_dl_fini+777>:	mov    r12,QWORD PTR [rax+0x8]
<_dl_fini+781>:	mov    rax,QWORD PTR [rbx+0x120]
<_dl_fini+788>:	add    r12,QWORD PTR [rbx]
```

rbx指向的刚好就是栈上残留的ld.so的地址，因此我们可以控制\[rbx]的值

r12默认指向的是fini_array，通过控制rbx，我们可以让r12指向bss，也就是我们可以劫持控制流了

但是劫持控制流之后呢？

我们可以再跳回main函数

```
.text:00000000004007A3                 mov     edx, 1000h      ; nbytes
.text:00000000004007A8                 mov     esi, offset buf ; buf
.text:00000000004007AD                 mov     edi, 0          ; fd
.text:00000000004007B2                 call    read
```

再次读内容到bss段，再printf出来

如果比较细心的话，可以发现这个时候栈上第23个参数刚好指向的是printf的返回地址，也就是我们可以在printf之后再跳回0x4007A3，也就是能无限循环printf

有了无限循环printf，那么就和平常的有循环的printf一样做了

这个时候我们就有了任意写，可以写栈上printf返回地址后面的内容，写一个bss段的地址，再配合 pop rsp这个gadget就可以进行rop了

这里还有一个小坑，就是printf超过0x2000个字节之后用 %hn 写不了值，所以要爆破到适合的栈地址，不过概率也挺高的

有了rop之后呢？我们还是leak不了，这个时候可以借助一个神奇的gadget

```
.text:00000000004006E8                 adc     [rbp+48h], edx
```
rbp和edx我们都是可以控制的，刚好bss段中有stdin,stdout,sterr这几个值，指向的是libc

所以我们可以利用这个gadget将stderr改成one_gadget，再利用__libc_csu_init中的
```
call    qword ptr [r12+rbx*8]
```
就可以get shell了

get shell之后就挺简单了，利用重定向拿flag

```
cat flag >&0
```
