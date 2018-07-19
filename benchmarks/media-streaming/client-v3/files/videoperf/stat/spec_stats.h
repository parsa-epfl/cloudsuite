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

#ifndef SPEC_STATS
#define SPEC_STATS

#include <conn.h>
#include <call.h>

/* Initialize the data structures used for tracking SPECweb99 statistics */
void spec_stats_init();

/* This function is called when a connection is established. */
void track_spec_connection(Conn * c );

/* This function is called once a request has been sent to the server*/
void track_spec_request(Call *c );

/* This function is called once the first byte of a reply is received
   from the server. */
void track_spec_response(Call * c, double response_start_time);

/* This function is called when the last byte of a reply is received
   from the server */
void track_spec_reply(Call * c, double transfer_time);

/* This function is called when a connection times out. It should
   only track timeouts for those connections on which a request
   was sent */
void process_spec_timeout(Conn * c );

/* Print a summary of the SPECweb99 statistics. The summary focuses
   on timeouts, but also includes several other statistics */
void print_spec_timeouts();

#endif /* SPEC_STATS */
