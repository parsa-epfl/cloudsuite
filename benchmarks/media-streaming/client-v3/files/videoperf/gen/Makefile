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
		$(SSL_INCLUDES) -I/usr/kerberos/include

DEFS = -DHAVE_CONFIG_H
CPPFLAGS =  -DNDEBUG -D_GNU_SOURCE -D_XOPEN_SOURCE
CFLAGS = -g -O2 -Wall 
LDFLAGS = 
LIBS = -lssl -lcrypto -lm 

CPPFLAGS	+= -DBIG_FD_SETSIZE

# Uncomment this to enable tracking statistics about each call (request)
DEFS    += -DUW_CALL_STATS
# Uncomment this to enable session log file specified dynamic timeouts
DEFS    += -DUW_DYNOUT -DHAVE_EPOLL
# Uncomment this to enable "pace_time" in wsesslog files
DEFS    += -DUW_PACE_REQUESTS

COMPILE = $(CC) -c $(DEFS) $(INCLUDES) $(CPPFLAGS) $(CFLAGS) -static -static-libgcc
LINK = $(CC) $(LDFLAGS) -static -static-libgcc -o $@



.c.o:
	$(COMPILE) $<

LIBGEN_OBJS = call_seq.o conn_rate.o misc.o rate.o session.o \
	videosesslog.o sess_cookie.o

#	uri_fixed.o uri_wlog.o uri_wset.o \
#	wsess.o wsesslog.o wsesspage.o \
#	sess_cookie.o

all: libgen.a

libgen.a: $(LIBGEN_OBJS)
	ar r $@ $(LIBGEN_OBJS)
	$(RANLIB) $@

depend:

install:

clean:
	rm -f libgen.a $(LIBGEN_OBJS)

distclean: clean
	rm -f *~

.PHONY: all install clean distclean depend
