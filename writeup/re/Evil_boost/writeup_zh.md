## evil_boost

> 源代码`evil_boost.cpp`
>
> 附件`evil_boost.exe`
>
> 编译`compile.txt`



### 分析

```C++
#include<boost/phoenix/phoenix.hpp>
#include<iostream>
#include<string>
#include<string.h>

namespace opt = boost::program_options;

using namespace std;
using namespace boost::spirit;
using namespace phoenix;

int main(int argc, char** argv) {
	std::cout << "Have you input your name??" << std::endl;
	opt::options_description desc("All options");
	desc.add_options()
		("cplusplus,cpp", opt::value<int>()->default_value(99), "your C++ grades")
		("python,py", opt::value<int>()->default_value(88), "your python grades")
		("javascript,js", opt::value<int>()->default_value(77), "your javascript grades")
		("name", opt::value<std::string>(), "your name")
		("help", "produce help message");
	opt::variables_map vm;
	//解析命令行选项并把值存储到"vm"
	opt::store(opt::parse_command_line(argc, argv, desc), vm);
	opt::notify(vm);
```

如代码所示，解析命令行参数并存储



```C++
if (vm.count("name")) {
		std::string __name = vm["name"].as<std::string>();
		char c1 = vm["cplusplus"].as<int>();
		char c2 = vm["python"].as<int>();
		char c3 = vm["javascript"].as<int>();

		if (vm["cplusplus"].as<int>() == 999) {
			if (vm["python"].as<int>() == 777) {
				if (vm["javascript"].as<int>() == 233) {
					unsigned char enc_false_flag[25] = {
						0x4c,0x70,0x71,0x6b,0x38,0x71,0x6b,0x38,0x6c,
						0x70,0x7d,0x38,0x6f,0x6a,0x77,0x76,0x7f,0x38,
						0x7e,0x74,0x79,0x7f,0x36,0x36,0x36
					};
					for (int i = 0; i < 25; i++) {
						if (((unsigned char)__name[i] ^ (char)(c1 + c2 * c3)) != enc_false_flag[i]) {
							std::cout << "error" << std::endl;
							_exit(i);
						}
					}
				}
				std::cout << "You get the flag! flag{" << __name << "}" << std::endl;
				//flag{This is the wrong flag...}
			}
		}
	}
```

如果输入了name，会获得cpp、python、JavaScript的成绩，然后解密flag，最后输出一个假的flag



```C++
/* 计算表达式相关 */

	//为rule准备一个val变量，类型为double
	//准确的说：是一个phoenix类，它和其它的phoenix类组成lambda表达式，在lambda里可以看作一个double
	struct calc_closure :boost::spirit::closure<calc_closure, double> {
		member1 val;
	};
	//定义ContextT策略为calc_closure::context_t
	rule<phrase_scanner_t, calc_closure::context_t> factor, term, exp;
	//直接使用phoenix的lambda表达式作为Actor
	factor = real_p[factor.val = arg1] | ('(' >> exp[factor.val = arg1] >> ')');
	term = factor[term.val = arg1] >> *(('*' >> factor[term.val *= arg1]) | ('/' >> factor[term.val /= arg1]));
	exp = term[exp.val = arg1] >> *(('+' >> term[exp.val += arg1]) | ('-' >> term[exp.val -= arg1]));


	const char* szExp = vm["name"].as<std::string>().c_str();
	double result;
	parse_info<>r = parse(szExp, exp[assign_a(result)], space_p);
```





```C++
// 5e0*(5-1/5)==24

	if (strlen(szExp) != 11) {
		_exit(strlen(szExp));
	}


	int count_num = 0;
	int count_alpha = 0;

	for (int i = 0; i < strlen(szExp); i++) {
		if ((szExp[i] < '9') && (szExp[i] >= '0')) {
			count_num++;
		}
		else if ((szExp[i] > 'a') && (szExp[i] < 'z')) {
			count_alpha++;
		}
		else if ((szExp[i] > 'A') && (szExp[i] < 'Z')) {
			std::cout << "GG..." << std::endl;
			Sleep(100000000);
		}
		else if ((szExp[i] != '-') && (szExp[i] != '*') && (szExp[i] != '(')
			&& (szExp[i] != ')') && (szExp[i] != '/')) {
			_exit(-1);
		}
	}


	//只能有5个数字和1个小写字母，就是'e'
	if ((count_num != 5) || (count_alpha != 1)) {
		_exit(count_num);
	}
	else {
		if ((szExp[1] < 'a') || (szExp[1] > 'z')) {
			Sleep(10000000);
			std::cout << "You failed!" << std::endl;
		}
	}

	if (result - 24 < 0.0000001 || result - 24 > 0.0000001) {
		std::cout << "You finally get sth." << std::endl;
		std::cout << "Maybe you missed a code branch..." << std::endl;
		std::cout << "MD5 is 293316bfd246fa84e566d7999df88e79,You should check it!" << std::endl;
		std::cout << "de1ctf{" << vm["name"].as<std::string>() << "}" << std::endl;
	}
```

长度为11,5个数字，1个小写字母(只能是e)



因为只能使用乘除减，5551是比较容易想到的，但也不排除可能有其他解，给出了md5

根据浮点数的计算`(result - 24 < 0.0000001 || result - 24 > 0.0000001)`很容易反推是在计算 24点，最后输入的name就是flag