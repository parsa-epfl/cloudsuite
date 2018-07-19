# Generated automatically from Makefile.in by configure.
SHELL=/bin/sh

top_srcdir = ..
top_builddir = ..

prefix = /usr/local
bindir = ${exec_prefix}/bin
mandir = ${prefix}/man

CC = gcc
RANLIB = ranlib

INCLUDES = -I$(top_builddir) -I$(top_srcdir) -I$(top_srcdir)/lib \
		$(SSL_INCLUDES) -I/usr/kerberos/include

DEFS = -DHAVE_CONFIG_H
CPPFLAGS =  -DNDEBUG -D_GNU_SOURCE -D_XOPEN_SOURCE
CFLAGS = -g -O2 -Wall 
LDFLAGS = 
LIBS = -lssl -lcrypto -lm 

CPPFLAGS	+= -DBIG_FD_SETSIZE

COMPILE = $(CC) -c $(DEFS) $(INCLUDES) $(CPPFLAGS) $(CFLAGS) -static -static-libgcc
LINK = $(CC) $(LDFLAGS) -static -static-libgcc -o $@



.c.o:
	$(COMPILE) $<

LIBUTIL_OBJS = getopt.o getopt1.o ssl_writev.o

all: libutil.a

libutil.a: $(LIBUTIL_OBJS)
	ar r $@ $(LIBUTIL_OBJS)
	$(RANLIB) $@

depend:

install:

clean:
	rm -f libutil.a $(LIBUTIL_OBJS)

distclean: clean
	rm -f *~

.PHONY: all install clean distclean depend
