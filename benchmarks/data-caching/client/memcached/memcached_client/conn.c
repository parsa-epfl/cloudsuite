
#include "conn.h"

extern int verbose;

struct conn* createConnection(const char* ip_address, int port, int protocol, int naggles) {

  struct conn* connection = malloc(sizeof(struct conn));
  memset(connection, 0, sizeof(struct conn));

  if(protocol == UDP_MODE) {

    connection->sock = openUdpSocket(ip_address, port);
    connection->protocol = UDP_MODE;

  } else {

    connection->sock = openTcpSocket(ip_address, port);
    connection->protocol = TCP_MODE;
    if(!naggles) {
      //Disable naggle's algorithm
      int flag = 1;
      int result = setsockopt(connection->sock,            /* socket affected */
                              IPPROTO_TCP,     /* set option at TCP level */
                              TCP_NODELAY,     /* name of option */
                              (char *) &flag,  /* the cast is historical cruft */
                               sizeof(int));    /* length of option value */
      if (result < 0){
        printf("couldn't set tcp_nodelay\n");
        exit(-1);
      }
    }

  }


  static int uid_gen;
  connection->uid = uid_gen;
  uid_gen++;

  if (verbose) 
      printf("Created connection on fd %d, uid %d\n", connection->sock, connection->uid);

  return connection;

}//End createConnection()

int openTcpSocket(const char* ipAddress, int port) {

  //Create a socket
  int sock = socket(AF_INET, SOCK_STREAM, 0);
  if( sock < 0 ){
    printf("ERROR: Couldn't create a socket\n");
    exit(-1);
  }

  struct sockaddr_in server;
  memset(&server, 0, sizeof(server));

  //Use IPv4
  server.sin_family = AF_INET;

  //Convert IP address to network order
  if( inet_pton(AF_INET, ipAddress, &server.sin_addr.s_addr) < 0){
    printf("IP Address error\n");
    exit(-1);
  }

  //Use the standard memcached port
  int pport= (port!=0) ? port : MEMCACHED_PORT;
  server.sin_port = htons(pport);
  int error = connect(sock, (struct sockaddr *)&server, sizeof(server));
  if(error < 0){
    printf("Connection error\n");
    exit(-1);
  }

  if (verbose) 
      printf("TCP connected\n");

  return sock;

}//End openTcpSocket()

int openUdpSocket(const char* ipAddress, int port) {

  int sock = socket(AF_INET, SOCK_DGRAM, 0);
  struct sockaddr_in server;
  memset(&server, 0, sizeof(struct sockaddr));
  server.sin_family = AF_INET;
  int pport= (port!=0) ? port : MEMCACHED_PORT;
  server.sin_port = htons(pport);

  //Convert IP address to network order
  if( inet_pton(AF_INET, ipAddress, &server.sin_addr.s_addr) < 0){
    printf("IP Address error\n");
    exit(-1);
  }

  int error = connect(sock, (struct sockaddr *)&server, sizeof(server));
  if(error < 0){
    printf("Couldn't connect with udp\n");
    exit(-1);
  }

  if (verbose) 
      printf("UDP connected\n");

  return sock;

}//End openUdpSocket()
