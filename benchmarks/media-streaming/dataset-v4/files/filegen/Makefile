CC = gcc
CFLAGS = -Wall -g
LIBS = -lm

all: make_zipf gen_fileset

.PHONY: all

make_zipf: make_zipf.c
	$(CC) $(CFLAGS) -o $@ $^ $(LIBS)

gen_fileset: gen_fileset.c
	$(CC) $(CFLAGS) -o $@ $^ $(LIBS)
