# Generated automatically from Makefile.in by configure.
SHELL=/bin/sh

srcdir = .
top_srcdir = .
top_builddir = .

prefix = /usr/local
bindir = ${exec_prefix}/bin
mandir = ${prefix}/man


SUBDIRS	= lib gen stat

CC = gcc
RANLIB = ranlib
MKDIR = $(top_srcdir)/mkinstalldirs
INSTALL = /usr/bin/install -c
INSTALL_PROGRAM = ${INSTALL}
INSTALL_DATA = ${INSTALL} -m 644

# INCLUDES = -I$(top_srcdir)/include -I$(top_builddir) -I$(top_srcdir) -I$(top_srcdir)/lib
INCLUDES = -I$(top_srcdir)/include -I$(top_builddir) -I$(top_srcdir) -I$(top_srcdir)/lib -I/usr/kerberos/include
DEFS = -DHAVE_CONFIG_H
CPPFLAGS =  -DNDEBUG -D_GNU_SOURCE -D_XOPEN_SOURCE
CFLAGS = -g -O2 -Wall 
LDFLAGS = 
LIBS = -lssl -lcrypto -lm 

ifeq (x86_64, ia64)
  CPPFLAGS += -DIA64
endif

# Uncomment this to keep track of time spent in system calls
#DEFS	+= -DTIME_SYSCALLS
# Uncomment this to have httperf wait nicely when doing a select()
DEFS	+= -DDONT_POLL
# Uncomment this to enable --use-cpu-mask functionality (requires OS support)
DEFS	+= -DHAVE_SCHED_AFFINITY -DHAVE_EPOLL
# Uncomment this to enable tracking statistics about each call (request)
DEFS    += -DUW_CALL_STATS
# Uncomment this to enable session log file specified dynamic timeouts
DEFS    += -DUW_DYNOUT
# Uncomment this to enable statistics that ignore specified ramp up and ramp down periods
#DEFS    += -DUW_STABLE_STATS

CPPFLAGS	+= -DBIG_FD_SETSIZE

# Uncomment this if you want to enable
# --idle-connections N which starts an idleconn process
# on the same machine. Can be used with trun's IDLERATE and IDLECONN.
#CPPFLAGS        += -DIDLECONN


COMPILE = $(CC) -c $(DEFS) $(INCLUDES) $(CPPFLAGS) $(CFLAGS) 
LINK = $(CC) $(LDFLAGS) -o $@



.c.o:
	$(COMPILE) $<

HTTPERF_OBJS = httperf.o object.o call.o conn.o sess.o core.o event.o \
	http.o timer.o sys_sched_affinity.o

TTEST_OBJS = ttest.o timer.o

all: all-recursive httperf idleconn

httperf: $(HTTPERF_OBJS) gen/libgen.a stat/libstat.a lib/libutil.a
	$(LINK) $(HTTPERF_OBJS) \
		gen/libgen.a stat/libstat.a lib/libutil.a $(LIBS)

idleconn: idleconn.o
	$(LINK) idleconn.o $(LIBS)

install: install-recursive httperf
	$(MKDIR) $(bindir) $(mandir)/man1
	$(INSTALL_PROGRAM) httperf $(bindir)/httperf
	$(INSTALL_DATA) $(srcdir)/httperf.man $(mandir)/man1/httperf.1

ttest: ttest.o timer.o

clean:	clean-recursive
	rm -f $(HTTPERF_OBJS) $(TTEST_OBJS) idleconn.o httperf idleconn ttest

distclean: distclean-recursive
	rm -f *~

all-recursive install-recursive clean-recursive distclean-recursive \
	depend-recursive:
	@for subdir in $(SUBDIRS); do            \
	  target=`echo $@ | sed s/-recursive//`; \
	  echo making $$target in $$subdir;     \
	  (cd $$subdir && $(MAKE) $$target)     \
	   || case "$(MFLAGS)" in *k*) fail=yes;; *) exit 1;; esac; \
	done && test -z "$$fail"

.PHONY: all install clean distclean depend \
	all-recursive install-recursive clean-recursive distclean-recursive \
	depend-recursive
