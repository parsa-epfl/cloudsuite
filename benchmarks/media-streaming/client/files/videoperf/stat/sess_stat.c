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

/* Session statistics collector.  */

#include <assert.h>
#include <float.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>

#include <httperf.h>
#include <call.h>
#include <event.h>
#include <session.h>
#include <stats.h>

static struct
{
  u_int num_rate_samples;
  u_int num_completed_since_last_sample;
  Time rate_sum;
  Time rate_sum2;
  Time rate_min;
  Time rate_max;

  u_int num_completed;
  Time lifetime_sum;

  u_int num_failed;
  Time failtime_sum;

  u_int num_conns;	/* total # of connections on successful sessions */

  /* session-length histogram: */
  u_int longest_session;
  u_int len_hist_alloced;
  u_int *len_hist;
}
st;

#define SESS_PRIVATE_DATA(c)						\
  ((Sess_Private_Data *) ((char *)(c) + sess_private_data_offset))

typedef struct Sess_Private_Data
{
  u_int num_calls_completed;	/* how many calls completed? */
  u_int num_conns;		/* # of connections on this session */
  Time birth_time;		/* when this session got created */
}
Sess_Private_Data;

static size_t sess_private_data_offset = -1;

  static void
perf_sample (Event_Type et, Object *obj, Any_Type reg_arg, Any_Type call_arg)
{
  Time weight = call_arg.d;
  double rate;

  assert (et == EV_PERF_SAMPLE);

  rate = weight*st.num_completed_since_last_sample;
  st.num_completed_since_last_sample = 0;

  // aansaarii
  if(verbose){
    printf ("session-rate = %-8.1f (sess/s), session-completed: %-8u, session-failed: %-8u, session-total: %-8u\n", rate, st.num_completed, st.num_failed, st.num_completed + st.num_failed);
  }

  ++st.num_rate_samples;
  st.rate_sum += rate;
  st.rate_sum2 += SQUARE (rate);
  if (rate < st.rate_min)
    st.rate_min = rate;
  if (rate > st.rate_max)
    st.rate_max = rate;
}

  static void
sess_created (Event_Type et, Object *obj, Any_Type regarg, Any_Type callarg)
{
  Sess_Private_Data *priv;
  Sess *sess;

  assert (et == EV_SESS_NEW && object_is_sess (obj));
  sess = (Sess *) obj;
  priv = SESS_PRIVATE_DATA (sess);
  priv->birth_time = timer_now ();
}

  static void
sess_destroyed (Event_Type et, Object *obj, Any_Type regarg, Any_Type callarg)
{
  size_t old_size, new_size;
  Sess_Private_Data *priv;
  Sess *sess;
  Time delta, now = timer_now ();

  assert (et == EV_SESS_DESTROYED && object_is_sess (obj));
  sess = (Sess *) obj;
  priv = SESS_PRIVATE_DATA (sess);

  delta = (now - priv->birth_time);
  if (sess->failed)
  {
    ++st.num_failed;
    st.failtime_sum += delta;
  }
  else
  {
    st.num_conns += priv->num_conns;
    ++st.num_completed_since_last_sample;
    ++st.num_completed;
    st.lifetime_sum += delta;
  }

  if (priv->num_calls_completed > st.longest_session)
  {
    st.longest_session = priv->num_calls_completed;

    if (st.longest_session >= st.len_hist_alloced)
    {
      old_size = st.len_hist_alloced*sizeof (st.len_hist[0]);
      st.len_hist_alloced = st.longest_session + 16;
      new_size = st.len_hist_alloced*sizeof (st.len_hist[0]);

      st.len_hist = realloc (st.len_hist, new_size);
      if (!st.len_hist)
      {
        fprintf (stderr, "%s.sess_stat: Out of memory\n", prog_name);
        exit (1);
      }
      memset ((char *) st.len_hist + old_size, 0, new_size - old_size);
    }
  }
  ++st.len_hist[priv->num_calls_completed];
}

  static void
conn_connected (Event_Type et, Object *obj, Any_Type regarg, Any_Type callarg)
{
  Sess_Private_Data *priv;
  Sess *sess;
  Conn *conn;

  assert (et == EV_CONN_CONNECTED && object_is_conn (obj));
  conn = (Conn *) obj;
  sess = session_get_sess_from_conn (conn);
  priv = SESS_PRIVATE_DATA (sess);
  ++priv->num_conns;
}

  static void
call_done (Event_Type et, Object *obj, Any_Type regarg, Any_Type callarg)
{
  Sess_Private_Data *priv;
  Sess *sess;
  Call *call;

  assert (et == EV_CALL_RECV_STOP && object_is_call (obj));
  call = (Call *) obj;
  sess = session_get_sess_from_call (call);
  priv = SESS_PRIVATE_DATA (sess);
  ++priv->num_calls_completed;
}

  static void
dump_stats (Event_Type et, Object *obj, Any_Type regarg, Any_Type callarg)
{
  if(!verbose){ return; }
  double min, avg, stddev, delta;
  int i;

  delta = timer_now() - test_time_start;

  avg = 0;
  stddev = 0;
  if (delta > 0)
    avg = st.num_completed / delta;
  if (st.num_rate_samples > 1)
    stddev = STDDEV (st.rate_sum, st.rate_sum2, st.num_rate_samples);

  if (st.num_rate_samples > 0)
    min = st.rate_min;
  else
    min = 0.0;
  printf ("\nSession rate [sess/s]: min %.2f avg %.2f max %.2f "
      "stddev %.2f (%u/%u)\n", min, avg, st.rate_max, stddev,
      st.num_completed, st.num_completed + st.num_failed);

  printf ("Session: avg %.2f connections/session\n",
      st.num_completed > 0 ? st.num_conns/(double) st.num_completed : 0.0);

  avg = 0.0;
  if (st.num_completed > 0)
    avg = st.lifetime_sum/st.num_completed;
  printf ("Session lifetime [s]: %.1f\n", avg);

  avg = 0.0;
  if (st.num_failed > 0)
    avg = st.failtime_sum/st.num_failed;
  printf ("Session failtime [s]: %.1f\n", avg);

  printf ("Session length histogram:");
  for (i = 0; i <= st.longest_session; ++i)
    printf (" %u", st.len_hist[i]);
  putchar ('\n');
  printf ("----------\n");
}

  static void
init (void)
{
  Any_Type arg;
  size_t size;

  sess_private_data_offset = object_expand (OBJ_SESS,
      sizeof (Sess_Private_Data));
  st.len_hist_alloced = 16;
  size = st.len_hist_alloced*sizeof (st.len_hist[0]);
  st.len_hist = malloc (size);
  memset (st.len_hist, 0, size);

  st.rate_min = DBL_MAX;

  if (!st.len_hist)
  {
    fprintf (stderr, "%s.sess_stat: Out of memory\n", prog_name);
    exit (1);
  }
  arg.l = 0;
  event_register_handler (EV_PERF_SAMPLE, perf_sample, arg);
  event_register_handler (EV_SESS_NEW, sess_created, arg);
  event_register_handler (EV_SESS_DESTROYED, sess_destroyed, arg);

  event_register_handler (EV_CONN_CONNECTED, conn_connected, arg);

  event_register_handler (EV_CALL_RECV_STOP, call_done, arg);

  event_register_handler (EV_DUMP_STATS, dump_stats, arg);
}

  static void
dump (void)
{
  double min, avg, stddev, delta;
  int i;

  FILE *fp = NULL;
  if (NULL == param.output_log) {
    fp = fopen("output.log", "a");	
  } else {
    fp = fopen(param.output_log, "a");
  }

  delta = test_time_stop - test_time_start;

  avg = 0;
  stddev = 0;
  if (delta > 0)
    avg = st.num_completed / delta;
  if (st.num_rate_samples > 1)
    stddev = STDDEV (st.rate_sum, st.rate_sum2, st.num_rate_samples);

  if (st.num_rate_samples > 0)
    min = st.rate_min;
  else
    min = 0.0;
  fprintf (fp, "\nSession rate [sess/s]: min %.2f avg %.2f max %.2f "
      "stddev %.2f (%u/%u)\n", min, avg, st.rate_max, stddev,
      st.num_completed, st.num_completed + st.num_failed);

  fprintf (fp, "Session: avg %.2f connections/session\n",
      st.num_completed > 0 ? st.num_conns/(double) st.num_completed : 0.0);

  avg = 0.0;
  if (st.num_completed > 0)
    avg = st.lifetime_sum/st.num_completed;
  fprintf (fp, "Session lifetime [s]: %.1f\n", avg);

  avg = 0.0;
  if (st.num_failed > 0)
    avg = st.failtime_sum/st.num_failed;
  fprintf (fp, "Session failtime [s]: %.1f\n", avg);

  fprintf (fp, "Session length histogram:");
  for (i = 0; i <= st.longest_session; ++i)
    fprintf (fp, " %u", st.len_hist[i]);
  fprintf(fp, "\n");
  putchar ('\n');
}

Stat_Collector session_stat =
{
  "collects session-related statistics",
  init,
  no_op,
  no_op,
  dump
};
