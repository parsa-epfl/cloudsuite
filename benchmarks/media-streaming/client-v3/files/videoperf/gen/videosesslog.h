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

#ifndef _WSESSLOG_H_
#define _WSESSLOG_H_ 

#ifdef UW_CALL_STATS

#include <conn.h>
#include <call.h>

/* This function is for testing purposes
 * It will print out the remaining uris to be requested fora given connection */
void print_remaining_uri (Conn * conn);

/* This function returns the number of calls that are remaining 
 * NOTE: this is intented to be used by call_stats after a connection times out */
int num_missed_calls(Conn * conn);

/* This function sets the private data structs up befor get_next_missed_uri is called
 * NOTE: this is intented to be used by call_stats after a connection times out */
void set_missed_calls(Conn *conn);

/* This function returns the uri of the next request in order 
 * NOTE: this is intented to be used by call_stats after a connection times out */
char * get_next_missed_uri(Conn *conn);

/* This function returns the file size of the next request in order 
 * NOTE: this is intented to be used by call_stats after a connection times out */
int get_next_missed_size(Conn *conn);

#ifdef UW_DYNOUT
/* This function returns the timelimit of the next request in order 
 * NOTE: this is intented to be used by call_stats after a connection times out */
int get_next_missed_timelimit(Conn *conn);
#endif /* UW_DYNOUT */

#endif /* UW_CALL_STATS */

#endif /* _WSESSLOG_H_ */
