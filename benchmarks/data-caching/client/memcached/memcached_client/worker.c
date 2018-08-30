
#include "worker.h"

void* workerFunction(void* arg) {

  struct worker* worker = arg;
  printf("Creating worker on tid %u\n", (unsigned int)pthread_self());


/*  int s;
  cpu_set_t cpuset;
  pthread_t thread;
  thread = pthread_self(); */

//  sgenrand(worker->cpu_num, worker->config->random_seed);

  struct timeval timestamp;
  gettimeofday(&timestamp, NULL);
  int seed=(timestamp.tv_usec+worker->cpu_num)%2309;			 
  sgenrand(seed, &(worker->myMT19937p));
// sgenrand(worker->config->random_seed, &(worker->myMT19937p));
/* Set affinity mask to include CPUs 0 to 7 */
/*  CPU_ZERO(&cpuset);
  CPU_SET(worker->cpu_num, &cpuset);
  s = pthread_setaffinity_np(thread, sizeof(cpu_set_t), &cpuset);
  if (s != 0) {
    printf("Couldn't set CPU affinity\n");
  } else {
    printf("Set tid %u affinity to CPU %d\n", (unsigned int)pthread_self(), worker->cpu_num);
  }
*/
  workerLoop(worker);

  return NULL;

}//End workerFunction()


void workerLoop(struct worker* worker) {

  event_base_priority_init(worker->event_base, 2);

  //Seed event for each fd
  int i;
  for( i = 0; i < worker->nConnections; i++) {

    struct event* ev = event_new(worker->event_base, worker->connections[i]->sock, EV_WRITE|EV_PERSIST, sendCallback, worker);
    event_priority_set(ev, 1);
    event_add(ev, NULL);

    ev = event_new(worker->event_base, worker->connections[i]->sock, EV_READ|EV_PERSIST, receiveCallback, worker);
    event_priority_set(ev, 2);
    event_add(ev, NULL);

  }//End for i

  gettimeofday(&(worker->last_write_time), NULL);
  printf("starting receive base loop\n");
  int error = event_base_loop(worker->event_base, 0);

  if(error == -1) {
    printf("Error starting libevent\n");
  } else if(error == 1) {
    printf("No events registered with libevent\n");
  }

  printf("base loop done\n");

}//End workerLoop()


int pushRequest(struct worker* worker, struct request* request) {

//  printf("push: size %d head %d tail %d\n", worker->n_requests, worker->head, worker->tail);

  if(worker->n_requests == QUEUE_SIZE){
    printf("Reached queusize max\n");
    return 0;
  }
  
  worker->request_queue[worker->tail] = request;
  worker->tail = (worker->tail + 1) % QUEUE_SIZE;
  worker->n_requests++;

  return 1;

}//End pushRequest()

struct request* getNextRequest(struct worker* worker) {

  if(worker->n_requests == 0) {
    return NULL;
  }

  struct request* request = worker->request_queue[worker->head];
  worker->head = (worker->head + 1) % QUEUE_SIZE;
  worker->n_requests--;

  return request;

}//End getNextRequest()

void sendCallback(int fd, short eventType, void* args) {
  struct worker* worker = args;
  struct timeval timestamp, timediff, timeadd;
  gettimeofday(&timestamp, NULL);

  timersub(&timestamp, &(worker->last_write_time), &timediff);
  double diff = timediff.tv_usec * 1e-6  + timediff.tv_sec;

  struct int_dist* interarrival_dist = worker->config->interarrival_dist;
  int interarrival_time  = 0;
  //Null interarrival_dist means no waiting
  if(interarrival_dist != NULL){
    if(worker->interarrival_time <= 0){
        interarrival_time = getIntQuantile(interarrival_dist); //In microseconds
        //   printf("new interarrival_time %d\n", interarrival_time);
        worker->interarrival_time = interarrival_time;
    } else {
        interarrival_time = worker->interarrival_time; 
    }
      if( interarrival_time/1.0e6 > diff){
          return;
      }
  }
  worker->interarrival_time = -1;

  timeadd.tv_sec = 0; timeadd.tv_usec = interarrival_time; 
  timeradd(&(worker->last_write_time), &timeadd, &(worker->last_write_time));

  struct request* request = NULL;
  if(worker->incr_fix_queue_tail != worker->incr_fix_queue_head) {
    request = worker->incr_fix_queue[worker->incr_fix_queue_head];
    worker->incr_fix_queue_head = (worker->incr_fix_queue_head + 1) % INCR_FIX_QUEUE_SIZE;
//    printf("fixing\n");
  } else {
  //  printf(")preload %d warmup key %d\n", worker->config->pre_load, worker->warmup_key);
    if(worker->config->pre_load == 1 && worker->warmup_key < 0) {
      return;
    } else {
      request = generateRequest(worker->config, worker);
    }
  }
  if(request->header.opcode == OP_SET){
//    printf("Generated SET request of size %d\n", request->value_size);
  }
  if( !pushRequest(worker, request) ) {
    //Queue is full, bail
//    printf("Full queue\n");
    deleteRequest(request);
    return;
  }


  sendRequest(request);
  
 
}//End sendCallback()


void receiveCallback(int fd, short eventType, void* args) {

  struct worker* worker = args;

  struct request* request = getNextRequest(worker);
  if(request == NULL) { 
    printf("Error: Tried to get a null request\n");
    return;
  }

  struct timeval readTimestamp, timediff;
  gettimeofday(&readTimestamp, NULL);
  timersub(&readTimestamp, &(request->send_time), &timediff);
  double diff = timediff.tv_usec * 1e-6  + timediff.tv_sec;

  receiveResponse(request, diff); 
  deleteRequest(request);
  worker->received_warmup_keys++;

  if(worker->config->pre_load == 1 && worker->config->dep_dist != NULL && worker->received_warmup_keys == worker->config->keysToPreload){
    printf("You are warmed up, sir\n");
    exit(0);
  }
}//End receiveCallback()

void readF(int* temp){
 FILE *fp;
 char buff[4]={0};
 fp = fopen("cpu.txt","r");
 if(!fp) return;
 *temp=0; 
 while (fgets(buff,4, fp)!=NULL){
        printf("%s",buff);
         *temp=atoi(buff);
 } 
 fclose(fp);	
}

void writeF(int temp){
 FILE *fp;
 fp = fopen("cpu.txt","a");
 fprintf(fp, "%d\n",temp);
 fclose(fp);
}



void createWorkers(struct config* config) {

  config->workers = malloc(sizeof(struct worker*)*config->n_workers);
  int i;
  for( i = 0; i < config->n_workers; i++) {
    config->workers[i] = createWorker(config, i);
  }

  if(config->n_workers > config->n_connections_total ) {
    printf("Overridge n_connections_total because < n_workers\n");
    config->n_connections_total = config->n_workers;
  }

  int total_connections = 0;
  for(i = 0; i < config->n_workers; i++) {
    int num_worker_connections = config->n_connections_total/config->n_workers + (i < config->n_connections_total % config->n_workers);
    printf("num_worker_connections %d\n", num_worker_connections);
    total_connections += num_worker_connections;
    config->workers[i]->connections = malloc(sizeof(struct conn*) * num_worker_connections);
    config->workers[i]->nConnections = num_worker_connections;
    config->workers[i]->received_warmup_keys = 0;
    int j;
    int server=i % config->n_servers; 
    for(j = 0; j < num_worker_connections; j++) {
      config->workers[i]->connections[j] = createConnection(config->server_ip_address[server], config->server_port[server], config->protocol_mode, config->naggles);
    }
    int rc;
    //Create receive thread
    rc = pthread_create(&config->workers[i]->thread, NULL, workerFunction, config->workers[i]);
    if(rc) {
      printf("Error creating receive thread\n");
    }
  }
  printf("Created %d connections total\n", total_connections);


}//createWorkers()


struct worker* createWorker(struct config* config, int cpuNum) {

  //Create connections
  struct worker* worker = malloc(sizeof(struct worker));
  worker->event_base = event_base_new();


  worker->config = config;
  worker->head = 0;
  worker->tail = 0;
  worker->n_requests = 0;
  worker->cpu_num = cpuNum;
  worker->interarrival_time = 0;
  worker->incr_fix_queue_tail = 0; // THSES probably need to be fixed
  worker->incr_fix_queue_head = 0;
  if(config->dep_dist != NULL && config->pre_load) {
    worker->warmup_key = config->keysToPreload-1;
    worker->warmup_key_check = 0;
  }


  return worker;

}//End createWorker()


