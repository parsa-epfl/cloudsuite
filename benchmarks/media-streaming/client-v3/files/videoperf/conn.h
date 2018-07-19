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
#ifndef conn_h
#define conn_h

#include "config.h"

#include <sys/uio.h>

#include <httperf.h>
#include <object.h>
#include <timer.h>

#ifdef HAVE_SSL
# include <openssl/ssl.h>
# include <openssl/err.h>
#endif

/* Maximum header line length that we can process properly.  Longer
   lines will be treated as if they were only this long (i.e., they
   will be truncated).  */
#define MAX_HDR_LINE_LEN	1024

struct Call;

typedef enum Conn_State
{
	S_INITIAL,
	S_CONNECTING,
	S_CONNECTED,
	S_REPLY_STATUS,
	S_REPLY_HEADER,
	S_REPLY_CONTINUE,
	S_REPLY_DATA,
	S_REPLY_CHUNKED,
	S_REPLY_FOOTER,
	S_REPLY_DONE,
	S_CLOSING,
	S_FREE
}
Conn_State;

typedef struct Conn
{
	Object obj;

	Conn_State state;
	struct Conn *next;
	struct Call *sendq;		/* calls whose request needs to be sent */
	struct Call *sendq_tail;
	struct Call *recvq;		/* calls waiting for a reply */
	struct Call *recvq_tail;
	Timer *watchdog;

	struct
	{
		Time time_connect_start;	/* time connect() got called */
#ifdef UW_CALL_STATS
		Time time_to_connect;	/* time to connect */
		Time time_of_timeout; /* time connection timeout occurs (if it occurs) */
#endif /* UW_CALL_STATS */
		u_int num_calls_completed;	/* # of calls that completed */
		u_int num_calls;                /* # of calls that should be completed */
#ifdef UW_STABLE_STATS
		u_int is_stable;	/* true when not ramping up or ramping down */
#endif /* UW_STABLE_STATS */
	}
	basic;			/* maintained by stat/stats_basic.c */

	size_t hostname_len;
	const char *hostname;	/* server's hostname (or 0 for default) */
	size_t fqdname_len;
	const char *fqdname;	/* fully qualified server name (or 0) */
	int log_index;		/* The index of the log that is the  */
	char local_ip[16];		/* The local-ip to bind this connection to. */
	int port;			/* server's port (or -1 for default) */
	int	sd;			/* socket descriptor */
#ifdef HAVE_EPOLL
	int added_to_epoll;		/* has the sd been added to epoll event set? */
#endif
	int myport;			/* local port number or -1 */
	/* Since replies are read off the socket sequentially, much of the
	   reply-processing related state can be kept here instead of in
	   the reply structure: */
	struct iovec line;		/* buffer used to parse reply headers */
	size_t content_length;	/* content length (or INF if unknown) */
	u_int has_body : 1;		/* does reply have a body? */
	u_int is_chunked : 1;	/* is the reply chunked? */
	int  timed_out;		/* did this connection time out?*/
	char line_buf[MAX_HDR_LINE_LEN];	/* default line buffer */

#ifdef HAVE_SSL
	SSL *ssl;			/* SSL connection info */
#endif

	u_long id;
}
Conn;

extern int max_num_conn;
extern Conn *conn;

/* Initialize the new connection object C.  */
extern void conn_init (Conn *c);

/* Destroy the connection-specific state in connection object C.  */
extern void conn_deinit (Conn *c);

#define conn_new()	((Conn *) object_new (OBJ_CONN))
#define conn_inc_ref(c)	object_inc_ref ((Object *) (c))
#define conn_dec_ref(c)	object_dec_ref ((Object *) (c))

#endif /* conn_h */
