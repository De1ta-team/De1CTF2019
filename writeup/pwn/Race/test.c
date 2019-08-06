#include <linux/module.h>
#include <linux/kernel.h>
#include <linux/miscdevice.h>
#include <linux/init.h>
#include <linux/slab.h>
#include <linux/fs.h>
#include <linux/list.h>
#include <linux/idr.h>
#include <linux/uaccess.h>

#define test_ioctl_read		0x23333
#define test_ioctl_write	0x23334
#define test_ioctl_del  	0x23335

unsigned long length = 0;
char * buffer = 0;

struct data_struct
{
	unsigned long size;
	char __user *buf;
};

typedef long (*ftype)(struct data_struct *);

long test_read(struct data_struct *data)
{
	unsigned long size = data->size;
	if (size == NULL)
		return -EINVAL;
	if (size > length && size <= 0x400)
	{
		kfree(buffer);
		buffer = kzalloc(size, GFP_KERNEL);
		if (buffer == NULL)
			return -ENOMEM;
		length = size;
	}
    if (size <= length)
	    return copy_from_user(buffer,data->buf,size);
    return -EINVAL;
}

long test_write(struct data_struct *data)
{
	unsigned long size = data->size;
	if (length >= size && length)
		return copy_to_user(data->buf,buffer,size);
	return -EINVAL;
}

long test_del(void )
{
    kfree(buffer);
    buffer = NULL;
    length = NULL;
    return 0;
}

static long test_ioctl(struct file *filp, unsigned int cmd, unsigned long arg )
{
    struct data_struct data;
    if(copy_from_user(&data, (unsigned long *)arg, sizeof(data)))
        return -EINVAL;
    switch ( cmd )
    {
		case test_ioctl_read:
			return test_read(&data);
		case test_ioctl_write:
			return test_write(&data);
		case test_ioctl_del:
			return test_del();
	}
    return 0;
}

struct file_operations test_fops = {
    owner:          THIS_MODULE,
    unlocked_ioctl: test_ioctl,
};

static struct miscdevice test_miscdev = {
    name:   "test",
    fops:   &test_fops
};

static int __init init_test ( void )
{
    misc_register(&test_miscdev);
    return 0;
}

static void __exit exit_test ( void )
{
    misc_deregister(&test_miscdev);
}

module_init(init_test);
module_exit(exit_test);

MODULE_LICENSE("GPL");
