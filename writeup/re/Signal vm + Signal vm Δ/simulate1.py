from struct import pack
code = []
jmp_map = {}
with open("../clion/assembly.txt") as f:
    i = 0
    for lines in f:
        code.append(lines[16:].strip())
        if lines.startswith("loc_"):
            tmp = lines.index(':')
            jmp_map[lines[:tmp]] = i
        i += 1
ip = 0
reg = [0] * 8
memory = [65, 108, 109, 111, 115, 116, 32, 104, 101, 97, 118, 101, 110, 32, 119, 101, 115, 116, 32, 118, 105, 114, 103,
          105, 110, 105, 97, 44, 32, 98, 108, 117, 101, 32, 114, 105, 100, 103, 101, 32, 109, 111, 117, 110, 116, 97,
          105, 110, 115, 00] \
         + [100, 101, 49, 99, 116, 102, 123, 55, 104, 51, 110, 95, 102, 52, 114, 51, 95, 117, 95, 119, 51, 108, 108, 95,
            53, 119, 51, 51, 55, 95, 99, 114, 52, 103, 49, 51, 95, 72, 73, 76, 76, 95, 119, 104, 51, 114, 51, 95, 48,
            102, 51, 110, 95, 55, 49, 109, 51, 53, 95, 49, 95, 118, 51, 95, 114, 48, 118, 51, 100, 125] + [0] * 30 \
         + [0] * 100 \
         + [214, 77, 45, 133, 119, 151, 96, 98, 43, 136, 134, 202, 114, 151, 235, 137, 152, 243, 120, 38, 131, 41, 94,
            39, 67, 251, 184, 23, 124, 206, 58, 115, 207, 251, 199, 156, 96, 175, 156, 200, 117, 205, 55, 123, 59, 155,
            78, 195, 218, 216, 206, 113, 43, 48, 104, 70, 11, 255, 60, 241, 241, 69, 196, 208, 196, 255, 81, 241, 136,
            81] + [0] * 30
# print(memory[0:50])
# print(memory[50:150])
# print(memory[150:250])
# print(memory[250:350])


while ip < len(code):
    local = code[ip]
    if local[7] == 'b':
        print("1", end='')
    # print(local)
    op = local[0:3]
    operand0 = 0
    operand1 = 0
    if op[0] != 'j' and op != 'r':
        if local[8] == 'r' and local[12] == 'r':
            operand0 = int(local[9])
            operand1 = int(local[13])
            type = 0x00
        elif local[8] == 'r' and local[12] != 'r' and local[12] != '[':
            operand0 = int(local[9])
            operand1 = int(local[12:].strip())

            type = 0x01
        elif local[8] == '[' and local[14] == 'r':
            operand0 = int(local[10])
            operand1 = int(local[15:].strip())
            type = 0x20

        elif local[8] == 'r' and local[12] == '[':
            operand0 = int(local[9])
            operand1 = int(local[14])
            type = 0x02
        else:
            print(local)

    if op == 'mov':
        if type == 0x00:
            reg[operand0] = reg[operand1]
        elif type == 0x01:
            reg[operand0] = operand1
        elif type == 0x20:
            memory[reg[operand0]] = reg[operand1]
        elif type == 0x02:
            reg[operand0] = memory[reg[operand1]]
        else:
            print(local)
        # print("0x%02x %3d %3d"%(type,operand0,operand1)+"    "+local)
    elif op == 'cmp':
        if type == 0x00:
            r7 = reg[operand0] - reg[operand1]
        elif type == 0x01:
            r7 = reg[operand0] - operand1
        # print(local)
    elif op[0] == 'j':
        operand = jmp_map[local[8:].strip()]
        if local[0:3] == 'jmp':
            # ip = ip
            ip = operand - 1
        elif local[0:3] == 'jz ' or local[0:2] == 'je ':
            if r7 == 0:
                # ip = ip
                ip = operand - 1
        elif local[0:3] == 'jnz' or local[0:3] == 'jne':
            if r7 != 0:
                # ip = ip
                ip = operand - 1
        elif local[0:3] == 'ja ':
            if r7 > 0:
                # ip = ip
                ip = operand - 1
        elif local[0:3] == 'jae':
            if r7 >= 0:
                # ip = ip
                ip = operand - 1
        elif local[0:3] == 'jb ':
            if r7 < 0:
                # ip = ip
                ip = operand - 1
        elif local[0:3] == 'jbe' or local[0:3] == 'jna':
            if r7 < 0 or r7 == 0:
                # ip = ip
                ip = operand - 1
        else:
            print(local + "     ", operand)
        # elif local[0:2] == 'jg'
    else:
        op = local[0:3]
        if op == 'add':
            if type == 0x00:
                reg[operand0] += reg[operand1]
            elif type == 0x01:
                reg[operand0] += operand1
        elif op == 'sub':
            if type == 0x00:
                reg[operand0] -= reg[operand1]
            elif type == 0x01:
                reg[operand0] -= operand1
        elif op == 'imu':
            if type == 0x00:
                reg[operand0] *= reg[operand1]
            elif type == 0x01:
                reg[operand0] *= operand1
        elif op == 'idi':
            if type == 0x00:
                reg[operand0] //= reg[operand1]
            elif type == 0x01:
                reg[operand0] //= operand1
        elif op == 'mod':
            if type == 0x00:
                reg[operand0] %= reg[operand1]
            elif type == 0x01:
                reg[operand0] %= operand1
        elif op[0:2] == 'or':
            if type == 0x00:
                reg[operand0] |= reg[operand1]
            elif type == 0x01:
                reg[operand0] |= operand1
        elif op == 'and':
            if type == 0x00:
                reg[operand0] &= reg[operand1]
            elif type == 0x01:
                reg[operand0] &= operand1
        elif op == 'xor':
            if type == 0x00:
                reg[operand0] ^= reg[operand1]
            elif type == 0x01:
                reg[operand0] ^= operand1
        elif op == 'shl':
            if type == 0x00:
                reg[operand0] <<= reg[operand1]
            elif type == 0x01:
                reg[operand0] <<= operand1
        elif op == 'shr':
            if type == 0x00:
                reg[operand0] >>= reg[operand1]
            elif type == 0x01:
                reg[operand0] >>= operand1
        else:
            break
    ip += 1


tmp_code = []
for ip in range(len(code)):
    tmp = b""
    local = code[ip]
    op = local[0:3]
    operand0 = 0
    operand1 = 0
    if op[0] != 'j' and op != 'r':
        if local[8] == 'r' and local[12] == 'r':
            operand0 = int(local[9])
            operand1 = int(local[13])
            type = 0x00
        elif local[8] == 'r' and local[12] != 'r' and local[12] != '[':
            operand0 = int(local[9])
            operand1 = int(local[12:].strip())
            type = 0x01
        elif local[8] == '[' and local[14] == 'r':
            operand0 = int(local[10])
            operand1 = int(local[15:].strip())
            type = 0x20

        elif local[8] == 'r' and local[12] == '[':
            operand0 = int(local[9])
            operand1 = int(local[14])
            type = 0x02
    if op == "mov":
        tmp += b"\x06"
        tmp += bytes([type])
        if local[8] == 'r' and local[12] != 'r' and local[12] != '[':
            tmp += bytes([operand0])
            tmp += pack("<i",operand1)
        else:
            tmp += bytes([operand0]) + bytes([operand1])
    elif op[0] == "j":
        tmp += b"\x00\x00"
        operand = jmp_map[local[8:].strip()]
        if local[0:3] == 'jmp':
            tmp += b"\x00"
        elif local[0:3] == 'jz ' or local[0:2] == 'je ':
            tmp += b"\x01"
        elif local[0:3] == 'jnz' or local[0:3] == 'jne':
            tmp += b"\x02"
        elif local[0:3] == 'ja ':
            tmp += b"\x03"
        elif local[0:3] == 'jae':
            tmp += b"\x04"
        elif local[0:3] == 'jb ':
            tmp += b"\x05"
        elif local[0:3] == 'jbe' or local[0:3] == 'jna':
            tmp += b"\x06"
        tmp += pack("<i", operand)
    elif op == 'cmp':
        tmp += b"\x30\xC0\xF6\xF8"
        tmp += bytes([type])
        if local[8] == 'r' and local[12] != 'r' and local[12] != '[':
            tmp += bytes([operand0])
            tmp += pack("<i",operand1)
        else:
            tmp += bytes([operand0]) + bytes([operand1])
    elif op == "ret":
        tmp += b"\xC3"
    else:
        tmp += b"\xCC"
        if op == 'add':
            tmp += b"\x00"
        elif op == 'sub':
            tmp += b"\x01"
        elif op == 'imu':
            tmp += b"\x02"
        elif op == 'idi':
            tmp += b"\x03"
        elif op == 'mod':
            tmp += b"\x04"
        elif op[0:2] == 'or':
            tmp += b"\x05"
        elif op == 'and':
            tmp += b"\x06"
        elif op == 'xor':
            tmp += b"\x07"
        elif op == 'shl':
            tmp += b"\x08"
        elif op == 'shr':
            tmp += b"\x09"
        tmp += bytes([type])
        if local[8] == 'r' and local[12] != 'r' and local[12] != '[':
            tmp += bytes([operand0])
            tmp += pack("<i",operand1)
        else:
            tmp += bytes([operand0]) + bytes([operand1])
    tmp_code.append(tmp)

for i in range(len(code)):
    local = code[i]
    op = local[0:3]
    if op[0] == 'j':
        tmp = tmp_code[i][0:3]
        start = 0
        for j in range(i):
            start += len(tmp_code[j])
        dest = jmp_map[local[8:].strip()]
        end = 0
        for j in range(dest):
            end += len(tmp_code[j])
        tmp = tmp + pack("<i",end-start)
        t_code = tmp_code[0:i]
        t_code.append(tmp)
        t_code = t_code + tmp_code[i+1:]
        tmp_code[:] = t_code[:]
res = b""
for i in tmp_code:
    res+=i
with open('bytecode1','wb') as f:
    f.write(res)
print(res)
