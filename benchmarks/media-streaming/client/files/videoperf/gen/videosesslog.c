
/*
   httperf -- a tool for measuring web server performance
   Copyright (C) 2000  Hewlett-Packard Company
   Contributed by Richard Carter <carter@hpl.hp.com>

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

/* Creates sessions at the fixed rate PARAM.RATE.  The session descriptions
   are read in from a configuration file.

   There is currently no tool that translates from standard log
   formats to the format accepted by this module.

   An example input file follows:

#
# This file specifies the potentially-bursty uri sequence for a number of
# user sessions.  The format rules of this file are as follows:
#
# Comment lines start with a '#' as the first character.  # anywhere else
# is considered part of the uri.
#
# Lines with only whitespace delimit session definitions (multiple blank
# lines do not generate "null" sessions).
#
# Lines otherwise specify a uri-sequence (1 uri per line).  If the
# first character of the line is whitespace (e.g. space or tab), the
# uri is considered to be part of a burst that is sent out after the
# previous non-burst uri.
#

# session 1 definition (this is a comment)

/foo.html
/pict1.gif
/pict2.gif
/foo2.html
/pict3.gif
/pict4.gif

#session 2 definition

/foo3.html
/foo4.html
/pict5.gif

Any comment on this module contact carter@hpl.hp.com.  */

#include <assert.h>
#include <ctype.h>
#include <errno.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>

#include <httperf.h>
#include <conn.h>
#include <core.h>
#include <event.h>
#include <rate.h>
#include <session.h>
#include <timer.h>

#ifdef UW_PACE_REQUESTS
/* declare function defined in stat/call_stats.c */
double get_call_total_conn_time( Call *c );
#endif /* UW_PACE_REQUESTS */


/* Changes involved in adding headers to individual requests will be surrouned
 * by #ifdef WSESSLOG_HEADERS ... #endif
 */
#define WSESSLOG_HEADERS

/* Maximum number of sessions that can be defined in the configuration
   file.  */
#define MAX_SESSION_TEMPLATES	250000

#ifndef TRUE
#define TRUE  (1)
#endif
#ifndef FALSE
#define FALSE (0)
#endif

#define SESS_PRIVATE_DATA(c)						\
  ((Sess_Private_Data *) ((char *)(c) + sess_private_data_offset))

typedef struct req REQ;
struct req
{
  REQ *next;
  int method;
  char *uri;
  int uri_len;
  char *cookie;
  int cookie_len;
  char *contents;
  int contents_len;

#ifdef WSESSLOG_HEADERS
  char extra_hdrs[16384]; /* plenty for "Content-length: 1234567890" + custom headers */
#else
  char extra_hdrs[50];	/* plenty for "Content-length: 1234567890" */
#endif /* WSESSLOG_HEADERS */

  int extra_hdrs_len;

#ifdef UW_DYNOUT
  int timelimit;
#endif /* UW_DYNOUT */

  /* These fields are used for call stats only */
  int file_size;
};

typedef struct burst BURST;
struct burst
{
  BURST *next;
  int num_reqs;
  Time user_think_time;
#ifdef UW_PACE_REQUESTS
  int pace_requests;
#endif /* UW_PACE_REQUESTS */
  REQ *req_list;
};

typedef struct Sess_Private_Data Sess_Private_Data;
struct Sess_Private_Data
{
  u_int num_calls_in_this_burst; /* # of calls created for this burst */
  u_int num_calls_target;	/* total # of calls desired */
  u_int num_calls_destroyed;	/* # of calls destroyed so far */
  Timer *timer;		/* timer for session think time */

  int total_num_reqs;		/* total number of requests in this session */
  int http_version;		/* HTTP version number */

  BURST *current_burst;	/* the current burst we're working on */
  REQ *current_req;		/* the current request we're working on */

  int port;		/* specifies port used for this session, 0 => use default */
  const char *server; /* specifies server used for this session, NULL => use default */ 
#ifdef HAVE_SSL
  int use_ssl; /* 1 = use ssl, 2 = don't use ssl, other = default*/
#endif /* HAVE_SSL */

  /* these fields are used for call stats only */
  REQ *cur_missed_req;
  BURST *cur_missed_bur;
  REQ *prv_missed_req;
  char local_ip[16];
};

typedef struct Session_Log_Desc Session_Log_Desc;

struct Session_Log_Desc
{
  Sess_Private_Data session_templates[MAX_SESSION_TEMPLATES]; 
  int num_templates;
  int next_session_template;
};

static Session_Log_Desc* session_logs;


/* Methods allowed for a request: */
enum
{
  HM_DELETE, HM_GET, HM_HEAD, HM_OPTIONS, HM_POST, HM_PUT, HM_TRACE,
  HM_LEN
};

static const char *call_method_name[] =
{
  "DELETE", "GET", "HEAD", "OPTIONS", "POST", "PUT", "TRACE"
};

double* request_mix_cdf = NULL;

static size_t sess_private_data_offset;
static int num_sessions_generated;
static int num_sessions_destroyed;
static Rate_Generator rg_sess;

  static void
sess_destroyed (Event_Type et, Object *obj, Any_Type regarg, Any_Type callarg)
{
  Sess_Private_Data *priv;
  Sess *sess;

  assert (et == EV_SESS_DESTROYED && object_is_sess (obj));
  sess = (Sess *) obj;

  priv = SESS_PRIVATE_DATA (sess);
  if (priv->timer)
  {
    timer_cancel (priv->timer);
    priv->timer = 0;
  }

  if (++num_sessions_destroyed >= param.num_sessions)
    core_exit ();
}

  static void
issue_calls (Sess *sess, Sess_Private_Data *priv)
{
  int i, to_create, retval, n;
  const char *method_str;
  Call *call;
  REQ *req;

  /* Mimic browser behavior of fetching html object, then a couple of
     embedded objects: */

  to_create = 1;

  if (priv->num_calls_in_this_burst > 0)
  {
    to_create = priv->current_burst->num_reqs - priv->num_calls_in_this_burst;
  }

  n = session_max_qlen (sess) - session_current_qlen (sess);
  if (n < to_create)
  {
    to_create = n;
  }

  priv->num_calls_in_this_burst += to_create; 

  for (i = 0; i < to_create; ++i)
  {
    call = call_new ();
    if (!call)
    {
      sess_failure (sess);
      return;
    }

    /* fill in the new call: */
    req = priv->current_req;
    if (req == NULL)
    {
      panic ("%s: internal error, requests ran past end of burst\n",prog_name);
    }

    call_set_version (call, priv->http_version);
    method_str = call_method_name[req->method];
    call_set_method (call, method_str, strlen (method_str));
    call_set_uri (call, req->uri, req->uri_len);

#ifdef UW_DYNOUT
    call->timelimit = req->timelimit;
#endif /* UW_DYNOUT */

    /* used for call stats */
    call->file_size = req->file_size;

    if (req->cookie_len > 0)
    {
      /* add "Cookie:" header if necessary: */
      call_append_request_header (call, req->cookie, req->cookie_len);
    }
#ifdef WSESSLOG_HEADERS
    if (req->contents_len > 0 || req->extra_hdrs_len > 0)
    {
      /* add "Content-length:" header and contents, if necessary: */
      call_append_request_header (call, req->extra_hdrs,
          req->extra_hdrs_len);
      if (req->contents_len > 0)
      {
        call_set_contents(call, req->contents, req->contents_len);
      }
    }
#else
    if (req->extra_hdrs_len > 0)
    {
      /* add "Content-length:" header and contents, if necessary: */
      call_append_request_header (call, req->extra_hdrs,
          req->extra_hdrs_len);
      call_set_contents (call, req->contents, req->contents_len);
    }
#endif /* WSESSLOG_HEADERS */

#ifdef UW_CALL_STATS
    if (param.client.id >= 0)
    {
      sprintf (call->id_hdr, "Client-Id: %d %d\r\n", param.client.id, (int) call->id);

      call_append_request_header (call, call->id_hdr, strlen(call->id_hdr));
    }
#endif /* UW_CALL_STATS */

    priv->current_req = req->next;

    if (DBG > 0)
    {
      fprintf (stderr, "%s: accessing URI `%s'\n", prog_name, req->uri);
    }

    retval = session_issue_call (sess, call);
    call_dec_ref (call);

    if (retval < 0)
    {
      return;
    }
  }
}

  static void
user_think_time_expired (Timer *t, Any_Type arg)
{
  Sess *sess = arg.vp;
  Sess_Private_Data *priv;

  assert (object_is_sess (sess));

  priv = SESS_PRIVATE_DATA (sess);
  priv->timer = 0;
  issue_calls (sess, priv);
}

/*
 * Build a CDF for the Request-mix percentage for each video resolution.
 * The CDF will be used later to generate the requests by the probability.
 */
  static void
build_request_mix_cdf(void)
{
  int i = 0;
  request_mix_cdf = malloc(param.videosesslog.num_logs * sizeof(double));
  request_mix_cdf[0] = param.videosesslog.sess_perc[0];
  for (i = 1; i < param.videosesslog.num_logs; i++) {
    request_mix_cdf[i] = request_mix_cdf[i-1] + param.videosesslog.sess_perc[i];
  }
}

/* Create a new session and fill in our private information.  */
  static int
sess_create (Any_Type arg)
{
  Session_Log_Desc *sess_log_desc = NULL;
  Sess_Private_Data *priv, *template;
  Sess *sess;
  double random_val = 0.0;
  int i = 0;
  int session_log_index = -1;

  if (num_sessions_generated++ >= param.num_sessions) {
    //core_exit();
    return -1;
  }

  sess = sess_new ();

  // Choose a log depending on the probability
  random_val = (double) rand() / (double) RAND_MAX;	
  for (i=0; i< param.videosesslog.num_logs; i++) {
    if (random_val <= request_mix_cdf[i]) {
      sess_log_desc = (session_logs+i);
      session_log_index = i;
      break;
    }
  }

  //fprintf(stderr, "random_val = %lf, i = %d\n", random_val, i);
  // Figure out the index in the templates array we are in
  if (++(sess_log_desc->next_session_template) >= sess_log_desc->num_templates) {
    sess_log_desc->next_session_template = 0;
  }

  template = &sess_log_desc->session_templates[sess_log_desc->next_session_template]; 
  sess->num_requests = template->total_num_reqs;
  priv = SESS_PRIVATE_DATA (sess);
  priv->current_burst = template->current_burst;
  priv->current_req = priv->current_burst->req_list;
  priv->total_num_reqs = template->total_num_reqs;
  priv->num_calls_target = priv->current_burst->num_reqs;
  priv->http_version = template->http_version;
  sess->port = template->port;
  sess->server = template->server;
  strcpy(priv->local_ip, template->local_ip);
  strcpy(sess->local_ip, template->local_ip);
  sess->log_index = session_log_index; 
#ifdef HAVE_SSL
  sess->use_ssl = template->use_ssl;
#endif /* HAVE_SSL */

  if (DBG > 0)
    fprintf (stderr, "Starting session, first burst_len = %d\n",
        priv->num_calls_target);

  issue_calls (sess, SESS_PRIVATE_DATA (sess));
  return 0;
}

#ifdef UW_PACE_REQUESTS
  static void
prepare_for_next_burst (Sess *sess, Sess_Private_Data *priv, Time conn_time)
#else
  static void
prepare_for_next_burst (Sess *sess, Sess_Private_Data *priv )
#endif /* UW_PACE_REQUESTS */
{
  Time think_time;
  Any_Type arg;

  if (priv->current_burst != NULL)
  {
#ifdef UW_PACE_REQUESTS
    /* printf( "UW_PACE_REQUESTS: conn_time = %.2f, think_time = %.2f\n",
       conn_time, priv->current_burst->user_think_time ); */
    if (priv->current_burst->pace_requests && priv->current_burst->user_think_time > conn_time) {
      think_time = priv->current_burst->user_think_time - conn_time;
      /* printf( "UW_PACE_REQUESTS: wait_time = %.2f\n", think_time ); */
    }
    else
#endif /* UW_PACE_REQUESTS */
      think_time = priv->current_burst->user_think_time;

    /* advance to next burst: */
    priv->current_burst = priv->current_burst->next;

    if (priv->current_burst != NULL)
    {
      priv->current_req = priv->current_burst->req_list;
      priv->num_calls_in_this_burst = 0;
      priv->num_calls_target += priv->current_burst->num_reqs;

      assert (!priv->timer);
      arg.vp = sess;
      priv->timer = timer_schedule (user_think_time_expired,
          arg, think_time);
    }
  }
}

  static void
call_destroyed (Event_Type et, Object *obj, Any_Type regarg, Any_Type callarg)
{
  Sess_Private_Data *priv;
  Sess *sess;
  Call *call;

  assert (et == EV_CALL_DESTROYED && object_is_call (obj));
  call = (Call *) obj;
  sess = session_get_sess_from_call (call);
  priv = SESS_PRIVATE_DATA (sess);

  if (sess->failed)
    return;

  ++priv->num_calls_destroyed;

  if (priv->num_calls_destroyed >= priv->total_num_reqs)
  {
    /* we're done with this session */
    sess_dec_ref (sess);
  }
  else if (priv->num_calls_in_this_burst < priv->current_burst->num_reqs)
  {
    issue_calls (sess, priv);
  }
  else if (priv->num_calls_destroyed >= priv->num_calls_target)
  {
#ifdef UW_PACE_REQUESTS
#ifdef UW_CALL_STATS
    prepare_for_next_burst (sess, priv, get_call_total_conn_time( call ) );
#else
    prepare_for_next_burst (sess, priv, 0.0 );
#endif /* UW_CALL_STATS */
#else
    prepare_for_next_burst (sess, priv );
#endif /* UW_PACE_REQUESTS */
  }
}

/* Allocates memory for a REQ and assigns values to data members.
   This is used during configuration file parsing only.  */
  static REQ*
new_request (char *uristr)
{
  REQ *retptr;

  retptr = (REQ *) malloc (sizeof (*retptr));
  if (retptr == NULL || uristr == NULL)
    panic ("%s: ran out of memory while parsing %s\n",
        prog_name, param.videosesslog.file);  

  memset (retptr, 0, sizeof (*retptr));
  retptr->uri = uristr;
  retptr->uri_len = strlen (uristr);
  retptr->method = HM_GET;
  retptr->file_size = -2;
#ifdef UW_DYNOUT
  retptr->timelimit = param.timeout;
#endif /* UW_DYNOUT */
  return retptr;
}

/* Like new_request except this is for burst descriptors.  */
  static BURST*
new_burst (REQ *r)
{
  BURST *retptr;

  retptr = (BURST *) malloc (sizeof (*retptr));
  if (retptr == NULL)
    panic ("%s: ran out of memory while parsing %s\n",
        prog_name, param.videosesslog.file);  
  memset (retptr, 0, sizeof (*retptr));
  retptr->user_think_time = param.videosesslog.think_time;
  retptr->req_list = r;
  return retptr;
}

/*
   static void
   add_hash_entries ()
   {
   struct sockaddr_in *sin;
   Sess_Private_Data *sptr;
   int template_num = 0;
   int port;
   const char *server;

   sptr = &session_templates[0];

   for (; template_num < num_templates; template_num++)
   {
//use session specific values if it is set
//otherwise, us the default 
port = sptr->port ? sptr->port : param.port;
server = sptr->server ? sptr->server : param.server;

//check to make sure this entry does not already exist
sin = hash_lookup (server, strlen (server), port);
if (!sin)
{
core_addr_intern (server, strlen (server), port);
}

//next session template
sptr++;
}

}
*/
/* Read in session-defining configuration file and create in-memory
   data structures from which to assign uri_s to calls. */
  static void
parse_config (void)
{
  FILE *fp;
  int lineno, i;
  //int reqnum;
  Sess_Private_Data *sptr;
  char line[10000];	/* some uri's get pretty long */
  char uri[10000];	/* some uri's get pretty long */
  u_int major, minor;
  int http_version;
  char method_str[1000];
  char this_arg[10000];

#ifdef WSESSLOG_HEADERS
  char headers_str[16384]; /*for capturing extra headers */
  char contents[10000] = {0};
#else
  char contents[10000];
#endif /* WSESSLOG_HEADERS */

#ifdef HAVE_SSL
  char use_ssl[100];
#endif	
  char server[10000];
  int len;
  double think_time;
  int bytes_read;
  REQ *reqptr;
  //BURST *bptr;
  BURST *current_burst = 0;
  char *from, *to, *parsed_so_far;
  int ch;
  int single_quoted, double_quoted, escaped, done;
  int first_req;
#ifdef UW_DYNOUT
  int timelimit = param.timeout;
#endif /* UW_DYNOUT */
  int file_size = -2;
  int port;

  Session_Log_Desc* current_session_log = NULL;
  int log_file_counter = 0;

  // Initialize the session_templates, depending on the number of log files specified
  session_logs = calloc(param.videosesslog.num_logs * sizeof(Session_Log_Desc), 1);	

  // Build the request-mix cdf
  build_request_mix_cdf();
  for (log_file_counter = 0; log_file_counter < param.videosesslog.num_logs; log_file_counter++) {
    current_session_log = (session_logs + log_file_counter);
    fp = fopen (param.videosesslog.file[log_file_counter], "r");
    if (fp == NULL)
    {
      panic ("%s: can't open %s\n", prog_name, param.videosesslog.file);  
    }

    /*
       printf("Session templates use %d bytes of memory\n", sizeof(session_templates));
       */

    current_session_log->num_templates = 0;
    sptr = &(current_session_log->session_templates[0]);

    /* default values if they are not specified */
    sptr->port = 0;
    sptr->server = NULL;
    strcpy(sptr->local_ip, param.videosesslog.local_ip[log_file_counter]);
#ifdef HAVE_SSL
    sptr->use_ssl = 0;
#endif	

    for (lineno = 1; fgets (line, sizeof (line), fp); lineno++)
    {
      if (line[0] == '#')
      {
        continue;		/* skip over comment lines */
      }

      if (sscanf (line,"%s%n", uri, &bytes_read) != 1)
      {
        /* must be a session-delimiting blank line */
        if (sptr->current_req != NULL)
        {
          sptr++;		/* advance to next session */
          /* default values if they are not specified */
          sptr->port = 0;
          sptr->server = NULL;
          strcpy(sptr->local_ip, param.videosesslog.local_ip[log_file_counter]);

#ifdef HAVE_SSL
          sptr->use_ssl = 0;
#endif	
        }
        continue;
      }

      /* session properties*/
      if (strncmp (uri, "session", 7) == 0)
      {
        parsed_so_far = line + bytes_read;

        while (sscanf (parsed_so_far, " %s%n", this_arg, &bytes_read) == 1)
        {
          if (sscanf (this_arg, "port=%d", &port) == 1)
          {
            sptr->port = port;
            //add_port (port);
          }
          else if (sscanf (this_arg, "server=%s", server) == 1)
          {
            sptr->server = strdup (server);
            if (sptr->server == NULL)
            {
              panic ("%s: ran out of memory while parsing %s\n",
                  prog_name, param.videosesslog.file);  
            }
          }
#ifdef HAVE_SSL
          else if (sscanf (this_arg, "ssl=%s", use_ssl) == 1)
          {
            if (strncmp (use_ssl, "on", 3) == 0)
            {
              sptr->use_ssl = 1;
            }
            else if (strncmp (use_ssl, "off", 4) == 0)
            {
              sptr->use_ssl = 2;
            }
            else 
            {
              panic ("ssl can be 'on' or 'off', '%s' is not valid\n", use_ssl);
            }
          }
#endif /* HAVE_SSL */
          parsed_so_far += bytes_read;
        }
        continue;
      }

      /* looks like a request-specifying line */
      reqptr = new_request (strdup (uri));

      if (sptr->current_req == NULL)
      {
        first_req = TRUE;
        current_session_log->num_templates++;
        if (current_session_log->num_templates > MAX_SESSION_TEMPLATES)
        {
          panic ("%s: too many sessions (%d) specified in %s\n",prog_name, current_session_log->num_templates, param.videosesslog.file);  
        }
        sptr->http_version = param.http_version;
        current_burst = sptr->current_burst = new_burst (reqptr);
      }
      else
      {
        first_req = FALSE;
        if (!isspace (line[0]))
        {
          /* this uri starts a new burst */
          current_burst = (current_burst->next = new_burst (reqptr));
        }
        else
        {
          sptr->current_req->next = reqptr;
        }
      }

      /* do some common steps for all new requests */
      current_burst->num_reqs++;
      sptr->total_num_reqs++;
      sptr->current_req = reqptr;

      /* parse rest of line to specify additional parameters of this
         request and burst */
      parsed_so_far = line + bytes_read;

      while (sscanf (parsed_so_far, " %s%n", this_arg, &bytes_read) == 1)
      {
        if (sscanf (this_arg, "method=%s", method_str) == 1)
        {
          for (i = 0; i < HM_LEN; i++)
          {
            if (!strncmp (method_str,call_method_name[i],strlen (call_method_name[i])))
            {
              sptr->current_req->method = i;
              break;
            }
          }
          if (i == HM_LEN)
          {
            panic ("%s: did not recognize method '%s' in %s\n",prog_name, method_str, param.videosesslog.file);  
          }
        }
        else if (sscanf (this_arg, "think=%lf", &think_time) == 1)
        {
          current_burst->user_think_time = think_time;
#ifdef UW_PACE_REQUESTS
          current_burst->pace_requests = 0;
#endif /* UW_PACE_REQUESTS */
        }
#ifdef UW_PACE_REQUESTS
        else if (sscanf (this_arg, "pace_time=%lf", &think_time) == 1)
        {
          current_burst->user_think_time = think_time;
          current_burst->pace_requests = 1;
        }
#endif /* UW_PACE_REQUESTS */
#ifdef UW_DYNOUT
        else if (sscanf (this_arg, "timeout=%d", &timelimit) == 1)
        {
          sptr->current_req->timelimit = timelimit;
        }
#endif /* UW_DYNOUT */
        else if (sscanf (this_arg, "size=%d", &file_size) == 1)
        {
          sptr->current_req->file_size = file_size;
        }
#ifdef WSESSLOG_HEADERS
        else if (sscanf (this_arg, "headers=%s", headers_str) == 1)
        {
          /* this is tricky since headers might be a quoted
             string with embedded spaces or escaped quotes.  We
             should parse this carefully from parsed_so_far */
          from = strchr (parsed_so_far, '=') + 1;
          to = headers_str;
          single_quoted = FALSE;
          double_quoted = FALSE;
          escaped = FALSE;
          done = FALSE;
          while ((ch = *from++) != '\0' && !done)
          {
            if (escaped == TRUE)
            {
              switch (ch)
              {
                case 'n':
                  *to++ = '\n';
                  break;
                case 'r':
                  *to++ = '\r';
                  break;
                case 't':
                  *to++ = '\t';
                  break;
                case '\n':
                  *to++ = '\n';
                  /* this allows an escaped newline to
                     continue the parsing to the next line. */
                  if (fgets(line,sizeof(line),fp) == NULL)
                  {
                    lineno++;
                    panic ("%s: premature EOF seen in '%s'\n",
                        prog_name, param.videosesslog.file);  
                  }
                  parsed_so_far = from = line;
                  break;
                default:
                  *to++ = ch;
                  break;
              }
              escaped = FALSE;
            }
            else if (ch == '"' && double_quoted)
            {
              double_quoted = FALSE;
            }
            else if (ch == '\'' && single_quoted)
            {
              single_quoted = FALSE;
            }
            else
            {
              switch (ch)
              {
                case '\t':
                case '\n':
                case ' ':
                  if (single_quoted == FALSE &&
                      double_quoted == FALSE)
                    done = TRUE;	/* we are done */
                  else
                    *to++ = ch;
                  break;
                case '\\':		/* backslash */
                  escaped = TRUE;
                  break;
                case '"':		/* double quote */
                  if (single_quoted)
                    *to++ = ch;
                  else
                    double_quoted = TRUE;
                  break;
                case '\'':		/* single quote */
                  if (double_quoted)
                    *to++ = ch;
                  else
                    single_quoted = TRUE;
                  break;
                default:
                  *to++ = ch;
                  break;
              }
            }
          }
          *to = '\0';
          from--;		/* back up 'from' to '\0' or white-space */
          bytes_read = from - parsed_so_far;
          int headerslen = strlen(headers_str);
          if(headerslen != 0 && sptr->current_req->contents_len <= 0)
          {
            snprintf (sptr->current_req->extra_hdrs,
                sizeof(sptr->current_req->extra_hdrs),
                "%s\r\n",
                headers_str);
            sptr->current_req->extra_hdrs_len =
              strlen (sptr->current_req->extra_hdrs);
          }
          else if (headerslen != 0 && sptr->current_req->contents_len > 0)
          {
            /* append content length to the end of existing headers */
            snprintf (sptr->current_req->extra_hdrs,
                sizeof(sptr->current_req->extra_hdrs),
                "%s\r\nContent-length: %d\r\n",
                headers_str,
                sptr->current_req->contents_len);
            sptr->current_req->extra_hdrs_len =
              strlen (sptr->current_req->extra_hdrs);
          }

        }
#endif /* WSESSLOG_HEADERS */

        else if (strncmp (this_arg, "cookie=", 7) == 0 ||strncmp (this_arg, "contents=", 9) == 0)
        {
          /*
           * These parameters are tricky, since they can involve a
           * quoted string with whitespace and escaped characters.
           * Therefore, we can't rely on sscanf(), and must carefully
           * parse the line ourselves starting at parsed_so_far.
           * We've bundled 'cookie=' and 'contents=' together simply
           * to avoid code duplication.
           */
          from = strchr (parsed_so_far, '=') + 1;
          to = contents;
          single_quoted = FALSE;
          double_quoted = FALSE;
          escaped = FALSE;
          done = FALSE;
          while ((ch = *from++) != '\0' && !done)
          {
            if (escaped == TRUE)
            {
              switch (ch)
              {
                case 'n':
                  *to++ = '\n';
                  break;
                case 'r':
                  *to++ = '\r';
                  break;
                case 't':
                  *to++ = '\t';
                  break;
                case '\n':
                  *to++ = '\n';
                  /* this allows an escaped newline to
                     continue the parsing to the next line. */
                  if (fgets(line,sizeof(line),fp) == NULL)
                  {
                    lineno++;
                    panic ("%s: premature EOF seen in '%s'\n",
                        prog_name, param.videosesslog.file);  
                  }
                  parsed_so_far = from = line;
                  break;
                default:
                  *to++ = ch;
                  break;
              }
              escaped = FALSE;
            }
            else if (ch == '"' && double_quoted)
            {
              double_quoted = FALSE;
            }
            else if (ch == '\'' && single_quoted)
            {
              single_quoted = FALSE;
            }
            else
            {
              switch (ch)
              {
                case '\t':
                case '\n':
                case ' ':
                  if (single_quoted == FALSE && double_quoted == FALSE)
                  {
                    done = TRUE;	/* we are done */
                  }
                  else
                  {
                    *to++ = ch;
                  }
                  break;
                case '\\':		/* backslash */
                  escaped = TRUE;
                  break;
                case '"':		/* double quote */
                  if (single_quoted)
                  {
                    *to++ = ch;
                  }
                  else
                  {
                    double_quoted = TRUE;
                  }
                  break;
                case '\'':		/* single quote */
                  if (double_quoted)
                  {
                    *to++ = ch;
                  }
                  else
                  {
                    single_quoted = TRUE;
                  }
                  break;
                default:
                  *to++ = ch;
                  break;
              }
            }
          }
          from--;		/* back up 'from' to '\0' or white-space */
          bytes_read = from - parsed_so_far;
          if ((len = to - contents) > 0)
          {
            *to = '\0';
            if (strncmp (this_arg, "cookie=", 7) == 0)
            {
              /*
               * The reason for the len + 10 below is to allow for
               * 'Cookie: ' preceding the cookie data and '\r\n'
               * following it.
               */
              sptr->current_req->cookie_len = len + 10;
              sptr->current_req->cookie = malloc(len + 11);
              sprintf(sptr->current_req->cookie,"Cookie: %s\r\n",contents);
            }
            else  /* strncmp (this_arg, "contents=", 9) == 0 */
            {
              sptr->current_req->contents_len = len;
              sptr->current_req->contents = strdup (contents);
#ifdef WSESSLOG_HEADERS
              sprintf(sptr->current_req->extra_hdrs,
                  "%sContent-length: %d\r\n",
                  strdup(sptr->current_req->extra_hdrs),
                  sptr->current_req->contents_len);
#else
              sprintf (sptr->current_req->extra_hdrs,"Content-length: %d\r\n",sptr->current_req->contents_len);
#endif /* WSESSLOG_HEADERS */

              sptr->current_req->extra_hdrs_len = strlen (sptr->current_req->extra_hdrs);
            }
          }
        }
        else if (sscanf (this_arg, "http=%u.%u", &major, &minor) == 2)
        {
          http_version = (major << 16) | (minor & 0xffff);
          switch (http_version)
          {
            case 0x10000:
            case 0x10001:
              break;
            default:
              panic ("%s: unsupported HTTP version 0x%x in %s\n",prog_name, http_version, param.videosesslog.file);
          }
          if (first_req)
          {
            sptr->http_version = http_version;
          }
          else if (http_version != sptr->http_version)
          {
            panic ("%s: illegal change of HTTP version in %s\n",prog_name, param.videosesslog.file);
          }
        }
        else
        {
          /* do not recognize this arg */
          panic ("%s: did not recognize arg '%s' in %s\n",prog_name, this_arg, param.videosesslog.file);  
        }
        parsed_so_far += bytes_read;
      }
    }
    fclose (fp);
  }

  /* add any server:port combinations to the core hash table that where specified to be used
   * by an individual session in the log file */
  //add_hash_entries();

  /*
     if (param.session_offsets) 
     {
     int each;
     each = num_templates / param.client.num_clients; 
     next_session_template = param.client.id * each;

     printf("client %d/%d starting at session number %d of %d\n", 
     param.client.id, param.client.num_clients, next_session_template,
     num_templates);
     }
     */

  /*
     if (DBG > 3)
     {
     fprintf (stderr,"%s: session list follows:\n\n", prog_name);

     for (i = 0; i < num_templates; i++)
     {
     sptr = &session_templates[i];
     fprintf (stderr, "#session %d (total_reqs=%d):\n",
     i, sptr->total_num_reqs);

     for (bptr = sptr->current_burst; bptr; bptr = bptr->next)
     {
     for (reqptr = bptr->req_list, reqnum = 0;reqptr;reqptr = reqptr->next, reqnum++)
     {
     if (reqnum >= bptr->num_reqs)
     {
     panic ("%s: internal error detected in parsing %s\n",prog_name, param.videosesslog.file);  
     }

     if (reqnum > 0)
     {
     fprintf (stderr, "\t");
     }

     fprintf (stderr, "%s", reqptr->uri);

     if (reqnum == 0 && bptr->user_think_time != param.videosesslog.think_time)
     {
#ifdef UW_PACE_REQUESTS
if (bptr->pace_requests)
fprintf (stderr, " pace=%0.2f",(double) bptr->user_think_time);
else
#endif
fprintf (stderr, " think=%0.2f",(double) bptr->user_think_time);
}

if (reqptr->method != HM_GET)
{
fprintf (stderr," method=%s",call_method_name[reqptr->method]);
}

if (reqptr->contents != NULL)
{
fprintf (stderr, " contents='%s'", reqptr->contents);
}

fprintf (stderr, "\n");
}
}
fprintf (stderr, "\n");
}
}
*/
}

  static void
init (void)
{
  Any_Type arg;

  parse_config ();

  sess_private_data_offset = object_expand (OBJ_SESS,
      sizeof (Sess_Private_Data));
  rg_sess.rate = &param.rate;
  rg_sess.tick = sess_create;
  rg_sess.arg.l = 0;

  arg.l = 0;
  event_register_handler (EV_SESS_DESTROYED, sess_destroyed, arg);
  event_register_handler (EV_CALL_DESTROYED, call_destroyed, arg);

  /* This must come last so the session event handlers are executed
     before this module's handlers.  */
  session_init ();
}

  static void
start (void)
{
  rate_generator_start (&rg_sess, EV_SESS_DESTROYED);
}

Load_Generator videosesslog =
{
  "creates log-based session workload",
  init,
  start,
  no_op
};

  Sess_Private_Data * 
priv_from_conn (Conn *conn)
{
  assert (object_is_conn (conn));

  Sess * s;
  Sess_Private_Data *priv;

  s = session_get_sess_from_conn(conn);

  priv = SESS_PRIVATE_DATA (s);

  return priv;
}

  void
set_missed_calls(Conn *conn)
{
  assert (conn != NULL);

  Sess_Private_Data *priv;

  priv = priv_from_conn (conn);

  priv->cur_missed_req = priv->current_req;

  priv->cur_missed_bur = priv->current_burst; 

  priv->prv_missed_req = priv->current_req;
}

  char *
get_next_missed_uri(Conn *conn)
{
  assert (conn != NULL);

  Sess_Private_Data *priv;
  char * result;
  result = "--";

  if (!conn) {
    fprintf(stderr, "get_next_missed_uri: Conn became NULL!\n");
  }
  priv = priv_from_conn(conn);

  assert (priv->cur_missed_bur != NULL);

  if (priv->cur_missed_req != NULL)
  {
    result = priv->cur_missed_req->uri;
    priv->prv_missed_req = priv->cur_missed_req;
    priv->cur_missed_req = priv->cur_missed_req->next;
  }
  else
  {
    priv->cur_missed_bur = priv->cur_missed_bur->next;
    if (priv->cur_missed_bur != NULL)
    {
      priv->cur_missed_req = priv->cur_missed_bur->req_list;
      result = priv->cur_missed_req->uri;
      priv->prv_missed_req = priv->cur_missed_req;
      priv->cur_missed_req = priv->cur_missed_req->next;
    }
  }

  return result;
}

  int
get_next_missed_size(Conn *conn)
{
  Sess_Private_Data *priv;

  priv = priv_from_conn(conn);

  assert (priv->prv_missed_req != NULL);

  return priv->prv_missed_req->file_size;

}

#ifdef UW_DYNOUT
  int
get_next_missed_timelimit(Conn *conn)
{
  Sess_Private_Data *priv;

  priv = priv_from_conn(conn);

  assert (priv->prv_missed_req != NULL);

  return priv->prv_missed_req->timelimit;

}
#endif /* UW_DYNOUT */

  void
print_remaining_uri (Conn *conn)
{
  Sess_Private_Data *priv;
  REQ *cur_req;
  BURST *cur_bur;

  priv = priv_from_conn(conn);

  cur_req = priv->cur_missed_req;
  cur_bur = priv->cur_missed_bur;

  while (cur_bur != NULL)
  {
    while (cur_req != NULL)
    {
      printf("URI: %s\n", cur_req->uri);

      cur_req = cur_req->next;
    }
    cur_bur = cur_bur->next;
    if (cur_bur != NULL)
    {
      cur_req = cur_bur->req_list;
    }
  }
}

  int 
num_missed_calls (Conn *conn)
{
  Sess_Private_Data *priv;

  priv = priv_from_conn(conn);

  if (conn->state >= S_CONNECTED)
  {
    return priv->total_num_reqs - priv->num_calls_destroyed - 1;
  }
  else
  {
    return priv->total_num_reqs;
  }
}
