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

#include "config.h"

#include <assert.h>
#include <ctype.h>
#include <errno.h>
#include <fcntl.h>
#include <netdb.h>
#include <signal.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>

#ifdef HAVE_EPOLL
#include <sys/epoll.h>
#endif

#include <sys/ioctl.h>
#include <sys/socket.h>
#include <sys/time.h>
#include <sys/types.h>
#include <sys/resource.h>	/* grrr, must come after sys/types.h for BSD */

#include <netinet/in.h>
#include <netinet/tcp.h>
#include <arpa/inet.h>

#include <httperf.h>
#include <call.h>
#include <core.h>
#include <event.h>
#include <http.h>
#include <conn.h>

#define HASH_TABLE_SIZE	1024	/* can't have more than this many servers */
#define MIN_IP_PORT	IPPORT_RESERVED
#define MAX_IP_PORT	65535
#define BITSPERLONG	(8*sizeof (u_long))

static int running = 1;
static int iteration;
static u_long max_burst_len;
static big_fd_set rdfds, wrfds;
static int min_sd = 0x7fffffff, max_sd = 0, alloced_sd_to_conn = 0;
static struct timeval select_timeout;

#ifdef HAVE_EPOLL
static int epoll_fd;
static struct epoll_event *epoll_events;
static int epoll_max_events;
static int epoll_timeout;
#endif /* HAVE_EPOLL */

static struct sockaddr_in myaddr;
Conn **sd_to_conn;
static u_long port_free_map[((MAX_IP_PORT - MIN_IP_PORT + BITSPERLONG)
    / BITSPERLONG)];
  static char http10req[] =
  " HTTP/1.0\r\nUser-Agent: httperf/"VERSION"\r\nHost: ";
  static char http11req[] =
  " HTTP/1.1\r\nUser-Agent: httperf/"VERSION"\r\nHost: ";

  static char http10req_nohost[] =
  " HTTP/1.0\r\nUser-Agent: httperf/"VERSION"\r\n";
  static char http11req_nohost[] =
  " HTTP/1.1\r\nUser-Agent: httperf/"VERSION"\r\n";

#ifndef SOL_TCP
# define SOL_TCP 6	/* probably ought to do getprotlbyname () */
#endif

#ifdef TIME_CORE_LOOP
  static float timer_tick_time, select_time, work_time;
  static Time core_loop_timer_start;
#endif

#ifdef TIME_CORE_LOOP
# define TIME_BEGIN() core_loop_timer_start = timer_now_forced()
# define TIME_END(var) var += (timer_now_forced() - core_loop_timer_start)
#else
# define TIME_BEGIN()
# define TIME_END(var)
#endif

#ifdef TIME_SYSCALLS
# define SYSCALL(n,s)							\
{									\
  Time start, stop;							\
  do									\
  {									\
    errno = 0;							\
    start = timer_now_forced ();					\
    s;				 /* execute the syscall */	\
    stop = timer_now_forced ();					\
    syscall_time[SC_##n] += stop - start;				\
    ++syscall_count[SC_##n];					\
  }									\
  while (errno == EINTR);						\
}

  enum Syscalls
{
  SC_BIND, SC_CONNECT, SC_READ, SC_SELECT, SC_SOCKET, SC_WRITEV,
  SC_SSL_READ, SC_SSL_WRITEV,
  SC_EPOLL_CREATE, SC_EPOLL_CTL, SC_EPOLL_WAIT,
  SC_NUM_SYSCALLS
};

static const char * const syscall_name[SC_NUM_SYSCALLS] =
{
  "bind", "connct", "read", "select", "socket", "writev",
  "ssl_read", "ssl_writev",
  "epoll_create", "epoll_ctl", "epoll_wait"
};
static Time syscall_time[SC_NUM_SYSCALLS];
static u_int syscall_count[SC_NUM_SYSCALLS];
#else
# define SYSCALL(n,s)				\
{						\
  do						\
  {						\
    errno = 0;				\
    s;					\
  }						\
  while (errno == EINTR);			\
}
#endif

struct hash_entry
{
  const char *hostname;
  int port;
  struct sockaddr_in sin;
}
hash_table[HASH_TABLE_SIZE];

void process_spec_timeout(Conn * c);

void BIG_FD_CLR(int fd, big_fd_set *fdsetp) 
{
  unsigned long _tmp = fd / __BIG_NFDBITS;
  unsigned long _rem = fd % __BIG_NFDBITS;
  fdsetp->fds_bits[_tmp] &= ~(1UL<<_rem);
}

int  BIG_FD_ISSET(int fd, big_fd_set *fdsetp)
{
  unsigned long _tmp = fd / __BIG_NFDBITS;
  unsigned long _rem = fd % __BIG_NFDBITS;
  return (fdsetp->fds_bits[_tmp] & (1UL<<_rem)) != 0;
}

void BIG_FD_SET(int fd, big_fd_set *fdsetp)
{
  unsigned long _tmp = fd / __BIG_NFDBITS;
  unsigned long _rem = fd % __BIG_NFDBITS;
  fdsetp->fds_bits[_tmp] |= (1UL<<_rem);
}

  static int
hash_code (const char *server, size_t server_len, int port)
{
  u_char *cp = (u_char *) server;
  u_long h = port;
  u_long g;
  int ch;

  /* Basically the ELF hash algorithm: */

  while ((ch = *cp++) != '\0')
  {
    h = (h << 4) + ch;
    if ((g = (h & 0xf0000000)) != 0)
    {
      h ^= g >> 24;
      h &= ~g;
    }
  }
  return h;
}

  static struct hash_entry*
hash_enter (const char *server, size_t server_len, int port,
    struct sockaddr_in *sin)
{
  struct hash_entry *he;

  int index = hash_code (server, server_len, port) % HASH_TABLE_SIZE;
  printf("hash_enter: %s %d\n", server, port);
  while (hash_table[index].hostname)
  {
    ++index;
    if (index >= HASH_TABLE_SIZE)
      index = 0;
  }
  he = hash_table + index;
  he->hostname = server;
  he->port = port;
  he->sin = *sin;
  return he;
}

  struct sockaddr_in*
hash_lookup (const char *server, size_t server_len, int port)
{
  int index, start_index;

  index = start_index = hash_code (server, server_len, port) % HASH_TABLE_SIZE;
  while (hash_table[index].hostname)
  {
    if (hash_table[index].port == port
        && strcmp (hash_table[index].hostname, server) == 0)
      return &hash_table[index].sin;

    ++index;
    if (index >= HASH_TABLE_SIZE)
      index = 0;
    if (index == start_index)
      break;
  }
  return 0;
}

  static int
lffs (long w)
{
  int r;

  if (sizeof (w) == sizeof (int))
    r = ffs (w);
  else
  {
    r = ffs (w);
#if SIZEOF_LONG > 4
    if (r == 0)
    {
      r = ffs (w >> (8*sizeof (int)));
      if (r > 0)
        r += 8*sizeof (int);
    }
#endif
  }
  return r;
}

  static void
port_put (int port)
{
  int i, bit;

  port -= MIN_IP_PORT;
  i   = port / BITSPERLONG;
  bit = port % BITSPERLONG;
  port_free_map[i] |= (1UL << bit);
}

  static int
port_get (void)
{
  static u_long mask = ~0UL;
  static int previous = 0;
  int port, bit, i;

  i = previous;
  if ((port_free_map[i] & mask) == 0)
  {
    do
    {
      ++i;
      if (i >= NELEMS (port_free_map))
        i = 0;
      if (i == previous)
      {
        if (DBG > 0)
          fprintf (stderr,
              "%s.port_get: Yikes! I'm out of port numbers!\n",
              prog_name);
        return -1;
      }
    }
    while (port_free_map[i] == 0);
    mask = ~0UL;
  }
  previous = i;

  bit = lffs (port_free_map[i] & mask) - 1;
  if (bit >= BITSPERLONG - 1)
    mask = 0;
  else
    mask = ~((1UL << (bit + 1)) - 1);
  // commented by aansaarii
  // if the port is utilized by another program, it will be utilized by httperf
  // and it will never have a chance to be freed, httperf will eventually run out of ports
  //port_free_map[i] &= ~(1UL << bit);
  port = bit + i*BITSPERLONG + MIN_IP_PORT;
  return port;
}

  static void
conn_failure (Conn *s, int err)
{
  Any_Type arg;

  arg.l = err;
  event_signal (EV_CONN_FAILED, (Object *) s, arg);

  core_close (s);
}

  static void
conn_timeout (Timer *t, Any_Type arg)
{
  Conn *s = arg.vp;
  Time now;
  Call *c;
#ifdef UW_CALL_STATS
  u_long conn_id;
#endif /* UW_CALL_STATS */
  assert (object_is_conn (s));
  s->watchdog = 0;
#ifdef UW_CALL_STATS
  conn_id = s->id;
#endif /* UW_CALL_STATS */

  if (DBG > 0)
  {
    c = 0;
    if (s->sd >= 0)
    {
      now = timer_now ();
      if (BIG_FD_ISSET (s->sd, &rdfds)
          && s->recvq && now >= s->recvq->timeout)
        c = s->recvq;
      else if (BIG_FD_ISSET (s->sd, &wrfds)
          && s->sendq && now >= s->sendq->timeout)
        c = s->sendq;
    }
    if (DBG > 0)
    {
      fprintf (stderr, "connection_timeout");
      if (c)
        fprintf (stderr, ".%lu", c->id);
      fprintf (stderr, ": t=%p, connection=%p\n", t, s);
    }
  }

  arg.l = 0;
  event_signal (EV_CONN_TIMEOUT, (Object *) s, arg);
#ifdef UW_CALL_STATS
  if (s->id == conn_id) {
    core_close (s);
  }
#else
  core_close (s);
#endif /* UW_CALL_STATS */
}

  static void
set_active (Conn *s, big_fd_set *fdset)
{
  int sd = s->sd;

  BIG_FD_SET (sd, fdset);
  if (sd < min_sd)
    min_sd = sd;
  if (sd >= max_sd)
    max_sd = sd;
}

#ifdef HAVE_EPOLL

  static void
update_epoll_event (Conn *s)
{
  struct epoll_event evt;
  int epoll_op, rv;
  int sd = s->sd;

  evt.events = 0;

  if (BIG_FD_ISSET(sd, &rdfds))
    evt.events |= EPOLLIN;
  if (BIG_FD_ISSET(sd, &wrfds))
    evt.events |= EPOLLOUT;

  evt.data.fd = sd;

  if (s->added_to_epoll)
  {
    epoll_op = EPOLL_CTL_MOD;
  }
  else
  {
    epoll_op = EPOLL_CTL_ADD;
    s->added_to_epoll = 1;
  }

  SYSCALL (EPOLL_CTL,
      rv = epoll_ctl(epoll_fd, epoll_op, sd, &evt));
  if (rv != 0)
  {
    fprintf (stderr, "%s: epoll_ctl failed on sd %d: %s\n",
        prog_name, sd, strerror(errno));
    exit (1);
  }
}

#endif /* HAVE_EPOLL */

  static void
schedule_timeouts (Conn *s)
{
  Any_Type arg;
  Time timeout = 0.0;

  if (s->watchdog)
    return;

  if (s->sendq) 
    timeout = s->sendq->timeout;

  if (s->recvq && (timeout == 0.0 || timeout > s->recvq->timeout))
    timeout = s->recvq->timeout;

  if (timeout > 0.0)
  {
    arg.vp = s;
    s->watchdog = timer_schedule (conn_timeout, arg,
        timeout - timer_now ());
  }
}

  static void
interested_in_reading (Conn *s)
{
  set_active (s, &rdfds);
#ifdef HAVE_EPOLL
  if (param.use_epoll)
    update_epoll_event (s); 
#endif
  schedule_timeouts (s);
}

  static void
interested_in_reading_no_timeout (Conn *s)
{
  set_active (s, &rdfds);
#ifdef HAVE_EPOLL
  if (param.use_epoll)
    update_epoll_event (s); 
#endif
}

  static void
interested_in_writing (Conn *s)
{
  set_active(s, &wrfds);
#ifdef HAVE_EPOLL
  if (param.use_epoll)
    update_epoll_event (s); 
#endif
  schedule_timeouts (s);
}

  static void
do_send (Conn *conn)
{
  int async_errno;
  socklen_t len;
  struct iovec *iovp;
  int sd = conn->sd;
  ssize_t nsent = 0;
  Any_Type arg;
  Call *call;

  while (1)
  {
    call = conn->sendq;
    assert (call);

    arg.l = 0;
    event_signal (EV_CALL_SEND_RAW_DATA, (Object *) call, arg);

#ifdef HAVE_SSL
    if (conn->ssl)
    {
      extern ssize_t SSL_writev (SSL *, const struct iovec *, int);
      SYSCALL (SSL_WRITEV,
          nsent = SSL_writev(conn->ssl,
            call->req.iov + call->req.iov_index,
            (NELEMS (call->req.iov)
             - call->req.iov_index)));
    }
    else
#endif
    {
      SYSCALL (WRITEV,
          nsent = writev (sd, call->req.iov + call->req.iov_index,
            (NELEMS (call->req.iov)
             - call->req.iov_index)));
    }

    if (DBG > 0)
      fprintf (stderr, "do_send.%lu: wrote %ld bytes on %p\n", call->id,
          (long) nsent, conn);

    if (nsent < 0)
    {
      if (errno == EAGAIN)
        return;

      len = sizeof (async_errno);
      if (getsockopt (sd, SOL_SOCKET, SO_ERROR, &async_errno, &len) == 0
          && async_errno != 0)
        errno = async_errno;

      if (DBG > 0)
        fprintf (stderr, "%s.do_send: writev() failed: %s\n",
            prog_name, strerror (errno));

      conn_failure (conn, errno);
      return;
    }

    call->req.size += nsent;

    iovp = call->req.iov + call->req.iov_index;
    while (iovp < call->req.iov + NELEMS (call->req.iov))
    {
      if (nsent < iovp->iov_len)
      {
        iovp->iov_len -= nsent;
        iovp->iov_base = (caddr_t) ((char *) iovp->iov_base + nsent);
        break;
      }
      else
      {
        /* we're done with this fragment: */
        nsent -= iovp->iov_len;
        *iovp = call->req.iov_saved;
        ++iovp;
        call->req.iov_saved = *iovp;
      }
    }
    call->req.iov_index = iovp - call->req.iov;
    if (call->req.iov_index < NELEMS (call->req.iov))
    {
#ifdef UW_DYNOUT
      if (call->timelimit != 0)
      {
        call->timeout = timer_now () + call->timelimit;
      }
      else
      {
        call->timeout = param.timeout ? timer_now () + param.timeout : 0.0;
      }
#else
      /* there are more header bytes to write */
      call->timeout = param.timeout ? timer_now () + param.timeout : 0.0;
#endif /* UW_DYNOUT */
      interested_in_writing (conn);
      return;
    }

    /* we're done with sending this request */
    conn->sendq = call->sendq_next;
    if (!conn->sendq)
    {
      conn->sendq_tail = 0;
      BIG_FD_CLR (sd, &wrfds);
    }
    arg.l = 0;
    event_signal (EV_CALL_SEND_STOP, (Object *) call, arg);
    if (conn->state >= S_CLOSING)
    {
      call_dec_ref (call);
      return;
    }

    /* get ready to receive matching reply (note that we implicitly
       pass on the reference to the call from the sendq to the
       recvq): */
    call->recvq_next = 0;
    if (!conn->recvq)
      conn->recvq = conn->recvq_tail = call;
    else
    {
      conn->recvq_tail->recvq_next = call;
      conn->recvq_tail = call;
    }

#ifdef UW_DYNOUT
    if (call->timelimit != 0)
    {
      call->timeout = call->timelimit + param.think_timeout;
    }
    else
    {
      call->timeout = param.timeout + param.think_timeout;
    }

#else
    call->timeout = param.timeout + param.think_timeout;
#endif /* UW_DYNOUT */
    if (call->timeout > 0.0)
      call->timeout += timer_now ();
    interested_in_reading (conn);
    if (conn->state < S_REPLY_STATUS)
      conn->state = S_REPLY_STATUS;	/* expecting reply status */

    if (!conn->sendq)
      return;

    arg.l = 0;
    event_signal (EV_CALL_SEND_START, (Object *) conn->sendq, arg);
    if (conn->state >= S_CLOSING)
      return;
  }
}

  static void
recv_done (Call *call)
{
  Conn *conn = call->conn;
  Any_Type arg;

  conn->recvq = call->recvq_next;
  if (!conn->recvq)
  {
    BIG_FD_CLR (conn->sd, &rdfds);
    conn->recvq_tail = 0;
  }
  /* we're done with receiving this request */
  arg.l = 0;
  event_signal (EV_CALL_RECV_STOP, (Object *) call, arg);

  call_dec_ref (call);
}

char buf[1048576];
  static void
do_recv (Conn *s)
{
  char *cp;
  Call *c = s->recvq;
  int i, saved_errno;
  ssize_t nread = 0;
  size_t buf_len;

  assert (c);

#ifdef HAVE_SSL
  if (s->ssl)
  {
    SYSCALL (SSL_READ,
        nread = SSL_read (s->ssl, buf, sizeof (buf) - 1));
  }
  else
#endif
  {
    SYSCALL (READ,
        nread = read (s->sd, buf, sizeof (buf) - 1));
  }
  saved_errno = errno;
  if (nread <= 0)
  {
    if (DBG > 0)
    {
      fprintf (stderr, "do_recv.%lu: received %lu reply bytes on %p\n",
          c->id,
          (u_long) (c->reply.header_bytes + c->reply.content_bytes),
          s);
      if (nread < 0)
        fprintf (stderr, "%s.do_recv: read() failed: %s\n",
            prog_name, strerror (saved_errno));
    }
    if (nread < 0)
    {
      if (saved_errno != EAGAIN)
        conn_failure (s, saved_errno);
    }
    else if (s->state != S_REPLY_DATA)
      conn_failure (s, ECONNRESET);
    else
    {
      if (s->state < S_CLOSING)
        s->state = S_REPLY_DONE;
      recv_done (c);
    }
    return;
  }
  buf[nread] = '\0';	/* ensure buffer is '\0' terminated */

  if (DBG > 3)
  {
    /* dump received data in hex & ascii: */

    fprintf (stderr, "do_recv.%lu: received reply data:\n", c->id);
    for (cp = buf; cp < buf + nread; )
    {
      fprintf (stderr, "  %04x:",
          (int) (c->reply.header_bytes + c->reply.content_bytes
            + (cp - buf)));
      for (i = 0; i < 16 && i < buf + nread - cp; ++i)
        fprintf (stderr, " %02x", cp[i] & 0xff);
      i *= 3;
      while (i++ < 50)
        fputc (' ', stderr);
      for (i = 0; i < 16 && cp < buf + nread; ++i, ++cp)
        fprintf (stderr, "%c", isprint (*cp) ? *cp : '.');
      fprintf (stderr, "\n");
    }
  }

  /* process the replies in this buffer: */

  buf_len = nread;
  cp = buf;
  do
  {
    c = s->recvq;
    assert (c);
    http_process_reply_bytes (c, &cp, &buf_len);

    if (s->content_length == 0) {
      if (DBG > 0) {
        fprintf(stderr, "c = %p cp = %p buf_len = %d\n", c, cp, (int) buf_len);
        fprintf(stderr, "do_recv: content length = 0");
      }

      /* NOTE: httperf doesn't look at the content length
       * when determining how much body to read so a zero byte
       * file followed by a footer causes problems.
       * But then it also looks like httperf doesn't handle
       * Content-length + a footer properly for non zero byte files.
       */
      if (buf_len == 0) {
        s->state = S_REPLY_DONE;
        if (DBG > 0) {
          fprintf(stderr, "do_recv: setting state to S_REPLY_DONE\n");
        }
      }
    }

    if (s->state == S_REPLY_DONE)
    {
      recv_done (c);
      if (s->state >= S_CLOSING)
        return;

      s->state = S_REPLY_STATUS;
    }
  }
  while (buf_len > 0);

  if (s->recvq)
    interested_in_reading_no_timeout (c->conn);

}

  struct sockaddr_in*
core_addr_intern (const char *server, size_t server_len, int port)
{
  struct sockaddr_in sin;
  struct hash_entry *h;
  struct hostent *he;
  Any_Type arg;

  memset (&sin, 0, sizeof (sin));
  sin.sin_family = AF_INET;
  sin.sin_port = htons (port);

  arg.cvp = server;
  event_signal (EV_HOSTNAME_LOOKUP_START, 0, arg);
  he = gethostbyname (server);
  event_signal (EV_HOSTNAME_LOOKUP_STOP, 0, arg);
  if (he)
  {
    if (he->h_addrtype != AF_INET
        || he->h_length != sizeof (sin.sin_addr))
    {
      fprintf (stderr, "%s: can't deal with addr family %d or size %d\n",
          prog_name, he->h_addrtype, he->h_length);
      exit (1);
    }
    memcpy (&sin.sin_addr, he->h_addr_list[0], sizeof (sin.sin_addr));
  }
  else
  {
    if (!inet_aton (server, &sin.sin_addr))
    {
      fprintf (stderr, "%s.core_addr_intern: invalid server address %s\n",
          prog_name, server);
      exit (1);
    }
  }
  h = hash_enter (server, server_len, port, &sin);
  if (!h)
    return 0;
  return &h->sin;
}

#ifdef HAVE_EPOLL

  static void
init_epoll (void)
{
  int epoll_events_size;

  /* How many epoll events should we get at once? I'll do
     one for now. FIXME: Figure out how many to use, or
     add a command-line parameter, or something. */
  epoll_max_events = 2048;

  /* Allocate epoll events. */
  epoll_events_size = epoll_max_events * sizeof(struct epoll_event);
  epoll_events = (struct epoll_event *) malloc(epoll_events_size);

  if (!epoll_events) 
  {
    fprintf (stderr, "%s: failed to allocate space for epoll events: %s\n",
        prog_name, strerror (errno));
    exit (1);
  }

  /* Create epoll file descriptor. */
  SYSCALL (EPOLL_WAIT,
      epoll_fd = epoll_create(epoll_max_events));

  if (epoll_fd < 0)
  {
    fprintf (stderr, "%s: failed to allocate space for epoll events: %s\n",
        prog_name, strerror (errno));
    exit (1);
  }

  /* Determine epoll timeout (the same way the select() timeout
     is set in core_init() below). */
#ifdef DONT_POLL
  epoll_timeout = (int) (TIMER_INTERVAL * 1e3);
#else
  epoll_timeout = 0;
#endif
}

#endif /* HAVE_EPOLL */

  void
core_init (void)
{
  struct rlimit rlimit;
  int port = param.port;
  int i;

  memset (&hash_table, 0, sizeof (hash_table));
  memset (&rdfds, 0, sizeof (rdfds));
  memset (&wrfds, 0, sizeof (wrfds));
  memset (&myaddr, 0, sizeof (myaddr));
  memset (&port_free_map, 0xff, sizeof (port_free_map));

  /* Don't disturb just because a TCP connection closed on us... */
  signal (SIGPIPE, SIG_IGN);

#ifdef DONT_POLL
  /* This causes select() to take several milliseconds on both
     Linux/x86 and HP-UX 10.20.  */
  select_timeout.tv_sec  = (u_long) TIMER_INTERVAL;
  select_timeout.tv_usec = (u_long) (TIMER_INTERVAL * 1e6);
#else
  /* This causes httperf to become a CPU hog as it polls for
     filedescriptors to become readable/writable.  This is OK as long
     as httperf is the only (interesting) user-level process that
     executes on a machine.  */
  select_timeout.tv_sec  = 0;
  select_timeout.tv_usec = 0;
#endif

  /* initialize epoll stuff */
#ifdef HAVE_EPOLL
  if (param.use_epoll)
    init_epoll();
#endif

  /* boost open file limit to the max: */
  /*
     if (getrlimit (RLIMIT_NOFILE, &rlimit) < 0)
     {
     fprintf (stderr, "%s: failed to get number of open file limit: %s",
     prog_name, strerror (errno));
     exit (1);
     }

     if (rlimit.rlim_max > BIG_FD_SETSIZE)
     {
     fprintf (stderr, "%s: warning: open file limit = %ld > BIG_FD_SETSIZE\n"
     "  limiting max. # of open files to BIG_FD_SETSIZE = %ld\n", 
     prog_name, (long int) rlimit.rlim_max, (long int) BIG_FD_SETSIZE);
     rlimit.rlim_max = BIG_FD_SETSIZE;
     }


     rlimit.rlim_cur = rlimit.rlim_max;
     if (setrlimit (RLIMIT_NOFILE, &rlimit) < 0)
     {
     fprintf (stderr, "%s: failed to increase number of open file limit: %s",
     prog_name, strerror (errno));
     exit (1);
     }

*/

  fprintf (stderr, "%s: maximum number of open descriptors = %lld\n",
      prog_name, (long long) rlimit.rlim_max);

  if (param.server)
  {
    if (param.lb_ports.num_ports > 0)
    {
      for(i=0; i<param.lb_ports.num_ports; i++)
      {
        port = param.lb_ports.port[i];

        if (DBG > 2)
          fprintf (stderr, "Adding server: %s port: %d\n", 
              param.server, port);
        core_addr_intern (param.server, strlen (param.server), port);
      }
    }
    else
    {
      core_addr_intern (param.server, strlen (param.server), port);
    }
  }
}

#ifdef HAVE_SSL

  void
core_ssl_connect (Conn *s)
{
  Any_Type arg;
  int ssl_err;

  if (DBG > 2)
    fprintf (stderr, "core_ssl_connect(conn=%p)\n", (void *) s);

  if (SSL_set_fd (s->ssl, s->sd) == 0)
  {
    ERR_print_errors_fp (stderr);
    exit (-1);
  }

  ssl_err = SSL_connect (s->ssl);
  if (ssl_err < 0)
  {
    int reason = SSL_get_error(s->ssl, ssl_err);

    if (reason == SSL_ERROR_WANT_READ || reason == SSL_ERROR_WANT_WRITE)
    {
      if (DBG > 2)
        fprintf (stderr, "core_ssl_connect: want to %s more...\n",
            (reason == SSL_ERROR_WANT_READ) ? "read" : "write");
      if (reason == SSL_ERROR_WANT_READ && !BIG_FD_ISSET (s->sd, &rdfds))
      {
        BIG_FD_CLR (s->sd, &wrfds);
        interested_in_reading (s);
      }
      else if (reason == SSL_ERROR_WANT_WRITE && !BIG_FD_ISSET (s->sd, &wrfds))
      {
        BIG_FD_CLR (s->sd, &rdfds);
        interested_in_writing (s);
      }
      s->state = S_CONNECTING;
      return;
    }
    fprintf (stderr,
        "%s: failed to connect to SSL server (err=%d, reason=%d)\n",
        prog_name, ssl_err, reason);
    ERR_print_errors_fp (stderr);
    exit (-1);
  }

  s->state = S_CONNECTED;

  if (DBG > 0)
    fprintf (stderr, "core_ssl_connect: SSL is connected!\n");

  if (DBG > 1)
  {
    SSL_CIPHER *ssl_cipher;

    ssl_cipher = SSL_get_current_cipher (s->ssl);
    if (!ssl_cipher)
      fprintf (stderr, "core_ssl_connect: server refused all client cipher "
          "suites!\n");
    // else
    // 	fprintf (stderr, "core_ssl_connect: cipher=%s, valid=%d, id=%lu\n",
    // 			ssl_cipher->name, ssl_cipher->valid, ssl_cipher->id);
  }

  arg.l = 0;
  event_signal (EV_CONN_CONNECTED, (Object *) s, arg);
}

#endif /* HAVE_SSL */

  int
core_connect (Conn *s)
{
  int sd, result, async_errno;
  socklen_t len;
  int saved_err = CONN_ERR_NOT_SET;
  struct sockaddr_in *sin;
  struct linger linger;
  int myport, optval;
  Any_Type arg;
  static int prev_iteration = -1;
  static u_long burst_len;

  if (iteration == prev_iteration)
    ++burst_len;
  else
  {
    if (burst_len > max_burst_len)
      max_burst_len = burst_len;
    burst_len = 1;
    prev_iteration = iteration;
  }

  SYSCALL (SOCKET,
      sd = socket (AF_INET, SOCK_STREAM, 0));
  saved_err = errno;
  if (sd < 0)
  {
    if (DBG > 0)
      fprintf (stderr, "%s.core_connect.socket: %s (max_sd=%d)\n",
          prog_name, strerror (errno), max_sd);
    goto failure;
  }

  if (fcntl (sd, F_SETFL, O_NONBLOCK) < 0)
  {
    saved_err = errno;
    fprintf (stderr, "%s.core_connect.fcntl: %s\n",
        prog_name, strerror (errno));
    goto failure;
  }

  if (param.close_with_reset)
  {
    linger.l_onoff = 1;
    linger.l_linger = 0;
    if (setsockopt (sd, SOL_SOCKET, SO_LINGER, &linger, sizeof (linger)) < 0)
    {
      saved_err = errno;
      fprintf (stderr, "%s.core_connect.setsockopt(SO_LINGER): %s\n",
          prog_name, strerror (errno));
      goto failure;
    }
  }

  /* Disable Nagle algorithm so we don't delay needlessly when
     pipelining requests.  */
  optval = 1;
  if (setsockopt (sd, SOL_TCP, TCP_NODELAY, &optval, sizeof (optval)) < 0)
  {
    saved_err = errno;
    fprintf (stderr, "%s.core_connect.setsockopt(SO_SNDBUF): %s\n",
        prog_name, strerror (errno));
    goto failure;
  }

  optval = param.send_buffer_size;
  if (setsockopt (sd, SOL_SOCKET, SO_SNDBUF, &optval, sizeof (optval)) < 0)
  {
    saved_err = errno;
    fprintf (stderr, "%s.core_connect.setsockopt(SO_SNDBUF): %s\n",
        prog_name, strerror (errno));
    goto failure;
  }

  optval = param.recv_buffer_size;
  if (setsockopt (sd, SOL_SOCKET, SO_RCVBUF, &optval, sizeof (optval)) < 0)
  {
    saved_err = errno;
    fprintf (stderr, "%s.core_connect.setsockopt(SO_SNDBUF): %s\n",
        prog_name, strerror (errno));
    goto failure;
  }

  s->sd = sd;
  if (sd >= alloced_sd_to_conn)
  {
    size_t size, old_size;

    old_size = alloced_sd_to_conn * sizeof (sd_to_conn[0]);
    alloced_sd_to_conn += 2048;
    size = alloced_sd_to_conn * sizeof (sd_to_conn[0]);
    if (sd_to_conn) {
      sd_to_conn = realloc (sd_to_conn, size);
      saved_err = errno;
    }
    else {
      sd_to_conn = malloc (size);
      saved_err = errno;
    }
    if (!sd_to_conn)
    {
      if (DBG > 0)
        fprintf (stderr, "%s.core_connect.realloc: %s\n",
            prog_name, strerror (errno));
      goto failure;
    }
    memset ((char *) sd_to_conn + old_size, 0, size - old_size);
  }
  assert (!sd_to_conn[sd]);
  sd_to_conn[sd] = s;

  sin = hash_lookup (s->hostname, s->hostname_len, s->port);
  if (!sin)
  {
    saved_err = CONN_ERR_HASH_LOOKUP_FAILED;
    if (DBG > 0)
      fprintf (stderr, "%s.core_connect: unknown server/port %s:%d\n",
          prog_name, s->hostname, s->port);
    goto failure;
  }

  arg.l = 0;
  event_signal (EV_CONN_CONNECTING, (Object *) s, arg);
  if (s->state >= S_CLOSING) {
    saved_err = CONN_ERR_STATE_PAST_CLOSING;
    goto failure;
  }

  if (param.hog)
  {
    while (1)
    {
      myport = port_get ();
      if (myport < 0) {
        saved_err = CONN_ERR_NO_MORE_PORTS;
        goto failure;
      }
      if (s->local_ip && s->local_ip[0] != '\0') {
        inet_pton(AF_INET, s->local_ip, &(myaddr.sin_addr));
      }

      myaddr.sin_family = AF_INET;
      myaddr.sin_port = htons (myport);
      SYSCALL (BIND,
          result = bind (sd, (struct sockaddr *) &myaddr, sizeof (myaddr)));
      saved_err = errno;
      if (result == 0){
        // edited by aansaarii
        // set the port as allocated if it is binded to this httperf process
        int port = myport - MIN_IP_PORT;
        int i   = port / BITSPERLONG;
        int bit = port % BITSPERLONG;
        port_free_map[i] &= ~(1UL << bit);
        // end edit
        break;
      }

      if (errno != EADDRINUSE && errno == EADDRNOTAVAIL)
      {
        if (DBG > 0)
          fprintf (stderr, "%s.core_connect.bind: %s\n",
              prog_name, strerror (errno));
        goto failure;
      }
    }
    s->myport = myport;
  }

  SYSCALL (CONNECT,
      result = connect (sd, (struct sockaddr *) sin, sizeof (*sin)));
  saved_err = errno;
  if (result == 0)
  {
#ifdef HAVE_SSL
    if (s->ssl)
      core_ssl_connect (s);
    else
#endif
    {
      s->state = S_CONNECTED;
      arg.l = 0;
      event_signal (EV_CONN_CONNECTED, (Object *) s, arg);
    }
  }
  else if (errno == EINPROGRESS)
  {
    /* The socket becomes writable only after the connection has
       been established.  Hence we wait for writability to
       detect connection establishment.  */
    s->state = S_CONNECTING;
    interested_in_writing (s);
    if (param.timeout > 0.0)
    {
      arg.vp = s;
      assert (!s->watchdog);
      s->watchdog = timer_schedule (conn_timeout, arg, param.timeout);
    }
  }
  else
  {
    len = sizeof (async_errno);
    if (getsockopt (sd, SOL_SOCKET, SO_ERROR, &async_errno, &len) == 0
        && async_errno != 0)
      errno = async_errno;
    saved_err = async_errno;

    if (DBG > 0)
      fprintf (stderr, "%s.core_connect.connect: %s (max_sd=%d)\n",
          prog_name, strerror (errno), max_sd);
    goto failure;
  }
  return 0;

failure:
  conn_failure (s, saved_err);
  return -1;
}

  int
core_send (Conn *conn, Call *call)
{
  Any_Type arg;

  arg.l = 0;
  event_signal (EV_CALL_ISSUE, (Object *) call, arg);

  call->conn = conn;	/* NO refcounting here (see call.h).  */

#ifdef UW_CALL_STATS
  if (param.call_stats > 0)
  {
    sprintf (call->id_hdr, "Client-Id: %d %d\r\n", param.client.id, (int) call->id);
    call_append_request_header (call, call->id_hdr, strlen(call->id_hdr));
  }
#endif /* UW_CALL_STATS */

  if (param.no_host_hdr)
  {
    call->req.iov[IE_HOST].iov_base = (caddr_t) "";
    call->req.iov[IE_HOST].iov_len = 0;
  }
  else if (!call->req.iov[IE_HOST].iov_base)
  {
    /* Default call's hostname to connection's hostname: */
    call->req.iov[IE_HOST].iov_base = (caddr_t) conn->hostname;
    call->req.iov[IE_HOST].iov_len = conn->hostname_len;
  }

  /* NOTE: the protocol version indicates what the _client_ can
     understand.  If we send HTTP/1.1, it doesn't mean that the server
     has to speak HTTP/1.1.  In other words, sending an HTTP/1.1
     header leaves it up to the server whether it wants to reply with
     a 1.0 or 1.1 reply.  */
  switch (call->req.version)
  {
    case 0x10000:
      if (param.no_host_hdr)
      {
        call->req.iov[IE_PROTL].iov_base = (caddr_t) http10req_nohost;
        call->req.iov[IE_PROTL].iov_len = sizeof (http10req_nohost) - 1;
      }
      else
      {
        call->req.iov[IE_PROTL].iov_base = (caddr_t) http10req;
        call->req.iov[IE_PROTL].iov_len = sizeof (http10req) - 1;
      }
      break;

    case 0x10001:
      if (param.no_host_hdr)
      {
        call->req.iov[IE_PROTL].iov_base = http11req_nohost;
        call->req.iov[IE_PROTL].iov_len = sizeof (http11req_nohost) - 1;
      }
      else
      {
        call->req.iov[IE_PROTL].iov_base = http11req;
        call->req.iov[IE_PROTL].iov_len = sizeof (http11req) - 1;
      }
      break;

    default:
      fprintf (stderr, "%s: unexpected version code %x\n",
          prog_name, call->req.version);
      exit (1);
  }
  call->req.iov_index = 0;
  call->req.iov_saved = call->req.iov[0];

  /* insert call into connection's send queue: */
  call_inc_ref (call);
  call->sendq_next = 0;
  if (!conn->sendq)
  {
    conn->sendq = conn->sendq_tail = call;
    arg.l = 0;
    event_signal (EV_CALL_SEND_START, (Object *) call, arg);
    if (conn->state >= S_CLOSING)
      return -1;

#ifdef UW_DYNOUT
    if (call->timelimit != 0)
    {
      call->timeout = timer_now () + call->timelimit;
    }
    else
    {
      call->timeout = param.timeout ? timer_now () + param.timeout : 0.0;
    }
#else
    call->timeout = param.timeout ? timer_now () + param.timeout : 0.0;
#endif /* UW_DYNOUT */
    interested_in_writing (conn);
  }
  else
  {
    conn->sendq_tail->sendq_next = call;
    conn->sendq_tail = call;
  }
  return 0;
}

  void
core_close (Conn *conn)
{
  Call *call, *call_next;
  Any_Type arg;
  int sd;
  int prev_state = conn->state;
  struct linger linger;

  struct epoll_event evt;
  int epoll_op, rv;

  if (conn->state >= S_CLOSING)
    return;			/* guard against recursive calls */
  conn->state = S_CLOSING;

  if (DBG >= 10)
    fprintf (stderr, "%s.core_close(conn=%p)\n", prog_name, conn);

  if (conn->watchdog)
  {
    timer_cancel (conn->watchdog);
    conn->watchdog = 0;
  }

  /* first, get rid of all pending calls: */
  for (call = conn->sendq; call; call = call_next)
  {
    call_next = call->sendq_next;
    call_dec_ref (call);
  }
  conn->sendq = 0;

  for (call = conn->recvq; call; call = call_next)
  {
    call_next = call->recvq_next;
    call_dec_ref (call);
  }
  conn->recvq = 0;

#ifdef OLDWAY
  arg.l = 0;
#else
  arg.i = prev_state;
#endif
  event_signal (EV_CONN_CLOSE, (Object *) conn, arg);
  assert (conn->state == S_CLOSING);

  sd = conn->sd;
  conn->sd = -1;

  if (sd >=0 && conn->timed_out) 
  {
    /* if we get a timeout sent a reset on a close
     * if --close-with-reset is used this has already been taken care of
     */
    if (param.timeout_with_reset && !param.close_with_reset)
    {
      linger.l_onoff = 1;
      linger.l_linger = 0;
      if (DBG > 0) {
        printf("conn timed out setting up for reset\n");
      }
      if (setsockopt (sd, SOL_SOCKET, SO_LINGER, &linger, sizeof (linger)) < 0)
      {
        fprintf (stderr, "%s.core_close.setsockopt(SO_LINGER): %s\n",
            prog_name, strerror (errno));
        exit(1);
      }
    }
  }

#ifdef HAVE_SSL
  if (conn->ssl)
    SSL_shutdown (conn->ssl);
#endif

  if (sd >= 0)
  {
    /*
       if (conn->added_to_epoll) {
       evt.events = 0;

       evt.events = EPOLLIN | EPOLLOUT;

       evt.data.fd = sd;
       epoll_op = EPOLL_CTL_DEL ;

       SYSCALL (EPOLL_CTL,
       rv = epoll_ctl(epoll_fd, epoll_op, sd, &evt));
       if (rv != 0)
       {
       fprintf (stderr, "%s: epoll_ctl failed on sd %d: %s\n",
       prog_name, sd, strerror(errno));
       exit (1);
       }
       }
       */
    close (sd);
    sd_to_conn[sd] = 0;
    BIG_FD_CLR (sd, &wrfds);
    BIG_FD_CLR (sd, &rdfds);

  }
  if (conn->myport > 0)
    port_put (conn->myport);

  /* A connection that has been closed is not useful anymore, so we
     give up the reference obtained when creating the session.  This
     normally initiates destruction of the connection.  */
  conn_dec_ref (conn);
}

  static void
core_loop_handle_socket (int sd, int is_readable, int is_writable)
{
  Conn *conn;
  int rc, async_errno;
  socklen_t len;
  Any_Type arg;
  /* only handle sockets that haven't timed out yet */
  conn = sd_to_conn[sd];

  if (!conn) {
    //	  fprintf(stderr, "core_loop_handle_socket: Conn became NULL!\n");
    return;
  }
  conn_inc_ref (conn);

  /* Don't cancel this here just because we received some data. Wait for the whole chunk to arrive.
     if (conn->watchdog)
     {
     timer_cancel (conn->watchdog);
     conn->watchdog = 0;
     }
     */

  if (conn->state == S_CONNECTING)
  {
#ifdef HAVE_SSL
    if (conn->ssl)
      core_ssl_connect (conn);
    else
#endif
      if (is_writable)
      {
        len = sizeof (async_errno);
        rc = getsockopt (sd, SOL_SOCKET, SO_ERROR, &async_errno, &len);
        if (rc < 0)
        {
          printf("%s: getsockopt failed\n", __FUNCTION__);
          perror("core_loop_handle_socket ");
          exit(1);
        }
        else if (async_errno == 0)
        {
          BIG_FD_CLR (sd, &wrfds);
          conn->state = S_CONNECTED;
          arg.l = 0;

          event_signal (EV_CONN_CONNECTED, (Object *) conn, arg);
        }
        else
        {
          // printf("%s: connect failed %s\n", __FUNCTION__, strerror(async_errno));
          conn_failure(conn, async_errno);
        }
      }
  }
  else
  {
    if (is_writable && conn->sendq)
      do_send (conn);
    if (is_readable && conn->recvq)
      do_recv (conn);
  }

  conn_dec_ref (conn);
}

#ifdef HAVE_EPOLL

  static void
core_loop_epoll (void)
{
  int is_readable, is_writable, n, sd, i = 0;

  while (running)
  {
    TIME_BEGIN();
    timer_tick ();
    TIME_END(timer_tick_time);

    TIME_BEGIN();

    SYSCALL (EPOLL_WAIT,
        n = epoll_wait (epoll_fd, epoll_events, epoll_max_events, epoll_timeout));
    if (n < 0)
    {
      fprintf (stderr, "%s.core_loop_epoll: epoll_wait failed: %s\n",
          prog_name, strerror (errno));
      exit (1);
    }
    TIME_END(select_time);

    TIME_BEGIN();

    ++iteration;

    for (i = 0; i < n; i++)
    {
      sd = epoll_events[i].data.fd;

      //	  fprintf(stderr, "fd = %d\n", sd);
      is_readable = is_writable = 0;
      if (epoll_events[i].events & EPOLLIN)
        is_readable = 1;
      if (epoll_events[i].events & EPOLLOUT)
        is_writable = 1;

      if (is_readable || is_writable)
      {
        core_loop_handle_socket (sd, is_readable, is_writable);

        if (n > 0)
          timer_tick ();
      }
    }
    TIME_END(work_time);
  }
}

#endif /* HAVE_EPOLL */

  void
core_loop_select (void)
{
  int is_readable, is_writable, n, sd, bit, min_i, max_i, i = 0;
  big_fd_set readable, writable;
  fd_mask mask;

  while (running)
  {
    struct timeval tv = select_timeout;

    TIME_BEGIN();
    timer_tick ();
    TIME_END(timer_tick_time);

    TIME_BEGIN();

    readable = rdfds;
    writable = wrfds;
    min_i = min_sd / NFDBITS;
    max_i = max_sd / NFDBITS;

    SYSCALL (SELECT,
        n = select (max_sd + 1, &readable, &writable, 0, &tv));

    if (n < 0)
    {
      fprintf (stderr, "%s.core_loop_select: select failed: %s\n",
          prog_name, strerror (errno));
      exit (1);
    }

    TIME_END(select_time);

    TIME_BEGIN();

    ++iteration;

    while (n > 0)
    {
      /* find the index of the fdmask that has something going on: */
      do
      {
        ++i;
        if (i > max_i)
          i = min_i;

        assert (i <= max_i);
        mask = readable.fds_bits[i] | writable.fds_bits[i];
      }
      while (!mask);

      bit = 0;
      sd = i*NFDBITS + bit;
      do
      {
        if (mask & 1)
        {
          --n;

          is_readable =
            (BIG_FD_ISSET (sd, &readable) && BIG_FD_ISSET (sd, &rdfds));
          is_writable =
            (BIG_FD_ISSET (sd, &writable) && BIG_FD_ISSET (sd, &wrfds));

          if (is_readable || is_writable)
          {
            core_loop_handle_socket (sd, is_readable, is_writable);

            if (n > 0)
              timer_tick ();
          }
        }
        mask = ((u_long) mask) >> 1;
        ++sd;
      }
      while (mask);
    }

    TIME_END(work_time);

  } /* while running */
}

  void
core_loop (void)
{
#ifdef HAVE_EPOLL
  if (param.use_epoll) {
    core_loop_epoll();
  } else {
    core_loop_select();
  }
#else
  core_loop_select();
#endif /* HAVE_EPOLL */
}


  void
core_exit (void)
{
  running = 0;

  printf ("Maximum connect burst length: %lu\n", max_burst_len);

#ifdef TIME_SYSCALLS
  {
    u_int count;
    Time time;
    int i;

    printf ("Average syscall execution times:\n");
    for (i = 0; i < NELEMS (syscall_name); ++i)
    {
      count = syscall_count[i];
      time = syscall_time[i];
      printf ("\t%s:\t%.3f ms/call (%.3fs total, %u calls)\n",
          syscall_name[i], count > 0 ? 1e3*time/count : 0, time, count);

    }
    putchar ('\n');
  }
#endif

#ifdef TIME_CORE_LOOP
  {
    float iters = (float) iteration;

    printf ("Average time spent in stages of core loop:\n");
    printf ("\ttimer_tick(): %.3f\n", 1000 * (timer_tick_time / iters));
    printf ("\tselect() or epoll_wait(): %.3f\n", 1000 * (select_time / iters));
    printf ("\twork: %.3f\n\n", 1000 * (work_time / iters));
  }
#endif
}

