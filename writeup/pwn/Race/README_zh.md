# Race wp

## 一、竞态泄露slab地址

题目很明显就是copy_to_user和copy_from_user时的竞争删除导致的漏洞，为了扩大竞争条件的窗口期需要mmap一块内存，当copy_to_user复制到用户空间时会引发缺页中断，这样可能会导致进程切换。需要注意的是复制的大小不能是8字节，不然再多的删除进程也是没用的，具体可以看copy_to_user的[实现](https://elixir.bootlin.com/linux/v5.0-rc8/source/include/linux/uaccess.h#L149)。由于本地和服务器环境有一些差别，竞争删除的进程数会有一点不同。

理想的效果：

|||
|:-:|:-|
|test_write||
|copy_to_user||
|缺页中断||
||test_del|
||kfree释放buffer|
|copy_to_user||

这样就可以顺利拿到slab地址

## 二、分配大量内存，占位physmap

就mmap大量地址吧，qemu给了128M内存，进程可以顺利申请64M内存，这样就占了一半的内存，后面有50%的几率跳到控制的[physmap](https://www.cnblogs.com/0xJDchen/p/6143102.html)。（实际上找个好一点的偏移基本上100%成功）

## 三、竞态写释放后的slab object

通过第一步获得slab地址，从而推出physmap的起始地址（这两个区域很接近，或者应该说physmap包含了slab，这点不确定，没深入源码）

为了扩大竞争条件的窗口期，我是通过将猜测的physmap地址直接写入文件（不经过缓冲区，直接写入文件，[O_DIRECT](https://www.cnblogs.com/muahao/p/7903230.html)），然后再mmap映射文件去读。后面流程和竞争读一样，copy_from_user的时候，将buffer删掉，这样就可以改写下一块空闲slab地址，然后接着open("/dev/ptmx",O_RDWR);就可以申请tty_struct到可控physmap地址上。

## 四、查找physmap地址别名

查找mmap出来的地址，如果不为NULL就代表找到了第三步申请的tty_struct结构体。这样就可以在用户态修改内核分配的tty_struct。

## 五、tty_struct常规用法

open("/dev/ptmx",O_RDWR);实际上会分配两个tty_struct，主从模式。实际上用户态可控的tty_struct是[pts](https://blog.csdn.net/luckywang1103/article/details/71191821)的（因为第一个tty_struct会分配到删除了的buffer地址，第二个tty_struct才会分配到physmap上），所以还要open(pts_name, O_RDONLY | O_NOCTTY);然后才是常规的ioctl操作。

这里懒得找gadgets，就直接调用set_memory_x设置可执行，后面再跳到shellcode在内核态下执行就好了。

PS:向经典的ret2dir致敬。本来只是打算uaf加ret2dir的，后面写着写着就成伪竞态了。 :)

## reference

copy_to_user : https://elixir.bootlin.com/linux/v5.0-rc8/source/include/linux/uaccess.h#L149

ret2dir : https://www.cnblogs.com/0xJDchen/p/6143102.html

O_DIRECT : https://www.cnblogs.com/muahao/p/7903230.html

ptmx : https://blog.csdn.net/luckywang1103/article/details/71191821

