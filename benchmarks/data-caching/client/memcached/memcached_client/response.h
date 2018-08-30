#ifndef RESPONSE_H
#define RESPONSE_H

#include "request.h"
#include "worker.h"

//Error codes
#define NO_ERROR         ((char) 0x0000) 
#define KEY_NOT_FOUND    ((char) 0x0001)
#define KEY_EXISTS       ((char) 0x0002)
#define VALUE_TOO_LARGE  ((char) 0x0003)
#define INVALID_ARGUMENT ((char) 0x0004)
#define ITEM_NOT_STORED  ((char) 0x0005)
#define INC_DCR_NON_NUM  ((char) 0x0006)
#define UNKNOWN_COMMAND  ((char) 0x0081)
#define OUT_OF_MEMORY    ((char) 0x0082)


struct response_header {
  char magic;
  char opcode;
  char key_length[2];
  char extras_length;
  char data_type;
  char status[2];
  char total_body_length[4];
  uint32_t opaque;
  char CAS[8];
};

struct udp_response_header{
  struct udp_header* udp_header;
  struct response_header response_header;
};


struct response {
  struct request* request;
  struct response_header response_header;
  int value_size;
};

void receiveResponse(struct request* request, double difftime);
int udpReceiveResponse(struct request* request, int final, double difftime);
int tcpReceiveResponse(struct request* request, int final, double difftime);
int processResponse(struct response* response, int final, double difftime);

void checkError(int errorCode, char* key, char* value);

#endif
