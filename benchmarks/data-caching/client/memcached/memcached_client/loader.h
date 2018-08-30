// client - a memcached load tester
// David Meisner 2010 (meisner@umich.edu)

#ifndef CLIENT_H
#define CLIENT_H


#include <event2/event.h>
#include <malloc.h>
#include <math.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <sys/time.h>
#include <unistd.h>
#include <time.h> 
#include <errno.h> 
#include "config.h"
#include "worker.h"
#include "util.h"
#include <pthread.h>
#include "stats.h"
#include "generate.h"



struct config* parseArgs(int argc, char** argv);
void printConfiguration(struct config* config);
void loadServerFile(struct config* config);
void cleanUp(struct config* config);
int main(int argc, char** argv);

#endif
