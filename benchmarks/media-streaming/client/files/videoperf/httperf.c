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
Fundamentals:

There are three subsystems to httperf:

1) The load generator which determines what URI is fetched next.

2) The core engine that handles the mechanics of issuing a request.

3) The instrumentation infrastructure that measures various aspects
of the transaction(s).

Since there is considerable potential variation in all three, it
seems like an event-based approach might be ideal in tying the three
together.  Ideally, it should be possible to write a new load
generator without modifications to the other subsystems.  Similarly,
it should be possible to add instrumentation without requiring
changes to the load generator or http engine.

Axioms:
- The only point at which the client will fall back is if
the client itself is overloaded.  There is no point trying
to fix up this case---simply declare defeat and abort the
test.
 */
#include "config.h"

#ifdef __FreeBSD__
#include <ieeefp.h>
#endif

#include <ctype.h>
#include <errno.h>
#include <getopt.h>
#include <signal.h>
#include <stdarg.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <execinfo.h>

#include <sys/time.h>
#include <sys/resource.h>

#include <core.h>
#include <event.h>
#include <httperf.h>
#include <conn.h>
#include <timer.h>
#include <time.h>

#ifdef HAVE_SSL
#  include <openssl/rand.h>
#endif

#define RATE_INTERVAL	5.0
#define STATS_INTERVAL 15.0

#define MAX_HOSTNAME_LEN   (100)

const char *prog_name;
int verbose;
Cmdline_Params param;
Time test_time_start;
Time test_time_stop;
struct rusage test_rusage_start;
struct rusage test_rusage_stop;
size_t object_type_size[OBJ_NUM_TYPES];

Time dump_stats_start;

#ifdef HAVE_SSL
SSL_CTX *ssl_ctx;
#endif

#ifdef DEBUG
int debug_level;
#endif

static Time perf_sample_start;

static struct option longopts[] =
{
	{"add-header",   required_argument, (void *) &param.additional_header, 0},
	{"burst-length", required_argument, &param.burst_len,		0},
	{"client",	     required_argument, (void *) &param.client,		0},
	{"client-session-offsets", no_argument, (void *) &param.session_offsets, 1},
	{"close-with-reset", no_argument,	&param.close_with_reset,	1},
	{"timeout-with-reset", no_argument,	&param.timeout_with_reset,	1},
	{"debug",	     required_argument,	0,				'd'},
#ifdef HAVE_EPOLL
	{"epoll",        no_argument,	&param.use_epoll,		1},
#endif /* HAVE_EPOLL */
	{"failure-status", required_argument, &param.failure_status,	0},
	{"help",	     no_argument,	0,				'h'},
	{"hog",	     no_argument,	&param.hog,			1},
	{"http-version", required_argument, (void *) &param.http_version,	0},
	{"lb-ports",     required_argument, (void *) &param.lb_ports,	0},
	{"max-connections", required_argument, &param.max_conns,		0},
#ifdef IDLECONN
	{"idle-connections", required_argument, &param.idle_conns,		0},
	{"idleconn",     required_argument, (void *) &param.idleconn, 	0},
#endif /* IDLECONN */
	{"max-piped-calls", required_argument, &param.max_piped,		0},
	{"method",	     required_argument,	(void *) &param.method,		0},
	{"no-host-hdr",  no_argument,	&param.no_host_hdr,		1},
	{"num-calls",    required_argument, (void *) &param.num_calls,	0},
	{"num-conns",    required_argument, (void *) &param.num_conns,	0},
	{"period",	     required_argument,	(void *) &param.rate.mean_iat,	0},
	{"port",	     required_argument, (void *) &param.port,		0},
	{"print-reply",  optional_argument, &param.print_reply,		0},
	{"print-request",optional_argument, &param.print_request, 		0},
#ifdef UW_STABLE_STATS
	{"ramp-up-num-conns", required_argument, (void *) &param.ramp_up_conns, 0},
	{"ramp-down-num-conns", required_argument, (void *) &param.ramp_down_conns, 0},
	{"stagger-start", no_argument,      &param.client.stagger_start, 1},
#endif /* UW_STABLE_STATS */
	{"rate",	     required_argument,	(void *) &param.rate,		0},
	{"rate-interval", required_argument, (void *) &param.rate_interval, 0},
	{"recv-buffer",  required_argument, (void *) &param.recv_buffer_size, 0},
	{"retry-on-failure", no_argument,	&param.retry_on_failure,	1},
	{"send-buffer",  required_argument, (void *) &param.send_buffer_size, 0},
	{"server",	     required_argument, (void *) &param.server,		0},
	{"server-name",  required_argument, (void *) &param.server_name,	0},
	{"session-cookies", no_argument,	(void *) &param.session_cookies,	1},
#ifdef HAVE_SSL
	{"ssl",	     no_argument,	&param.use_ssl,			1},
	{"ssl-ciphers",  required_argument, (void *) &param.ssl_cipher_list, 0},
	{"ssl-no-reuse", no_argument,	&param.ssl_reuse,		0},
#endif
	{"spec-stats",   no_argument,       (void *) &param.spec_stats,       1},
#ifdef UW_CALL_STATS
	{"call-stats",   required_argument, (void *) &param.call_stats,       -1},
	{"call-stats-file",   required_argument, (void *) &param.call_stats_file,       0},
#endif
	{"separate-post-stats", no_argument, (void *) &param.separate_post_stats, 1},
	{"stats-interval",required_argument, (void *) &param.stats_interval,	0},
	{"think-timeout",required_argument, (void *) &param.think_timeout,	0},
	{"timeout",      required_argument,	(void *) &param.timeout,		0},
	{"uri",	     required_argument, (void *) &param.uri,		0},
#ifdef HAVE_SCHED_AFFINITY
	{"use-cpu-mask", required_argument, (void *) &param.cpu_mask,	0},
#endif
	{"verbose",	     no_argument,	0,				'v'},
	{"version",	     no_argument,	0,				'V'},
	{"videosesslog",     required_argument, (void *) &param.videosesslog,	0},
	{"num-sessions",     required_argument, (void *) &param.num_sessions, 0},
	{"output-log",   required_argument, (void *) &param.output_log,	0},
	{0,		     0,			0,				0}
};

	static void
usage (void)
{
	printf ("Usage: %s "
			"[-hdvV] [--add-header S] [--burst-length N] [--client N/N]\n"
			"\t[--close-with-reset] [--debug N] [--failure-status N]\n"
			"\t[--timeout-with-reset]\n"
			"\t[--help] [--hog] [--http-version S] [lb-ports P1,...,Pn]\n"
			"\t[--max-connections N]\n"
#ifdef HAVE_EPOLL
			"\t[--epoll]\n"
#endif /* HAVE_EPOLL */
#ifdef IDLECONN
			"\t[--idle-connections N]\n"
			"\t[--idleconn <path to idleconn binary>]\n"
#endif /* IDLECONN */
			"\t[--max-piped-calls N] [--method S] [--no-host-hdr]\n"
#ifndef SRINI_RATE
			"\t[--num-calls N] [--num-conns N] [--period [d|u|e|v|l]T1"
			"[,D1][,T2][,D2]]\n"
#else
			"\t[--num-calls N] [--num-conns N] [--period [d|u|e]T1[,T2]]\n"
#endif
			"\t[--port N] "
			"[--print-reply [header|body]] [--print-request [header|body]]\n"
			"\t[--rate X] [--rate-interval X] [--recv-buffer N] [--retry-on-failure] "
			"[--send-buffer N]\n"

#ifdef UW_STABLE_STATS
			"\t[--ramp-up-num-conns N] [--ramp-down-num-conns N]\n"
			"\t[--stagger-start]\n"
#endif /* UW_STABLE_STATS */

			"\t[--server S] [--server-name S] [--session-cookies] \n"
			"\t[--spec-stats]\n"
#ifdef UW_CALL_STATS
			"\t[--call-stats N]\n"
			"\t[--call-stats-file S]\n"
#endif
			"\t[--separate-post-stats]\n"
#ifdef HAVE_SSL
			"\t[--ssl] [--ssl-ciphers L] [--ssl-no-reuse]\n"
#endif
			"\t[--stats-interval N] [--think-timeout X] [--timeout X]\n"
			//"\t[--uri S]"
#ifdef HAVE_SCHED_AFFINITY
			"\t[--use-cpu-mask [0x]N]"
#endif
			" [--verbose] [--version]\n"
			//"\t[--wlog y|n,file] [--wsess N,N,X]\n"
			"\t[--videosesslog [file1,file2,...],[perc1,perc2...],[local-ip1,local-ip2,...] \n"
			"\t[--num-sessions N] \n"
			"\t[--output-log file\n",
			//"\t[--wset N,X]\n",
		prog_name);
}

	void
panic (const char *msg, ...)
{
	va_list va;

	va_start (va, msg);
	vfprintf (stderr, msg, va);
	va_end (va);
	exit (1);
}

	void
no_op (void)
{
}

	static void
perf_sample (Timer *t, Any_Type regarg)
{
	Any_Type callarg;

	callarg.d = 1.0 / (timer_now () - perf_sample_start);
	event_signal (EV_PERF_SAMPLE, 0, callarg);

	/* prepare for next sample interval: */
	perf_sample_start = timer_now ();
	timer_schedule (perf_sample, regarg, param.rate_interval);
}

	static void
dump_stats (Timer *t, Any_Type regarg)
{
	Any_Type callarg;

  getrusage (RUSAGE_SELF, &test_rusage_stop); // aansaarii: required to measure CPU utilization
	event_signal (EV_DUMP_STATS, 0, callarg);

	/* prepare for next sample interval: */
	dump_stats_start = timer_now ();
	timer_schedule (dump_stats, regarg, param.stats_interval);
}

// Print stack trace on segfault
void sigsegv_handler(int sig) {
  void *array[128];
  size_t size;

  size = backtrace(array, 128);

  fprintf(stderr, "Backtrace:\n");
  backtrace_symbols_fd(array, size, STDERR_FILENO);
  exit(1);
}


	int
main (int argc, char **argv)
{
        // Register segfault handler
        signal(SIGSEGV, sigsegv_handler);

#ifndef SRINI_RATE
	int numRates = 0;
#endif
	extern Load_Generator conn_rate, call_seq;
	extern Load_Generator videosesslog, sess_cookie, misc;
	extern Stat_Collector stats_basic, session_stat;
	extern Stat_Collector stats_print_reply;
	extern char *optarg;
	int session_workload = 0;
	int num_gen = 3;
	int got_client_param = 0;
	Load_Generator *gen[5] =
	{
		&call_seq,
		&conn_rate,
	};
	int num_stats = 1;
	Stat_Collector *stat[3] =
	{
		&stats_basic
	};
	int i, ch, longindex;
	u_int minor, major;
	char *end, *name;
	Any_Type arg;
	void *flag;
	Time t;
	char hostname[MAX_HOSTNAME_LEN];
	struct tm *local = 0;
	time_t local_t;

	printf("sizeof(fd_set) = %lu\n", (unsigned long) sizeof(fd_set));

#ifdef __FreeBSD__
	/* This works around a bug in earlier versions of FreeBSD that cause
	   non-finite IEEE arithmetic to cause SIGFPE instead of the
	   non-finite arithmetic as defined by IEEE.  */
	fpsetmask (0);
#endif

	object_type_size[OBJ_CONN] = sizeof (Conn);
	object_type_size[OBJ_CALL] = sizeof (Call);

#ifdef HAVE_SCHED_AFFINITY
	param.cpu_mask = 0xffffffff;		/* use any CPU */
#endif
	param.http_version = 0x10001;		/* default to HTTP/1.1 */
	param.client.id = 0;
	param.client.num_clients = 1;
#ifdef UW_STABLE_STATS
	param.client.stagger_start = 0;
	param.ramp_up_conns = 0;
	param.ramp_down_conns = 0;
#endif /* UW_STABLE_STATS */
	param.server = "localhost";
	param.port = -1;
	param.uri = "/";
	param.num_calls = 1;
	param.burst_len = 1;
	param.num_conns = 1;
	/* These should be set to the minimum of 2*bandwidth*delay and the
	   maximum request/reply size for single-call connections.  */
	param.send_buffer_size =  4096;
	param.recv_buffer_size = 16384;
	param.rate.dist = DETERMINISTIC;
	param.rate_interval = RATE_INTERVAL;
  param.stats_interval = STATS_INTERVAL; // aansaarii
	param.spec_stats = 0;
#ifdef UW_CALL_STATS
	param.call_stats = -1;
	param.call_stats_file = "";
#endif
	param.separate_post_stats = 0;
#ifdef HAVE_SSL
	param.ssl_reuse = 1;
#endif
	param.num_sessions = 0;

	/* get program name: */
	prog_name = strrchr (argv[0], '/');
	if (prog_name)
		++prog_name;
	else
		prog_name = argv[0];

	/* process command line options: */
	while ((ch = getopt_long (argc, argv, "d:hvV", longopts, &longindex)) >= 0)
	{
		switch (ch)
		{
			case 0:
				flag = longopts[longindex].flag;

				if (flag == &param.method)
					param.method = optarg;
				else if (flag == &param.additional_header)
					param.additional_header = optarg;
				else if (flag == &param.num_calls)
				{
					errno = 0;
					param.num_calls = strtoul (optarg, &end, 10);
					if (errno == ERANGE || end == optarg || *end)
					{
						fprintf (stderr, "%s: illegal number of calls %s\n",
								prog_name, optarg);
						exit (1);
					}
				}
				else if (flag == &param.http_version)
				{
					if (sscanf (optarg, "%u.%u", &major, &minor) != 2)
					{
						fprintf (stderr, "%s: illegal version number %s\n",
								prog_name, optarg);
						exit (1);
					}
					param.http_version = (major << 16) | (minor & 0xffff);
				}
				else if (flag == &param.burst_len)
				{
					errno = 0;
					param.burst_len = strtoul (optarg, &end, 10);
					if (errno == ERANGE || end == optarg || *end
							|| param.burst_len < 1)
					{
						fprintf (stderr, "%s: illegal burst-length %s\n",
								prog_name, optarg);
						exit (1);
					}
				}
				else if (flag == &param.failure_status)
				{
					errno = 0;
					param.failure_status = strtoul (optarg, &end, 10);
					if (errno == ERANGE || end == optarg || *end
							|| param.failure_status <= 0)
					{
						fprintf (stderr, "%s: illegal failure status %s\n",
								prog_name, optarg);
						exit (1);
					}
				}
				else if (flag == &param.num_conns)
				{
					errno = 0;
					param.num_conns = strtoul (optarg, &end, 10);
					if (errno == ERANGE || end == optarg || *end)
					{
						fprintf (stderr, "%s: illegal number of connections %s\n",
								prog_name, optarg);
						exit (1);
					}
				}
				else if (flag == &param.max_conns)
				{
					errno = 0;
					param.max_conns = strtoul (optarg, &end, 10);
					if (errno == ERANGE || end == optarg || *end
							|| param.max_conns < 0)
					{
						fprintf (stderr, "%s: illegal max. # of connection %s\n",
								prog_name, optarg);
						exit (1);
					}
				}
#ifdef IDLECONN
				else if (flag == &param.idle_conns)
				{
					errno = 0;
					param.idle_conns = strtoul (optarg, &end, 10);
					if (errno == ERANGE || end == optarg || *end
							|| param.idle_conns < 0)
					{
						fprintf (stderr, "%s: illegal idle. # of connection %s\n",
								prog_name, optarg);
						exit (1);
					}
				}
				else if (flag == &param.idleconn)
				{
					param.idleconn = optarg;
				}
#endif /* IDLECONN */
				else if (flag == &param.max_piped)
				{
					errno = 0;
					param.max_piped = strtoul (optarg, &end, 10);
					if (errno == ERANGE || end == optarg || *end
							|| param.max_piped < 0)
					{
						fprintf (stderr, "%s: illegal max. # of piped calls %s\n",
								prog_name, optarg);
						exit (1);
					}
				}
				else if (flag == &param.port)
				{
					errno = 0;
					param.port = strtoul (optarg, &end, 10);
					if (errno == ERANGE || end == optarg || *end
							|| (unsigned) param.port > 0xffff)
					{
						fprintf (stderr, "%s: illegal port number %s\n",
								prog_name, optarg);
						exit (1);
					}
				}
				else if (flag == &param.lb_ports)
				{
					errno = 0;
					name = "bad server port (1st param)";
					param.lb_ports.port[param.lb_ports.num_ports++] = 
						strtoul (optarg, &end, 0);
					if (end == optarg || errno == ERANGE)
						goto bad_lb_port_param;
					optarg = end + 1;

					name = "bad server port (subsequent param)";
					while ((*end == ',')&&(param.lb_ports.num_ports < MAX_SVR_PORTS))
					{
						optarg = end + 1;

						param.lb_ports.port[param.lb_ports.num_ports++] = 
							strtoul (optarg, &end, 0);
						if (end == optarg || errno == ERANGE)
							goto bad_lb_port_param;
					}

					if (*end)
					{
bad_lb_port_param:
						fprintf (stderr, "%s: %s in --lb-port arg (rest: `%s')",
								prog_name, name, end);
						if (errno)
							fprintf (stderr, ": %s", strerror (errno));
						fputc ('\n', stderr);
						exit (1);
					}
				}
				else if (flag == &param.print_request || flag == &param.print_reply)
				{
					int val;

					if (!optarg)
						val = PRINT_HEADER | PRINT_BODY;
					else
						switch (tolower (optarg[0]))
						{
							case 'h': val = PRINT_HEADER;	break;
							case 'b': val = PRINT_BODY;	break;
							default:  val = PRINT_HEADER | PRINT_BODY; break;
						}
					*(int *) flag = val;
				}
				else if (flag == &param.rate)
				{
					errno = 0;
					param.rate.rate_param = strtod (optarg, &end);
					if (errno == ERANGE || end == optarg || *end)
					{
						fprintf (stderr, "%s: illegal request rate %s\n",
								prog_name, optarg);
						exit (1);
					}
					if (param.rate.rate_param <= 0.0)
						param.rate.mean_iat = 0.0;
					else
						param.rate.mean_iat = 1/param.rate.rate_param;
					param.rate.dist = DETERMINISTIC;
				}
				else if (flag == &param.rate_interval)
				{
					errno = 0;
					param.rate_interval = strtod(optarg,&end);
					if (errno == ERANGE || end == optarg || *end)
					{
						fprintf (stderr, "%s: illegal request rate %s\n",
								prog_name, optarg);
						exit (1);
					} else if ( param.rate_interval < 0.0 ) {
						fprintf(stderr, "Rate interval must be positive floating point value!\n");
						exit(1);
					}
				}
				else if (flag == &param.rate.mean_iat)	/* --period */
				{
					param.rate.dist = DETERMINISTIC;
					if (!isdigit (*optarg))
						switch (tolower(*optarg++))
						{
							case 'd': param.rate.dist = DETERMINISTIC; break;
							case 'u': param.rate.dist = UNIFORM; break;
							case 'e': param.rate.dist = EXPONENTIAL; break;
#ifndef SRINI_RATE
							case 'v': param.rate.dist = VARIABLE; break;
							case 'l': param.rate.dist = VARIABLE_EXP; break;
#endif
							default:
								  fprintf (stderr, "%s: illegal interarrival distribution "
										  "'%c' in %s\n",
										  prog_name, optarg[-1], optarg - 1);
								  exit (1);
						}

					/* remaining params depend on selected distribution: */
					errno = 0;
					switch (param.rate.dist)
					{
						case DETERMINISTIC:
						case EXPONENTIAL:
							param.rate.mean_iat = strtod (optarg, &end);
							if (errno == ERANGE || end == optarg || *end
									|| param.rate.mean_iat < 0)
							{
								fprintf (stderr, "%s: illegal mean interarrival "
										"time %s\n", prog_name, optarg);
								exit (1);
							}
							break;

						case UNIFORM:
							param.rate.min_iat = strtod (optarg, &end);
							if (errno == ERANGE || end == optarg
									|| param.rate.min_iat < 0)
							{
								fprintf (stderr, "%s: illegal minimum interarrival "
										"time %s\n", prog_name, optarg);
								exit (1);
							}
							if (*end != ',')
							{
								fprintf (stderr, "%s: minimum interarrival time not "
										"followed by `,MAX_IAT' (rest: `%s')\n",
										prog_name, end);
								exit (1);
							}
							optarg = end + 1;
							param.rate.max_iat = strtod (optarg, &end);
							if (errno == ERANGE || end == optarg || *end
									|| param.rate.max_iat < 0)
							{
								fprintf (stderr, "%s: illegal request period %s\n",
										prog_name, optarg);
								exit (1);
							}
							param.rate.mean_iat = 0.5*(param.rate.min_iat
									+ param.rate.max_iat);
							break;

#ifndef SRINI_RATE
						case VARIABLE:
						case VARIABLE_EXP:
							while (1) 
							{
								param.rate.iat[numRates] = strtod (optarg, &end);
								if (errno == ERANGE || end == optarg
										|| param.rate.iat[numRates] < 0)
								{
									fprintf (stderr, "%s: illegal minimum interarrival "
											"time %s\n", prog_name, optarg);
									exit (1);
								}
								if (*end != ',')
								{
									fprintf (stderr, "%s: inter-arrival time not "
											"followed by `,duration' (rest: `%s')\n",
											prog_name, end);
									exit (1);
								}
								optarg = end + 1;
								param.rate.duration[numRates] = strtod (optarg, &end);
								if (errno == ERANGE || end == optarg 
										|| param.rate.duration[numRates] < 0)
								{
									fprintf (stderr, "%s: illegal duration %s\n",
											prog_name, optarg);
									exit (1);
								}
								if (numRates == 0)
									param.rate.mean_iat = param.rate.iat[numRates];
								else
									param.rate.mean_iat += param.rate.iat[numRates];
								numRates ++;
								if (*end != ',')  
								{
									param.rate.numRates = numRates;
									break;
								} 
								else
									optarg = end + 1; 
							}
							param.rate.mean_iat /= numRates;
							break;		  
#endif

						default:
							fprintf (stderr, "%s: internal error parsing %s\n",
									prog_name, optarg);
							exit (1);
							break;
					}
					param.rate.rate_param = ((param.rate.mean_iat <= 0.0)
							? 0.0 : (1.0 / param.rate.mean_iat));
				}
				else if (flag == &param.recv_buffer_size)
				{
					errno = 0;
					param.recv_buffer_size = strtoul (optarg, &end, 10);
					if (errno == ERANGE || end == optarg || *end
							|| param.port > 0xffff)
					{
						fprintf (stderr, "%s: illegal receive buffer size %s\n",
								prog_name, optarg);
						exit (1);
					}
				}
				else if (flag == &param.send_buffer_size)
				{
					errno = 0;
					param.send_buffer_size = strtoul (optarg, &end, 10);
					if (errno == ERANGE || end == optarg || *end
							|| param.port > 0xffff)
					{
						fprintf (stderr, "%s: illegal send buffer size %s\n",
								prog_name, optarg);
						exit (1);
					}
				}
				else if (flag == &param.client)
				{
					got_client_param = 1;
					errno = 0;
					param.client.id = strtoul (optarg, &end, 0);
					if (end == optarg || errno == ERANGE)
					{
						fprintf (stderr, "%s: bad client id (rest: `%s')\n",
								prog_name, optarg);
						exit (1);
					}

					if (*end != '/')
					{
						fprintf (stderr,
								"%s: client id not followed by `/' (rest: `%s')\n",
								prog_name, end);
						exit (1);
					}
					optarg = end + 1;

					param.client.num_clients = strtoul (optarg, &end, 0);
					if (end == optarg || errno == ERANGE
							|| param.client.id >= param.client.num_clients)
					{
						fprintf (stderr, "%s: bad number of clients (rest: `%s')\n",
								prog_name, optarg);
						exit (1);
					}
				}
				else if (flag == &param.server)
					param.server = optarg;
				else if (flag == &param.server_name)
					param.server_name = optarg;
				else if (flag == &param.output_log)
					param.output_log =  optarg;
#ifdef HAVE_SSL
				else if (flag == &param.ssl_cipher_list)
					param.ssl_cipher_list = optarg;
#endif
				else if (flag == &param.stats_interval)
				{
					errno = 0;
					param.stats_interval = strtoul (optarg, &end, 10);
					if (errno == ERANGE || end == optarg || *end)
					{
						fprintf (stderr, "%s: illegal statistics interval length"
								" %s\n",
								prog_name, optarg);
						exit (1);
					}
				}
#ifdef UW_CALL_STATS
				else if (flag == &param.call_stats)
				{ 
					param.call_stats = atoi(optarg);
					printf("%s: Collecting call stats buffer size = %d\n",
							prog_name, param.call_stats);
				}
				else if (flag == &param.call_stats_file)
				{ 
					param.call_stats_file = strdup( optarg );
					printf("%s: file sizes from = %s\n",
							prog_name, param.call_stats_file);
				}
#endif
				else if (flag == &param.uri)
					param.uri = optarg;
				else if (flag == &param.think_timeout)
				{
					errno = 0;
					param.think_timeout = strtod (optarg, &end);
					if (errno == ERANGE || end == optarg || *end)
					{
						fprintf (stderr, "%s: illegal think timeout value %s\n",
								prog_name, optarg);
						exit (1);
					}
				}
				else if (flag == &param.timeout)
				{
					errno = 0;
					param.timeout = strtod (optarg, &end);
					if (errno == ERANGE || end == optarg || *end)
					{
						fprintf (stderr, "%s: illegal connect timeout %s\n",
								prog_name, optarg);
						exit (1);
					}
				}
				/*
				else if (flag == &param.wlog)
				{
					gen[1] = &uri_wlog;	// XXX fix me---somehow 

					param.wlog.do_loop = (*optarg == 'y') || (*optarg == 'Y');
					param.wlog.file = optarg + 2;
				}
				else if (flag == &param.wsess)
				{
					num_gen = 2;		// XXX fix me---somehow
					gen[0] = &wsess;

					stat[num_stats++] = &session_stat;

					errno = 0;
					name = "bad number of sessions (1st param)";
					param.wsess.num_sessions = strtoul (optarg, &end, 0);
					if (end == optarg || errno == ERANGE)
						goto bad_wsess_param;
					optarg = end + 1;

					name = "bad number of calls per session (2nd param)";
					if (*end != ',')
						goto bad_wsess_param;
					optarg = end + 1;

					param.wsess.num_calls = strtoul (optarg, &end, 0);
					if (end == optarg || errno == ERANGE)
						goto bad_wsess_param;

					name = "bad user think time (3rd param)";
					if (*end != ',')
						goto bad_wsess_param;
					optarg = end + 1;

					param.wsess.think_time = strtod (optarg, &end);
					if (end == optarg || errno == ERANGE 
							|| param.wsess.think_time < 0.0)
						goto bad_wsess_param;

					name = "extraneous parameter";
					if (*end)
					{
bad_wsess_param:
						fprintf (stderr, "%s: %s in --wsess arg (rest: `%s')",
								prog_name, name, end);
						if (errno)
							fprintf (stderr, ": %s", strerror (errno));
						fputc ('\n', stderr);
						exit (1);
					}
					session_workload = 1;
				}
				else if (flag == &param.wsesspage)
				{
					num_gen = 2;		// XXX fix me---somehow
					gen[0] = &wsesspage;

					stat[num_stats++] = &session_stat;

					errno = 0;
					name = "bad number of sessions (1st param)";
					param.wsesspage.num_sessions = strtoul (optarg, &end, 0);
					if (end == optarg || errno == ERANGE)
						goto bad_wsesspage_param;
					optarg = end + 1;

					name = "bad number of user requests per session (2nd param)";
					if (*end != ',')
						goto bad_wsesspage_param;
					optarg = end + 1;

					param.wsesspage.num_reqs = strtoul (optarg, &end, 0);
					if (end == optarg || errno == ERANGE)
						goto bad_wsesspage_param;

					name = "bad user think time (3rd param)";
					if (*end != ',')
						goto bad_wsesspage_param;
					optarg = end + 1;

					param.wsesspage.think_time = strtod (optarg, &end);
					if (end == optarg || errno == ERANGE 
							|| param.wsesspage.think_time < 0.0)
						goto bad_wsesspage_param;

					name = "extraneous parameter";
					if (*end)
					{
bad_wsesspage_param:
						fprintf (stderr, "%s: %s in --wsesspage arg (rest: `%s')",
								prog_name, name, end);
						if (errno)
							fprintf (stderr, ": %s", strerror (errno));
						fputc ('\n', stderr);
						exit (1);
					}
					session_workload = 1;
				}
				*/

				else if (flag == &param.videosesslog)
				{
					char file_list[5*1024];
					char req_mix_list[1024];
					char local_ip_list[1024];
					char *tmp;
					int end_index = -1, i=0, j=0, k=0;
					char delim[2] = ",";
					char* token = NULL;

					num_gen = 1;		// XXX fix me---somehow
					gen[0] = &videosesslog;

					stat[num_stats++] = &session_stat;

					errno = 0;
					/*
					name = "bad number of sessions (1st param)";
					param.videosesslog.num_sessions = strtoul (optarg, &end, 0);
					if (end == optarg || errno == ERANGE)
						goto bad_videosesslog_param;
					optarg = end + 1;

					name = "bad user think time (2nd param)";
					if (*end != ',')
						goto bad_videosesslog_param;
					optarg = end + 1;

					param.videosesslog.think_time = strtod (optarg, &end);
					if (end == optarg || errno == ERANGE 
							|| param.videosesslog.think_time < 0.0)
						goto bad_videosesslog_param;

					if (*end != ',')
						goto bad_videosesslog_param;
					optarg = end + 1;
				 	*/	
					name = "bad session file-list (1st param)";
					/* 
					   Read the file-list and perc-list
					   Identify the indices correctly
					   The file-list is in [file1, file2, file3...]
					   The req-mix is in [perc1, perc2, perc3, ...]
					*/
					if (*optarg != '[')
						goto bad_videosesslog_param;
					optarg++;
					
					tmp = strchr(optarg, ']');
					end_index = (int)(tmp - optarg);
					strncpy(file_list, optarg, end_index);
					file_list[end_index] = '\0';

					// Tokenize the files
					token = strtok(file_list, delim);
				
					while(token != NULL) {
						if (i >= MAX_LOG_FILES) {
							name = "Maximum number of log files is 4.";
							goto bad_videosesslog_param;
						}
						strcpy(param.videosesslog.file[i++], token);
						token = strtok(NULL, delim);
					}										
					end = optarg+end_index+1;

					name = "bad request mix percentage (2nd param)";
					if (*end != ',')
						goto bad_videosesslog_param;
					optarg = end + 1;
					if (*optarg != '[')
						goto bad_videosesslog_param;
					optarg++;
					
					tmp = strchr(optarg, ']');
					end_index = (int)(tmp - optarg);
					strncpy(req_mix_list, optarg, end_index);

					token = strtok(req_mix_list, delim);
					
					while (token != NULL) {
						if (j >= MAX_LOG_FILES) {
							name = "Maximum number of log files is 4.";
							goto bad_videosesslog_param;
						}
						param.videosesslog.sess_perc[j++] = strtod(token, NULL);
						if (errno == ERANGE)
							goto bad_videosesslog_param;
						token = strtok(NULL, delim);
					}

					name = "Mismatch in number of files and request mix";
					if (i != j)
						goto bad_videosesslog_param;
	
					end = optarg+end_index+1;	

				 	param.videosesslog.num_logs = i;	

					if (*end == '\0') 
						goto skip_local_ip_param;
					
					name = "Bad local-ips (5th parameters)";
					if (*end == ',') {
						// There are local-ips specified
						optarg = end + 1;
						if (*optarg != '[') 
							goto bad_videosesslog_param;
						optarg++;

						tmp = strchr(optarg, ']');
						end_index = (int)(tmp - optarg);
						strncpy(local_ip_list, optarg, end_index);
						
						token = strtok(local_ip_list, delim);

						while (token != NULL) {
							if (k >= MAX_LOG_FILES) {
								name = "Maximum number of log files is 4.";
								goto bad_videosesslog_param;
							}
							strcpy(param.videosesslog.local_ip[k++], token);
							token = strtok(NULL, delim);
						}
						name = "Mismatch in number of log files and local-ips specified.";
						if (i != k) 
							goto bad_videosesslog_param;
					}
										
					end = optarg+end_index+1;		
					name = "extraneous parameter";
					if (*end)
					{
bad_videosesslog_param:
						fprintf (stderr, "%s: %s in --videosesslog arg (rest: `%s')",
								prog_name, name, end);
						if (errno)
							fprintf (stderr, ": %s", strerror (errno));
						fputc ('\n', stderr);
						exit (1);
					}
skip_local_ip_param:
					session_workload = 1;
				}
				else if (flag == &param.num_sessions)
				{
					param.num_sessions = strtoul (optarg, &end, 0);	
				}
				/*
				else if (flag == &param.wset)
				{
					gen[1] = &uri_wset;	// XXX fix me---somehow 

					errno = 0;
					name = "bad working set size (1st parameter)";
					param.wset.num_files = strtoul (optarg, &end, 0);
					if (end == optarg || errno == ERANGE)
						goto bad_wset_param;

					name = "bad target miss rate (2nd parameter)";
					if (*end != ',')
						goto bad_wset_param;
					optarg = end + 1;

					param.wset.target_miss_rate = strtod (optarg, &end);
					if (end == optarg || errno == ERANGE
							|| param.wset.target_miss_rate < 0.0
							|| param.wset.target_miss_rate > 1.0)
						goto bad_wset_param;

					name = "extraneous parameter";
					if (*end)
					{
bad_wset_param:
						fprintf (stderr, "%s: %s in --wset arg (rest: `%s')",
								prog_name, name, optarg);
						if (errno)
							fprintf (stderr, ": %s", strerror (errno));
						fputc ('\n', stderr);
						exit (1);
					}
				}
				*/
#ifdef HAVE_SCHED_AFFINITY
				else if (flag == &param.cpu_mask)
				{
					errno = 0;
					if (!strncmp(optarg, "0x", 2))
						param.cpu_mask = strtoul (optarg, &end, 16);
					else
						param.cpu_mask = strtoul (optarg, &end, 10);
					if (errno == ERANGE || end == optarg || *end)
					{
						fprintf (stderr, "%s: illegal CPU mask %s\n",
								prog_name, optarg);
						exit (1);
					}
				}
#endif /* HAVE_SCHED_AFFINITY */

#ifdef UW_STABLE_STATS
				else if (flag == &param.ramp_up_conns)
				{
					errno = 0;
					param.ramp_up_conns = strtod (optarg, &end);
					if (errno == ERANGE || end == optarg || *end)
					{
						fprintf (stderr, "%s: illegal number of connections %s\n",
								prog_name, optarg);
						exit (1);
					}
				}
				else if (flag == &param.ramp_down_conns)
				{
					errno = 0;
					param.ramp_down_conns = strtod (optarg, &end);
					if (errno == ERANGE || end == optarg || *end)
					{
						fprintf (stderr, "%s: illegal number of connections %s\n",
								prog_name, optarg);
						exit (1);
					}
				}
#endif /* UW_STABLE_STATS */

				break;

			case 'd':
#ifdef DEBUG
				errno = 0;
				debug_level = strtoul (optarg, &end, 10);
				if (errno == ERANGE || end == optarg || *end)
				{
					fprintf (stderr, "%s: illegal debug level %s\n",
							prog_name, optarg);
					exit (1);
				}
#else
				fprintf (stderr, "%s: sorry, need to recompile with -DDEBUG on...\n",
						prog_name);
#endif
				break;

			case 'v':
				++verbose;
				break;

			case 'V':
				printf ("%s: httperf-"VERSION" compiled "__DATE__" with"
#ifndef DEBUG
						"out"
#endif
						" DEBUG with"
#ifndef TIME_SYSCALLS
						"out"
#endif
						" TIME_SYSCALLS.\n", prog_name);
				// Can't exit here, trun uses -V for all runs
				// exit(0);
				break;

			case 'h':
				usage ();
				exit (0);

			case ':':
				fprintf (stderr, "%s: parameter missing for option %s\n",
						prog_name, longopts[longindex].name);
				exit (1);

			case '?':
				/* Invalid or ambiguous option name or extraneous parameter.
				   getopt_long () already issued an explanation to the user,
				   so all we do is call it quites.  */
				exit (1);

			default:
				fprintf (stderr,
						"%s: getopt_long: unexpected value (%d)\n",
						prog_name, ch);
				exit (1);
		}
	}
	if (param.num_sessions <= 0) {
		fprintf(stderr, "Num_sessions: invalid value %d\n", param.num_sessions);
		exit(-1);
	}

#ifdef HAVE_SSL
	/* videosesslog may have some sessions which use ssl */
	if (param.use_ssl || param.num_sessions)
	{
		char buf[1024];

		if (param.port < 0)
			param.port = 443;

		SSL_load_error_strings ();
		SSLeay_add_ssl_algorithms ();

		/* for some strange reason, SSLv23_client_method () doesn't work here */
    SSL_CTX_set_min_proto_version(ssl_ctx, TLS1_3_VERSION);
    ssl_ctx = SSL_CTX_new (TLS_client_method ());
		if (!ssl_ctx)
		{
		 	ERR_print_errors_fp (stderr);
		 	exit (-1);
		}

		memset (buf, 0, sizeof (buf));
		RAND_seed (buf, sizeof (buf));
	}
#endif
	if (param.port < 0)
		param.port = 80;

	if (param.print_reply || param.print_request)
		stat[num_stats++] = &stats_print_reply;

	if (param.session_offsets) {
		if (!got_client_param) {
			printf("Can't use --client-session-offsets without --client I/N\n");
			exit(1);
		}
	}

	if (param.session_cookies)
	{
		if (!session_workload)
		{
			fprintf (stderr, "%s: --session-cookie requires session-oriented "
					"workload (e.g., --wsess)\n", prog_name);
			exit (-1);
		}
		gen[num_gen++] = &sess_cookie;
	}

	if (param.additional_header || param.method)
		gen[num_gen++] = &misc;

	/* echo command invocation for logging purposes: */
	printf ("%s", prog_name);
	if (verbose) printf (" --verbose");
	switch (param.print_reply)
	{
		case 0:		break;
		case PRINT_HEADER:	printf (" --print-reply=header"); break;
		case PRINT_BODY:	printf (" --print-reply=body"); break;
		default:		printf (" --print-reply"); break;
	}
	switch (param.print_request)
	{
		case 0:		break;
		case PRINT_HEADER:	printf (" --print-request=header"); break;
		case PRINT_BODY:	printf (" --print-request=body"); break;
		default:		printf (" --print-request"); break;
	}
	if (param.hog) printf (" --hog");
	if (param.close_with_reset) printf (" --close-with-reset");
	if (param.timeout_with_reset) printf (" --timeout-with-reset");
	if (param.think_timeout > 0) printf (" --think-timeout=%g",
			param.think_timeout);
	if (param.timeout > 0) printf (" --timeout=%g", param.timeout);
	printf (" --client=%u/%u", param.client.id, param.client.num_clients);
	if (param.server) printf (" --server=%s", param.server);
	if (param.server_name) printf (" --server_name=%s", param.server_name);
	if (param.port) printf (" --port=%d", param.port);
	if (param.uri) printf (" --uri=%s", param.uri);
	if (param.failure_status) printf (" --failure-status=%u",
			param.failure_status);
	if (param.http_version != 0x10001)
		printf (" --http-version=%u.%u", param.http_version >> 16,
				param.http_version & 0xffff);
	if (param.max_conns)
		printf (" --max-connections=%u", param.max_conns);
#ifdef IDLECONN
	if (param.idle_conns)
		printf (" --idle-connections=%u", param.idle_conns);
	if (param.idleconn)
		printf (" --idleconn=%s", param.idleconn);
#endif /* IDLECONN */
	if (param.max_piped)
		printf (" --max-piped-calls=%u", param.max_piped);
	if (param.rate_interval != 5.0)
		printf("--rate-interval=%g", param.rate_interval);
	if (param.rate.rate_param > 0.0)
	{
		switch (param.rate.dist)
		{
			case DETERMINISTIC:
				/* for backwards compatibility, continue to use --rate: */
				printf (" --rate=%g", param.rate.rate_param);
				break;

			case UNIFORM:
				printf (" --period=u%g,%g",
						param.rate.min_iat, param.rate.max_iat);
				break;

			case EXPONENTIAL:
				printf (" --period=e%g", param.rate.mean_iat);
				break;

#ifndef SRINI_RATE
			case VARIABLE:
			case VARIABLE_EXP:
				{
					int m;
					printf (" --period=%c", (param.rate.dist == VARIABLE) ? 'v' : 'l');
					for (m=0; m<param.rate.numRates; m++) 
					{
						if (m != 0)
							printf (",");
						printf ("%g,%g", param.rate.iat[m], 
								param.rate.duration[m]);
					}
				}
				break;
#endif

			default:
				printf("--period=??");
				break;
		}
	}

#ifdef UW_STABLE_STATS
	if (param.client.stagger_start) printf (" --stagger-start");
	if (param.ramp_up_conns > 0) {
		printf (" --ramp-up-num-conns=%d", param.ramp_up_conns );
	}
	if (param.ramp_down_conns > 0) {
		printf (" --ramp-down-num-conns=%d", param.ramp_down_conns );
	}
#endif /* UW_STABLE_STATS */
	printf (" --send-buffer=%d", param.send_buffer_size);
	if (param.retry_on_failure) printf (" --retry-on-failure");
	printf (" --recv-buffer=%d", param.recv_buffer_size);
	if (param.session_cookies) printf (" --session-cookies");
#ifdef HAVE_EPOLL
	if (param.use_epoll) printf (" --epoll");
#endif
#ifdef HAVE_SSL
	if (param.use_ssl) printf (" --ssl");
	if (param.ssl_cipher_list)
		printf(" --ssl-ciphers=%s", param.ssl_cipher_list);
	if (!param.ssl_reuse) printf (" --ssl-no-reuse");
#endif
#ifdef UW_CALL_STATS
	if (param.call_stats != -1)
		printf (" --call-stats=%d", param.call_stats);
	if (param.call_stats_file != 0)
		printf (" --call-stats-file=%s", param.call_stats_file);
#endif
	if (param.stats_interval)
		printf (" --stats-interval=%d", param.stats_interval);
	if (param.additional_header)
		printf (" --add-header='%s'", param.additional_header);
	if (param.method) printf (" --method=%s", param.method);
	if (param.num_sessions)
	{
		/* This overrides any --wsess, --num-conns, --num-calls,
		   --burst-length and any uri generator */
		// TODO printf (" --videosesslog=%u,%.3f,%s", param.videosesslog.num_sessions,
		//		param.videosesslog.think_time, param.videosesslog.file);
	}
	/*
	else if (param.wsesspage.num_sessions)
	{
		printf (" --wsesspage=%u,%u,%.3f", param.wsesspage.num_sessions,
				param.wsesspage.num_reqs, param.wsesspage.think_time);
	}
	else
	{
		if (param.wsess.num_sessions)
			printf (" --wsess=%u,%u,%.3f", param.wsess.num_sessions,
					param.wsess.num_calls, param.wsess.think_time);
		else
		{
			if (param.num_conns) printf (" --num-conns=%d", param.num_conns);
			if (param.num_calls) printf (" --num-calls=%d",
					param.num_calls);
		}
		if (param.burst_len != 1) printf (" --burst-length=%d", param.burst_len);
		if (param.wset.num_files) printf (" --wset=%u,%.3f",
				param.wset.num_files,
				param.wset.target_miss_rate);
	}
	*/
	printf ("\n");

	gethostname(hostname, MAX_HOSTNAME_LEN-1);
	printf("Run on hostname: %s\n", hostname);

	local_t = time(NULL);
	local = localtime(&local_t);
	printf("Run at: %s\n", asctime(local));

#ifdef HAVE_SCHED_AFFINITY
	{
		int rc;
		unsigned long cpu_mask;

		if (param.cpu_mask != 0xffffffff)
		{
			rc = sched_setaffinity_videoperf (0, sizeof(param.cpu_mask),
					&param.cpu_mask);
			if (rc < 0)
			{
				fprintf (stderr,
						"%s: sched_setaffinity failed, rc=%d errno=%d (%s)\n",
						prog_name, rc, errno, strerror(errno));
				exit (1);
			}
		        rc = sched_getaffinity_videoperf (0, sizeof(cpu_mask), &cpu_mask);
		        if (rc < 0)
		        {
			        fprintf (stderr, "%s: sched_getaffinity failed, rc=%d errno=%d (%s)\n",
					 prog_name, rc, errno, strerror(errno));
			        exit (1);
		        }
		        printf("Effective CPU mask: 0x%lx\n", cpu_mask);
                }
	}
#endif /* HAVE_SCHED_AFFINITY */

#ifdef IDLECONN
	int idlepid = 0;
	if (param.idle_conns) {
		idlepid = fork();
		if (idlepid < 0) {
			fprintf (stderr, "%s: could not fork idleconn process\n", prog_name);
			exit(1);
		} else if (idlepid == 0) {
			// fork: idleconn server port numidle
			char iconn[100];
			sprintf(iconn, "%u", param.idle_conns);
			char iport[100];
			sprintf(iport, "%u", param.port);
			char icmd[1000];
			if (param.idleconn) {
				strcpy(icmd, param.idleconn);
			} else {
				strcpy(icmd, "idleconn");
			}

			int res = execlp(icmd, icmd, param.server, iport, iconn, NULL);
			if (res != 0) {
				printf("res = %d\n", errno);
				fprintf (stderr, "%s: could not exec %s process\n", prog_name, icmd);
				exit(1);
			}
		}
	}
#endif /* IDLECONN */

	timer_init ();
	core_init ();

	signal (SIGINT, (void (*)()) core_exit);

	for (i = 0; i < num_stats; ++i)
		(*stat[i]->init)();
	for (i = 0; i < num_gen; ++i)
		(*gen[i]->init) ();

	/* Update `now'.  This is to keep things accurate even when some of
	   the initialization routines take a long time to execute.  */
	timer_tick ();

	/* ensure that clients sample rates at different times: */
	t = (param.client.id + 1.0)*param.rate_interval/param.client.num_clients;
	arg.l = 0;
	timer_schedule (perf_sample, arg, t);
	perf_sample_start = timer_now ();

	if (param.stats_interval)
	{
		timer_schedule (dump_stats, arg, t);
		dump_stats_start = timer_now ();
	}

#ifdef OLDWAY
	/* We believe this is needed for staggered starts */
	/* There might be a different/better way to do this */
	/* setting test time here because it is used when starting stats */
	test_time_start = timer_now ();
#endif /* OLDWAY */

	for (i = 0; i < num_gen; ++i)
		(*gen[i]->start) ();
	for (i = 0; i < num_stats; ++i)
		(*stat[i]->start)();

	getrusage (RUSAGE_SELF, &test_rusage_start);
	test_time_start = timer_now ();
	core_loop ();
	test_time_stop = timer_now ();
	getrusage (RUSAGE_SELF, &test_rusage_stop);

	for (i = 0; i < num_stats; ++i)
		(*stat[i]->stop)();
	for (i = 0; i < num_gen; ++i)
		(*gen[i]->stop) ();
	for (i = 0; i < num_stats; ++i)
		(*stat[i]->dump)();

#if IDLECONN
	if (idlepid > 0)
		kill(idlepid, SIGINT);
#endif /* IDLECONN */
	return 0;
}


