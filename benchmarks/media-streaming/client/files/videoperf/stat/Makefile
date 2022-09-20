# Generated automatically from Makefile.in by configure.
SHELL=/bin/sh

srcdir = .
top_srcdir = ..
top_builddir = ..

prefix = /usr/local
bindir = ${exec_prefix}/bin
mandir = ${prefix}/man

CC = gcc
RANLIB = ranlib

INCLUDES = -I$(top_builddir) -I$(srcdir) -I$(top_srcdir) -I$(top_srcdir)/lib \
		-I$(top_srcdir)/gen $(SSL_INCLUDES) -I/usr/kerberos/include

DEFS = -DHAVE_CONFIG_H
CPPFLAGS =  -DNDEBUG -D_GNU_SOURCE -D_XOPEN_SOURCE
CFLAGS = -g -O2 -Wall 
LDFLAGS = 
LIBS = -lssl -lcrypto -lm 

# Uncomment this to enable tracking statistics about each call (request)
DEFS    += -DUW_CALL_STATS
# Uncomment this to enable session log file specified dynamic timeouts
DEFS    += -DUW_DYNOUT -DHAVE_EPOLL
# Uncomment this to enable "pace_time" in wsesslog files
DEFS    += -DUW_PACE_REQUESTS
# Uncomment this to enable statistics that ignore specified ramp up and ramp down periods
#DEFS    += -DUW_STABLE_STATS
# Uncomment this to generate stats about the variation in throughput
DEFS    += -DUW_THROUGHPUT_STATS

CPPFLAGS	+= -DBIG_FD_SETSIZE

COMPILE = $(CC) -c $(DEFS) $(INCLUDES) $(CPPFLAGS) $(CFLAGS) -static -static-libgcc
LINK = $(CC) $(LDFLAGS) -static -static-libgcc -o $@



.c.o:
	$(COMPILE) $<

LIBSTAT_OBJS = basic.o sess_stat.o print_reply.o spec_stats.o call_stats.o

all: libstat.a

libstat.a: $(LIBSTAT_OBJS)
	ar r $@ $(LIBSTAT_OBJS)
	$(RANLIB) $@

depend:

install:

clean:
	rm -f libstat.a $(LIBSTAT_OBJS)

distclean: clean
	rm -f *~

.PHONY: all install clean distclean depend
