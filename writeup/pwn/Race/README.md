# Race wp

## 一、Try to leak slab address via race condition

The bug is obviously.When we call copy_to_user and copy_from_user, in the same time deleting the buffer. In order to expand the window of the race condition, we need to mmap a piece of memory. When copy_to_user is coping data from buffer to the user space, will cause page fault, which may cause process schedule. Be careful, the copied size can not be 8 bytes, you can see the [implementation](https://elixir.bootlin.com/linux/v5.0-rc8/source/include/linux/uaccess.h#L149) of copy_to_user. Due to the differences between the local and server environments, the number of deleting process will be a little different.

Execute Sequence：

|||
|:-:|:-|
|test_write||
|copy_to_user||
|缺页中断||
||test_del|
||kfree释放buffer|
|copy_to_user||

So, we can successfully leak the slab address. 

## 二、Physmap spray

We need to allocate a lot of memory to do physmap spray. The total memory allocated to qemu is 128M，and we can allocate 64M memory in a process，so there is 50% chance to land on the [physmap](https://www.blackhat.com/docs/eu-14/materials/eu-14-Kemerlis-Ret2dir-Deconstructing-Kernel-Isolation.pdf) we controled.（In fact, if we find a good offset, we can succeed everytime）

## 三、Overwriting the first 8 bytes of a freed slab object.

We can calculate the physmap address via the slab address.（These two is very closed，and I guess the physmap containing slab, but I can't sure）

In order to expand the window between the writting and the deleting，I write the physmap address into a file directly（no kernel buffer, [O_DIRECT](http://man7.org/linux/man-pages/man2/open.2.html)）, then just mmap the file into memory. The next step is exactly like what we do in section one. So we can overwrite the next freed slab object to the physmap addr under our control. After all, we just need to open ptmx (open("/dev/ptmx",O_RDWR)), so that we will have a tty_struct in control.

## 四、Find physmap address alias

We just need to find out where in the memory that we mmaped in section two is not zero. That means we find the tty_struct in user space.

## 五、tty_struct tricks

In fact, opening ptmx will allocate two tty_struct(master and slave), and the second tty_struct (slave, [pts](https://docs.oracle.com/cd/E19253-01/816-4855/termsub15-14/index.html)) is under our control. So in order to call ioctl on the slave side, we need to open pts (open(pts_name, O_RDONLY | O_NOCTTY)). 

I just changed tty->ops->ioctl to set_memory_x to get the physmap address excutable, and finally jump into the physmap where supposed to be placed with shellcode. 

PS:ret2dir is very awesome. Originally, I only intended to use uaf with ret2dir, but gradually it became a race condition. :)

## reference

copy_to_user : https://elixir.bootlin.com/linux/v5.0-rc8/source/include/linux/uaccess.h#L149

ret2dir : https://www.blackhat.com/docs/eu-14/materials/eu-14-Kemerlis-Ret2dir-Deconstructing-Kernel-Isolation.pdf

O_DIRECT : http://man7.org/linux/man-pages/man2/open.2.html

ptmx : https://docs.oracle.com/cd/E19253-01/816-4855/termsub15-14/index.html


