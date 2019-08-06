#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include <stdint.h>

uint8_t reg[50] = {0};
uint8_t key[50] = {65, 108, 109, 111, 115, 116, 32, 104, 101, 97, 118, 101, 110, 32, 119, 101, 115, 116, 32, 118, 105, 114, 103, 105, 110, 105, 97, 44, 32, 98, 108, 117, 101, 32, 114, 105, 100, 103, 101, 32, 109, 111, 117, 110, 116, 97, 105, 110, 115,00};
uint8_t plain[100] = {0};
uint8_t cipher[100] = {0};
uint8_t cmp[100] = {214, 77, 45, 133, 119, 151, 96, 98, 43, 136, 134, 202, 114, 151, 235, 137, 152, 243, 120, 38, 131, 41, 94, 39, 67, 251, 184, 23, 124, 206, 58, 115, 207, 251, 199, 156, 96, 175, 156, 200, 117, 205, 55, 123, 59, 155, 78, 195, 218, 216, 206, 113, 43, 48, 104, 70, 11, 255, 60, 241, 241, 69, 196, 208, 196, 255, 81, 241, 136, 81};

//Almost heaven west virginia, blue ridge mountains

int check()
{
    uint8_t i,j,k;
    uint8_t tmp = 0;
    i = 0;
    while(plain[i]!=0)
        i++;
    if(i!=70)
        return 0;
    for(i = 0;i<10;i++)
    {
        for(j = 0;j<7;j++)
        {
            tmp = 0;
            for(k = 0;k<7;k++)
            {
                tmp+=plain[i*7+k]*key[k*7+j];
            }
            cipher[i*7+j] = tmp;
        }
    }
    for(i = 0;i<70;i++)
    {
        if(cipher[i]!=cmp[i])
            return 0;
    }
    return 1;
}

int main()
{
    printf("Check up:");
    scanf("%s", plain);
    if(check())
        puts("True.");
    else
        puts("False.");

    return 0;
}