[中文](./readme_zh.md) [English](./readme.md)

[Docker](./docker) [Exp](./exp.py)

# CloudMusic_rev WriteUp

Here is 1.0 version writeup:

[impakho/ciscn2019_final_web1](https://github.com/impakho/ciscn2019_final_web1)

This challenge is 2.0 version.

Read the webpage source code, find out `#firmware` in `index` page.

![1](./img/1.png)

`#firmware` page, only admin permitted.

![2](./img/2.png)

And then reg and login. In my share page, you can see a english song, the others are chinese songs.

This english song is the default song in web music player.

So go to see the source code of `#share`. You can see `/media/share.php?` and `btoa`. It's not difficult to find an arbitrary file read exploit here.

![3](./img/3.png)

Try to read `../index.php` and visit `http://127.0.0.1/media/share.php?Li4vaW5kZXgucGhw`.

![4](./img/4.png)

It blocks `.php` file. But it gives us a hint, we can use `urlencode` to bypass.

![5](./img/5.png)

Read the `../index.php` file successfully. We can also read other file.

We have to read the admin password to use `#firmware`.

The exploit is in `/include/upload.php`. It use `/lib/parser.so` to parse the mp3 file we uploaded. It checks the admin password inside `.so`.

![6](./img/6.png)

We use `IDA` to decompile `/lib/parser.so` file. The exploit is in `read_title` / `read_artist` / `read_album` function which use `strcpy`. We can `off by null` to set `mframe_data` first byte to `0x00`. Then we can read `mem_mpasswd` aka admin password.

![7](./img/7.png)

Comparing to `1.0 version`, this is a wrong `parser.so`. It uses `strlen` to get the length. `unicode` will not work anymore.

We can make a frame (length: `0x70`), and then upload the mp3 file to get admin password.

The `mp3` file is in `exp.py` script.

We use admin password to login, and visit `#firmware`.

![8](./img/8.png)

![9](./img/9.png)

Leak the source code, and read `#firmware` source code.

We can upload a `.so` file here. And guess the filename based on server time.

Finally, load the `.so` file to `rce`.

We can use `__attribute__ ((constructor))` to `rce`.

Just like this:

```
#include <stdio.h>
#include <string.h>

char _version[0x130];
char * version = &_version;

__attribute__ ((constructor)) void fun(){
    memset(version,0,0x130);
    FILE * fp=popen("/usr/bin/tac /flag", "r");
    if (fp==NULL) return;
    fread(version, 1, 0x100, fp);
    pclose(fp);
}
```

![10](./img/10.png)

Comparing with `1.0` version, there is no `rce` result here.

So we can write the result to `/uploads/firmware/` or `/uploads/music/`.

Our user `www-data` have no permission to read the `/flag` file.

We need to find `suid` program to read the flag. `/usr/bin/tac` have `suid` permission. So we can use it to read the flag.

The payload is `/usr/bin/tac /flag > /var/www/html/uploads/firmware/xxxxx`.

Script to `getflag`, see the `exp.py` file.

Flag：`de1ctf{W3b_ANND_PWNNN_C1ou9mus1c_revvvv11}`
