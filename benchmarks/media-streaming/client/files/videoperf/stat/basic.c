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

/* Basic statistics collector.  */


#include "config.h"

#include <stdio.h>
#include <stdlib.h>
#include <assert.h>
#include <errno.h>
#include <float.h>
#include <stdio.h>
#include <string.h>
#include <limits.h> 

#include <httperf.h>
#include <call.h>
#include <event.h>
#include <stats.h>
#include <spec_stats.h>

#ifdef UW_CALL_STATS
#include <call_stats.h>
#endif /* UW_CALL_STATS */


extern Cmdline_Params param;

/* Increase this if it does not cover at least 50% of all response
   times.  */
#define MAX_LIFETIME	100.0 /* max. conn. lifetime in seconds */
#define BIN_WIDTH	1e-3  /* !! CHANGE CODE BELOW IF YOU CHANGE THESE */

#ifdef OLDWAY
/* Newer compilers complain about this */
#define NUM_BINS	((u_int) (MAX_LIFETIME / BIN_WIDTH))
#else
/* NOTE THIS SHOULD BE CALCULATED FROM ABOVE VALUES */
/* MAX_LIFETIME / BIN_WIDTH */
#define NUM_BINS	(100000)
#endif

unsigned int *errno_errs_reported   = 0;
unsigned int *httperf_errs_reported = 0;
#define MAX_ERRNO           (500)
void print_errno_info();

/* #define LOCAL_DEBUG */
#ifdef LOCAL_DEBUG

void print_connected_info(char *func, int sd, int state);

#define ldbg(...) \
	do {\
		printf(__VA_ARGS__); \
		fflush(stdout); \
		fflush(stderr); \
	} while (0);

#else

#define ldbg(...)
#define print_connected_info(a, b, c)

#endif /* LOCAL_DEBUG */


static struct
{
	u_int num_conns_issued;	/* total # of connections issued */
	u_int num_replies[6];	/* completion count per status class */
	u_int num_client_timeouts;	/* # of client timeouts */
	u_int num_sock_fdunavail;	/* # of times out of filedescriptors */
	u_int num_sock_ftabfull;	/* # of times file table was full */
	u_int num_sock_refused;	/* # of ECONNREFUSED */
	u_int num_sock_reset;	/* # of ECONNRESET */
	u_int num_sock_timeouts;	/* # of ETIMEDOUT */
	u_int num_sock_addrunavail;/* # of EADDRNOTAVAIL */
	u_int num_sock_addrinuse;    /* # of EADDRINUSE */
	u_int num_other_errors;	/* # of other errors */
	u_int max_conns;		/* max # of concurrent connections */
	int max_connected_conns;  /* max # of concurrent connected connections */
	int num_connected_conns;  /* # of concurrent connected connections */
	int num_total_connected_conns;
	int num_client_connected_timeouts;    /* timeout happened after  connection was established */
	int num_client_unconnected_timeouts;  /* timeout happened before connection was established */

	u_int num_lifetimes;
	Time conn_lifetime_sum;	/* sum of connection lifetimes */
	Time conn_lifetime_sum2;	/* sum of connection lifetimes squared */
	Time conn_lifetime_min;	/* minimum connection lifetime */
	Time conn_lifetime_max;	/* maximum connection lifetime */

	Time conn_lifetime_sum_notm; /* sum of conn lifetimes for conns that don't time out */
	Time conn_lifetime_sum_tm;   /* sum of conn lifetimes for conns that do time out */
	int timeout_replies;         /* num replies recvd from conns that eventually timed out */
	int no_timeout_replies;      /* num replies recvd from conns tha did not time out */

#ifdef UW_STABLE_STATS
	/* keep track of stats for the entire experiment, not just stable time */
	u_int all_conns_issued;	/* total # of connections issued */
	int num_total_all_conns;
	int max_all_conns;  	/* max # of concurrent connected connections */
	u_int num_all_sent;		/* # of requests sent */
	u_int num_all_replies;	/* completion count for all connections */
	u_int all_num_lifetimes;
	Time all_lifetime_sum;		/* sum of connection lifetimes */
	Time all_lifetime_sum2;		/* sum of connection lifetimes squared */
	Time all_lifetime_min;		/* minimum connection lifetime */
	Time all_lifetime_max;		/* maximum connection lifetime */

	Time all_lifetime_sum_notm; 	/* sum of conn lifetimes for conns that don't time out */
	Time all_lifetime_sum_tm;   	/* sum of conn lifetimes for conns that do time out */
	int all_timeout_replies;         /* num replies recvd from conns that eventually timed out */
	int all_no_timeout_replies;      /* num replies recvd from conns tha did not time out */
	u_int is_stable;			/* true when not ramping up or ramping down */
#endif /* UW_STABLE_STATS */

	u_int num_reply_samples;
	Time reply_rate_sum;
	Time reply_rate_sum2;
	Time reply_rate_min;
	Time reply_rate_max;
#ifdef UW_THROUGHPUT_STATS
	double throughput_sum;
	double throughput_sum2;
	double throughput_min;
	double throughput_max;
#endif /* UW_THROUGHPUT_STATS */

	u_int num_connects;		/* # of completed connect()s */
	Time conn_connect_sum;	/* sum of connect times */

	u_int num_responses;
	Time call_response_sum;	/* sum of response times */

	Time call_xfer_sum;		/* sum of response times */

	u_int num_sent;		/* # of requests sent */
	size_t req_bytes_sent;

	u_wide hdr_bytes_received;	/* sum of all header bytes */
	u_wide reply_bytes_received;	/* sum of all data bytes */
	u_wide footer_bytes_received;	/* sum of all footer bytes */

#ifdef UW_STABLE_STATS
	u_wide all_hdr_bytes_received;      /* sum of all header bytes */
	u_wide all_reply_bytes_received;    /* sum of all data bytes */
	u_wide all_footer_bytes_received;   /* sum of all footer bytes */
#endif /* UW_STABLE_STATS */

	u_int conn_lifetime_hist[NUM_BINS];	/* histogram of connection lifetimes */
	u_int num_active_conns[MAX_LOG_FILES];
}
basic;

static u_int num_active_conns;
static u_int num_replies;	/* # of replies received in this interval */

#ifdef UW_THROUGHPUT_STATS
static u_wide bytes_received;	/* number of bytes received in this interval */
#endif /* UW_THROUGHPUT_STATS */

#ifdef UW_STABLE_STATS
static long stable_conns_start;	/* number of connections to ignore at beginning */
static long stable_conns_end;	/* ignore connections starting from this id	*/
#endif /* UW_STABLE_STATS */

int started_dump_stats = 0;

	static void
perf_sample (Event_Type et, Object *obj, Any_Type reg_arg, Any_Type call_arg)
{
	Time weight = call_arg.d;
	double rate;
	int i = 0;

	assert (et == EV_PERF_SAMPLE);

	rate = weight*num_replies;

	if (!started_dump_stats) {
    if(verbose)	{
      printf("Benchmark stats...\n");
    }
		started_dump_stats = 1;
	}
  if(verbose){
    printf("\n\n\n");
	  for (i=0; i < MAX_LOG_FILES; i++) {
		  printf("%d ", basic.num_active_conns[i]);
	  }
	  printf("\n");
  }
#ifdef UW_THROUGHPUT_STATS
	/* convert throughput into Mbps */
	double throughput = weight * bytes_received * 8.0 / 1048576; // aansaarii: 1048576 = 1 M (2^20)
	if (verbose){
		printf("Last Period: throughput (Mbps) = %-8.2lf reply-rate = %-8.1f connected-conns = %-8d\n", 
				throughput, rate, basic.num_connected_conns);  
    // aansaarii
    printf ("Errors: total %-8u client-timo %-8u socket-timo %-8u "
                        "connrefused %-8u connreset %-8u\n"
                        "Errors: fd-unavail %-8u addrunavail %-8u ftab-full %-8u "
                        "addrinuse %-8u other %-8u\n",
                        (basic.num_client_timeouts + basic.num_sock_timeouts
                         + basic.num_sock_fdunavail + basic.num_sock_ftabfull
                         + basic.num_sock_refused + basic.num_sock_reset
                         + basic.num_sock_addrunavail + basic.num_sock_addrinuse
                         + basic.num_other_errors),
                        basic.num_client_timeouts, basic.num_sock_timeouts,
                        basic.num_sock_refused, basic.num_sock_reset,
                        basic.num_sock_fdunavail, basic.num_sock_addrunavail,
                        basic.num_sock_ftabfull, basic.num_sock_addrinuse,
                        basic.num_other_errors);
    fflush(stdout);
  }
#else
	if (verbose){
		printf("reply-rate = %-8.1f connected-conns = %-8d\n", 
				rate, basic.num_connected_conns);
    // aansaarii
    printf ("Errors: total %-8u client-timo %-8u socket-timo %-8u "
                        "connrefused %-8u connreset %-8u\n"
                        "Errors: fd-unavail %-8u addrunavail %-8u ftab-full %-8u "
                        "addrinuse %-8u other %-8u\n",
                        (basic.num_client_timeouts + basic.num_sock_timeouts
                         + basic.num_sock_fdunavail + basic.num_sock_ftabfull
                         + basic.num_sock_refused + basic.num_sock_reset
                         + basic.num_sock_addrunavail + basic.num_sock_addrinuse
                         + basic.num_other_errors),
                        basic.num_client_timeouts, basic.num_sock_timeouts,
                        basic.num_sock_refused, basic.num_sock_reset,
                        basic.num_sock_fdunavail, basic.num_sock_addrunavail,
                        basic.num_sock_ftabfull, basic.num_sock_addrinuse,
                        basic.num_other_errors);
    fflush(stdout);
  }
#endif /* UW_THROUGHPUT_STATS */

#ifdef UW_STABLE_STATS
	if (basic.is_stable)
#endif /* UW_STABLE_STATS */
	{
		basic.reply_rate_sum += rate;
		basic.reply_rate_sum2 += SQUARE (rate);
		if (rate < basic.reply_rate_min)
			basic.reply_rate_min = rate;
		if (rate > basic.reply_rate_max)
			basic.reply_rate_max = rate;
		++basic.num_reply_samples;

#ifdef UW_THROUGHPUT_STATS
		basic.throughput_sum += throughput;
		basic.throughput_sum2 += SQUARE (throughput);
		if (throughput < basic.throughput_min)
			basic.throughput_min = throughput;
		if (throughput > basic.throughput_max)
			basic.throughput_max = throughput;
#endif /* UW_THROUGHPUT_STATS */
	}

	/* prepare for next sample interval: */
	num_replies = 0;

#ifdef UW_THROUGHPUT_STATS
	bytes_received = 0;
#endif /* UW_THROUGHPUT_STATS */
}

	static void
conn_timeout (Event_Type et, Object *obj, Any_Type reg_arg, Any_Type call_arg)
{
	Conn *s = (Conn *) obj;
	assert (et == EV_CONN_TIMEOUT);

	s->timed_out = 1;

#ifdef UW_CALL_STATS
	s->basic.time_of_timeout = timer_now();
#endif /* UW_CALL_STATS */

#ifdef UW_STABLE_STATS
	if (s->basic.is_stable) {
		++basic.num_client_timeouts;
		if ((s->state >= S_CONNECTED) && (s->sd > 0)) {
			++basic.num_client_connected_timeouts;
		} else {
			++basic.num_client_unconnected_timeouts;
		}
	}
#else
	++basic.num_client_timeouts;
	if ((s->state >= S_CONNECTED) && (s->sd > 0)) {
		++basic.num_client_connected_timeouts;
	} else {
		++basic.num_client_unconnected_timeouts;
	}
#endif /* UW_STABLE_STATS */
	if( param.spec_stats > 0 ) {
		process_spec_timeout( s );
	}

#ifdef UW_CALL_STATS
	if( param.call_stats >= 0 ) {
		process_call_timeout( s );
	}
#endif /* UW_CALL_STATS */

	print_connected_info(__FUNCTION__, s->sd, s->state);
}

	static void
conn_close(Event_Type et, Object *obj, Any_Type reg_arg, Any_Type call_arg)
{
	Conn *s = (Conn *) obj;
	int prev_state = call_arg.i;

	/* only decrement the count if the connection was connected
	 * and the socket descriptor is still valid.
	 * If these aren't true then the connection wasn't actually
	 * established and it hasn't been counted.
	 */
	if ((prev_state >= S_CONNECTED) && (s->sd > 0)) {
		--basic.num_connected_conns;
	}
	print_connected_info(__FUNCTION__, s->sd, prev_state);
}

	static void
conn_fail (Event_Type et, Object *obj, Any_Type reg_arg, Any_Type call_arg)
{
#ifdef LOCAL_DEBUG
	Conn *s = (Conn *) obj;
#endif
	/* static int first_time = 1; */
	int err = call_arg.i;
	int index = -1;

	assert (et == EV_CONN_FAILED);

	print_connected_info(__FUNCTION__, s->sd, s->state);

	switch (err)
	{
#ifdef __linux__
		case EINVAL:	/* Linux has a strange way of saying "out of fds"... */
#endif
		case EMFILE:	++basic.num_sock_fdunavail; break;
		case ENFILE:	++basic.num_sock_ftabfull; break;
		case ECONNREFUSED:	++basic.num_sock_refused; break;
		case ETIMEDOUT:	++basic.num_sock_timeouts; break;
		case EADDRINUSE:	++basic.num_sock_addrinuse; break;

		case EPIPE:
		case ECONNRESET:
					++basic.num_sock_reset;
					break;

		default:
					++basic.num_other_errors;

					if (err < 0) {
						index = -err;
						httperf_errs_reported[index] += 1;

						if (httperf_errs_reported[index] == 1) {
							switch (err) {
								case CONN_ERR_NOT_SET:
									fprintf (stderr, "%s: connection failed but error value wasn't set properly\n", prog_name);
									break;

								case CONN_ERR_NO_MORE_PORTS:
									fprintf (stderr, "%s: connection failed because no ports were available\n", prog_name);
									/* fprintf(stderr, "index = %d count = %d\n", index, httperf_errs_reported[index]); */
									break;

								case CONN_ERR_HASH_LOOKUP_FAILED:
									fprintf (stderr, "%s: connection failed because hash_lookup failed\n", prog_name);
									break;

								case CONN_ERR_STATE_PAST_CLOSING:
									fprintf (stderr, "%s: connection failed because of incorrect state\n", prog_name);
									break;

								default:
									fprintf (stderr, "%s: connection failed with unexpected error %d\n",
											prog_name, err);
							}
						}
					} else {
						errno_errs_reported[err]++;
						if (errno_errs_reported[err] == 1) {
							fprintf (stderr, "%s: connection failed with unexpected error %d\n",
									prog_name, err);
						}
					}
					break;
	}
}

	static void
conn_created (Event_Type et, Object *obj, Any_Type reg_arg, Any_Type c_arg)
{
	++num_active_conns;
	if (num_active_conns > basic.max_conns)
		basic.max_conns = num_active_conns;
}

	static void
conn_connecting (Event_Type et, Object *obj, Any_Type reg_arg, Any_Type c_arg)
{
	Conn *s = (Conn *) obj;

	basic.num_active_conns[s->log_index]++;

	assert (et == EV_CONN_CONNECTING && object_is_conn (s));

	s->basic.time_connect_start = timer_now ();
#ifdef UW_STABLE_STATS
	// printf("s->id = %ld\n", s->id);
	// printf("stable_conns_start = %ld\n", stable_conns_start);
	// printf("stable_conns_end = %ld\n", stable_conns_end);
	if (((long) s->id >= stable_conns_start) && ((long) s->id < stable_conns_end)) {
		// printf("is stable\n");
		basic.is_stable = 1;
		s->basic.is_stable = 1;
		++basic.num_conns_issued;
	} else {
		// printf("is NOT stable\n");
		basic.is_stable = 0;
		s->basic.is_stable = 0;
	}
	++basic.all_conns_issued;
#else
	++basic.num_conns_issued;
#endif /* UW_STABLE_STATS */
}

	static void
conn_connected (Event_Type et, Object *obj, Any_Type reg_arg,
		Any_Type call_arg)
{
	Conn *s = (Conn *) obj;

	assert (et == EV_CONN_CONNECTED && object_is_conn (s));

#ifdef UW_CALL_STATS
	s->basic.time_to_connect = timer_now () - s->basic.time_connect_start;
	basic.conn_connect_sum += s->basic.time_to_connect;
#else
	basic.conn_connect_sum += timer_now () - s->basic.time_connect_start;
#endif /* UW_CALL_STATS */

	++basic.num_connects;

	if (s->state >= S_CONNECTED) {
		++basic.num_connected_conns;
#ifdef UW_STABLE_STATS
		if (s->basic.is_stable) {
			++basic.num_total_connected_conns;
			if (basic.num_connected_conns > basic.max_connected_conns) {
				basic.max_connected_conns = basic.num_connected_conns;
			}
		}

		++basic.num_total_all_conns;
		if (basic.num_connected_conns > basic.max_all_conns) {
			basic.max_all_conns = basic.num_connected_conns;
		}
#else
		++basic.num_total_connected_conns;
		if (basic.num_connected_conns > basic.max_connected_conns) {
			basic.max_connected_conns = basic.num_connected_conns;
		}
#endif /* UW_STABLE_STATS */
	}
	if( param.spec_stats > 0 ) {
		track_spec_connection( s );
	}
	print_connected_info(__FUNCTION__, s->sd, s->state);

}

	static void
conn_destroyed (Event_Type et, Object *obj, Any_Type reg_arg, Any_Type c_arg)
{
	Conn *s = (Conn *) obj;
	Time lifetime;
	u_int bin;

	assert (et == EV_CONN_DESTROYED && object_is_conn (s)
			&& num_active_conns > 0);

	lifetime = timer_now () - s->basic.time_connect_start;

	basic.num_active_conns[s->log_index]--;
#ifdef UW_STABLE_STATS
	if( s->timed_out ) {
		basic.all_lifetime_sum_tm += lifetime;
		basic.all_timeout_replies += s->basic.num_calls_completed;
	} else {
		basic.all_lifetime_sum_notm += lifetime;
		basic.all_no_timeout_replies += s->basic.num_calls_completed;
	}
	if (s->basic.num_calls_completed > 0) {
		basic.all_lifetime_sum += lifetime;
		basic.all_lifetime_sum2 += SQUARE (lifetime);
		if (lifetime < basic.all_lifetime_min)
			basic.all_lifetime_min = lifetime;
		if (lifetime > basic.all_lifetime_max)
			basic.all_lifetime_max = lifetime;
		++basic.all_num_lifetimes;
	}
	if (s->basic.is_stable) {
		if( s->timed_out ) {
			basic.conn_lifetime_sum_tm += lifetime;
			basic.timeout_replies += s->basic.num_calls_completed;
		} else {
			basic.conn_lifetime_sum_notm += lifetime;
			basic.no_timeout_replies += s->basic.num_calls_completed;
		}

		if (s->basic.num_calls_completed > 0) {
			basic.conn_lifetime_sum += lifetime;
			basic.conn_lifetime_sum2 += SQUARE (lifetime);
			if (lifetime < basic.conn_lifetime_min)
				basic.conn_lifetime_min = lifetime;
			if (lifetime > basic.conn_lifetime_max)
				basic.conn_lifetime_max = lifetime;
			++basic.num_lifetimes;
			bin = lifetime*NUM_BINS/MAX_LIFETIME;
			if (bin >= NUM_BINS)
				bin = NUM_BINS-1;
			++basic.conn_lifetime_hist[bin];
		}
	}
#else
	if( s->timed_out ) {
		basic.conn_lifetime_sum_tm += lifetime;
		basic.timeout_replies += s->basic.num_calls_completed;
	} else {
		basic.conn_lifetime_sum_notm += lifetime;
		basic.no_timeout_replies += s->basic.num_calls_completed;
	}
	if (s->basic.num_calls_completed > 0)
	{
		basic.conn_lifetime_sum += lifetime;
		basic.conn_lifetime_sum2 += SQUARE (lifetime);
		if (lifetime < basic.conn_lifetime_min)
			basic.conn_lifetime_min = lifetime;
		if (lifetime > basic.conn_lifetime_max)
			basic.conn_lifetime_max = lifetime;
		++basic.num_lifetimes;
		bin = lifetime*NUM_BINS/MAX_LIFETIME;
		if (bin >= NUM_BINS)
			bin = NUM_BINS-1;
		++basic.conn_lifetime_hist[bin];
	}
#endif /* UW_STABLE_STATS */

	--num_active_conns;
}

	static void
send_start (Event_Type et, Object *obj, Any_Type reg_arg, Any_Type call_arg)
{
	Call *c = (Call *) obj;

	assert (et == EV_CALL_SEND_START && object_is_call (c));

	c->basic.time_send_start = timer_now ();
}

	static void
send_stop (Event_Type et, Object *obj, Any_Type reg_arg, Any_Type call_arg)
{
	Call *c = (Call *) obj;

	assert (et == EV_CALL_SEND_STOP && object_is_call (c));

#ifdef UW_STABLE_STATS
	if (basic.is_stable) {
		basic.req_bytes_sent += c->req.size;
		++basic.num_sent;
	}
	++basic.num_all_sent;
#else
	basic.req_bytes_sent += c->req.size;
	++basic.num_sent;
#endif /* UW_STABLE_STATS */
	if( param.spec_stats > 0 ) {
		track_spec_request(c);
	}

#ifdef UW_CALL_STATS
	if( param.call_stats>= 0 ) {
		track_call_request(c);
	}
#endif /* UW_CALL_STATS */
}

	static void
recv_start (Event_Type et, Object *obj, Any_Type reg_arg, Any_Type call_arg)
{
	Call *c = (Call *) obj;
	Time now;
	double resp_time = 0.0;

	assert (et == EV_CALL_RECV_START && object_is_call (c));

	now = timer_now ();

	resp_time = now - c->basic.time_send_start;  
	c->basic.time_recv_start = now;

#ifdef UW_STABLE_STATS
	if (basic.is_stable) {
		basic.call_response_sum += resp_time;
		++basic.num_responses;
	}
#else
	basic.call_response_sum += resp_time;
	++basic.num_responses;
#endif /* UW_STABLE_STATS */
	if( param.spec_stats > 0 ) {
		track_spec_response( c, resp_time );
	}
}

	static void
recv_stop (Event_Type et, Object *obj, Any_Type reg_arg, Any_Type call_arg)
{
	Call *c = (Call *) obj;
	int index;
	double transfer_time = 0.0;

	assert (et == EV_CALL_RECV_STOP && object_is_call (c));
	assert (c->basic.time_recv_start > 0);

	transfer_time  = timer_now () - c->basic.time_recv_start;

#ifdef UW_STABLE_STATS
	if (basic.is_stable) {
		basic.call_xfer_sum += transfer_time;
		basic.hdr_bytes_received += c->reply.header_bytes;
		basic.reply_bytes_received += c->reply.content_bytes;
		basic.footer_bytes_received += c->reply.footer_bytes;
		index = (c->reply.status / 100);
		assert ((unsigned) index < NELEMS (basic.num_replies));
		++basic.num_replies[index];
	}
	++basic.num_all_replies;

	basic.all_hdr_bytes_received += c->reply.header_bytes;
	basic.all_reply_bytes_received += c->reply.content_bytes;
	basic.all_footer_bytes_received += c->reply.footer_bytes;

#else
	basic.call_xfer_sum += transfer_time;
	basic.hdr_bytes_received += c->reply.header_bytes;
	basic.reply_bytes_received += c->reply.content_bytes;
	basic.footer_bytes_received += c->reply.footer_bytes;
	index = (c->reply.status / 100);
	assert ((unsigned) index < NELEMS (basic.num_replies));
	++basic.num_replies[index];
#endif /* UW_STABLE_STATS */

#ifdef UW_THROUGHPUT_STATS
	/* track total number of bytes received during interval */
	bytes_received += c->reply.header_bytes + c->reply.content_bytes
		+ c->reply.footer_bytes;
#endif /* UW_THROUGHPUT_STATS */
	++num_replies;
	++c->conn->basic.num_calls_completed;
	if ( param.spec_stats > 0 ) {
		track_spec_reply( c, transfer_time );
	}

#ifdef UW_CALL_STATS
	if ( param.call_stats >= 0 ) {
		track_call_reply( c, transfer_time );
	}
#endif /* UW_CALL_STATS */
}

	static void
dump_stats (Event_Type et, Object *obj, Any_Type req_arg, Any_Type call_arg)
{
	Time conn_period = 0.0, call_period = 0.0;
	Time conn_time = 0.0, resp_time = 0.0, xfer_time = 0.0;
	Time call_size = 0.0, hdr_size = 0.0, reply_size = 0.0, footer_size = 0.0;
	Time lifetime_avg = 0.0, lifetime_stddev = 0.0, lifetime_median = 0.0;
	double avg = 0.0, stddev = 0.0;
	int i, total_replies = 0;
	Time delta, user, sys;
	u_wide total_size;
	Time time;
	u_int n;

	assert (et == EV_DUMP_STATS);

	for (i = 1; i < NELEMS (basic.num_replies); ++i)
		total_replies += basic.num_replies[i];

#ifdef UW_STABLE_STATS
	delta = timer_now() - test_time_start;
	if (basic.is_stable && stable_conns_start >= 0) {

		/* subtract time it took to become stable */
		delta -= stable_conns_start * param.rate.mean_iat;
	}
#else
	delta = timer_now() - test_time_start;
#endif /* UW_STABLE_STATS */

	if (verbose > 1)
	{
		printf ("\nConnection lifetime histogram (time in ms):\n");
		for (i = 0; i < NUM_BINS; ++i)
			if (basic.conn_lifetime_hist[i])
			{
				if (i > 0 && basic.conn_lifetime_hist[i - 1] == 0)
					printf ("%14c\n", ':');
				time = (i + 0.5)*BIN_WIDTH;
				printf ("%16.1f %u\n", 1e3*time, basic.conn_lifetime_hist[i]);
			}
	}

	printf ("\nTotal: connections %u requests %u replies %u "
			"test-duration %.3f s\n",
			basic.num_conns_issued, basic.num_sent, total_replies,
			delta);
	printf("Number of connected connections is currently = %d\n", basic.num_connected_conns);
	if (basic.num_connected_conns != 0) {
		printf("WARNING: was expecting 0 and got %d\n", basic.num_connected_conns);
	}

	putchar ('\n');

	if (basic.num_conns_issued)
		conn_period = delta/basic.num_conns_issued;
	printf ("Connection rate: %.1f conn/s (%.1f ms/conn, "
			"<=%u concurrent connections)\n",
			basic.num_conns_issued / delta, 1e3*conn_period, basic.max_conns);

	printf ("Connected connection rate: %.1f conn/s (%.1f ms/conn, "
			"<=%d concurrent connected connections)\n",
			basic.num_total_connected_conns / delta, 1e3*conn_period, basic.max_connected_conns);

	if (basic.num_lifetimes > 0)
	{
		lifetime_avg = (basic.conn_lifetime_sum / basic.num_lifetimes);
		if (basic.num_lifetimes > 1)
			lifetime_stddev = STDDEV (basic.conn_lifetime_sum,
					basic.conn_lifetime_sum2,
					basic.num_lifetimes);
		n = 0;
		for (i = 0; i < NUM_BINS; ++i)
		{
			n += basic.conn_lifetime_hist[i];
			if (n >= 0.5*basic.num_lifetimes)
			{
				lifetime_median = (i + 0.5)*BIN_WIDTH;
				break;
			}
		}
	}  
	else
	{
		lifetime_avg = param.timeout;
		lifetime_median = param.timeout;
	}
	printf ("Connection time [ms]: min %.1f avg %.1f max %.1f median %.1f "
			"stddev %.1f\n",
			basic.num_lifetimes > 0 ? 1e3 * basic.conn_lifetime_min : 0.0,
			1e3 * lifetime_avg,
			1e3 * basic.conn_lifetime_max, 1e3 * lifetime_median,
			1e3 * lifetime_stddev);
	if (basic.num_connects > 0)
		conn_time = basic.conn_connect_sum / basic.num_connects;
	printf ("Connection time [ms]: connect %.1f\n", 1e3*conn_time);
	printf ("Connection length [replies/conn]: %.3f\n",
			basic.num_lifetimes > 0
			? total_replies/ (double) basic.num_lifetimes : 0.0);
	putchar ('\n');

#ifdef UW_STABLE_STATS
	lifetime_avg = 0.0;
	lifetime_stddev = 0.0;
	lifetime_median = 0.0;
	if (basic.all_num_lifetimes > 0) {
		lifetime_avg = basic.all_lifetime_sum / basic.all_num_lifetimes;
		if (basic.all_num_lifetimes > 1)
			lifetime_stddev = STDDEV (basic.all_lifetime_sum,
					basic.all_lifetime_sum2,
					basic.all_num_lifetimes);
	}  
	printf ("All Conn time [ms]: min %.1f avg %.1f max %.1f median %.1f "
			"stddev %.1f\n",
			basic.all_num_lifetimes > 0 ? 1e3 * basic.all_lifetime_min : 0.0,
			1e3 * lifetime_avg,
			1e3 * basic.all_lifetime_max, 1e3 * lifetime_median,
			1e3 * lifetime_stddev);
	putchar ('\n');
#endif /* UW_STABLE_STATS */

	if (basic.num_sent > 0)
		call_period = delta/basic.num_sent;
	printf ("Request rate: %.1f req/s (%.1f ms/req)\n",
			basic.num_sent / delta, 1e3*call_period);

	if (basic.num_sent)
		call_size = basic.req_bytes_sent / basic.num_sent;
	printf ("Request size [B]: %.1f\n", call_size);

	putchar ('\n');

	if (basic.num_reply_samples > 0)
	{
		avg = (basic.reply_rate_sum / basic.num_reply_samples);
		if (basic.num_reply_samples > 1)
			stddev = STDDEV (basic.reply_rate_sum,
					basic.reply_rate_sum2,
					basic.num_reply_samples);
	}
	printf ("Reply rate [replies/s]: min %.1f avg %.1f max %.1f stddev %.1f "
			"(%u samples)\n",
			basic.num_reply_samples > 0 ? basic.reply_rate_min : 0.0,
			avg, basic.reply_rate_max,
			stddev, basic.num_reply_samples);

#ifdef UW_THROUGHPUT_STATS
	avg = 0.0;
	stddev = 0.0;
	if (basic.num_reply_samples > 0)
	{
		avg = (basic.throughput_sum / basic.num_reply_samples);
		if (basic.num_reply_samples > 1)
			stddev = STDDEV (basic.throughput_sum,
					basic.throughput_sum2,
					basic.num_reply_samples);
	}
	printf ("Throughput [Mbps]: min %.2f avg %.2f max %.2f stddev %.2f "
			"(%u samples)\n",
			basic.num_reply_samples > 0 ? basic.throughput_min : 0.0,
			avg, basic.throughput_max,
			stddev, basic.num_reply_samples);
#endif /* UW_THROUGHPUT_STATS */

	if (basic.num_responses > 0)
		resp_time = basic.call_response_sum / basic.num_responses;
	if (total_replies > 0)
		xfer_time = basic.call_xfer_sum / total_replies;
	else {
		/* if no responses, asssume transfer time is the timeout period */
		xfer_time = param.timeout;
	}
	printf ("Reply time [ms]: response %.1f transfer %.1f\n",
			1e3*resp_time, 1e3*xfer_time);

	if (total_replies)
	{
		hdr_size = basic.hdr_bytes_received / total_replies;
		reply_size = basic.reply_bytes_received / total_replies;
		footer_size = basic.footer_bytes_received / total_replies;
	}
	printf ("Reply size [B]: header %.1f content %.1f footer %.1f "
			"(total %.1f)\n", hdr_size, reply_size, footer_size,
			hdr_size + reply_size + footer_size);

	printf ("Reply status: 1xx=%u 2xx=%u 3xx=%u 4xx=%u 5xx=%u\n",
			basic.num_replies[1], basic.num_replies[2], basic.num_replies[3],
			basic.num_replies[4], basic.num_replies[5]);

	putchar ('\n');

	user = (TV_TO_SEC (test_rusage_stop.ru_utime)
			- TV_TO_SEC (test_rusage_start.ru_utime));
	sys = (TV_TO_SEC (test_rusage_stop.ru_stime)
			- TV_TO_SEC (test_rusage_start.ru_stime));
	printf ("CPU time [s]: user %.2f system %.2f (user %.1f%% system %.1f%% "
			"total %.1f%%)\n", user, sys, 100.0*user/delta, 100.0*sys/delta,
			100.0*(user + sys)/delta);

	total_size = (basic.req_bytes_sent
			+ basic.hdr_bytes_received + basic.reply_bytes_received);
	printf ("Net I/O: %.1f KB/s (%.1f*10^6 bps)\n",
			total_size/delta / 1024.0, 8e-6*total_size/delta);

	putchar ('\n');

	printf ("Errors: total %u client-timo %u socket-timo %u "
			"connrefused %u connreset %u\n"
			"Errors: fd-unavail %u addrunavail %u ftab-full %u "
			"addrinuse %u other %u\n",
			(basic.num_client_timeouts + basic.num_sock_timeouts
			 + basic.num_sock_fdunavail + basic.num_sock_ftabfull
			 + basic.num_sock_refused + basic.num_sock_reset
			 + basic.num_sock_addrunavail + basic.num_sock_addrinuse
			 + basic.num_other_errors),
			basic.num_client_timeouts, basic.num_sock_timeouts,
			basic.num_sock_refused, basic.num_sock_reset,
			basic.num_sock_fdunavail, basic.num_sock_addrunavail,
			basic.num_sock_ftabfull, basic.num_sock_addrinuse,
			basic.num_other_errors);

	printf("Unconnected timeouts %d connected timeouts %d total (%d) client_timeouts %d\n",
			basic.num_client_unconnected_timeouts, 
			basic.num_client_connected_timeouts, 
			basic.num_client_unconnected_timeouts + basic.num_client_connected_timeouts, 
			basic.num_client_timeouts);

	printf ("----------\n");

	print_errno_info();
	if ( param.spec_stats > 0 ) {
		print_spec_timeouts();
	}

#if 0
	printf ("%6s %6s\n", "errno", "count"); 
	for (i=0; i<MAX_ERRNO; i++) {
		printf("i = %d count = %d\n", i, errno_errs_reported[i]);
		if (errno_errs_reported[i]) {
			printf("%6d %6d\n", i, errno_errs_reported[i]);
		}
	}
	printf("httperf errors\n");
	printf ("%6s %6s\n", "errno", "count"); 
	for (i=0; i<(CONN_ERR_COUNT); i++) {
		printf("i = %d count = %d\n", i, httperf_errs_reported[i]);
		if (httperf_errs_reported[i]) {
			printf("%6d %6d\n", -i, httperf_errs_reported[i]);
		}
	}
#endif
}


	static void
err_report_init(void)
{
	/* TODO - figure out a way to portably figure out the max errno value */
	int max_errno = MAX_ERRNO;
#if 0
	char *s;

	max_errno = 1;

	/* Doesn't work as strerror doesn't return NULL for unknown errors */
	/* TODO - I couldn't find any other way to somewhat portably
	 * find out the maximum errno value 
	 */
	while ((s = strerror(max_errno)) != 0) {
		printf("%s\n", s);
		max_errno++;
	}
#endif

#if 0
	/* I don't think that it's sys_errlist on all systems */
	max_errno = sizeof(sys_errlist) / (sizeof(char *));
#endif


	printf("stat basic init: using maximum errno = %d\n", max_errno);

	if (errno_errs_reported == 0) {
		errno_errs_reported = (unsigned int *) malloc(max_errno * sizeof(int));
		if (!errno_errs_reported) {
			fprintf(stderr, "stats init: malloc failed\n");
			exit(1);
		} else {
			memset(errno_errs_reported, 0, max_errno * sizeof(int));
		}
	}

	if (httperf_errs_reported == 0) {
		httperf_errs_reported = (unsigned int *) malloc(CONN_ERR_COUNT * sizeof(int));
		if (!httperf_errs_reported) {
			fprintf(stderr, "stats init: malloc failed\n");
			exit(1);
		} else {
			memset(httperf_errs_reported, 0, CONN_ERR_COUNT * sizeof(int));
		}
	}
}  

#ifdef UW_STABLE_STATS
	static void
stable_stats_init(void)
{
	basic.all_conns_issued = 0;
	basic.num_total_all_conns = 0;
	basic.max_all_conns = 0;
	basic.num_all_sent = 0;
	basic.num_all_replies = 0;
	basic.all_num_lifetimes = 0;
	basic.all_lifetime_sum = 0.0;
	basic.all_lifetime_sum2 = 0.0;
	basic.all_lifetime_min = DBL_MAX;
	basic.all_lifetime_max = 0.0;
	basic.all_lifetime_sum_notm = 0.0;
	basic.all_lifetime_sum_tm = 0.0;
	basic.all_timeout_replies = 0;
	basic.all_no_timeout_replies = 0;

	/* collect stable statistics for connections, starting after <timeout> seconds */
	/* have elapsed and ending <timeout> seconds before the end of the experiment. */
	if (param.ramp_up_conns > 0)
		stable_conns_start = param.ramp_up_conns;
	else
		stable_conns_start = -1;

	if (param.ramp_down_conns > 0) {
		if (param.wsesslog.num_sessions) {
			stable_conns_end = param.wsesslog.num_sessions - param.ramp_down_conns;
		} else {
			printf("We don't currently handle --ramp-down-conns unless wsesslog is used\n");
			exit(1);
		}
	} else {
		stable_conns_end = INT_MAX;
	}

	printf( "Stable period is %ld to %ld connections\n", stable_conns_start, stable_conns_end );
}
#endif /* UW_STABLE_STATS */

	static void
init (void)
{
	Any_Type arg;

	err_report_init();
	if( param.spec_stats > 0 ) {
		spec_stats_init();
	}

#ifdef UW_CALL_STATS
	if( param.call_stats >= 0) {
		call_stats_init();
	}
#endif /* UW_CALL_STATS */

	basic.max_conns = 0;
	basic.max_connected_conns = 0;
	basic.num_connected_conns = 0;
	basic.num_client_connected_timeouts = 0;
	basic.num_client_unconnected_timeouts = 0;

	basic.conn_lifetime_min = DBL_MAX;
	basic.reply_rate_min = DBL_MAX;
#ifdef UW_THROUGHPUT_STATS
	basic.throughput_min = DBL_MAX;
#endif /* UW_THROUGHPUT_STATS */
	basic.conn_lifetime_sum_notm = 0.0;
	basic.conn_lifetime_sum_tm = 0.0;  
	basic.timeout_replies = 0;     
	basic.no_timeout_replies = 0; 

#ifdef UW_STABLE_STATS
	stable_stats_init();
#endif /* UW_STABLE_STATS */

	arg.l = 0;
	event_register_handler (EV_PERF_SAMPLE, perf_sample, arg);
	event_register_handler (EV_CONN_FAILED, conn_fail, arg);
	event_register_handler (EV_CONN_TIMEOUT, conn_timeout, arg);
	event_register_handler (EV_CONN_NEW, conn_created, arg);
	event_register_handler (EV_CONN_CONNECTING, conn_connecting, arg);
	event_register_handler (EV_CONN_CONNECTED, conn_connected, arg);
	event_register_handler (EV_CONN_CLOSE, conn_close, arg);
	event_register_handler (EV_CONN_DESTROYED, conn_destroyed, arg);
	event_register_handler (EV_CALL_SEND_START, send_start, arg);
	event_register_handler (EV_CALL_SEND_STOP, send_stop, arg);
	event_register_handler (EV_CALL_RECV_START, recv_start, arg);
	event_register_handler (EV_CALL_RECV_STOP, recv_stop, arg);
	event_register_handler (EV_DUMP_STATS, dump_stats, arg);
}

	static void
dump (void)
{
	Time conn_period = 0.0, call_period = 0.0;
	Time conn_time = 0.0, resp_time = 0.0, xfer_time = 0.0;
	Time call_size = 0.0, hdr_size = 0.0, reply_size = 0.0, footer_size = 0.0;
	Time lifetime_avg = 0.0, lifetime_stddev = 0.0, lifetime_median = 0.0;
	double avg = 0.0, stddev = 0.0, reply_rate_overall = 0.0;
	double response_time = 0.0;
	int i, total_replies = 0;
	Time delta, user, sys;
	u_wide total_size;
	Time time;
	u_int n;

	FILE *fp = NULL;
	if (NULL == param.output_log) {
		fp = fopen("output.log", "w");	
	} else {
		fp = fopen(param.output_log, "w");
	}
	if (fp == NULL) {
		fprintf(stderr, "Error: Couldn't write output results!\n");
		return;
	}
	for (i = 1; i < NELEMS (basic.num_replies); ++i)
		total_replies += basic.num_replies[i];

#ifdef UW_STABLE_STATS
	if (stable_conns_start < 0) {
		if (stable_conns_end > param.wsesslog.num_sessions)
			delta = test_time_stop - test_time_start;
		else
			delta = stable_conns_end * param.rate.mean_iat;
	} else {
		if (stable_conns_end > param.wsesslog.num_sessions) {
			delta = (param.wsesslog.num_sessions - stable_conns_start) * param.rate.mean_iat;
		} else {
			delta = (stable_conns_end - stable_conns_start) * param.rate.mean_iat;
		}
	}
#else
	delta = test_time_stop - test_time_start;
#endif /* UW_STABLE_STATS */

	if (verbose > 1)
	{
		fprintf (fp,"\nConnection lifetime histogram (time in ms):\n");
		for (i = 0; i < NUM_BINS; ++i)
			if (basic.conn_lifetime_hist[i])
			{
				if (i > 0 && basic.conn_lifetime_hist[i - 1] == 0)
					fprintf (fp,"%14c\n", ':');
				time = (i + 0.5)*BIN_WIDTH;
				fprintf (fp,"%16.1f %u\n", 1e3*time, basic.conn_lifetime_hist[i]);
			}
	}

	fprintf (fp,"\nTotal: connections %u requests %u replies %u "
			"test-duration %.3f s\n",
			basic.num_conns_issued, basic.num_sent, total_replies,
			delta);
	fprintf(fp,"Number of connected connections is currently = %d\n", basic.num_connected_conns);
	if (basic.num_connected_conns != 0) {
		fprintf(fp,"WARNING: was expecting 0 and got %d\n", basic.num_connected_conns);
	}

	fprintf(fp, "\n");

	if (basic.num_conns_issued)
		conn_period = delta/basic.num_conns_issued;
	fprintf (fp,"Connection rate: %.1f conn/s (%.1f ms/conn, "
			"<=%u concurrent connections)\n",
			basic.num_conns_issued / delta, 1e3*conn_period, basic.max_conns);

	fprintf (fp,"Connected connection rate: %.1f conn/s (%.1f ms/conn, "
			"<=%d concurrent connected connections)\n",
			basic.num_total_connected_conns / delta, 1e3*conn_period, basic.max_connected_conns);

	if (basic.num_lifetimes > 0)
	{
		lifetime_avg = (basic.conn_lifetime_sum / basic.num_lifetimes);
		if (basic.num_lifetimes > 1)
			lifetime_stddev = STDDEV (basic.conn_lifetime_sum,
					basic.conn_lifetime_sum2,
					basic.num_lifetimes);
		n = 0;
		for (i = 0; i < NUM_BINS; ++i)
		{
			n += basic.conn_lifetime_hist[i];
			if (n >= 0.5*basic.num_lifetimes)
			{
				lifetime_median = (i + 0.5)*BIN_WIDTH;
				break;
			}
		}
	}  
	else
	{
		lifetime_avg = param.timeout;
		lifetime_median = param.timeout;
	}
	fprintf (fp, "Connection time [ms]: min %.1f avg %.1f max %.1f median %.1f "
			"stddev %.1f\n",
			basic.num_lifetimes > 0 ? 1e3 * basic.conn_lifetime_min : 0.0,
			1e3 * lifetime_avg,
			1e3 * basic.conn_lifetime_max, 1e3 * lifetime_median,
			1e3 * lifetime_stddev);
	if (basic.num_connects > 0)
		conn_time = basic.conn_connect_sum / basic.num_connects;
	fprintf (fp, "Connection time [ms]: connect %.1f\n", 1e3*conn_time);
	fprintf (fp, "Connection length [replies/conn]: %.3f\n",
			basic.num_lifetimes > 0
			? total_replies/ (double) basic.num_lifetimes : 0.0);
	fprintf (fp, "\n");

	//  if( basic.num_lifetimes > 0 ) {
	// n = basic.timeout_replies + basic.no_timeout_replies;
	// fprintf(fp, "Number of sessions that eventually timed-out: %d (%.1f %%)\n", basic.timeout_replies, 100.0 * basic.timeout_replies / n);
	// if(basic.no_timeout_replies > 0 )
	//   response_time = 1e3 * basic.conn_lifetime_sum_notm / basic.no_timeout_replies;
	// else
	//   response_time = 0.0;
	// fprintf(fp, "\n");
	fprintf(fp, "Response time (no timeouts) [ms]: %.1f\n", response_time);
	if(basic.timeout_replies > 0 )
		response_time = 1e3 * basic.conn_lifetime_sum_tm / basic.timeout_replies;
	else
		response_time = 0.0;
	fprintf(fp, "Response time (only timeouts) [ms]: %.1f\n", response_time); 
	if( n > 0 )
		response_time = 1e3 * (basic.conn_lifetime_sum_tm + basic.conn_lifetime_sum_notm) / n;
	else
		response_time = 1e3 * param.timeout;
	fprintf(fp, "Response time (all) [ms]: %.1f\n", response_time); 
	// }

#ifdef UW_STABLE_STATS
	lifetime_stddev = 0.0;
	lifetime_median = 0.0;
	if (basic.all_num_lifetimes > 0) {
		lifetime_avg = basic.all_lifetime_sum / basic.all_num_lifetimes;
		if (basic.all_num_lifetimes > 1)
			lifetime_stddev = STDDEV (basic.all_lifetime_sum,
					basic.all_lifetime_sum2,
					basic.all_num_lifetimes);
#if 0
		n = 0;
		for (i = 0; i < NUM_BINS; ++i) {
			n += basic.all_lifetime_hist[i];
			if (n >= 0.5*basic.all_num_lifetimes) {
				lifetime_median = (i + 0.5)*BIN_WIDTH;
				break;
			}
		}
#endif
	}  
	fprintf(fp, "\n");
	fprintf (fp, "\nAll: connections %u requests %u replies %u test-duration %.3f s\n",
			basic.all_conns_issued, basic.num_all_sent, basic.num_all_replies,
			test_time_stop - test_time_start );
	fprintf (fp, "All Conn time [ms]: min %.1f avg %.1f max %.1f median %.1f "
			"stddev %.1f\n",
			basic.all_num_lifetimes > 0 ? 1e3 * basic.all_lifetime_min : 0.0,
			1e3 * lifetime_avg,
			1e3 * basic.all_lifetime_max, 1e3 * lifetime_median,
			1e3 * lifetime_stddev);
	n = basic.all_timeout_replies + basic.all_no_timeout_replies;
	fprintf(fp, "All Connections timed-out: %d (%.1f %%)\n", basic.all_timeout_replies, 100.0 * basic.all_timeout_replies / n);
	if( basic.all_no_timeout_replies > 0 )
		response_time = 1e3 * basic.all_lifetime_sum_notm / basic.all_no_timeout_replies;
	else
		response_time = 0.0;
	fprintf(fp, "All Resp time (no timeouts) [ms]: %.1f\n", response_time);
	if(basic.all_timeout_replies > 0 )
		response_time = 1e3 * basic.all_lifetime_sum_tm / basic.all_timeout_replies;
	else
		response_time = 0.0;
	fprintf(fp, "All Resp time (only timeouts) [ms]: %.1f\n", response_time); 
	if( n > 0 )
		response_time = 1e3 * (basic.all_lifetime_sum_tm + basic.all_lifetime_sum_notm) / n;
	else
		response_time = 1e3 * param.timeout;
	fprintf(fp, "All Resp time (all) [ms]: %.1f\n", response_time); 

	if (basic.num_all_replies)
	{
		hdr_size = basic.all_hdr_bytes_received / basic.num_all_replies;
		reply_size = basic.all_reply_bytes_received / basic.num_all_replies;
		footer_size = basic.all_footer_bytes_received / basic.num_all_replies;
	}
	fprintf (fp, "All Reply size [B]: header %.1f content %.1f footer %.1f "
			"(total %.1f)\n", hdr_size, reply_size, footer_size,
			hdr_size + reply_size + footer_size);
	putchar ('\n');
#endif /* UW_STABLE_STATS */

	if (basic.num_sent > 0)
		call_period = delta/basic.num_sent;
	fprintf (fp, "Request rate: %.1f req/s (%.1f ms/req)\n",
			basic.num_sent / delta, 1e3*call_period);

	if (basic.num_sent)
		call_size = basic.req_bytes_sent / basic.num_sent;
	fprintf (fp, "Request size [B]: %.1f\n", call_size);

	fprintf (fp, "\n");

	reply_rate_overall = ((double) total_replies) / delta;
	fprintf(fp, "Overall reply rate: %.1f replies/sec\n",reply_rate_overall);

	if (basic.num_reply_samples > 0)
	{
		avg = (basic.reply_rate_sum / basic.num_reply_samples);
		if (basic.num_reply_samples > 1)
			stddev = STDDEV (basic.reply_rate_sum,
					basic.reply_rate_sum2,
					basic.num_reply_samples);
	}
	fprintf (fp, "Reply rate [replies/s]: min %.1f avg %.1f max %.1f stddev %.1f "
			"(%u samples)\n",
			basic.num_reply_samples > 0 ? basic.reply_rate_min : 0.0,
			avg, basic.reply_rate_max,
			stddev, basic.num_reply_samples);

#ifdef UW_THROUGHPUT_STATS
	avg = 0.0;
	stddev = 0.0;
	if (basic.num_reply_samples > 0)
	{
		avg = (basic.throughput_sum / basic.num_reply_samples);
		if (basic.num_reply_samples > 1)
			stddev = STDDEV (basic.throughput_sum,
					basic.throughput_sum2,
					basic.num_reply_samples);
	}
	fprintf (fp, "Throughput [Mbps]: min %.2f avg %.2f max %.2f stddev %.2f "
			"(%u samples)\n",
			basic.num_reply_samples > 0 ? basic.throughput_min : 0.0,
			avg, basic.throughput_max,
			stddev, basic.num_reply_samples);
#endif /* UW_THROUGHPUT_STATS */

	if (basic.num_responses > 0)
		resp_time = basic.call_response_sum / basic.num_responses;
	if (total_replies > 0)
		xfer_time = basic.call_xfer_sum / total_replies;
	else {
		/* if no responses, asssume transfer time is the timeout period */
		xfer_time = param.timeout;
	}
	fprintf (fp, "Reply time [ms]: response %.1f transfer %.1f\n",
			1e3*resp_time, 1e3*xfer_time);

	if (total_replies)
	{
		hdr_size = basic.hdr_bytes_received / total_replies;
		reply_size = basic.reply_bytes_received / total_replies;
		footer_size = basic.footer_bytes_received / total_replies;
	}
	fprintf (fp, "Reply size [B]: header %.1f content %.1f footer %.1f "
			"(total %.1f)\n", hdr_size, reply_size, footer_size,
			hdr_size + reply_size + footer_size);

	fprintf (fp, "Reply status: 1xx=%u 2xx=%u 3xx=%u 4xx=%u 5xx=%u\n",
			basic.num_replies[1], basic.num_replies[2], basic.num_replies[3],
			basic.num_replies[4], basic.num_replies[5]);

	fprintf(fp, "\n");

	user = (TV_TO_SEC (test_rusage_stop.ru_utime)
			- TV_TO_SEC (test_rusage_start.ru_utime));
	sys = (TV_TO_SEC (test_rusage_stop.ru_stime)
			- TV_TO_SEC (test_rusage_start.ru_stime));
	fprintf (fp, "CPU time [s]: user %.2f system %.2f (user %.1f%% system %.1f%% "
			"total %.1f%%)\n", user, sys, 100.0*user/delta, 100.0*sys/delta,
			100.0*(user + sys)/delta);

	total_size = (basic.req_bytes_sent
			+ basic.hdr_bytes_received + basic.reply_bytes_received);
	fprintf (fp, "Net I/O: %.1f KB/s (%.1f*10^6 bps)\n",
			total_size/delta / 1024.0, 8e-6*total_size/delta);

	fprintf (fp, "\n");

	fprintf (fp, "Errors: total %u client-timo %u socket-timo %u "
			"connrefused %u connreset %u\n"
			"Errors: fd-unavail %u addrunavail %u ftab-full %u "
			"addrinuse %u other %u\n",
			(basic.num_client_timeouts + basic.num_sock_timeouts
			 + basic.num_sock_fdunavail + basic.num_sock_ftabfull
			 + basic.num_sock_refused + basic.num_sock_reset
			 + basic.num_sock_addrunavail + basic.num_sock_addrinuse 
			 + basic.num_other_errors),
			basic.num_client_timeouts, basic.num_sock_timeouts,
			basic.num_sock_refused, basic.num_sock_reset,
			basic.num_sock_fdunavail, basic.num_sock_addrunavail,
			basic.num_sock_ftabfull, basic.num_sock_addrinuse,
			basic.num_other_errors);

	fprintf(fp, "Unconnected timeouts %d connected timeouts %d total (%d) client_timeouts %d\n",
			basic.num_client_unconnected_timeouts, 
			basic.num_client_connected_timeouts, 
			basic.num_client_unconnected_timeouts + basic.num_client_connected_timeouts, 
			basic.num_client_timeouts);

	print_errno_info();
	if( param.spec_stats > 0 ) {
		print_spec_timeouts();
	}

#ifdef UW_CALL_STATS
	if( param.call_stats > 0){
		print_call_stats();
	}
#endif /* UW_CALL_STATS */
	fprintf(fp, "\n");
	fclose(fp);
}

Stat_Collector stats_basic =
{
	"Basic statistics",
	init,
	no_op,
	no_op,
	dump
};

	void
print_errno_info()
{
	int i;
	int count;

	count = 0;
	for (i=0; i<MAX_ERRNO; i++) {
		count += errno_errs_reported[i];
	}

	if (count > 0) {
		printf("Tracked errnos\n");
		printf("%6s %6s %s\n", "errno", "count", "Error string"); 
		for (i=0; i<MAX_ERRNO; i++) {
			/* printf("i = %d count = %d\n", i, errno_errs_reported[i]); */
			if (errno_errs_reported[i]) {
				printf("%6d %6d %80s\n", i, errno_errs_reported[i], strerror(i));
			}
		}
	}

	count = 0;
	for (i=0; i<(CONN_ERR_COUNT); i++) {
		count += httperf_errs_reported[i];
	}

	if (count > 0) {
		printf("\nTracked internal httperf errors\n");
		printf ("%6s %6s %s\n", "Error", "count", "Message"); 
		for (i=0; i<(CONN_ERR_COUNT); i++) {
			/* printf("i = %d count = %d\n", i, httperf_errs_reported[i]); */
			if (httperf_errs_reported[i]) {
				printf("%6d %6d ", -i, httperf_errs_reported[i]);
				switch(-i) {
					case CONN_ERR_NOT_SET:
						printf("connection failed but error value wasn't set properly\n");
						break;

					case CONN_ERR_NO_MORE_PORTS:
						printf("connection failed because no ports were available\n");
						break;

					case CONN_ERR_HASH_LOOKUP_FAILED:
						printf("connection failed because hash_lookup failed\n");
						break;

					case CONN_ERR_STATE_PAST_CLOSING:
						printf("connection failed because of incorrect state\n");
						break;

					default:
						printf("no message for this type of error\n");
						break;
				}
			}
		}
	}
}

#ifdef LOCAL_DEBUG
	void
print_connected_info(char *func, int sd, int state)
{
	ldbg("%15s: sd = %5d state = %2d num_connected_conns = %8d "
			"max_connected_conns %8d",
			func, sd, state, 
			basic.num_connected_conns, 
			basic.max_connected_conns);

	if (state >= S_CONNECTED && sd > 0) {
		ldbg(" (was connected)\n");
	} else {
		ldbg(" (was not connected)\n");
	}
}
#endif
