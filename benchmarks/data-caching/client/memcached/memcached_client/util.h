// client - a memcached load tester
// David Meisner 2010 (meisner@umich.edu)

#ifndef UTIL_H
#define UTIL_H

#include <stdio.h>
#include <stdlib.h>
#include <netdb.h>
#include <arpa/inet.h>
#include "stats.h"

#define DEBUG_READ_WRITE 0
struct worker;
void writeBlock(int fd, void* buffer, int writeSize);
void readBlock(int fd, void* buffer, int readSize);

char* nslookup(char* hostname);
void timingTests();
void timestampTest();
void lockTest();
int randomFunction();
int parRandomFunction(struct worker* worker);
double round(double d);


#endif
