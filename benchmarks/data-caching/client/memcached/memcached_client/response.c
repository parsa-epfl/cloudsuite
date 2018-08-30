#include "response.h"
#ifdef GEM5
#include "m5op.h"
#endif

void receiveResponse(struct request* request, double difftime) {

  struct request* currentRequest = request;
  int finalRequest = 0;
//  printf("entered receiveResponse\n"); 
  int notFound = 0;
  while(currentRequest != NULL && notFound == 0){
    if(currentRequest->next_request == NULL){
      finalRequest = 1;
    } else {
      finalRequest = 0;
    }
    if(request->connection->protocol == TCP_MODE){
  //    printf("receiving request final? %d\n", finalRequest);
      notFound = tcpReceiveResponse(request,finalRequest, difftime);
    } else if(request->connection->protocol == UDP_MODE){
      notFound = udpReceiveResponse(request,finalRequest, difftime);
    } else {
      printf("Undefined protocol\n");
      exit(-1);
    }
    currentRequest = currentRequest->next_request;
  }//End while()

}//End receiveResponse()

int udpReceiveResponse(struct request* request, int final, double difftime) {

  #define MAX_DATAGRAM_SIZE 2048

  int readSize = MAX_DATAGRAM_SIZE*sizeof(char);
  char* dataGramBuffer = malloc(readSize);
  int fd = request->connection->sock;

  int nRead = read(fd, dataGramBuffer, readSize);
  if(nRead > MAX_DATAGRAM_SIZE){
    printf("Datagram is too big\n");
    exit(-1);
  }

  char* ptr = dataGramBuffer;
  char udpHeader[8];
  memcpy(&udpHeader, ptr, 8);
  ptr += 8;

  struct response_header response_header;
  memcpy(&response_header, ptr, sizeof(struct response_header));
  ptr += sizeof(struct response_header);

  int extrasSize = (int) response_header.extras_length;

  int keySize = 0;
  keySize |= response_header.key_length[1];
  keySize |= response_header.key_length[0] << 8;

  int bodySize = 0;
  bodySize |= response_header.total_body_length[3]&0xFF;
  bodySize |= (response_header.total_body_length[2]&0xFF) <<8;
  bodySize |= (response_header.total_body_length[1]&0xFF) <<16;
  bodySize |= (response_header.total_body_length[0]&0xFF) <<24;

  int valueSize = bodySize - keySize - extrasSize;

  char* extras = malloc(extrasSize);
  char* key = malloc(keySize+1);
  char* value = malloc(valueSize+1);

  memcpy(extras, ptr, sizeof(extrasSize));
  ptr += sizeof(extrasSize);
  memcpy(key, ptr, sizeof(keySize));
  ptr += sizeof(keySize);
  memcpy(value, ptr, sizeof(valueSize));
  ptr += sizeof(valueSize);


  key[keySize] = '\0';
  value[valueSize] = '\0';

  struct response response;
  response.request = request;
  response.value_size = valueSize;
  response.response_header = response_header;
  int notFound = processResponse(&response,final, difftime);

  free(extras);
  free(key);
  free(value);


  free(dataGramBuffer);

  return notFound;

}//End udpReceiveResponse()


int tcpReceiveResponse(struct request* request, int final, double difftime) {

  struct response_header response_header;
  int fd = request->connection->sock;
  readBlock(fd, &response_header, sizeof(response_header));

  //Check the magic number is correct
  if(response_header.magic != MAGIC_RESPONSE) {
    printf("On read Incorrect magic number: %x should be: %x\n", response_header.magic, MAGIC_RESPONSE);
    exit(-1);
  }
#ifdef GEM5
  m5_work_end(response_header.opcode, response_header.opaque);
#endif

#ifdef FLEXUS
  if(request->request_type == TYPE_GET)
    MAGIC2(211, request->header.opaque);
  else if(request->request_type==TYPE_SET)
    MAGIC2(221,request->header.opaque);	
#endif

  int extrasSize = (int) response_header.extras_length;

  int keySize = 0;
  keySize |= response_header.key_length[1];
  keySize |= response_header.key_length[0] << 8;

  int bodySize = 0;
  bodySize |= response_header.total_body_length[3]&0xFF;
  bodySize |= (response_header.total_body_length[2]&0xFF) <<8;
  bodySize |= (response_header.total_body_length[1]&0xFF) <<16;
  bodySize |= (response_header.total_body_length[0]&0xFF) <<24;

  int valueSize = bodySize - keySize - extrasSize;

  char* extras = malloc(extrasSize);
  char* key = malloc(keySize+1);
  char* value = malloc(valueSize+1);
  readBlock(fd, extras, extrasSize);
  readBlock(fd, key, keySize);
  key[keySize] = '\0';

  readBlock(fd, value, valueSize);

  value[valueSize] = '\0';
  struct response response;
  response.request = request;
  response.value_size = valueSize;
  response.response_header = response_header;
  int notFound = processResponse(&response, final, difftime);

  free(extras);
  free(key);
  free(value);
   
  return notFound;

}//End tcpReceiveRequest()



int processResponse(struct response* response, int final, double difftime){

  struct response_header* response_header;
  response_header = &response->response_header;
  //Make sure the request didn't fail somehow
  int errorCode = 0;
  errorCode |= response_header->status[1];
  errorCode |= response_header->status[0] << 8;
  checkError(errorCode, response->request->key, response->request->value);

  int type = response->request->request_type;

  //Check if it's a failed INCR
  if( type == TYPE_INCR && errorCode != 0) {
    int op = SET;
    int type = TYPE_SET;
    char* key = response->request->key;
    struct worker* worker = response->request->worker;
    struct conn* conn = response->request->connection;
    struct request* request = createRequest(op, conn, worker, key, 0, type);
    request->next_request = NULL;
    if( ((worker->incr_fix_queue_tail + 1) % INCR_FIX_QUEUE_SIZE) == worker->incr_fix_queue_head) {
      printf("I was hoping this would never happen\n");
      exit(-1);
    }
    worker->incr_fix_queue[worker->incr_fix_queue_tail] = request;
    worker->incr_fix_queue_tail = (worker->incr_fix_queue_tail + 1) % INCR_FIX_QUEUE_SIZE;
  }


  //Update stats
  pthread_mutex_lock(&stats_lock);

  global_stats.ops++;
  //Check if this was a hit or miss
  if(errorCode == 0) {
    if(response_header->opcode == OP_GET || response_header->opcode == OP_GETQ) {
      global_stats.hits++;
    }
  } else {
    struct request* currentRequest = response->request;
    while(currentRequest != NULL) {
      global_stats.misses++;
      currentRequest = currentRequest->next_request;
    }//End while
  }

  if(type == TYPE_GET || type == TYPE_MULTIGET) {
    addSample(&global_stats.get_size, response->value_size);
  }

  if(!(errorCode == 1 || final == 1)){
    pthread_mutex_unlock(&stats_lock);
    return 0;
  }

  global_stats.requests++;
  if(type == TYPE_GET) {
    global_stats.gets++;
    addSample(&global_stats.get_size, response->value_size);
//    printf("Size is %d\n", response->value_size);
  } else if(type == TYPE_SET) {
    global_stats.sets++;
  } else if(type == TYPE_MULTIGET) {
    global_stats.multigets++;
  } else if(type == TYPE_INCR) {
    global_stats.incrs++;
  }

  addSample(&global_stats.response_time, difftime);

  pthread_mutex_unlock(&stats_lock);

  return 1;

}//End processResponse()

void checkError(int errorCode, char* key, char* value){

  switch(errorCode) {

    case NO_ERROR:
      return;
      break;

    case KEY_NOT_FOUND:
      return;
      break;

    case KEY_EXISTS:
      //printf("Key exists\n%s\n",value);
      return;
      break;

    case VALUE_TOO_LARGE:
      printf("Value too large\n%s\n", value);
      break;

    case INVALID_ARGUMENT:
      printf("Invalid argument\n key: %s value: %s\n", key, value);
      break;

    case ITEM_NOT_STORED:
      printf("Item not stored\n%s\n", value);
      break;

    case INC_DCR_NON_NUM:
//      printf("Incr/Decr on non-numeric value\n%s\n", value);
      return;
      break;

    case UNKNOWN_COMMAND:
      printf("Unknown command\n%s\n", value);
      break;

    case OUT_OF_MEMORY:
      printf("Out of memory\n%s\n", value);
      return;
      break;

    default:
      printf("Unknown error code\n%s\n", value);
  }

  printf("key: %s, value: %s\n", key, value);
  exit(-1);

}//End checkError()


