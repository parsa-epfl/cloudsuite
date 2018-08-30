
#ifndef REQUEST_H
#define REQUEST_H

#include "conn.h"
#include "worker.h"
#include "util.h"

#define MAGIC_REQUEST  ((char) 0x80)
#define MAGIC_RESPONSE ((char) 0x81)

#define OP_STAT ((char) 0x10)
#define OP_GET  ((char) 0x00)
#define OP_SET  ((char) 0x01)
#define OP_GETQ  ((char) 0x09)
#define OP_INCR  ((char) 0x05)
#define OP_DEL  ((char) 0x04)
#define OP_ADD  ((char) 0x02)
#define OP_REP  ((char) 0x03)

#define TYPE_GET 0
#define TYPE_SET 1
#define TYPE_MULTIGET 2
#define TYPE_INCR 3
#define TYPE_DEL 4
#define TYPE_ADD 5
#define TYPE_REP 6



#define GET 0
#define SET 1
#define GETQ 2
#define STAT 3
#define INCR 4
#define DEL 5
#define ADD 6
#define REP 7

#define MEMCACHE_HEADER_SIZE 24
#define MAX_KEY_LENGTH 250
#define MAX_VALUE_LENGTH (1024*1024)

struct request_header {
  char magic;
  char opcode;
  char key_length[2];
  char extras_length;
  char data_type;
  char reserved[2];
  unsigned char total_body_length[4];
  uint32_t opaque;
  char CAS[8];
};

struct udp_header {
  char request_id[2];
  char sequence_number[2];
  char n_datagrams[2];
  char reserved[2];
};

struct request{
  struct request_header header;
  struct conn* connection;
  struct worker* worker;
  char* extras;
  char* key;
  int key_size;
  char* value;
  int value_size;
  struct timeval send_time;
  int id;
  struct request* next_request;
  int request_type;
  int warmup_index;
  int bad_multiget;
};


struct request* createRequest(int requestType, struct conn* conn, struct worker* worker, char* key, char* value, int type);
void sendRequest(struct request* request);
void tcpSendRequest(struct request* request);
void udpSendRequest(struct request* request);
void receiveRequest(struct request* request);
void deleteRequest(struct request* request);


#endif
