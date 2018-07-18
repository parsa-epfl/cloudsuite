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
  on a per call basis.
  Author: Tyler Szepesi
  Date: August, 2010
*/
#ifdef UW_CALL_STATS

#include "call_stats.h"
#include <stdio.h>
#include <stdlib.h>
#include <call.h>
#include <string.h>
#include <assert.h>
#include <videosesslog.h>
#include <session.h>

#define URI_SIZE 80 /* Maximum size allowed for a URI for a given call */

/* --call-stats=buf_size  
 * indicates max number of calls tracked by call_stats */
int buf_size = 0; 

/* struct used to gather per call statistics */
typedef struct {
	char c_name[URI_SIZE]; /* file name */
	u_long c_call_id; /* call id */
	u_long c_conn_id; /* connection id */
	int c_size; /* file size */
	int c_bytes_recvd; /* bytes received */
	double c_perc_recvd; /* percentage of bytes received */
	double c_byte_rate; /* rate of receiving bytes */
	double c_conn_time; /* time to connect */
	double c_resp_time; /* time to receive */
	double c_total_time; /*total time */
	double c_time_lim; /* time limit */
	int c_timeout; /* timed out? */
	int c_status; /* HTTP status */
} call_data;

call_data *call_stats;

/* struct used to format the display of call statistics */
struct print_stats {
	char *hdr;		/* header */
	char *units;	/* units for data */
	char *hdr_frmt;	/* format used for header and units*/
	char *data_frmt;/* format used for data, should be the same width as above */
	char *data_blank; /* used for printing a blank data entry, use with hdr_frmt */
};

struct print_stats print_info[]={
	{"call number", 		"", 				"%-13s", 	"%-13d",		""},
	{"conn number", 		"", 				"%-13s", 	"%-13d", 		""},
	{"request size", 		"(bytes)", 	"%-14s", 	"%-14d",		""},
	{"response size", 	"(bytes)", 	"%-15s", 	"%-15d",		""},
	{"% bytes recvd", 	"", 				"%-15s", 	"%-15.2f",	""},
	{"byte rate", 			"(Mbps)", 	"%-11s", 	"%-11.1f",	""},
	{"conn time", 			"(s)", 			"%-11s", 	"%-11.7f",	""},
	{"response time", 	"(s)", 			"%-15s", 	"%-15.7f",	""},
	{"total time", 			"(s)", 			"%-12s", 	"%-12.7f",	""},
	{"HTTP status", 		"", 				"%-13s", 	"%-13d",		""},
	{"time limit", 			"(s)", 			"%-11s", 	"%-11.7f",	""},
	{"timed out", 			"", 				"%-11s", 	"%-11s",		""},
	{"file requested", 	"", 				"%s", 		"%s",				""},
	{0,0,0,0,0}
};

/* number of call_stats lines printed */
int print_count = 0;

/* struct used to map URIs to file sizes */
struct f_uri_size {
	char f_uri[URI_SIZE];
	int f_size;
};

struct f_uri_size *f_sizes;
struct f_uri_size *prev_f = NULL;
int num_files;

/* this function is used to construct the (URI, file size) mapping */
void build_fsize ()
{
	int size;
	int i;
	char buf[URI_SIZE];
	FILE *fd;
	int er;

	/* open the file */
	fd = fopen (param.call_stats_file, "r");
	if (fd == NULL)
	{
		fprintf(stderr, "unable to open the call_stats file: %s\n", param.call_stats_file);
		exit (1);
	}

	/* get the number of entries */
	er = fscanf (fd, "%d\n", &num_files);
	if (er < 1)
	{
		fprintf (stderr, "first line of call_stats file not a number\n");
		exit(1);
	}

	/* malloc the f_sizes array */
	f_sizes = malloc (sizeof (struct f_uri_size)*num_files);
	if (f_sizes == NULL)
	{
		fprintf(stderr, "malloc failed to allocate f_sizes array\n");
		exit (1);
	}

	/* fill in the f_sizes array */
	for (i = 0; i < num_files; i++)
	{
		/* read line */
		er = fscanf(fd, "%s %d\n", buf, &size);
		if (er < 2)
		{
			fprintf (stderr, "Line %d of call_stats file is not properly formated\n", i+2);
			exit(1);
		}

		/* populate entry in f_sizes array */
		strncpy (f_sizes[i].f_uri, buf, URI_SIZE);
		f_sizes[i].f_size = size;
	}

	/* close the file */
	fclose(fd);
}

/* this function is used to determine the size of a file based on its URI */
int get_f_size (char uri[URI_SIZE])
{
	int i;
	int match;

	if (strncmp (uri, "--", 2) == 0)
	{
		return -1;
	}

 /* Check if this is the same as the previous file looked up */
	if (prev_f != NULL)
	{
		if (strncmp(uri, prev_f->f_uri, URI_SIZE) == 0)
		{
			return prev_f->f_size;
		}
	}

	/* look up file in pre-constructed table */
	for (i = 0; i < num_files; i++)
	{
		match = strncmp (uri, f_sizes[i].f_uri, URI_SIZE);

		if (match == 0)
		{
			prev_f = &f_sizes[i];
			return f_sizes[i].f_size;
		}
	}

	return -2;
}

/* Print stats header info */
void
print_header_line()
{
	int i = 0;

	/* print column headers */
	while (print_info[i].hdr != 0)
	{
		printf(print_info[i].hdr_frmt, print_info[i].hdr);

		i++;
	}

	printf("\n");

	i = 0;

	/* print column units */
	while (print_info[i].hdr != 0)
	{
		printf(print_info[i].hdr_frmt, print_info[i].units);

		i++;
	}

	printf("\n");
}

/* Print the stats for one call */
void
print_call (call_data cd)
{
	/* reprint the header every 20 lines */
	if (print_count == 0)
	{
		print_header_line();
	}
	print_count++;
	if (print_count == 20)
	{
		print_count = 0;
	}

	/* convert timeout id to string */
	char * timeout = "yes-NC";
	int i = 0;

	if (cd.c_timeout == 1)
	{
		timeout = "yes-C";
	}
	else if (cd.c_timeout == 2)
	{
		timeout = "yes-NC";
	}
	else if (cd.c_timeout == -1)
	{
		timeout = "NA";
	}
	else
	{
		timeout = "no";
	}

	/* print out a row of stats 
	 * one row = one call */
	if (cd.c_call_id != -1)
	{
		printf(print_info[i].data_frmt, cd.c_call_id);
	}
	else
	{
		printf(print_info[i].hdr_frmt, print_info[i].data_blank);
	}
	i++;

	if (cd.c_conn_id  != -1)
	{
		printf(print_info[i].data_frmt, cd.c_conn_id);
	}
	else
	{
		printf(print_info[i].hdr_frmt, print_info[i].data_blank);
	}
	i++;

	if (cd.c_size != -1)
	{
		printf(print_info[i].data_frmt, cd.c_size);
	}
	else
	{
		printf(print_info[i].hdr_frmt, print_info[i].data_blank);
	}
	i++;

	if (cd.c_bytes_recvd != -1)
	{
		printf(print_info[i].data_frmt, cd.c_bytes_recvd);
	}
	else
	{
		printf(print_info[i].hdr_frmt, print_info[i].data_blank);
	}
  i++;


	if (cd.c_perc_recvd != -1)
	{
		printf(print_info[i].data_frmt, cd.c_perc_recvd);
	}
	else
	{
		printf(print_info[i].hdr_frmt, print_info[i].data_blank);
	}
  i++;

	if (cd.c_byte_rate != -1)
	{
		printf(print_info[i].data_frmt, cd.c_byte_rate);
	}
	else
	{
		printf(print_info[i].hdr_frmt, print_info[i].data_blank);
	}
	i++;

	if (cd.c_conn_time != -1)
	{
		printf(print_info[i].data_frmt, cd.c_conn_time);
	}
	else
	{
		printf(print_info[i].hdr_frmt, print_info[i].data_blank);
	}
	i++;

	if (cd.c_resp_time != -1)
	{
		printf(print_info[i].data_frmt, cd.c_resp_time);
	}
	else
	{
		printf(print_info[i].hdr_frmt, print_info[i].data_blank);
	}
	i++;

	if (cd.c_total_time != -1)
	{
		printf(print_info[i].data_frmt, cd.c_total_time);
	}
	else
	{
		printf(print_info[i].hdr_frmt, print_info[i].data_blank);
	}
	i++;

	if (cd.c_status != -1)
	{
		printf(print_info[i].data_frmt, cd.c_status);
	}
	else
	{
		printf(print_info[i].hdr_frmt, print_info[i].data_blank);
	}
	i++;

	if (cd.c_time_lim != -1)
	{
		printf(print_info[i].data_frmt, cd.c_time_lim);
	}
	else
	{
		printf(print_info[i].hdr_frmt, print_info[i].data_blank);
	}
	i++;

	printf(print_info[i++].data_frmt, timeout);
	printf(print_info[i++].data_frmt, cd.c_name);
	printf("\n");
}

/* Initialize the data structures used for tracking per call statistics */
void call_stats_init()
{
	int i;

	buf_size = param.call_stats;
	
	call_stats = malloc (sizeof(call_data)*buf_size);
	if (call_stats == NULL)
	{
		fprintf(stderr, "malloc failed to allocate call_stats array\n");
		exit (1);
	}

	if (strncmp (param.call_stats_file, "", 1) != 0)
	{
		build_fsize();
	}

	for (i = 0; i < buf_size; i++)
	{
		strcpy(call_stats[i].c_name, "--");
		call_stats[i].c_conn_id = -1;
		call_stats[i].c_call_id = -1;
		call_stats[i].c_size = -1; 
		call_stats[i].c_bytes_recvd = -1;
		call_stats[i].c_perc_recvd = -1;
		call_stats[i].c_byte_rate = -1;
		call_stats[i].c_conn_time = -1; 
		call_stats[i].c_resp_time = -1;
		call_stats[i].c_total_time = -1;
		call_stats[i].c_time_lim = -1;
		call_stats[i].c_timeout = -1;
		call_stats[i].c_status  = -1;
	}
}

/* this function is used to get the index into the call_stats array based 
 * on the call*/
int get_index (Call * c)
{
	int index = c->id;
	assert (index >= 0);

	if (index >= buf_size)
	{
		index = -1;
	}

	return index;
}

/* This function is called once a request has been sent to the server*/
void track_call_request(Call *c )
{
	assert (c != NULL);
	assert (c->conn != NULL);

	if (c->conn->basic.num_calls_completed == 0)
	{
		c->record_conn_time = 1;
	}

	int index = get_index(c);

	if (index != -1)
	{
		strncpy (call_stats[index].c_name, (char *) c->req.iov[IE_URI].iov_base, URI_SIZE);
		call_stats[index].c_conn_id = c->conn->id;
		call_stats[index].c_call_id = c->id;
#ifdef UW_DYNOUT
		if (c->timelimit != 0)
		{
			call_stats[index].c_time_lim  = c->timelimit;
		}
		else
		{
			call_stats[index].c_time_lim  = param.timeout;
		}
#else
		call_stats[index].c_time_lim  = param.timeout;
#endif /* UW_DYNOUT */
		if (param.num_sessions)
		{
			call_stats[index].c_size = c->file_size;
		}
		else if (strncmp (param.uri, "/", 2) != 0)
		{
			call_stats[index].c_size = get_f_size(call_stats[index].c_name);
		}
		else
		{
			call_stats[index].c_size = -2;
		}

		/* if this is the first call for a connection
		 * it is responsible for the connection time */
		/* ASSUMPTION: this only works if the second call waits for the
		 * first call to be completed before sending its request */
		if (c->conn->basic.num_calls_completed == 0)
		{
			call_stats[index].c_conn_time = c->conn->basic.time_to_connect; 
		}
		else
		{
			call_stats[index].c_conn_time = 0;
		}
	}
}

/* This function is called once the first byte of a reply is received
   from the server. */
void track_call_response(Call * c, double response_start_time)
{

}

/* This function is called when the last byte of a reply is received
   from the server */
void track_call_reply(Call * c, double transfer_time)
{
	assert (c != NULL);

	/* print information on the fly */
	if (param.call_stats == 0)
	{
		call_data cd;

		cd.c_call_id = c->id;
		cd.c_conn_id = c->conn->id;
		strncpy (cd.c_name, (char *) c->req.iov[IE_URI].iov_base, URI_SIZE);
		cd.c_size =c->file_size ;
		if (param.num_sessions)
		{
			cd.c_size = c->file_size;
		}
		else if (strncmp (param.uri, "/", 2) != 0)
		{
			cd.c_size = get_f_size(cd.c_name);
		}
		else
		{
			cd.c_size = -2;
		}
		cd.c_bytes_recvd = (int) c->reply.content_bytes;
		cd.c_status =c->reply.status ;
#ifdef UW_DYNOUT
		if (c->timelimit != 0)
		{
			cd.c_time_lim  = c->timelimit;
		}
		else
		{
			cd.c_time_lim  = param.timeout;
		}
#else
		cd.c_time_lim =param.timeout ;
#endif /* UW_DYNOUT */
		cd.c_timeout = 0;
		cd.c_conn_time = c->record_conn_time == 1 ? c->conn->basic.time_to_connect : 0;
		cd.c_resp_time = transfer_time + c->basic.time_recv_start - c->basic.time_send_start;
		cd.c_total_time = cd.c_conn_time + cd.c_resp_time;
		cd.c_byte_rate = (double) ((double) c->reply.content_bytes / (double) cd.c_resp_time) * 8.0 / 1000000.0;
		cd.c_perc_recvd = 100 * ((double) c->reply.content_bytes / (double) cd.c_size);

		print_call(cd);
	}
	else
	{
		int index = get_index(c);

		if (index != -1)
		{
			call_stats[index].c_timeout = 0;
			call_stats[index].c_resp_time = transfer_time + c->basic.time_recv_start - c->basic.time_send_start;
			call_stats[index].c_total_time = call_stats[index].c_resp_time + call_stats[index].c_conn_time;
			call_stats[index].c_status = c->reply.status;
			call_stats[index].c_bytes_recvd = (int) c->reply.content_bytes; 
			call_stats[index].c_perc_recvd = 100 * ((double) call_stats[index].c_bytes_recvd / (double) call_stats[index].c_size);
			call_stats[index].c_byte_rate = (double) ((double) call_stats[index].c_bytes_recvd / (double) call_stats[index].c_resp_time) * 8.0 / 1000000.0;
		}
	}
}

/* This function is called when a connection times out. */
void process_call_timeout(Conn * c )
{
	assert (c != NULL);
	Call * cur_call;
	int index;
	int timeout_type = 1;
	int i = 0;

	/* process calls in the connection's send queue 
	 * The send queue holds all calls that are waiting
	 * have their requests sent to the web server */
	cur_call = c->sendq;

	while (cur_call != NULL)
	{

		/* print information on the fly */
		if (param.call_stats == 0)
		{
				call_data cd;

				cd.c_call_id = cur_call->id;
				cd.c_conn_id = cur_call->conn->id;
				strncpy (cd.c_name, (char *) cur_call->req.iov[IE_URI].iov_base, URI_SIZE);
				if (param.num_sessions)
				{
					cd.c_size = cur_call->file_size;
				}
				else if (strncmp (param.uri, "/", 2) != 0)
				{
					cd.c_size = get_f_size(cd.c_name);
				}
				else
				{
					cd.c_size = -2;
				}
#ifdef UW_DYNOUT
				if (cur_call->timelimit != 0)
				{
					cd.c_time_lim  = cur_call->timelimit;
				}
				else
				{
					cd.c_time_lim  = param.timeout;
				}
#else
				cd.c_time_lim = param.timeout;
#endif /* UW_DYNOUT */
				cd.c_timeout = timeout_type;
				cd.c_conn_time = -1;
				cd.c_resp_time = -1;
				cd.c_total_time = -1;
				cd.c_byte_rate = -1;
				cd.c_perc_recvd = -1;
				cd.c_status = -1;
				cd.c_bytes_recvd = -1;

				print_call(cd);
		}
		else
		{
			index = get_index(cur_call);

			if (index != -1)
			{
				call_stats[index].c_timeout = timeout_type;
				call_stats[index].c_call_id = index;
				strncpy (call_stats[index].c_name, (char *) cur_call->req.iov[IE_URI].iov_base, URI_SIZE);
				if (param.num_sessions)
				{
					call_stats[index].c_size = cur_call->file_size;
				}
				else if (strncmp (param.uri, "/", 2) != 0)
				{
					call_stats[index].c_size = get_f_size(call_stats[index].c_name);
				}
				else
				{
					call_stats[index].c_size = -2;
				}
				call_stats[index].c_conn_id = c->id;
#ifdef UW_DYNOUT
				if (cur_call->timelimit != 0)
				{
					call_stats[index].c_time_lim  = cur_call->timelimit;
				}
				else
				{
					call_stats[index].c_time_lim  = param.timeout;
				}
#else
				call_stats[index].c_time_lim = param.timeout;
#endif /* UW_DYNOUT */
			}
		}
		cur_call = cur_call->sendq_next;
	}

	/* process calls in the connection's receive queue 
	 * The receive queue holds all call that are waiting
	 * for a response from the web server */
	cur_call = c->recvq;

	while (cur_call != NULL)
	{
	
		/* print information on the fly */
		if (param.call_stats == 0)
		{
				call_data cd;

				cd.c_call_id = cur_call->id;
				cd.c_conn_id = cur_call->conn->id;
				strncpy (cd.c_name, (char *) cur_call->req.iov[IE_URI].iov_base, URI_SIZE);
				if (param.num_sessions)
				{
					cd.c_size = cur_call->file_size;
				}
				else if (strncmp (param.uri, "/", 2) != 0)
				{
					cd.c_size = get_f_size(cd.c_name);
				}
				else
				{
					cd.c_size = -2;
				}
				cd.c_bytes_recvd = (int) cur_call->reply.content_bytes;
				cd.c_status = cur_call->reply.status ;
#ifdef UW_DYNOUT
				if (cur_call->timelimit != 0)
				{
					cd.c_time_lim  = cur_call->timelimit;
				}
				else
				{
					cd.c_time_lim  = param.timeout;
				}
#else
				cd.c_time_lim = param.timeout;
#endif /* UW_DYNOUT */
				cd.c_timeout = timeout_type;
				cd.c_conn_time = cur_call->record_conn_time == 1 ? cur_call->conn->basic.time_to_connect : 0;
				cd.c_resp_time = c->basic.time_of_timeout - cur_call->basic.time_send_start;
				cd.c_total_time = cd.c_conn_time + cd.c_resp_time;
				cd.c_byte_rate = (double) ((double) cur_call->reply.content_bytes / (double) cd.c_resp_time) * 8.0 / 1000000.0;
				cd.c_perc_recvd = 100 * ((double) cur_call->reply.content_bytes / (double) cd.c_size);

				print_call(cd);
		}
		else
		{
			index = get_index(cur_call);

			if (index != -1)
			{
				call_stats[index].c_timeout = timeout_type;
				call_stats[index].c_status = cur_call->reply.status;
				call_stats[index].c_bytes_recvd = (int) cur_call->reply.content_bytes; 
				call_stats[index].c_resp_time = c->basic.time_of_timeout - cur_call->basic.time_send_start;
				call_stats[index].c_total_time = call_stats[index].c_resp_time + call_stats[index].c_conn_time;
				call_stats[index].c_perc_recvd = 100 * (double) cur_call->reply.content_bytes / (double) call_stats[index].c_size;
				call_stats[index].c_byte_rate = (double) ((double) cur_call->reply.content_bytes / (double) call_stats[index].c_resp_time) * 8.0 / 1000000.0;
			}
		}
		cur_call = cur_call->recvq_next;
	}

	/* Simulate the calls that never happened by
	 * not allowing the id that they should have used
	 * to be used by another call */
	int calls;

	/* this is used if a session log file generated the requests */
	if (param.num_sessions)
	{
		calls = num_missed_calls(c);

		set_missed_calls(c);
	}
	else if (strncmp (param.uri, "/", 2) != 0)
	{
		/* this is used if a videosesslog file was not specified */
		if (c->state < S_CONNECTED)
		{
			timeout_type = 2;
			calls = param.num_calls - c->basic.num_calls_completed;
		}
		else
		{
			/* Don't count the call that timed out, as it has already been
			 * taken care of above */
			calls = param.num_calls - c->basic.num_calls_completed - 1;
		}
	}
	else
	{
		/* No other load generating mechanism is currently supported */
		calls = 0;
	}

	/* if this connection timed out before it was connected
	* then the first call was already created when using videosesslog*/
	if (c->state < S_CONNECTED && param.num_sessions)
	{
		cur_call = session_get_last_call(c);

		timeout_type = 2;

		/* print information on the fly */
		if (param.call_stats == 0)
		{
				call_data cd;

				cd.c_call_id = cur_call->id;
				cd.c_conn_id = c->id;
				strncpy (cd.c_name, (char *) cur_call->req.iov[IE_URI].iov_base, URI_SIZE);
				if (param.num_sessions)
				{
					cd.c_size = cur_call->file_size;
				}
				else if (strncmp (param.uri, "/", 2) != 0)
				{
					cd.c_size = get_f_size(cd.c_name);
				}
				else
				{
					cd.c_size = -2;
				}
#ifdef UW_DYNOUT
				if (cur_call->timelimit != 0)
				{
					cd.c_time_lim  = cur_call->timelimit;
				}
				else
				{
					cd.c_time_lim  = param.timeout;
				}
#else
				cd.c_time_lim = param.timeout;
#endif /* UW_DYNOUT */
				cd.c_timeout = timeout_type;
				cd.c_conn_time = c->basic.time_of_timeout - c->basic.time_connect_start;
				cd.c_resp_time = -1;
				cd.c_total_time = -1;
				cd.c_byte_rate = -1;
				cd.c_perc_recvd = -1;
				cd.c_status = -1;
				cd.c_bytes_recvd = -1;

				print_call(cd);
		}
		else
		{
			index = get_index(cur_call);

			if (index != -1)
			{
				call_stats[index].c_timeout = timeout_type;
				strncpy (call_stats[index].c_name, (char *) cur_call->req.iov[IE_URI].iov_base, URI_SIZE);
				if (param.num_sessions)
				{
					call_stats[index].c_size = cur_call->file_size;
				}
				else if (strncmp (param.uri, "/", 2) != 0)
				{
					call_stats[index].c_size = get_f_size(call_stats[index].c_name);
				}
				else
				{
					call_stats[index].c_size = -2;
				}
				call_stats[index].c_conn_id = c->id;
				call_stats[index].c_call_id = index;
				call_stats[index].c_conn_time = c->basic.time_of_timeout - c->basic.time_connect_start;
#ifdef UW_DYNOUT
				if (cur_call->timelimit != 0)
				{
					call_stats[index].c_time_lim  = cur_call->timelimit;
				}
				else
				{
					call_stats[index].c_time_lim  = param.timeout;
				}
#else
				call_stats[index].c_time_lim  = param.timeout;
#endif /* UW_DYNOUT */
			}
			calls--;
		}
	}

	if (calls > 0)
	{
		inc_call_next_id(calls);

		int top = get_call_next_id ();
		i = top - calls;

		if (top > buf_size && param.call_stats != 0)
		{
			top = buf_size;
		}

		for (; i < top; i++)
		{
			/* print information on the fly */
			if (param.call_stats == 0)
			{
					call_data cd;

					cd.c_call_id = i;
					cd.c_conn_id = c->id;
					if (param.num_sessions)
					{
						strncpy (cd.c_name, get_next_missed_uri(c), URI_SIZE);
						cd.c_size = get_next_missed_size(c);
					}
					else if (strncmp (param.uri, "/", 2) != 0)
					{
						strncpy (cd.c_name, param.uri, URI_SIZE);
						cd.c_size = get_f_size(cd.c_name);
					}
					else
					{
						strcpy(cd.c_name, "--");
						cd.c_size = -2;
					}
#ifdef UW_DYNOUT
					cd.c_time_lim  = get_next_missed_timelimit(c);
#else
					cd.c_time_lim = param.timeout;
#endif /* UW_DYNOUT */
					cd.c_timeout = timeout_type;
					cd.c_conn_time = -1;
					cd.c_resp_time = -1;
					cd.c_total_time = -1;
					cd.c_byte_rate = -1;
					cd.c_perc_recvd = -1;
					cd.c_status = -1;
					cd.c_bytes_recvd = -1;

					print_call(cd);
			}
			else
			{
				call_stats[i].c_call_id= i;
				call_stats[i].c_conn_id = c->id;
				call_stats[i].c_timeout = timeout_type;
				if (param.num_sessions)
				{
					//strncpy (call_stats[i].c_name, get_next_missed_uri(c), URI_SIZE);
					strncpy (call_stats[i].c_name, "BLA", 4);
					//call_stats[i].c_size = get_next_missed_size(c);
					call_stats[i].c_size = 0;
				}
				else if (strncmp (param.uri, "/", 2) != 0)
				{
					strncpy (call_stats[i].c_name, param.uri, URI_SIZE);
					call_stats[i].c_size = get_f_size(call_stats[i].c_name);
				}
				else
				{
					strcpy(call_stats[i].c_name, "--");
					call_stats[i].c_size = -2;
				}
#ifdef UW_DYNOUT
				//call_stats[i].c_time_lim  = get_next_missed_timelimit(c);
				call_stats[i].c_time_lim = param.timeout;
#else
				call_stats[i].c_time_lim = param.timeout;
#endif /* UW_DYNOUT */
			}
		}
	}
}

/* Print a summary of the per call statistics. */
void 
print_call_stats()
{
	int j=0;
	int top = 0;

	top = get_call_next_id();

	if (top >= buf_size)
	{
		top = buf_size;
		printf("\nCall Statistics (buffer filled):\n\n");
	}
	else
	{
		printf("\nCall Statistics:\n\n");
	}

	/* print data */
	for (j = 0; j < top; j++)
	{
		print_call(call_stats[j]);
	}
}

#ifdef UW_PACE_REQUESTS

/* return the total connection time for a call.  Returns 0.0 if not available */
double get_call_total_conn_time( Call *cur_call )
{
    int index = get_index(cur_call);

    if (index != -1)
        return( call_stats[index].c_total_time );
    else
        return( 0.0 );
}
#endif /* UW_PACE_REQUESTS */

#endif /* UW_CALL_STATS */
