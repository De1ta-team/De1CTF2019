## Cplusplus

> 源代码`Cplusplus.cpp`
>
> 附件`Cplusplus.exe`
>
> 编译`compile.txt`

### 分析

```C++
struct st {
	unsigned short num1;
	unsigned short num2;
	unsigned short num3;
};

st boostFn(const std::string& s) {
	using boost::spirit::qi::_1;
	using boost::spirit::qi::ushort_;
	using boost::spirit::qi::char_;
	using boost::phoenix::ref;

	struct st res;
	const char* first = s.data();
	const char* const end = first + s.size();
	bool success = boost::spirit::qi::parse(first, end,
		ushort_[ref(res.num1) = _1] >> char('@')
		>> ushort_[ref(res.num2) = _1] >> char('#')
		>> ushort_[ref(res.num3) = _1]
	);

	if (!success || first != end) {
		//throw std::logic_error("Parsing failed");
		_exit(0);
	}
	return res;
}
```

这段代码是`boost::spirit`相关，输入形如`num1@num2#num3`，用`@ #`分割三个`unsigned short`数值



```C++
void boostFunc(unsigned short& num) {
	//随机数check
	//预期的num是78
	if (num > 111) {
		_exit(0);
	}
	boost::mt19937 rng(num);
	rng.discard(num % 12);
	//拷贝构造，保留了所有状态
	boost::mt19937 rng_(rng);
	rng_.discard(num / 12);
	//这里相当于丢弃了num个随机结果
	if (rng_() != 3570126595) {
		_exit(0);
	}
	num -= (rng_() % 45);	// 45
}
```

一个`unsigned short`传入，小于等于111，把它作为随机引擎的种子，丢弃掉`num % 12`个随机数，然后用一次随机引擎的拷贝构造

> 注意，这里拷贝构造会完全保留随机引擎的状态，而不是回归初始状态
>
> 在IDA中就表现为直接一个memcpy

接着再丢弃掉`num/12`个随机数

然后输出一个随机数要求等于`3570126595`，最后由于是引用，传入的数值被改变



后面第二段check没什么好说的

第三段check是我的锅

```C++
if ((res.num3 % res.num1 != 12) && (res.num3 / res.num1) != 3) {
		//3 * 34 + 12 == 114
		std::cout << "You failed...again";
		_exit(0);
	}
```

这里出现了多解，后来排查发现是`||`被我误写为`&&`，因此只要满足右边的式子就会输出flag，最后加上了`md5`保证唯一解

这是我的失误，表示抱歉



### 解题

其实没有准备exp，不过因为第一段check中限制了数值大小只有一百多种可能，即使没有看懂程序逻辑，爆破也比较简单

