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

#include <stdio.h>
#include <unistd.h>
#include <httperf.h>
#include <timer.h>

const char *prog_name = "ttest";

Timer *t1, *t2;

void
expire (struct Timer *t)
{
  Time now = timer_now();
  printf ("Timer %p expired at %f\n", t, 
	  now);
#if 0
  if (t == t1)
    timer_cancel (t2);
#endif
}

int
main (int argc, char **argv)
{
  Any_Type a;

  timer_init ();

  timer_schedule (expire, ++a.i, 0.0);
  timer_schedule (expire, ++a.i, 2.0);
  timer_schedule (expire, ++a.i, 2.0);
  timer_schedule (expire, ++a.i, 3.0);
  t1 = timer_schedule (expire, ++a.i, 3.0);
  t2 = timer_schedule (expire, ++a.i, 5 +3.0);
  timer_schedule (expire,  ++a.i, 3.0);
  timer_schedule (expire, ++a.i, 10+3.0);
  timer_schedule (exit, ++a.i, 12+3.0);
  while (1)
    {
      printf("Time: %f\n", timer_now());
      timer_tick ();
      usleep (10000);
    }
}
