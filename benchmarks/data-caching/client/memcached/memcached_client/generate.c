
#include "generate.h"
#include "request.h"
#include "worker.h"
#include "util.h"

//
//  randomString()
//  Creates a string of length size
//
//  params:
//    size - Length of random string in chars, last char is \0
//
//  returns:
//    a pointer to a the string
//
char* randomString(int size){

  static const char text[] = "abcdefghijklmnopqrstuvwxyz"
                        "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

  char* randomString = malloc(sizeof(char) * size);

  int i;
  for( i = 0; i < size - 1; i++ ){
    randomString[i] = text[randomFunction() % (sizeof(text) - 1)];
  }

  randomString[i++] = '\0';

  return randomString;

}//End randomStr()


struct key_list* generateKeys(struct config* config) {


  struct key_list* keyList = malloc(sizeof(struct key_list));
  keyList->n_keys = config->n_keys;
  keyList->keys = malloc(sizeof(char*) * keyList->n_keys);

  int i;
  for( i = 0; i < keyList->n_keys; i++ ){ 
    int keySize = randomFunction() % MAX_KEY_SIZE;
    while(keySize <= 1){
      keySize = randomFunction() % MAX_KEY_SIZE;
    }
    keyList->keys[i] = randomString(keySize);
    //printf("i: %d key: %s\n", i, keyList->keys[i]);
  }//End for i

  return keyList;

}//End generateKeys()

struct dep_entry* getRandomDepEntry(struct dep_dist* dep_dist, struct worker* worker){

  double cdf_to_lookup = (parRandomFunction(worker) % 100000000)/100000000.0;
  //Do a binary search
  int top = 0;
  int bottom = dep_dist->n_entries-1;
  int current = bottom/2;
  struct dep_entry* dep_entry = NULL;
  while(top != bottom){
    dep_entry = dep_dist->dep_entries[current];
//    printf("top %d bottom %d current %d lookup %f cdf %f\n", top, bottom, current, cdf_to_lookup, dep_entry->cdf);
    if( dep_entry->cdf > cdf_to_lookup){
      bottom = current;
    } else {
      top = current + 1;
    }
    current = (bottom-top)/2 + top;
  }

 //  printf("top %d bottom %d current %d lookup %f cdf %f\n", top, bottom, current, cdf_to_lookup, dep_entry->cdf);

  return dep_entry;  
}

int getIntQuantile(struct int_dist* dist) {

  int quantileIndex = (randomFunction() % CDF_VALUES); 
  int value = dist->cdf_y[quantileIndex];
  //printf("index %d value %d\n", quantileIndex, value);

  return value;

}//End getQuantile()

struct int_dist* createConstantDistribution(int constant){

  struct int_dist* dist = malloc(sizeof(struct int_dist));
  int nValues = CDF_VALUES;
  int i;
  for( i = 0; i < nValues; i++ ){
    dist->cdf_y[i] = constant;
  }//End for i

  return dist;

}//End createConstantDistribution()

struct int_dist* createExponentialDistribution(int meanInterarrival) {

  struct int_dist* dist = malloc(sizeof(struct int_dist));
  int nValues = CDF_VALUES;
  int i;
  for( i = 0; i < nValues; i++ ){
    int value = (int)(-log(1- (double)i/(double)nValues) * meanInterarrival);
    dist->cdf_y[i] = value;
  }//End for i

  return dist;

}//End createExponentialDistribution()

struct int_dist* createUniformDistribution(int min, int max) {

  struct int_dist* dist = malloc(sizeof(struct int_dist));
  int nValues = CDF_VALUES;
  int i;
  double delta = (max - min)/((double)nValues);
  for( i = 0; i < nValues; i++ ){
    dist->cdf_y[i] = (int)(round(delta*i + min));
//    printf("%d, %d\n", i, dist->cdf_y[i]);
  }//End for i

  return dist;

}//createUniformDistribution()

struct int_dist* loadDistributionFile(char* filename) {

  struct int_dist* dist = malloc(sizeof(struct int_dist));

  FILE* file = fopen(filename, "r");
  char lineBuffer[1024];
  int i = 0;
  while (fgets(lineBuffer, sizeof(lineBuffer), file)) {
     // cdfValue is intentionally unused here
    strtok(lineBuffer, " ,");
    char* sizeValue = strtok(NULL, " ,");
    dist->cdf_y[i] = atoi(sizeValue);
    i++;
  }//End while()

  if(i != CDF_VALUES){
    printf("csv file didn't have the expect number of lines\n");
    exit(-1);  
  }

  fclose(file);

  return dist;

}//End loadDistributionFile()

double harmonicSum(int size, double alpha){
 double sum=0;
 int i;
 for(i=1; i<=size; i++)
   sum+= (1.0 / pow(1.0*i, alpha));
 return sum;
}
struct dep_dist* loadDepFile(struct config* config) { 

  printf("Loading key value file...");
  struct dep_dist* dist = malloc(sizeof(struct dep_dist));

  char lineBuffer[1024];
  int lines = 0;
  FILE* file = fopen(config->input_file, "r");
  while (fgets(lineBuffer, sizeof(lineBuffer), file)) {
    lines++;
  }
  fclose(file);
  dist->dep_entries = malloc(sizeof(struct dep_entry*)*lines);
  dist->n_entries = lines;
  int i = lines-1;
  file = fopen(config->input_file, "r");
  double avg_size=0; 
  while (fgets(lineBuffer, sizeof(lineBuffer), file)) {
    char* cdfValue = strtok(lineBuffer, " ,\n");
    char* sizeValue = strtok(NULL, " ,\n");
    char* key = strtok(NULL, " ,\n");
    struct dep_entry* entry = malloc(sizeof(struct dep_entry));
    entry->cdf = atof(cdfValue);
    entry->size = atoi(sizeValue);
    strcpy(entry->key, key);
    dist->dep_entries[i] = entry;
    i--;   
    avg_size+=entry->size; 
  }//End while()
  avg_size = avg_size/lines;
  config->keysToPreload = floor(1024.0*1024*config->server_memory/(avg_size+150));
  if(config->keysToPreload>lines) config->keysToPreload=lines-1;
  fclose(file);
#ifdef FLEXUS
  MAGIC2(200, 0);
#endif
  return dist;
}

struct dep_dist* loadAndScaleDepFile(struct config* config) { 

  printf("Loading key value file...");
  struct dep_dist* dist = malloc(sizeof(struct dep_dist));

  char lineBuffer[1024];
  int lines = 0;
  FILE* file = fopen(config->input_file, "r");
  FILE* fileOut = fopen(config->output_file, "w");
  while (fgets(lineBuffer, sizeof(lineBuffer), file)) {
    lines++;
  }
  fclose(file);
  
	  int newLines=lines*config->scaling_factor;
	  double sum2=harmonicSum(newLines, ALPHA);
	  double ratio=harmonicSum(lines, ALPHA)/sum2;
	  
	  dist->dep_entries = malloc(sizeof(struct dep_entry*)*newLines);
	  dist->n_entries = newLines;

	  int i = newLines-1;
	  file = fopen(config->input_file, "r");
	  double prev=1.0, oldPrev=1.0;
          double avg_size=0.0; 
	  while (fgets(lineBuffer, sizeof(lineBuffer), file)) {

	    char* cdfValue = strtok(lineBuffer, " ,\n");
	    char* sizeValue = strtok(NULL, " ,\n");
	    struct dep_entry* entry = malloc(sizeof(struct dep_entry));
	    double temp = entry->cdf = atof(cdfValue);
	    entry->cdf = prev-(oldPrev-temp)*ratio;
	    oldPrev=temp;
	    entry->size = atoi(sizeValue);
	    int keySize = randomFunction() % MAX_KEY_SIZE;
	    while(keySize <= 1) keySize = randomFunction() % MAX_KEY_SIZE;
	    char* newKey = randomString(keySize);
	    strcpy(entry->key, newKey);
	    dist->dep_entries[i] = entry;
            fprintf(fileOut, "%15.13f,  %d, %s\n", entry->cdf, entry->size, entry->key); 
	    prev = entry->cdf;
	    i--;    
	    avg_size+=entry->size; 	
	  }//End while()
	  fclose(file);
	  avg_size = avg_size/lines;
          config->keysToPreload = floor(1024.0*1024*config->server_memory/(avg_size+150));
          if(config->keysToPreload > newLines) config->keysToPreload = newLines;
 	  printf("Average Size = %10.5f\n", avg_size ); 
 	  printf("Keys to Preload = %d\n", config->keysToPreload ); 
	  while(i>=0){
	    struct dep_entry* entry = malloc(sizeof(struct dep_entry));
	    entry->cdf = prev-(1.0/pow(newLines-i, ALPHA))/sum2; 
	    entry->size = dist->dep_entries[lines+i]->size;
	    int keySize = randomFunction() % MAX_KEY_SIZE;
	    while(keySize <= 1) keySize = randomFunction() % MAX_KEY_SIZE;
	    char* newKey = randomString(keySize);
	    strcpy(entry->key, newKey);
	    dist->dep_entries[i] = entry;
            fprintf(fileOut, "%15.13f, %d, %s\n", entry->cdf, entry->size, entry->key); 
	    prev = entry->cdf;
            i--;
	    if(entry->cdf<0) printf("cdf=%10.8f\n", entry->cdf);
 	   }
  fclose(fileOut); 
#ifdef FLEXUS
  MAGIC2(200, 0);
#endif
  return dist;
}

struct request* generateRequest(struct config* config, struct worker* worker) {

  //Pick a random connection
  struct conn* conn = worker->connections[randomFunction(worker) % worker->nConnections];

  char* value = NULL;
  int valueSize = 0;
  char* key = NULL;

  int warmup_index = 0;
  if(config->dep_dist != NULL) {
  //printf("generating..\n");
    struct dep_entry* dep_entry = NULL;
    if(config->pre_load) {

      if(worker->warmup_key == -1) {
        printf("doh\n");
      }
      dep_entry = config->dep_dist->dep_entries[config->dep_dist->n_entries - worker->warmup_key_check-1];
      warmup_index = worker->warmup_key;
      worker->warmup_key--;
      worker->warmup_key_check++;
      key = dep_entry->key;
      valueSize = dep_entry->size;
      value = malloc(sizeof(char) * valueSize);
      memset(value, 'a', sizeof(char) * valueSize);
      value[valueSize-1] = '\0';
      int op = SET;
      int type = TYPE_SET;
      struct request* request;
      //printf("Picked key %d size %d\n", worker->warmup_key, valueSize);
      request = createRequest(op, conn, worker, key, value,type);
      request->next_request = NULL;
      return request;

    } else { 
     dep_entry = getRandomDepEntry(config->dep_dist, worker);
    }
    key = dep_entry->key;
    valueSize = dep_entry->size;
  //printf("key %s valueSize %d\n", key, valueSize);
  //Pick a key
  }else{
    int keyIndex = getIntQuantile(config->key_pop_dist);
    key = config->key_list->keys[keyIndex];
    if(strlen(key) == 0){
      printf("zero length key: <%s> index %d\n", key, keyIndex);
    }
  }

  //Pick a request type
  struct request* request = NULL;
  int op = 0;
  double rand = ((randomFunction(worker) % 10000)/10000.0);
     

   if (rand < config->incr_frac) {

      op = INCR;
      int type = TYPE_INCR;
      int keyIndex = getIntQuantile(config->key_pop_dist);
      key = config->key_list->keys[keyIndex];

      request = createRequest(op, conn, worker, key, value,type);
      request->next_request = NULL;      

  } else if( ((randomFunction(worker) % 10000)/10000.0) < config->get_frac) {

    //See if this should be a multiget
    rand = ((randomFunction(worker) % 10000)/10000.0);
//    if(rand < config->multiget_frac) {
    if(rand < config->multiget_frac) {
      //printf("generating multiget\n");
      //Yes it's a multiget
      int nGets;
      if(config->multiget_size == -1){
        nGets = getIntQuantile(config->multiget_dist);
      } else {
        nGets = config->multiget_size;
      }
      op = GETQ;
      int type = TYPE_MULTIGET;
      request = createRequest(op, conn, worker, key, value,type);
      //String together requests in linked list
      struct request* currentRequest = request;
       currentRequest->bad_multiget = 0;

      if(worker->config->bad_multiget)
           currentRequest->bad_multiget = 1;

      int i;
      for(i = 0; i < nGets-2; i++){
        struct request* nextRequest = createRequest(op, conn, worker, key, value, type);
        currentRequest->next_request = nextRequest;
        if(worker->config->bad_multiget)
          currentRequest->bad_multiget = 1;

        currentRequest = nextRequest;
        if(config->dep_dist != NULL){
          struct dep_entry* dep_entry = getRandomDepEntry(config->dep_dist, worker);
          key = dep_entry->key;
        } else {
          int keyIndex = getIntQuantile(config->key_pop_dist);
          key = config->key_list->keys[keyIndex];
        }
      }
      op = GET;

      struct request* nextRequest = createRequest(op, conn, worker, key, value, type);
      currentRequest->next_request = nextRequest;
      nextRequest->next_request = NULL;
#ifdef FLEXUS
      MAGIC2(210, request->header.opaque);	
#endif

    } else {
      //It's a get
      op = GET;
      request = createRequest(op, conn, worker, key, value, TYPE_GET);
      request->next_request = NULL;
#ifdef FLEXUS
      MAGIC2(210, request->header.opaque);	
#endif
    }

  } else {
    //It's a set
    op = SET;
    //Create a value
    if(config->dep_dist == NULL){
      if(config->fixed_size > 0) {
        valueSize = config->fixed_size;
      } else {
        valueSize = getIntQuantile(config->value_size_dist);
        if(valueSize == 0) {
          printf("failboat: zero sizedd value\n");
          exit(-1);
        }
      }
    }
    value = malloc(sizeof(char) * valueSize);
    memset(value, 'a', sizeof(char) * valueSize);
    value[valueSize-1] = '\0';
    request = createRequest(op, conn, worker, key, value, TYPE_SET);
    request->next_request = NULL;
#ifdef FLEXUS
    MAGIC2(220, request->header.opaque);	
#endif
  }

  request->warmup_index = warmup_index;
  return request;


}//End generateRequest()
