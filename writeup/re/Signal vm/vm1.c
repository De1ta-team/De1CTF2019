#include<stdio.h>
#include<unistd.h>
#include<sys/wait.h>
#include<sys/ptrace.h>
#include<sys/user.h>
#include<stdlib.h>
#include<stdint.h>
#define JUSTMOV(num) asm volatile("mov %0,%%rax"::"i"(num))

int R[8] = {0};
uint8_t memory[400] = {65, 108, 109, 111, 115, 116, 32, 104, 101, 97, 118, 101, 110, 32, 119, 101, 115, 116, 32, 118, 105, 114, 103, 105, 110, 105, 97, 44, 32, 98, 108, 117, 101, 32, 114, 105, 100, 103, 101, 32, 109, 111, 117, 110, 116, 97, 105, 110, 115, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 214, 77, 45, 133, 119, 151, 96, 98, 43, 136, 134, 202, 114, 151, 235, 137, 152, 243, 120, 38, 131, 41, 94, 39, 67, 251, 184, 23, 124, 206, 58, 115, 207, 251, 199, 156, 96, 175, 156, 200, 117, 205, 55, 123, 59, 155, 78, 195, 218, 216, 206, 113, 43, 48, 104, 70, 11, 255, 60, 241, 241, 69, 196, 208, 196, 255, 81, 241, 136, 81, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0};

int debug(int a)
{
    unsigned char status[16] = {0};
    unsigned char peek_data[32] = {0};
    unsigned char type = 0;
    unsigned char op_type = 0;
    unsigned long long confirm = 0;
    int op0 = 0;
    int op1 = 0;
    struct user_regs_struct reg ={0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0};
    wait((int *)status);
//    puts("\nwait.");
//    printf("%02x %02x %02x %02x \n",status[0],status[1],status[2],status[3]);
    while(status[0] == 0x7f)
    {
        ptrace(PTRACE_GETREGS, a, 0, &reg);
//        printf("0x%llx  ",reg.rip);
        *(long*)peek_data = ptrace(PTRACE_PEEKTEXT,a,reg.rip,0);
        if (status[1] ==04)
        {
//            puts("SIGILL,  mov");
            type = peek_data[1];
            op0 = (int)peek_data[2];
            if(type == 0x01)
            {
                op1 = *(int*)&peek_data[3];
                reg.rip+=7;
            }
            else
            {
                op1 = (int)peek_data[3];
                reg.rip+=4;
            }
            switch(type)
            {
                case 0x00:
                    R[op0] = R[op1];
//                    printf("SIGILL,  mov R%d, R%d\n",op0,op1);
                    break;
                case 0x01:
                    R[op0] = op1;
//                    printf("SIGILL,  mov R%d, %d\n",op0,op1);
                    break;
                case 0x20:
                    memory[R[op0]] = R[op1];
//                    printf("SIGILL,  mov [R%d], R%d\n",op0,op1);
                    break;
                case 0x02:
                    R[op0] = memory[R[op1]];
//                    printf("SIGILL,  mov R%d, [R%d]\n",op0,op1);
                    break;
            }
        }
        else if(status[1] == 5)
        {
//            puts("SIGTRAP, opt");
            op_type = peek_data[0];
            type = peek_data[1];
            op0 = peek_data[2];
            if(type == 0x01)
            {
                reg.rip+=7;
                op1 = *(int*)&peek_data[3];
            }
            else if(type == 0x00)
            {
                reg.rip+=4;
                op1 = R[peek_data[3]];
            }
            switch(op_type)
            {
                case 0:
                    R[op0]+=op1;
//                    printf("SIGTRAP, add R%d, %d\n",op0,op1);
                    break;
                case 1:
                    R[op0]-=op1;
//                    printf("SIGTRAP, sub R%d, %d\n",op0,op1);
                    break;
                case 2:
                    R[op0]*=op1;
//                    printf("SIGTRAP, mul R%d, %d\n",op0,op1);
                    break;
                case 3:
                    R[op0]/=op1;
//                    printf("SIGTRAP, div R%d, %d\n",op0,op1);
                    break;
                case 4:
                    R[op0]%=op1;
//                    printf("SIGTRAP, mod R%d, %d\n",op0,op1);
                    break;
                case 5:
                    R[op0]|=op1;
//                    printf("SIGTRAP, or  R%d, %d\n",op0,op1);
                    break;
                case 6:
                    R[op0]&=op1;
//                    printf("SIGTRAP, and R%d, %d\n",op0,op1);
                    break;
                case 7:
                    R[op0]^=op1;
//                    printf("SIGTRAP, xor R%d, %d\n",op0,op1);
                    break;
                case 8:
                    R[op0]<<=op1;
//                    printf("SIGTRAP, shl R%d, %d\n",op0,op1);
                    break;
                case 9:
                    R[op0]>>=op1;
//                    printf("SIGTRAP, shr R%d, %d\n",op0,op1);
                    break;
            }
        }
        else if(status[1] == 8)
        {
//            puts("SIGFPE,  cmp");
            type = peek_data[2];
            op0 = (int)peek_data[3];
            if(type == 0x01)
            {
                op1 = *(int*)&peek_data[4];
                R[7] = R[op0] - op1;
//                printf("SIGFPE,  cmp R%d, %d\n",op0,op1);
                reg.rip+=8;
            }
            else if(type == 0x00)
            {
                op1 = (int)peek_data[4];
                R[7] = R[op0] - R[op1];
//                printf("SIGFPE,  cmp R%d, R%d\n",op0,op1);
                reg.rip+=5;
            }
        }
        else if(status[1] == 11)
        {
//            printf("SIGSEGV, jmp ");
            type = peek_data[2];
            int offset = *(int*)&peek_data[3];
//            printf("0x%08x\n",offset);
            switch(type)
            {
                case 0x00:
                    reg.rip+=offset;
                    break;
                case 0x01:
                    if(R[7]==0)
                        reg.rip+=offset;
                    else
                        reg.rip+=7;
                    break;
                case 0x02:
                    if(R[7]!=0)
                        reg.rip+=offset;
                    else
                        reg.rip+=7;
                    break;
                case 0x03:
                    if(R[7]>0)
                        reg.rip+=offset;
                    else
                        reg.rip+=7;
                    break;
                case 0x04:
                    if(R[7]>=0)
                        reg.rip+=offset;
                    else
                        reg.rip+=7;
                    break;
                case 0x05:
                    if(R[7]<0)
                        reg.rip+=offset;
                    else
                        reg.rip+=7;
                    break;
                case 0x06:
                    if(R[7]<=0)
                        reg.rip+=offset;
                    else
                        reg.rip+=7;
                    break;
            }
        }
        confirm = reg.rip;
        ptrace(PTRACE_SETREGS,a,0,&reg);
        ptrace(PTRACE_CONT,a,0,0);
        wait((int*)status);
//        printf("%02x %02x %02x %02x \n",status[0],status[1],status[2],status[3]);
    }
    ptrace(PTRACE_KILL,a,0,0);
    return 0;
}

int start_vm()
{
    ptrace(0,0,0,0);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    JUSTMOV(0x1122334455667788);
    return 0;
}

int main()
{
    printf("Check up: ");
    scanf("%s",&memory[50]);
    int a;
    a = (int)fork();
    if(a<0)
    {
        return -1;
    }
    else if(a)
    {
//        printf("parent process. pid:%d\n", getpid());
//        printf("debugging.\n");
        debug(a);
        if(R[0])
            printf("Ture.\n");
        else
            printf("False.\n");
    }
    else 
    {
//        printf("child process. pid:%d\n",getpid());
        start_vm();
        exit(0);
    }

    return 0;
}