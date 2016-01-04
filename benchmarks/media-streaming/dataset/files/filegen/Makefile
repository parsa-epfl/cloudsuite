all : STDOUT graphs

# MIXED_VIDEO
STDOUT: make_zipf Makefile
	./make_zipf video_files-SD-20000.list video_files-HD-8000.list | tee STDOUT

video_files : make_zipf
	./make_zipf

# gcc -Wall -g -o make_zipf -lm make_zipf.c
make_zipf : make_zipf.c
	gcc -Wall -g -o make_zipf make_zipf.c -lm

gen_fileset : gen_fileset.c
	gcc -Wall -g -o gen_fileset -lm gen_fileset.c
