/*
    httperf -- a tool for measuring web server performance
    Copyright (C) 2000  Hewlett-Packard Company
    Contributed by David Mosberger-Tang <davidm@hpl.hp.com>

    This file is part of httperf, a web server performance measurment
    tool.

    This program is free software; you can redistribute it and/or
    modify it under the terms of the GNU General Public License as
    published by the Free Software Foundation; either version 2 of the
    License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
    General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
    02111-1307 USA
*/

/*
  This stats module was added to allow httperf to gather statistics
  on SPECweb99 workloads.
  Author: David Pariag
  Date: Feb 3rd, 2005  
*/


#include "spec_stats.h"
#include <stdio.h>
#include <stdlib.h>
#include <call.h>
#include <string.h>
#include <assert.h>

#define CHAR_TO_INT(c)   (c - 48)
#define SPEC_CLASSES 4
#define SPEC_SIZES 9
#define NUM_SPEC_FILES (SPEC_CLASSES) * (SPEC_SIZES) + 1
#define MAX_SESSION 15
#define SPEC_HEADER 279

#define URI_INVALID    (-1)
#define URI_COMMAND    (0)
#define URI_POST       (1)
#define URI_STATIC_GET (2)
#define URI_DYN_GET    (3)
int classify_uri(char *uri);
int get_file_index(char *str);

/* Use this struct to gather stats on each of the SPECweb99 files */
typedef struct {
  int requests;      /* Number of times this file was requested */
  int timeouts;      /* Number of requests that timed out */
  int missing_bytes; /* Number of requests that completed, but had missing bytes */
  int size;          /* Size of this file (unused for now) */  
  unsigned int bytes_recvd; /* Number of bytes recv'd when files of this type were requested */
  double sum_resp_times; /* Sum of response times for requests of this file */
#if 0
  /* might want to track and print these stats as well or to 
   * have array of for each of these types 
   */
  int static_requests;  /* times file was requested with static GET */
  int dyn_get_requests; /* times file was requested with dynamic GET */
  int post_requests;    /* times file was requested with POST */
#endif
} spec_file_data;

/* Track aggregate data for each of the 36 SPECweb99 file types */
spec_file_data file_stats[NUM_SPEC_FILES];

/******************************************************
 * Hardcode the file sizes for SPECweb99. We really
 * only want to print these at the end for information 
 * purposes 
 ******************************************************/
int file_sizes[NUM_SPEC_FILES] = {
  102, 204,307,409,512,614,716,819,921,1024,2048,3072,4096,
  5120,6144,7168,8192,9216,10240,20480,30720,40960,51200,
  61440,71680,81920,92160,102400,204800,307200,409600,
  512000,614400,716800,819200,921600,0  
};

/* This struct gathers stats for all sessions of a given length */
typedef struct {
  int count;          /* Number of sessions of this length that were initiated */
  int timeouts;       /* Number of timeouts for sessions of this length */
  int requests;       /* Number of requests sent over sessions of this length */
  int replies;        /* Number of replies that were received */
  int bytes_reqd;     /* Number of bytes requested over all requests */
  int bytes_recvd;    /* Number of bytes received over all requests  */
} session_data;

/* Track session stats by session-length */
session_data sess_stats[MAX_SESSION];


/* Initialize the data structures used in this module */
void spec_stats_init() {
  int i;
  
  for( i = 0; i < NUM_SPEC_FILES; i++ ) {
    file_stats[i].requests = 0;
    file_stats[i].timeouts = 0;
    file_stats[i].missing_bytes = 0;
    file_stats[i].size = 0;
  } 

  for( i = 0; i < MAX_SESSION; i++ ) {
    sess_stats[i].count = 0;
    sess_stats[i].timeouts = 0;
    sess_stats[i].requests = 0;
    sess_stats[i].replies = 0;
    sess_stats[i].bytes_reqd = 0;
    sess_stats[i].bytes_recvd = 0;    
  }
}


/* Hash a specweb uri based on the digits in the filename 
 * The uris look like some_path/dir_<xxxxx>/class<x>_<y>
 * where x,y and z are digits. This hash function is based
 * on the x & y digits in the filename at the end of the uri.
 * x ranges from 0 to 3.
 * y ranges from 0 to 8.
 * This functions returns a number between 0 and 35.
 */
int spec_hash( char * uri ) {
  int x_index;
  int y_index;
  int ret = 0;

  /* uri is a post request and looks like /specweb99-fcgi.pl? */
  if ( strlen(uri) == 19 ) {
    return NUM_SPEC_FILES - 1;
  }

  y_index = strlen(uri) - 1;
  x_index = y_index - 2;
  ret = (CHAR_TO_INT(uri[x_index]) * SPEC_SIZES) + (CHAR_TO_INT(uri[y_index]));
  if (ret < 0 || ret >= NUM_SPEC_FILES) {
    printf("spec_hash to return %d which is out of range\n", ret);
    exit(1);
  }
  return ret;
}


int get_spec_index( Call * c ) {
  char * uri = NULL; 
  int ret = -1;
  int x = URI_INVALID;
  
  if( c != NULL ){
    uri = c->req.iov[IE_URI].iov_base;
    if ( uri != NULL ) {
      if (!param.separate_post_stats) {
        x = classify_uri(uri);
        if (x == URI_COMMAND) {
          ret = -1;
        } else if (x == URI_POST) {
          if (DBG > 0) {
            printf("URI_POST: Content = [%s]\n", (char *) c->req.iov[IE_CONTENT].iov_base);
          }
          ret = get_file_index((char *) c->req.iov[IE_CONTENT].iov_base);
        } else {
          assert(x == URI_STATIC_GET || x == URI_DYN_GET);
          ret = spec_hash( uri );
        }
      } else {
        ret = spec_hash( uri );
      }
    }
  }
  
  if (DBG > 0) {
    printf("index = %d\n", ret);
  }
  return ret;
}


void track_spec_request(Call *c ) {
  int index = -1;
  int sess_index = 0;

  index = get_spec_index( c );
  if ( index != -1 ) {
    file_stats[index].requests++;
    /* Track bytes requested by session length */
    assert(c->conn != NULL );
    assert(c->conn->basic.num_calls <= MAX_SESSION );
    if ( index != NUM_SPEC_FILES - 1)
    {
      sess_index = c->conn->basic.num_calls - 1;
      sess_stats[sess_index].requests++;
      sess_stats[sess_index].bytes_reqd += file_sizes[index] + SPEC_HEADER;
    }
  }
}


/****************************************************************** 
  This function is called once the first byte of a response
  is received. The response_start_time parameter contains the
  elapsed time between the sending of the request, and the receipt
  of the first byte of the reply
 ******************************************************************/
void track_spec_response(Call * c, double response_start_time ) {
  int index = 0;
  
  index = get_spec_index( c );
  if( index != -1 ) {
    file_stats[index].sum_resp_times += response_start_time;
  }  
}



void track_spec_reply(Call * c, double transfer_time ) {
  int index = 0;
  int sess_index = 0;
  
  index = get_spec_index( c );
  if( index != -1 ) {
    /* Track the number of bytes received */
    file_stats[index].bytes_recvd += c->reply.content_bytes;
    if( c->reply.content_bytes < file_sizes[index] + SPEC_HEADER) {
      file_stats[index].missing_bytes++;
    }
    
    /* Record the transfer time, as part of overall response time */
    file_stats[index].sum_resp_times += transfer_time;    

    /* Track how many bytes were recv'd by session length */
    assert(c->conn != NULL );
    assert(c->conn->basic.num_calls <= MAX_SESSION );
    if ( index != NUM_SPEC_FILES - 1)
    {
      sess_index = c->conn->basic.num_calls - 1;
      sess_stats[sess_index].replies++;
      sess_stats[sess_index].bytes_recvd += c->reply.content_bytes;
    }
  }
}

void track_spec_connection(Conn * c ) {
  int sess_index;
  
  if( c == NULL ) return;
  if (DBG > 0) {
    printf("track_spec_connection num_calls = %d\n", c->basic.num_calls);
  }
  assert(c->basic.num_calls <= MAX_SESSION );
  assert(c->basic.num_calls >= 0);
  sess_index = c->basic.num_calls - 1;
  sess_stats[sess_index].count++;
}


void process_spec_timeout(Conn * c ) {
  char * uri = NULL;
  int index = -1;
  int sess_index = 0;
  int x = 0;

  if( c == NULL ) return;
  if( c->recvq_tail == NULL ) return;
  
  uri = (char * ) c->recvq_tail->req.iov[IE_URI].iov_base;
  if( uri != NULL ) {
    if (!param.separate_post_stats) {
      if ((x = classify_uri(uri)) == URI_COMMAND) {
	return;
      }
      if (x == URI_POST) {
        if (DBG > 0) {
	  printf("Content = [%s]\n", (char *) c->recvq_tail->req.iov[IE_CONTENT].iov_base);
        }
        index = get_file_index((char *) c->recvq_tail->req.iov[IE_CONTENT].iov_base);
      } else {
	assert(x == URI_STATIC_GET || x == URI_DYN_GET);
	index = spec_hash( uri );
      }
    } else {
      index = spec_hash( uri );
    }

    if (index < 0 || index >= NUM_SPEC_FILES) {
      printf("process_spec_timeout: index = %d is out of range\n", index);
      exit(1);
    }
    file_stats[index].timeouts++;
    file_stats[index].bytes_recvd += c->recvq_tail->reply.content_bytes;
  
    /* Track how many sessions of a particular length timed out */
    assert(c->basic.num_calls <= MAX_SESSION );
    if ( index != NUM_SPEC_FILES - 1 )
    {
      sess_index = c->basic.num_calls - 1;
      sess_stats[sess_index].timeouts++;
      sess_stats[sess_index].bytes_recvd += c->recvq_tail->reply.content_bytes;
    }
  }

}


void print_spec_timeouts() {
  spec_file_data * cur;
  int i,j, index;
  double percent, percent_recvd;
  double avg_reqs, avg_reps, avg_response;
  unsigned int requested;
  long long total_requested = 0;
  long long total_recvd = 0;  
  long long total_missing = 0;
  int total_requests = 0;
  int total_timeouts = 0;
  int total_conns = 0;
  int class_timeouts[SPEC_CLASSES] = {0,0,0,0};
  int class_requests[SPEC_CLASSES] = {0,0,0,0};
  long long class_bytes_requested[SPEC_CLASSES] = {0,0,0,0};
  long long class_bytes_recvd[SPEC_CLASSES] = {0,0,0,0};
  double class_resp_times[SPEC_CLASSES] = {0.0, 0.0, 0.0, 0.0};

  /* Calculate and print per-file statistics */
  printf("\nThe following table gives a breakdown of statistics by SPECweb99 file requested.\n");
  printf("The column headings are:\n");
  printf("Filename:      The name of the file (containing directory is ignored)\n");
  printf("Filesize:      The size of the file in bytes\n");
  printf("Timeouts:      The number of requests for this file that timed out\n");
  printf("Requests:      The number of requests for this file\n");
  printf("Timeout%s:      The %s of requests for this file that timed out\n", "%", "%");
  printf("Trunc'd: The number of requests that completed, but had missing byte n");
  printf("Bytes Recv'd:  The number of bytes received over all requests for this file\n");
  printf("Bytes Req'd:   The number of bytes that should have been received over all requests for this file\n");
  printf("Recv'd %s:      The %s of the requested bytes that were actually received\n", "%", "%");
  printf("Avg Resp:      The average response time when this file was requested (excludes connection time)\n");
  printf("\n\n");

  printf("%8s  %8s  %8s  %8s  %8s  %8s  %11s  %11s  %8s  %10s\n", 
 	 "Filename", "Filesize","Timeouts", "Requests", "Timeout%", "Trunc'd", "Bytes Recvd", "Bytes Reqd", "Recv'd%", "Avg Resp T");  
  
  for( i = 0; i < SPEC_CLASSES; i++ ) {
    for( j = 0; j < SPEC_SIZES; j++ ) {
      index = i * SPEC_SIZES + j;      
      
      cur = &(file_stats[index]);

      if( cur->requests > 0 ) {
	/* Calculate some totals */
	total_requests += cur->requests;
	total_timeouts += cur->timeouts;
	total_missing  += cur->missing_bytes;

	/* Track total requests and timeouts by file class */
	class_timeouts[i] += cur->timeouts;
	class_requests[i] += cur->requests;
	class_resp_times[i] += cur->sum_resp_times;

	/* Work out some percentages */
	percent = 100.0 * ((double) cur->timeouts) / ((double) cur->requests) ;
	requested = cur->requests * (file_sizes[index] + SPEC_HEADER);
	percent_recvd = 100.0 * ((double)cur->bytes_recvd) / ((double)requested);
	avg_response = 1000.0 * cur->sum_resp_times / (double) (cur->requests - cur->timeouts);

	/* Track total requested and received by file class */
	class_bytes_requested[i] += requested;
	class_bytes_recvd[i] += cur->bytes_recvd;

	/* Work out total requested and received (over all classes)*/
	total_requested += requested;
	total_recvd += cur->bytes_recvd;

	/* Print data for this file size */
	printf("%5s%d_%d  %8d  %8d  %8d  %8.2f  %8d  %11d  %11d  %8.1f  %10.1f\n", "class", 
	       i,j, file_sizes[index]+SPEC_HEADER, cur->timeouts, 
	       cur->requests, percent, cur->missing_bytes, 
	       cur->bytes_recvd, requested, percent_recvd, avg_response );
      }
    } /* for j */
  } /* for i */


//requested, percent_recvd, avg_response );

  percent = 100.0 * ((double) total_timeouts) / ((double) total_requests);
  percent_recvd = 100.0 * ((double) total_recvd) / ((double) total_requested );
  printf("%8s  %8s  %8d  %8d  %8.2f  %8lld  %11lld  %11lld  %8.1f  %10s\n",
	 "Total", " ", total_timeouts, total_requests, percent, total_missing,
	 total_recvd, total_requested, percent_recvd, " ");

  if (param.separate_post_stats) {
    cur = &(file_stats[NUM_SPEC_FILES-1]);
    percent = 100.0 * ((double) cur->timeouts) / ((double) cur->requests);
    avg_response = 1000.0 * cur->sum_resp_times / (double) (cur->requests - cur->timeouts);
    printf("%8s  %8s  %8d  %8d  %8.2f  %8d  %11d  %11s  %8s  %10.1f\n",
	  "POST", " ", cur->timeouts, cur->requests, percent, cur->missing_bytes, cur->bytes_recvd, " ", " ", avg_response);
  }
  
  /* Calculate and print per-class statistics */
  printf("\nSPECweb99 Timeouts (by class):\n");
  printf("%8s  %10s  %8s  %9s  %12s  %11s  %8s  %9s\n", 
	"Class", "Timeouts", "Requests", "Timeout%", "Bytes recv'd", "Bytes req'd", "Recv'd%", "Resp-time" );
  for( i = 0; i < SPEC_CLASSES; i++ ) {
    percent = 100.0 * ((double) class_timeouts[i]) / ((double) class_requests[i]);
    percent_recvd = 100.0 * ((double) class_bytes_recvd[i]) / ((double) class_bytes_requested[i]);
    avg_response = 1000.0 * (class_resp_times[i] / (double) (class_requests[i] - class_timeouts[i]));
    printf("%7s%1d  %10d  %8d  %9.2f  %12lld  %11lld  %8.2f  %9.1f\n", 
	   "class", i, class_timeouts[i], class_requests[i], percent, 
	   class_bytes_recvd[i], class_bytes_requested[i], percent_recvd, avg_response);
  }
  
  /* Calculate and print per-session-length statistics */
  printf("\nSPECweb99 Timeouts (by session length):\n");
  printf("%6s  %10s  %10s  %10s  %10s  %10s  %12s  %12s  %8s\n", 
	"Length", "Count", "Timeouts", "Timeout%", "Avg-req", "Avg-rep", "Bytes-recvd", "Bytes-reqd", "Recvd%");
  /* Re-calculate totals just to make sure (they should be the same as above) */
  total_conns = total_requested = total_recvd = total_timeouts = total_requests = 0;
  for(i=0; i< MAX_SESSION; i++ ) {
    if( sess_stats[i].requests > 0 ) {
      total_requests += sess_stats[i].requests;
      total_timeouts += sess_stats[i].timeouts;
      total_recvd += sess_stats[i].bytes_recvd;
      total_conns += sess_stats[i].count;
      total_requested += sess_stats[i].bytes_reqd;

      percent = 100.0 * ((double)sess_stats[i].timeouts  / (double) sess_stats[i].count);
      avg_reqs = ((double)sess_stats[i].requests / (double) sess_stats[i].count);
      avg_reps = ((double)sess_stats[i].replies  / (double) sess_stats[i].count);
      percent_recvd = 100.0 * ((double) sess_stats[i].bytes_recvd / (double) sess_stats[i].bytes_reqd);
      printf("%4s%2.2d  %10d  %10d  %10.2f  %10.2f  %10.2f  %12d  %12d  %8.2f\n",
		"sess", i+1, sess_stats[i].count, sess_stats[i].timeouts, percent, avg_reqs, avg_reps,
		sess_stats[i].bytes_recvd, sess_stats[i].bytes_reqd, percent_recvd );
    }    
  } /* for i */
  percent = 100.0 * ((double) total_timeouts) / ((double) total_conns);
  percent_recvd = 100.0 * ((double) total_recvd) / ((double) total_requested );
  printf("%6s  %10d  %10d  %10.2f  %10s  %10s  %12lld  %12lld  %8.2f\n", 
	 "Total", total_conns, total_timeouts, percent, " ", " ", total_recvd, total_requested, percent_recvd);
}

int
classify_uri(char *uri)
{
  int result = URI_INVALID;
  char *p = 0;

  if ((p = strchr(uri, '?')) != NULL) {
    /* post requests end with the ? */
    if ((strcmp(p, "?")) == '\0') {
      result = URI_POST;
    } else if (strncmp(p+1, "command", 7) == 0) {
      result = URI_COMMAND;
    } else {
      /* not a command but is dynamic */
      result = URI_DYN_GET;
    }
  } else {
    /* static request */
    result = URI_STATIC_GET;
  }

  if (DBG > 0) {
    printf("returning result = %d\n", result);
  }
  return result;
}

/* Figure out which class and number are used and combine them for a file index
 * urlroot=/specweb99/file_set/&dir=00000&class=2&num=1&client=10018^M
 */
#define CLASS_INDEX  (45)
#define NUM_INDEX    (51)
int
get_file_index(char *str)
{
  /* very fast but very fragile method */
  int class = CHAR_TO_INT(str[CLASS_INDEX]);
  int num =   CHAR_TO_INT(str[NUM_INDEX]);
  if (DBG > 0) {
    printf("class%c_%c\n", str[CLASS_INDEX], str[NUM_INDEX]);
  }

  if (class < 0 || class >= SPEC_CLASSES) {
    printf("get_file_index: class = %d is out of range\n", class);
    exit(1);
  }

  if (num < 0 || num >= SPEC_SIZES) {
    printf("get_file_index: num = %d is out of range\n", num);
    exit(1);
  }

  return (class * SPEC_SIZES) + num;
}
