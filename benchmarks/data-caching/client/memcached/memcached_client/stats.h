//
//  stats.h
//
//  Author: David Meisner (meisner@umich.edu)
//

#ifndef STATS_H
#define STATS_H

#include "loader.h"
#include "config.h"
#include <math.h>
#include <pthread.h>
#include <stdio.h>
#include <string.h>
#include <sys/time.h>


//#define N_HISTOGRAM_BINS 10000
//#define MIN_HISTOGRAM_VALUE 10e-6
//#define MAX_HISTOGRAM_VALUE 10

struct config;

struct timeval start_time;

//A single statistic
struct stat {
  double s0;
  double s1;
  double s2;
  double min;
  double max;
  int fulls[1000];
  int thousands[1000];
  int millis[50010];
  int micros[10000];
};

struct memcached_stats {
  int requests;
  int ops;
  int gets;
  int multigets;
  int sets;
  int hits;
  int misses;
  int multi_gets;
  int incrs;
  int adds;
  int replaces;
  int deletes;
  struct stat response_time;
  struct stat get_size;
  struct timeval last_time;
};

extern pthread_mutex_t stats_lock;
//For now, all statistics are handled by this global struct
struct memcached_stats global_stats;
double findQuantile(struct stat* stat, double quantile);
void printGlobalStats();
void checkExit(struct config* config);
void addSample(struct stat* stat, float sample);
double getAvg(struct stat* stat);
double getStdDev(struct stat* stat);
void statsLoop(struct config* config);


#endif

