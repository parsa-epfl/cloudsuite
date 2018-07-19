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

#ifndef httperf_h
#define httperf_h

#include "config.h"

#include <sys/time.h>
#include <sys/types.h>
#include <sys/resource.h>

#include "sys_sched_affinity.h"

#define VERSION	"0.8.6c"

typedef double Time;

#define NELEMS(a)	((sizeof (a)) / sizeof ((a)[0]))
#define TV_TO_SEC(tv)	((tv).tv_sec + 1e-6*(tv).tv_usec)

#ifndef SRINI_RATE
#define NUM_RATES 10
#endif

#define MAX_LOG_FILES 4

typedef union
{
	char c;
	int i;
	long l;
	u_char uc;
	u_int ui;
	u_long ul;
	float f;
	double d;
	void *vp;
	const void *cvp;
}
Any_Type;

typedef enum Dist_Type
{
	DETERMINISTIC,	/* also called fixed-rate */
	UNIFORM,		/* over interval [min_iat,max_iat) */
#ifndef SRINI_RATE
	VARIABLE,           /* allows varying input load */
	VARIABLE_EXP,       /* varying input load with exponential distribution */
#endif
	EXPONENTIAL		/* with mean mean_iat */
}
Dist_Type;

typedef struct Load_Generator
{
	const char *name;
	void (*init) (void);
	void (*start) (void);
	void (*stop) (void);
}
Load_Generator;

typedef struct Stat_Collector
{
	const char *name;
	/* START and STOP are timing sensitive, so they should be as short
	   as possible.  More expensive stuff can be done during INIT and
	   DUMP.  */
	void (*init) (void);
	void (*start) (void);
	void (*stop) (void);
	void (*dump) (void);
}
Stat_Collector;

typedef struct Rate_Info
{
	Dist_Type dist;		/* interarrival distribution */
	double rate_param;		/* 0 if mean_iat==0, else 1/mean_iat */
	Time mean_iat;		/* mean interarrival time */
	Time min_iat;		/* min interarrival time (for UNIFORM) */
	Time max_iat;	        /* max interarrival time (for UNIFORM) */
#ifndef SRINI_RATE
	int numRates;               /* number of rates we want to use */
	Time iat[NUM_RATES];
	Time duration[NUM_RATES];
#endif
}
Rate_Info;

#define PRINT_HEADER	(1 << 0)
#define PRINT_BODY	(1 << 1)

#define MAX_SVR_PORTS 16

typedef struct Cmdline_Params
{
	unsigned long cpu_mask; /* mask of schedulable CPUs (if supported) */
	int http_version;	/* (default) HTTP protocol version */
	const char *server;	/* (default) hostname */
	const char *server_name; /* fully qualified server name */
	char* output_log;
	int port;		/* (default) server port */
	const char *uri;	/* (default) uri */
	Rate_Info rate;
	Time timeout;	/* watchdog timeout */
	Time think_timeout;	/* timeout for server think time */
#ifdef HAVE_EPOLL
	int use_epoll;	/* use epoll instead of select */
#endif 
	int num_conns;	/* # of connections to generate */
	int num_calls;	/* # of calls to generate per connection */
	int burst_len;	/* # of calls to burst back-to-back */
	int max_piped;	/* max # of piped calls per connection */
	int max_conns;	/* max # of connections per session */
#ifdef IDLECONN
	int idle_conns;	/* idle # of connections per session */
	char* idleconn;	/* idleconn binary path */
#endif /* IDLECONN */
	int hog;		/* client may hog as much resources as possible */
	int send_buffer_size;
	int recv_buffer_size;
	int failure_status;	/* status code that should be considered failure */
	int retry_on_failure; /* when a call fails, should we retry? */
	int close_with_reset; /* close connections with TCP RESET? */
	int timeout_with_reset; /* one timeout close connections with TCP RESET? */
	int print_request;	 /* bit 0: print req headers, bit 1: print req body */
	int print_reply;	 /* bit 0: print repl headers, bit 1: print repl body */
	int session_cookies; /* handle set-cookies? (at the session level) */
	int no_host_hdr;	 /* don't send Host: header in request */
	int stats_interval;  /* print summary statistics every N seconds */
	int spec_stats;      /* Should we gather SPECweb99 specific statistics?  */
#ifdef UW_CALL_STATS
	int call_stats;      /* Should we gather call specific statistics?  */
	char *call_stats_file;      /* file containing file sizes used for call_stats */
#endif /* UW_CALL_STATS */
	int separate_post_stats; /* Should we separate stats for dyanmic POST requests?  */
	int verify_reply;    /* Should we verify (i.e. check the bytes of) SPECweb99 replies*/
	const char * verify_dir; /* Directory containing reference file set (for verification) */
	double rate_interval; /* interval at which rate info is sampled */
#ifdef HAVE_SSL
	int use_ssl;	/* connect via SSL */
	int ssl_reuse;	/* reuse SSL Session ID */
	const char *ssl_cipher_list; /* client's list of SSL cipher suites */
#endif

#ifdef UW_STABLE_STATS
	int ramp_up_conns;  /* num of conns to ignore at beginning of experiment */
	int ramp_down_conns; /* num of conns to ignore at end of experiment */
#endif /* UW_STABLE_STATS */

	const char *additional_header;	/* additional request header(s) */
	const char *method;	/* default call method */
	int session_offsets; /* should each client start in a different position
				in the wsesslog file */
	struct
	{
		u_int id;
		u_int num_clients;
		int stagger_start;
	}
	client;

	/*
	struct
	{
		char *file;	// name of the file where entries are
		char do_loop;	// boolean indicating if we want to loop on entries
	}
	wlog;
	struct
	{
		u_int num_sessions;	// # of sessions
		u_int num_calls;	// # of calls per session
		Time think_time;	// user think time between calls
	}
	wsess;
	struct
	{
		u_int num_sessions;	// # of sessions
		u_int num_reqs;		// # of user requests per session
		Time think_time;	// user think time between requests
	}
	wsesspage;
	struct
	{
		u_int num_sessions;	// # of user-sessions
		Time think_time;	// user think time between calls
		char *file;		// name of the file where session defs are
		int exit_early;		// exit early?
	}
	wsesslog;
	struct
	{
		u_int num_files;
		double target_miss_rate;
	}
	wset;
	*/
	struct
	{
		int cur_port;
		int num_ports;
		int port[MAX_SVR_PORTS];
	}
	lb_ports;
	struct
	{
		u_int num_logs;				/* # of session logs */
		char file[MAX_LOG_FILES][1024];		/* File names of session logs */
		double sess_perc[MAX_LOG_FILES];	/* Array of percentile for request-mix probability */
		char local_ip[MAX_LOG_FILES][16];	/* The local-ip addresses to bind to. */
		Time think_time;			/* user think time between calls */

	} 
	videosesslog;
	u_int num_sessions;				/* # of user-sessions */
}
Cmdline_Params;

extern const char *prog_name;
extern int verbose;
extern Cmdline_Params param;
extern Time test_time_start;
extern Time test_time_stop;
extern struct rusage test_rusage_start;
extern struct rusage test_rusage_stop;

#ifdef HAVE_SSL
# include <openssl/ssl.h>
extern SSL_CTX *ssl_ctx;
#endif

#ifdef DEBUG
extern int debug_level;
# define DBG debug_level
#else
# define DBG 0
#endif

extern void panic (const char *msg, ...);
extern void no_op (void);

/* Connection problems encountered by httperf - that are a 
 * result of a failed system call - so there is no errno to use 
 *
 * Note: we depend on errno being positive values and these being negative
 */

#define CONN_ERR_NOT_SET              (-1)
#define CONN_ERR_NO_MORE_PORTS        (-2)
#define CONN_ERR_HASH_LOOKUP_FAILED   (-3)
#define CONN_ERR_STATE_PAST_CLOSING   (-4)
// How many entries needed in the array.
#define CONN_ERR_COUNT                (-CONN_ERR_STATE_PAST_CLOSING + 1)

#endif /* httperf_h */
