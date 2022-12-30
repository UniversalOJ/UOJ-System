#include <iostream>
#include <thread>
#include <csignal>
#include <unistd.h>

using namespace std;

int main() {
	while (clock() < 6.666 * CLOCKS_PER_SEC) {
		for (int i = 0; i < 500000; i++);
	}

	return 0;
}
