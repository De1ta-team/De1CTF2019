#include<boost/spirit/include/qi.hpp>
#include<boost/spirit/include/phoenix.hpp>
#include<boost/spirit/include/phoenix_object.hpp>
#include<boost/lexical_cast.hpp>
#include<boost/random.hpp>
#include<string>
#include<string.h>
#include<time.h>
#include<assert.h>
#include<windows.h>


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


int main(int argc, char** argv) {
	//anti-debug
	int time1 = time(0);

	std::string input;
	std::cin >> input;
	st res = boostFn(input);
	//get unsigned short  num1,num2,num3
	boostFunc(res.num1);	// num1 = 78 - 44 = 34


	std::string str = boost::lexical_cast<std::string>(res.num2);
	//unsigned short -> std::string
	if (str.length() != 5) {
		_exit(0);
	}
	int time2 = time(0);
	if (time2 - time1 > 3) {
		//anti debug
		_exit(0);
	}
	const char* what = "eQDtW91a0qwryuLZvbXCEK8VghjklzxIOPASBNM2RsdfF56TYU34p7ioGHJcnm";
	int len = strlen(what);


	//20637
	if (what[str[0] - '0'] == 'D') {
		if (what[str[1] - '0'] == 'e') {
			if (what[str[2] - '0'] == '1') {
				if (what[str[3] - '0'] == 't') {
					if (what[str[4] - '0'] == 'a') {
						goto out;
					}
				}
			}
		}
	}
	Sleep(5);
	_exit(0);
out:
	//pass

	int time3 = time(0);
	if (time3 - time2 > 2) {
		_exit(0);
	}

	if ((res.num3 % res.num1 != 12) && (res.num3 / res.num1) != 3) {
		//3 * 34 + 12 == 114
		std::cout << "You failed...again";
		_exit(0);
	}
	std::cout << "Your flag is:" << std::endl;
	std::cout << "de1ctf{" << input << "}" << std::endl;

	return 0;
}