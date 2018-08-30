// client - a memcached load tester
// David Meisner 2010 (meisner@umich.edu)

#ifndef CONFIG_H
#define CONFIG_H
#include <stdint.h>
#include "generate.h"
#include "pthread.h"

#define TCP_MODE 0
#define UDP_MODE 1
#define MAX_SERVERS 4

//#define FLEXUS

#ifdef FLEXUS
#include "magic2_breakpoint.h"
#endif
struct config {
  int protocol_mode;

  int n_cpus;
  int n_keys;

  int n_workers;
  int n_servers;
  struct worker** workers;
  int n_connections_total;

  int run_time;
  int stats_time;
  int naggles;
  int multiget_size;
  char* server_ip_address[MAX_SERVERS];
  char* server_file;
  char* input_file;
  char* output_file;
  int server_port[MAX_SERVERS];
  int server_memory;
  int keysToPreload;
  int scaling_factor;
  float get_frac;
  float multiget_frac;
  float incr_frac;
  struct key_list* key_list;
  struct int_dist* key_pop_dist;
  struct int_dist* value_size_dist;
  struct int_dist* multiget_dist;
  struct int_dist* interarrival_dist;
  struct dep_dist* dep_dist;
  int arrival_distribution_type;
  int received_warmup_keys;
  int rps;
  int fixed_size;
  int zynga;
  int random_seed;
  int pre_load;
  int bad_multiget;

  uint32_t current_request_uid;


};

#endif
