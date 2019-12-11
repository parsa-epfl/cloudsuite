/*
    make_zipf
    Creates a set of log files with zipf distribution.
          
    This file is Copyright (C) 2011  Jim Summers

    Authors: Jim Summers <jasummer@cs.uwaterloo.ca>
  
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

#include <stdlib.h>
#include <stdio.h>
#include <math.h>
#include <limits.h>
#include <errno.h>
#include <string.h>
#include <assert.h>

/*
#include <unistd.h>
#include <sys/stat.h>
#include <sys/types.h>
*/
#define BYTES_PER_MB	(1024*1024)

/* output variable-sized chunks */
//#define VBR_CHUNKS

/* adjust chunk size by plus or minus this fraction */
#define VBR_FACTOR (0.1)

/* For reading the config file */
#define CONFIG_FILENAME "filegen_param.conf"
#define CONFIG_MAXBUF 1024
#define CONFIG_DELIM "="

/* user-supplied parameters */
static unsigned long random_seed = 40;
static int library_size = 100; 		/* number of videos available to be chosen. */
static double zipf_exponent = -0.8;		/* exponent used to generate Zipf distribution */
static double timeout_per_request = 10;		/* maximum time allowed to transmit a request to a client */
static int num_buffering_requests = 3;		/* Number of client chunks to request at full speed before */
						/* pacing the rest of the requests in the session.  */
static int session_time_chunking = 1;		/* If greater than 1, session times will be rounded up */
						/* to the next multiple higher than this.	       */
static int video_time_chunking = 1;		/* If greater than 1, video times will be rounded to */
						/* the nearest multiple of this number.		     */
static int min_timeout = 10;			/* The minimum timeout to use for a session */
static int num_log_files = 3;			/* number of client logs to create */
static int num_log_sessions = 20;		/* number of sessions per log file */
static int num_sessions;			/* total number of video sessions = num_log_files * num_log_sessions */
static int num_client_chunks_requested;		/* total number of client chunk requests */

static double max_duration = 800;		/* video library durations are truncated to this value when read */
static double max_session;			/* duration of the longest session that was used, in seconds */

/* start requests from an offset other than 0.  If FIXED, add a */
/* fixed amount.  If MIDPOINT, make request out of the middle of */
/* the file.  If ENDPOINY, make requests out of the last data in file */
//#define FIXED_OFFSET
#define FIXED_OFFSET_BYTES 1000000
//#define MIDPOINT_OFFSET
//#define ENDPOINT_OFFSET

/* actual values from generated workload */
static double average_video_MB;			/* Calculated average size of videos in MB */

/* header lines added to the top of each log file */
#define MAX_HEADER_LINES	50
#define HEADER_LINE_LENGTH	120
static char header_line[MAX_HEADER_LINES][HEADER_LINE_LENGTH];
static int num_header_lines;

static char log_basename[] = "cl";
static char preload_basename[] = "fs";



/* information used to create names in the logfiles */
typedef struct {
    char *log_warm_basename;		/* basename for files requested more than once */
    char *log_cold_basename;		/* basename for files requested only once. */
    int num_warm_files;			/* number of files to warm cache with */
    int fname_num_digits;		/* number of digits in number portion of filename */
    double client_MB_per_request;	/* size of requests by clients */
    double file_seconds_per_request;	/* number of seconds of video contained in each request */ 
} logfile_info_struct;

/* filenames to used for different libraries */

logfile_info_struct logfile_info[] = {
    { "full-2040p", "full-2040p", 10000, 5, 50, 10 },
    { "full-1440p", "full-1440p", 10000, 5, 12.5, 10 },
    { "full-1080p", "full-1080p", 10000, 5, 10, 10 },
    { "full-720p", "full-720p", 10000, 5, 6.25, 10 },
    { "full-480p", "full-480p", 10000, 5, 3.125, 10 },
    { "full-360p", "full-360p", 10000, 5, 1.25, 10 },
    { "full-240p", "full-240p", 10000, 5, 0.5, 10 },
    { "full-144p", "full-144p", 10000, 5, 0.1, 10 },
};


#define Q2040P 0
#define Q1440P 1
#define Q1080P 2
#define Q720P 3
#define Q480P 4
#define Q360P 5
#define Q240P 6
#define Q144P 7

int video_quality = Q480P;
char video_quality_str[10] = "480p";


/* information about each file in a fileset */
struct fs_info_struct;
typedef struct {
    int file_number;			/* number of file, for convenience */
    struct fs_info_struct *fsi;		/* the fileset that contains the video */
    double video_dur;			/* duration of video in seconds */
    int size;				/* size of video file in bytes */
    int rank;				/* rank of video within fileset */
} file_info_struct;

/* set of files with the same bitrate and common names */
/* provides mappings between rank and file number */
typedef struct fs_info_struct {
    int num_files;		/* number of files in fileset */
    file_info_struct *fi;	/* array of information about each file in fileset */
    int *video_rank;		/* ranks of videos, in index order */
    int *video_index;		/* index of videos, in rank order */
    logfile_info_struct	*li;	/* information used to create log file entries */
    double file_MB_rate;	/* data rate of video files, in MB/s */
    double *pdf;		/* popularity distribution function for the fileset */
} fileset_info_struct;

/* information about sessions making up the workload */
/* note that files may be from more than one fileset */
typedef struct {
    int rank;				/* rank of video chosen for session */
    fileset_info_struct *fsi;		/* the fileset that contains the video */
    double duration;			/* duration of session */
    int client_id;			/* which client is responsible for this session */
    file_info_struct *fi;		/* information about file containing video */
} session_info_struct;

static int num_videos;		/* number of videos in all the file sets. */

/* array of filesets used for sessions */
static int num_filesets;
static fileset_info_struct **fileset_list; 

/***************************** forward declarations *****************************/
static void create_random_ranks( fileset_info_struct *fsi );
static int create_log_files( session_info_struct *session_info, int total_num_videos );
static void write_file_duration_CDF( void );
/********************************************************************************/

/* PDF of video duration, or time to read entire video.		    	     */
/* This is the combination of 4 normal curves with the following parameters: */
#define VIDEO_DUR_SIZE 25
static double video_duration_cdf[VIDEO_DUR_SIZE] = { 0.018, 0.057, 0.109, 0.160, 0.202, 0.247, 0.296, 0.346, 0.395, 0.439,
						0.494, 0.544, 0.597, 0.642, 0.692, 0.723, 0.791, 0.817, 0.874, 0.928,
						0.960, 0.968, 0.973, 0.990, 1.0 };
static double video_duration[VIDEO_DUR_SIZE] = { 15, 30, 50, 70, 90, 112, 138, 161, 183, 200,
						215, 228, 244, 257, 285, 300, 362, 400, 500, 600,
						700, 800, 900, 1000, 1000 };


/*
 * Read the config file, if present.
 */
void read_config()
{
	FILE *f = fopen(CONFIG_FILENAME, "r");
	if (f != NULL) {
		char line[CONFIG_MAXBUF];
		int i = 0;
		while(fgets(line, sizeof(line), f) != NULL) {
			char* cfline;
			cfline = strstr((char*)line, CONFIG_DELIM);
			cfline = cfline + strlen(CONFIG_DELIM);

			if (i==0) {
				library_size = atoi(cfline);				
			} else if (i==1) {
				num_log_files = atoi(cfline);				
			} else if (i==2) {
				num_log_sessions = atoi(cfline);
			} else if (i==3) {
				printf("quality = %s %d\n", cfline, strcmp(cfline, "240p"));
				if(strncmp(cfline, "1080p", 5) == 0) {
					video_quality=2;
					strcpy(video_quality_str, "1080p");
				} else if(strncmp(cfline, "720p", 4) == 0) {
					video_quality=3;
					strcpy(video_quality_str, "720p");
				} else if(strncmp(cfline, "480p", 4) == 0) {
					video_quality=4;
					strcpy(video_quality_str, "480p");
				} else if(strncmp(cfline, "360p", 4) == 0) {
					video_quality=5;
					strcpy(video_quality_str, "360p");
				} else if(strncmp(cfline, "240p", 4) == 0) {
					video_quality=6;
					strcpy(video_quality_str, "240p");
				}
			}
			i++;
		}
		fclose(f);
	}
}

/*
 * Find the first cdf value higher than r. Interpolate r between the two straddling
 * points to determine the chosen value.
 */
static double interpolate_cdf( double r,	     /* random number between 0 and 1 */
				double *cdf,
				double *cdf_data,    /* data values for each CDF point */
				int cdf_size
			    )
{
    int i;

    /* search through CDF, don't bother looking at the last value */
    cdf_size--;
    for (i=0; i < cdf_size; ++i) {
	if (r <= cdf[i]) {
	    break;
	}
    }

    /* interpolate results */
    if (i == 0) {
	return( r / cdf[0] * cdf_data[0] );
    }
    else {
	return( cdf_data[i] - (cdf_data[i] - cdf_data[i-1]) * (cdf[i] - r) / (cdf[i] - cdf[i-1]) );
    }
}


#define MINIMUM_DURATION 5
#define VIDEO_DUR_HISTOGRAM_SIZE 1000
#define VIDEO_DUR_HISTOGRAM_BINSIZE  1
static int video_duration_histogram[VIDEO_DUR_HISTOGRAM_SIZE];
static int video_duration_histogram_points = 0;

/*
 * Return the duration of a video in seconds.
 * r is a random number between 0 and 1.
 */
static double compute_duration( double r )
{
    int	i;
    double duration;

    duration = interpolate_cdf( r, video_duration_cdf, video_duration, VIDEO_DUR_SIZE );
    if (duration < MINIMUM_DURATION)
	duration = MINIMUM_DURATION;

    /* update duration histogram */
    i = duration / VIDEO_DUR_HISTOGRAM_BINSIZE;
    if (i >= VIDEO_DUR_HISTOGRAM_SIZE)
	i = VIDEO_DUR_HISTOGRAM_SIZE;
    video_duration_histogram[i]++;
    video_duration_histogram_points++;

    return( duration );
}

/* CDF of session lengths.  This is the fraction of a full file that will be read. */
#define SESSION_FRAC_SIZE 20
static double session_frac_cdf[SESSION_FRAC_SIZE] = { 0.050, 0.100, 0.150, 0.200, 0.250, 0.300, 0.350, 0.400, 0.450, 0.500,
							0.550, 0.600, 0.650, 0.700, 0.750, 0.800, 0.820, 1.000 };
static double session_frac[SESSION_FRAC_SIZE] = { 0.067, 0.122, 0.174, 0.222, 0.277, 0.352, 0.439, 0.537, 0.627, 0.673,
							0.708, 0.738, 0.783, 0.835, 0.905, 0.982, 1.000, 1.000 };

#define SESSION_FRAC_HISTOGRAM_SIZE 1000
#define SESSION_FRAC_HISTOGRAM_BINSIZE  0.001
static int session_frac_histogram[SESSION_FRAC_HISTOGRAM_SIZE];
static int session_frac_histogram_points;

/*
 * Return the fraction of a full file to view.
 * r is a random number between 0 and 1.
 */
static double compute_session_frac( double r )
{
    int i;
    double frac;

    frac = interpolate_cdf( r, session_frac_cdf, session_frac, SESSION_FRAC_SIZE );

    /* update duration histogram */
    i = frac / SESSION_FRAC_HISTOGRAM_BINSIZE;
    if (i >= SESSION_FRAC_HISTOGRAM_SIZE)
	i = SESSION_FRAC_HISTOGRAM_SIZE;
    session_frac_histogram[i]++;
    session_frac_histogram_points++;

    return( frac );
}

/*
 * Fill the sample array with a Zipf probability distribution.
 */
static void make_zipf_distribution( double *sample, int num_ranks )
{
    double rank_total;
    int rank;

    /* create zipf distribution */
    rank_total=0.0;
    for (rank=0; rank < num_ranks; ++rank) {
	sample[rank] = pow( rank + 1.0, zipf_exponent );
        rank_total += sample[rank];
    }

    /* normalize distribution to compute probabilities */
    for (rank=0; rank < num_ranks; ++rank) {
	sample[rank] /= rank_total;
    }
}

/* Helper function for the qsort() routine.  We want to sort the ranks in
 * descending order, so return a negative number when the first item is
 * larger than the second.
 */
typedef struct {
    int num_views;	/* number of views */
    int index;		/* index in original order */
} rank_struct;
static int compare_rank_views( const void *c1, const void *c2 )
{
    return( ((rank_struct *) c2)->num_views - ((rank_struct *) c1)->num_views );
}

/*
 * Create a set of file durations to use for the video library
 */
static fileset_info_struct *create_video_library( int lib_size )
{
    fileset_info_struct *fsi;
    double total_duration;
    int i, j, k;
    FILE *f;
    int bin;
    int num_dur_histogram;
    int video_dur_hist_size;
    typedef struct {
	int *histogram;
	int size;
    } histogram_sample_struct;
    histogram_sample_struct *dur_histogram_sample;
    histogram_sample_struct *total_dur_histogram;
    int dur_histogram_sample_size;
    double video_dur_histogram_bin_size;
    int *dur_cdf;


    /* construct duration histograms for subsets of the distribution */
    num_dur_histogram = 3;
    video_dur_hist_size = 100;

    /* allocate a histogram for each subset and use the last for the total of all histograms */
    if ((dur_histogram_sample = calloc( num_dur_histogram+1, sizeof( histogram_sample_struct ) )) == NULL) {
	printf( "Unable to allocate file duration histogram pointer array.\n" );
	exit( 1 );
    }
    for (i=0;i<num_dur_histogram+1;++i) {
	if ((dur_histogram_sample[i].histogram = calloc( video_dur_hist_size, sizeof( int ) )) == NULL) {
	    printf( "Unable to allocate file duration histogram array.\n" );
	    exit( 1 );
	}
    }
    dur_histogram_sample_size = (lib_size + num_dur_histogram - 1) / num_dur_histogram;
    video_dur_histogram_bin_size = video_duration[VIDEO_DUR_SIZE - 1] / video_dur_hist_size;

    /* use extra histogram sample at end to store aggregate values */
    total_dur_histogram = dur_histogram_sample + num_dur_histogram;

    if ((fsi = malloc( sizeof( fileset_info_struct ) )) == NULL) {
	printf( "Unable to allocate library information array.\n" );
	exit( 1 );
    }
    if ((fsi->fi = malloc( lib_size * sizeof( file_info_struct ) )) == NULL) {
	printf( "Unable to allocate file information array.\n" );
	exit( 1 );
    }
    fsi->num_files = lib_size;
    fsi->li = &(logfile_info[ video_quality ]);
    fsi->file_MB_rate = fsi->li->client_MB_per_request / fsi->li->file_seconds_per_request;

    total_duration = 0.0;
    j = dur_histogram_sample_size;
    k = 0;
    for (i=0; i < lib_size; ++i) {

	fsi->fi[i].file_number = i;
	fsi->fi[i].video_dur = compute_duration( random() / (1.0 + INT_MAX) );
	fsi->fi[i].size = fsi->fi[i].video_dur * fsi->file_MB_rate * BYTES_PER_MB;
	total_duration += fsi->fi[i].video_dur;
	fsi->fi[i].fsi = fsi;

	bin = fsi->fi[i].video_dur / video_dur_histogram_bin_size;
	if (bin >= video_dur_hist_size) {
	    bin = video_dur_hist_size - 1; 
	}
	dur_histogram_sample[k].histogram[bin]++;
	dur_histogram_sample[k].size++;
	total_dur_histogram->histogram[bin]++;
	total_dur_histogram->size++;

	/* if a portion is complete, start collecting histogram stats in the next sample */
	if (i >= j) {
	    k++;
	    if (k >= num_dur_histogram)
		k = num_dur_histogram - 1;
	    j += dur_histogram_sample_size;
	}

    }
    assert( total_dur_histogram->size == lib_size );
    average_video_MB = total_duration / lib_size * fsi->file_MB_rate;

    /* write out the file duration CDF */
    write_file_duration_CDF();

    /* write out the file duration probability distribution */
    if ((f = fopen( "video_dur_prob.txt", "w" )) == NULL) {
	printf( "Error opening file %d\n", errno );
	exit( 1 );
    }

    /* output descriptions for columns, assuming 3 duration histograms */
    assert( num_dur_histogram == 3 );
    fprintf( f, "# video_duration PDF_low PDF_mid PDF_high PDF_all\n" );
    if ((dur_cdf = calloc( num_dur_histogram, sizeof( int ) )) == NULL) {
	printf( "Unable to allocate file duration histogram CDF array.\n" );
	exit( 1 );
    }
    j = 0;
    for (i=0;i<video_dur_hist_size;++i) {
	/* fprintf( f, "%.3f %.3f", (i + 0.5) * video_dur_histogram_bin_size, (double) total_dur_histogram->histogram[i] / lib_size ); */
	fprintf( f, "%.3f", (i + 0.5) * video_dur_histogram_bin_size );
	for (k=0;k<=num_dur_histogram;++k) {
	    fprintf( f, " %.3f", (double) dur_histogram_sample[k].histogram[i] / dur_histogram_sample[k].size  );
	}
	fputs( "\n", f );
    }
    fclose( f );

    /* create array of ranks for the videos */
    if ((fsi->video_rank = malloc( lib_size * sizeof( int ) )) == NULL) {
	printf( "Unable to allocate video rank array.\n" );
	exit( 1 );
    }
    create_random_ranks( fsi );

    return( fsi );
}

/*
 * Write out the video_dur_histogram file
 */
static void write_file_duration_CDF( void )
{
    FILE *f;
    int total;
    int i;

    if ((f = fopen( "video_dur_histogram.txt", "w" )) == NULL) {
	printf( "Error opening file %d\n", errno );
	exit( 1 );
    }
    fprintf( f, "# video_duration CDF\n" );
    total = 0;
    for (i=0;i<VIDEO_DUR_HISTOGRAM_SIZE;++i) {
	total += video_duration_histogram[i];
	fprintf( f, "%.3f %.3f\n", (double) (i + 1) * VIDEO_DUR_HISTOGRAM_BINSIZE, (double) total / video_duration_histogram_points );
    }
    fclose( f );
}

/* creates an array of ranks for each video, in a random permutation.
 * fsi must be initialized before calling this function.
 */
static void create_random_ranks( fileset_info_struct *fsi )
{
    int i, j;
    int j_pos;
    double median_size;
    double diff, median_diff;
    int median_pos;

    assert( fsi != NULL );
    assert( fsi->num_files != 0 );
    assert( fsi->fi != NULL );
    assert( fsi->video_rank != NULL );

    /* use a random permutation to assign a rank to each video index */
    for (i=0;i < fsi->num_files;++i) {
        j = random() / (1.0 + INT_MAX) * (i+1);
        fsi->video_rank[i] = fsi->video_rank[j];
        fsi->video_rank[j] = i;
    }

    /* adjust random permutation so that rank 0 and rank 1 files are */
    /* close to the median duration.				     */
    median_size = compute_duration( 0.5 );
    for (j=0;j<2;++j) {
	median_diff = fabs( fsi->fi[0].video_dur - median_size );
	median_pos = 0;
	j_pos = 0;

	/* find duration closest to the median */
	for (i=1;i<fsi->num_files;++i) {
	    diff = fabs( fsi->fi[i].video_dur - median_size );
	    if (diff < median_diff) {

		/* check if this median has already been used */
		if (fsi->video_rank[i] > j) {
		    median_diff = diff;
		    median_pos = i;
		}
	    }

	    /* also find the video with rank j */
	    if (fsi->video_rank[i] == j) {
		j_pos = i;
	    }
	}

	fsi->video_rank[j_pos] = fsi->video_rank[median_pos];
	fsi->video_rank[median_pos] = j;
    }

    /* set ranks in file_info structures */
    for (i=0;i < fsi->num_files;++i) {
	fsi->fi[i].rank = fsi->video_rank[i];
    }
}

/*
 * Read a set of file durations and ranks to use for the video library.
 * The input file is expected to have the same format as the video_files.txt
 */
static fileset_info_struct *read_video_library( const char *filename, logfile_info_struct *log_info, int *video_count )
{
    fileset_info_struct *fsi;
    FILE *f;
    int i;
    int bin;
    char file_line[300];
    int lib_size;
    int num_files;
    double total_duration;
    int file_num;
    double dur_seconds;
    int dur_chunks;
    double sess_dur;
    int sess_chunks;
    static char old_header[] = "# num length(B) length(s) length(chunks) max_session(s) max_session(chunks) rank sum_sessions(s)";
    int header_version;

    /* open file, determine which version, and count the number of lines */
    f = fopen( filename, "r" );
    if (f == NULL) {
	printf( "Error %d opening video library file \"%s\"\n", errno, filename );
	exit( 1 );
    }
    lib_size = 0;
    header_version = 1;
    while ((fgets( file_line, sizeof( file_line ), f )) != NULL) {
	if (file_line[0] == '#') {

	    /* determine if a new or old style video list */
	    if (strncmp( file_line, old_header, sizeof( old_header ) - 1 ) == 0) {
		header_version = 0;
	    }
	    continue;
	}
	lib_size++;
    }

    num_files = lib_size;
    if (video_count != NULL)
	*video_count = num_files;

    if ((fsi = malloc( sizeof( fileset_info_struct ) )) == NULL) {
	printf( "Unable to allocate library information array.\n" );
	exit( 1 );
    }
    fsi->num_files = num_files;
    fsi->li = log_info;
    fsi->file_MB_rate = log_info->client_MB_per_request / log_info->file_seconds_per_request;

    /* create array of file information from library */
    if ((fsi->fi = malloc( num_files * sizeof( file_info_struct ) )) == NULL) {
	printf( "Unable to allocate file information array.\n" );
	exit( 1 );
    }

    /* create array ranks */
    if ((fsi->video_rank = malloc( num_files * sizeof( int ) )) == NULL) {
	printf( "Unable to allocate video rank array.\n" );
	exit( 1 );
    }

    /* populate video_dur, video_bytes and video_rank arrays from the file */
    rewind( f );
    total_duration = 0.0;
    i=0;
    while (i < lib_size) {
        if (fgets( file_line, sizeof( file_line ), f ) == NULL) {
	    printf( "Error %d reading video library file \"%s\"\n", errno, filename );
	    exit( 1 );
        }
	if (file_line[0] == '#')
	    continue;
    
	if (header_version == 0) {
	    if (sscanf( file_line, "%d %d %lf %d %lf %d %d",
			    &file_num, &(fsi->fi[i].size), &dur_seconds, &dur_chunks,
			    &sess_dur, &sess_chunks, &(fsi->video_rank[i]) ) != 7) {
		printf( "Error in video file format: %s\n", file_line );
		exit( 1 );
	    }
	}
	else {
	    if (sscanf( file_line, "%d %d %lf %lf %d",
			    &file_num, &(fsi->fi[i].size), &dur_seconds,
			    &sess_dur, &(fsi->video_rank[i]) ) != 5) {
		printf( "Error in video file format: %s\n", file_line );
		exit( 1 );
	    }
	}

	fsi->fi[i].file_number = i;
	fsi->fi[i].video_dur = (double) fsi->fi[i].size / (fsi->file_MB_rate * BYTES_PER_MB);
	if (fsi->fi[i].video_dur > max_duration)
	    fsi->fi[i].video_dur = max_duration;
	fsi->fi[i].fsi = fsi;

	/* update duration histogram */
	bin = fsi->fi[i].video_dur / VIDEO_DUR_HISTOGRAM_BINSIZE;
	if (bin >= VIDEO_DUR_HISTOGRAM_SIZE)
	    bin = VIDEO_DUR_HISTOGRAM_SIZE;
	video_duration_histogram[bin]++;
	video_duration_histogram_points++;

	/* call random() so that random values used later will be the same as when we originally generated these videos */
	random();
	total_duration += fsi->fi[i].video_dur;
	++i;
    }
    fclose( f );

    /* write out the file duration CDF */
    write_file_duration_CDF();

    average_video_MB = total_duration / num_files * fsi->file_MB_rate;

    return( fsi );
}

#ifdef NOT_USED
/*
 * clean up library information
 */
static void free_video_library( fileset_info_struct *fsi )
{
    if (fsi != NULL) {
	fsi->num_files = 0;
	free( fsi->fi );
	free( fsi->video_rank );
	free( fsi->video_index );
	free( fsi );
    }
}
#endif

/*
 * Create the video_index array, after the video_rank array is initialized.
 */
static void make_video_index( fileset_info_struct *fsi )
{
    int i;

    /* create the inverse mapping, video indices in rank order */
    if ((fsi->video_index = malloc( fsi->num_files * sizeof( int ) )) == NULL) {
	printf( "Unable to allocate video index array.\n" );
	exit( 1 );
    }
    for (i=0;i < fsi->num_files;++i) {
	fsi->video_index[ fsi->video_rank[i] ] = i;
    }
}


int main(int argc, char *argv[])
{
    int rank;
    int i, j;
    double duration;
    double *max_session_time;
    double *sum_session_time;
    double total_session_time;
    double total_unique_session_time;
    double total_duration;
    int num_ranks;
    int total_num_multi;
    int total;
    FILE *f;
    fileset_info_struct *fileset_info;
    session_info_struct *session_info;
    logfile_info_struct *log_info;

    read_config();

    /* create text file that can be used for gnuplot titles */
    if ((f = fopen( "make_zipf_description.txt", "w" )) == NULL) {
	printf( "Error opening file %d\n", errno );
	exit( 1 );
    }
    num_sessions = num_log_files * num_log_sessions;
    fprintf( f, "%d videos, %d sessions, Zipf alpha %.3lf\n",
		library_size, num_sessions, zipf_exponent );
    fclose( f );

    srandom( random_seed );

    /* determine number of filesets supplied on the command line */
    num_filesets = argc - 1;
    if (num_filesets < 1)
	num_filesets = 1;
    if ((fileset_list = malloc( num_filesets * sizeof( fileset_info_struct * ) )) == NULL) {
	printf( "Unable to allocate fileset list.\n" );
	exit( 1 );
    }

    /* create or read multiple filesets of videos */
    if (argc <= 1) {
	if ((fileset_list[0] = create_video_library( library_size )) == NULL) {
	    printf( "Error creating video library.\n" );
	    exit( 1 );
	}
	make_video_index( fileset_list[0] );
	num_videos = library_size;
	
	fileset_list[0]->li = &(logfile_info[ video_quality ]);
    }
    else {
	for (i=0;i<num_filesets;++i) {

	    log_info = &logfile_info[video_quality];

	    if ((fileset_list[i] = read_video_library( argv[i+1], log_info, &num_videos )) == NULL) {
		printf( "Error reading video library %s.\n", argv[i+1] );
		exit( 1 );
	    }
	}
	num_videos = fileset_list[0]->num_files;
    }

    /* initialize other per-fileset information */
    for (i=0;i<num_filesets;++i) {
	make_video_index( fileset_list[i] );

	/* setup popularity distribution */
	if ((fileset_list[i]->pdf = malloc( fileset_list[i]->num_files * sizeof( double ) )) == NULL) {
	    printf( "Unable to allocate rank pdf array.\n" );
	    exit( 1 );
	}
	make_zipf_distribution( fileset_list[i]->pdf, fileset_list[i]->num_files );
	/* make_weibull_distribution( fileset_list[i]->pdf, fileset_list[i]->num_files ); */
    }

    /* statistics about the number of views for each video */
    typedef struct {
	rank_struct *rank_views;
	int num_unique;
	int num_multi;
    } session_view_stats_struct;
    session_view_stats_struct *session_viewstats, *svs;
    int fs_num;

    /* The goal of the next section is to ensure that files are assigned in */
    /* strict rank order.  That is, a file with a higher rank number should */
    /* never be viewed more times than one with a lower rank number.	    */
    /* Note that we need to track rank information for each different	    */
    /* fileset.  We also track the number of videos that are viewed only    */
    /* once.								    */

    /* create storage for viewing statistics */
    if ((session_viewstats = calloc( num_filesets, sizeof( session_view_stats_struct ) )) == NULL) {
	printf( "Unable to allocate session_view_stats array.\n" );
	exit( 1 );
    }
    for (i=0;i<num_filesets;++i) {
	if ((session_viewstats[i].rank_views = calloc( fileset_list[i]->num_files + 1, sizeof( rank_struct ) )) == NULL) {
	    printf( "Unable to allocate rank_views array.\n" );
	    exit( 1 );
	}
	for (j=0;j <= fileset_list[i]->num_files; ++j) {
	    session_viewstats[i].rank_views[j].index = j;
	}
	session_viewstats[i].num_unique = 0;
	session_viewstats[i].num_multi = 0;
    }

    /* create a list of session */
    if ((session_info = calloc( num_sessions, sizeof( session_info_struct ) )) == NULL) {
	printf( "Unable to allocate session_info array.\n" );
	exit( 1 );
    }

    /* select a random set of ranks from the sample information */
    for (i=0;i < num_sessions;++i) {
	session_info[i].client_id = i % num_log_files;

	/* choose fileset */
	fs_num = 0; 
	fileset_info = fileset_list[fs_num];
    	session_info[i].fsi = fileset_info;
	svs = &session_viewstats[fs_num];

	double p;

        /* pick a random number and use to pick a sample rank */
        p = random() / (1.0 + INT_MAX);
        for (rank=0; rank < fileset_info->num_files; ++rank) {
            p -= fileset_info->pdf[rank];
            if (p < 0.0) {
                break;
            }
        }

	/* update viewing stats */
        if (svs->rank_views[rank].num_views == 0) {
            svs->num_unique++;
        }
        else if (svs->rank_views[rank].num_views == 1) {
            svs->num_multi++;
        }
        session_info[i].rank = rank;
        svs->rank_views[rank].num_views++;
    }

    rank_struct *rank_unsorted;
    char dist_filename[100];

    num_ranks = 0;
    total_num_multi = 0;
    for (fs_num=0;fs_num<num_filesets;++fs_num) {
	svs = &session_viewstats[fs_num];
	fileset_info = fileset_list[fs_num];

	/* check number of warming files specified, and adjust so that */
	/* no one-time view videos are in the warming set.	       */
	if (fileset_info->li->num_warm_files > svs->num_multi)
	    fileset_info->li->num_warm_files = svs->num_multi;
	else if (fileset_info->li->num_warm_files > svs->num_unique)
	    fileset_info->li->num_warm_files = svs->num_unique;

	/* copy the unsorted ranks */
	if ((rank_unsorted = malloc( (fileset_info->num_files + 1) * sizeof( rank_struct ) )) == NULL) {
	    printf( "Unable to allocate rank_unsorted array.\n" );
	    exit( 1 );
	}
	memcpy( rank_unsorted, svs->rank_views, (fileset_info->num_files + 1) * sizeof( rank_struct ) );

	/* sort the rank array by number of views */
	qsort( svs->rank_views, fileset_info->num_files, sizeof( rank_struct ), compare_rank_views );

	/* output the distribution by rank */
	if (fs_num == 0)
	    strcpy( dist_filename, "distribution.txt" );
        else {
	    //snprintf( dist_filename, sizeof( dist_filename ), "dist%d.txt", fs_num );
	    strcpy( dist_filename, "distribution.txt" );
	    dist_filename[11] = '0' + fs_num;
        }
	if ((f = fopen( dist_filename, "w" )) == NULL) {
	    printf( "Error opening file %d\n", errno );
	    exit( 1 );
	}
	fprintf( f, "# video_rank num_sessions unsorted_num_sessions\n" );
	i=0;
	for (rank=0; rank < svs->num_unique; ++rank) {
	    assert( svs->rank_views[rank].num_views > 0 );
	    fprintf( f, "%d %d", rank + 1, svs->rank_views[rank].num_views );

	    /* also output number of views from the unsorted list */
	    while (i < fileset_info->num_files && rank_unsorted[i].num_views == 0) {
		i++;
	    }
	    fprintf( f, " %d\n", rank_unsorted[i].num_views );
	    i++;
	}
	fclose( f );
	free( rank_unsorted );

	/* change the indices for the videos to match the sorted rank order */
	for (i=0;i < num_sessions;++i) {

	    /* process only the current fileset */
	    if (session_info[i].fsi == fileset_info) {
		for (rank=0; rank < fileset_info->num_files; ++rank) {
		    if (svs->rank_views[rank].index == session_info[i].rank) {
			session_info[i].rank = rank;
			break;
		    }
		}
		assert( rank < fileset_info->num_files );
	    }
	}

	num_ranks += svs->num_unique;
	total_num_multi += svs->num_multi;
    } /* for (fs_num=0;fs_num<num_filesets;++fs_num) */


    /* track the number of sessions of each length (up to 1000 seconds) */
#define SESSION_HISTOGRAM_SIZE 1000
#define SESSION_HISTOGRAM_BINSIZE 1
    int	session_histogram[SESSION_HISTOGRAM_SIZE];
    int session_histogram_points = 0;
    for (i=0;i<SESSION_HISTOGRAM_SIZE;++i) {
	session_histogram[i] = 0;
    }

    /* track the total and maximum session time for each file */
    if ((sum_session_time = calloc( num_ranks, sizeof( double ) )) == NULL) {
	printf( "Unable to allocate sum_session_time array.\n" );
	exit( 1 );
    }
    if ((max_session_time = calloc( num_ranks, sizeof( double ) )) == NULL) {
	printf( "Unable to allocate max_session_time array.\n" );
	exit( 1 );
    }

    /* create sessions */
    double frac;
    total_session_time = 0.0;
    for (i=0;i < num_sessions;++i) {
	fileset_info = session_info[i].fsi;
        rank = session_info[i].rank;
	session_info[i].fi = &(fileset_info->fi[ fileset_info->video_index[ rank ] ]);

        /* determine how long the video will be viewed */
        frac = compute_session_frac( random() / (1.0 + INT_MAX) );
        duration = session_info[i].fi->video_dur * frac;

	if (session_time_chunking > 1) {
	    /* round to nearest chunk size */
	    if (duration < session_time_chunking)
		session_info[i].duration = duration;
	    else
		session_info[i].duration = (int) (duration / session_time_chunking + 0.5) * session_time_chunking;
	}
	else {
	    session_info[i].duration = duration;
	}
        total_session_time += duration;
        sum_session_time[rank] += duration;
        if (max_session_time[rank] < session_info[i].duration) {
            max_session_time[rank] = session_info[i].duration;
        }
        if (frac < 1.0) {
            session_histogram_points++;
            j = (int) (duration / SESSION_HISTOGRAM_BINSIZE);
            if (j < SESSION_HISTOGRAM_SIZE) {
                session_histogram[ j ]++;
            }
            else {
                session_histogram[ SESSION_HISTOGRAM_SIZE - 1 ]++;
            }
        }
    }

    /* output the histogram of session lengths */
    double total_frac;
    f = fopen( "session_histogram.txt", "w" );
    if (f == NULL) {
	printf( "Error opening file %d\n", errno );
	exit( 1 );
    }
    fprintf( f, "# session_time PDF CDF\n" );
    fprintf( f, "# histogram num points = %d of %d\n", session_histogram_points, num_sessions );
    total_frac = 0.0;
    for (i=0;i<SESSION_HISTOGRAM_SIZE;++i) {
	total_frac += (double) session_histogram[i] / session_histogram_points;
	fprintf( f, "%d %.3f %.2f\n", (i + 1) * SESSION_HISTOGRAM_BINSIZE, (double) session_histogram[i] / session_histogram_points, total_frac );
    }
    fclose( f );

    /* output file with information about all videos in the library */
    f = fopen( "video_files.txt", "w" );
    if (f == NULL) {
	printf( "Error opening file %d\n", errno );
	exit( 1 );
    }
    fprintf( f, "# num length(B) length(s) max_session(s) rank sum_sessions(s)\n" );

    int library_duration;

    /* output a description of each video, including summary information */
    /* about sessions in the generated workload.			 */
    library_duration = 0;
    fileset_info = fileset_list[0];
    for (i=0; i < num_videos; ++i) {
	rank = fileset_info->video_rank[i];
	library_duration += fileset_info->fi[i].video_dur;

	/* round time if specified */
	if (video_time_chunking > 1) {
	    duration = ceil( fileset_info->fi[i].video_dur / video_time_chunking ) * video_time_chunking;
	}
	else {
	    duration = fileset_info->fi[i].video_dur;
	}
	if (rank < num_ranks) {
	    fprintf( f, "%05d %9.0f %8.1f %8.1f %8d %8.1f\n", i,
			duration * (fileset_info->file_MB_rate * BYTES_PER_MB), fileset_info->fi[i].video_dur,
			max_session_time[rank], rank, sum_session_time[rank] );
	} else {
	    fprintf( f, "%05d %9.0f %8.1f %8.1f %8d %8.1f\n", i,
			duration * (fileset_info->file_MB_rate * BYTES_PER_MB), fileset_info->fi[i].video_dur,
			0.0, rank, 0.0 );
	}
    }
    fclose( f );

    /* create cumulative total of video memory requirements, in rank order */
    f = fopen( "memory.txt", "w" );
    if (f == NULL) {
	printf( "Error opening file %d\n", errno );
	exit( 1 );
    }
    fprintf( f, "# video_rank video_MB_CDF session_MB_CDF num_sessions_CDF video_MB_period\n" );
    int total_sessions;
    int	num_onetimers;
    total_unique_session_time = 0.0;
    total_duration = 0;
    total_sessions = 0;
    max_session = 0.0;
    num_onetimers = 0;
    fileset_info = fileset_list[0];
    svs = &session_viewstats[0];
    //for (i=0;i<num_ranks;++i) {
    for (i=0;i<svs->num_unique;++i) {
	if (max_session < max_session_time[i]) {
	    max_session = max_session_time[i];
	}
	total_unique_session_time += max_session_time[i];
	total_duration += fileset_info->fi[ fileset_info->video_index[i] ].video_dur;
	total_sessions += svs->rank_views[i].num_views;
	if (svs->rank_views[i].num_views == 1)
	    num_onetimers++;
	fprintf( f, "%d %.1f %.1f %.3f %.1f\n", i + 1, total_duration * fileset_info->file_MB_rate,
						total_unique_session_time * fileset_info->file_MB_rate,
						(double) total_sessions / num_sessions,
						average_video_MB / svs->rank_views[i].num_views * num_sessions );
    }
    fclose( f );

    /* write out the session fraction histogram */
    if ((f = fopen( "session_frac_histogram.txt", "w" )) == NULL) {
	printf( "Error opening file %d\n", errno );
	exit( 1 );
    }
    fprintf( f, "# session_time_fraction session_CDF\n" );
    total = 0;
    for (i=0;i<SESSION_FRAC_HISTOGRAM_SIZE;++i) {
	total += session_frac_histogram[i];
	fprintf( f, "%.3f %.3f\n", (double) (i + 1) * SESSION_FRAC_HISTOGRAM_BINSIZE, (double) total / session_frac_histogram_points );
    }
    fclose( f );

    /* dump sessions to a file and create a file containing per-session information */
    f = fopen( "session_info.txt", "w" );
    if (f == NULL) {
	printf( "Error opening file %d\n", errno );
	exit( 1 );
    }
    fprintf( f, "# video_rank session_time bit_rate num_requests\n" );
    for (i=0;i<SESSION_HISTOGRAM_SIZE;++i) {
	session_histogram[i] = 0;
    }
    num_client_chunks_requested = 0;
    int num_requests;
    for (i=0;i < num_sessions;++i) {
	num_requests = ceil( session_info[i].duration / session_info[i].fsi->li->file_seconds_per_request );
	num_client_chunks_requested += num_requests;
	fprintf( f, "%d %.3f %.0f %d\n", session_info[i].rank + 1, session_info[i].duration, session_info[i].fsi->file_MB_rate, num_requests );
    }
    fclose( f );

    /* NOTE: clients choose from a number of videos.  Each video has a different duration */
    /* and each client watches the video for a different session time.			  */
    /* The client requests the video content in a series of fixed-time requests.	  */
    num_header_lines = 0;
    sprintf( header_line[num_header_lines++], "random seed = %ld", random_seed );
    puts( header_line[num_header_lines-1] );
    sprintf( header_line[num_header_lines++], "Zipf exponent = %0.4f", zipf_exponent );
    puts( header_line[num_header_lines-1] );
    sprintf( header_line[num_header_lines++], "number of videos in library = %d", library_size );
    puts( header_line[num_header_lines-1] );
    sprintf( header_line[num_header_lines++], "number of sessions = %d (%d x %d)", num_sessions, num_log_files, num_log_sessions );
    puts( header_line[num_header_lines-1] );
    printf( "\n" );
    sprintf( header_line[num_header_lines++], "seconds of video per client request = %.2f s", fileset_list[0]->li->file_seconds_per_request );
    puts( header_line[num_header_lines-1] );
    sprintf( header_line[num_header_lines++], "size of client requests = %.2f MB", fileset_list[0]->li->client_MB_per_request );
    puts( header_line[num_header_lines-1] );
    sprintf( header_line[num_header_lines++], "file bit rate = %.3f Mbps", fileset_list[0]->file_MB_rate * (BYTES_PER_MB * 8) / 1000000 );
    puts( header_line[num_header_lines-1] );
    sprintf( header_line[num_header_lines++], "timeout for request delivery = %.2f s", timeout_per_request );
    puts( header_line[num_header_lines-1] );
    sprintf( header_line[num_header_lines++], "number of unpaced requests at session start = %d", num_buffering_requests );
    puts( header_line[num_header_lines-1] );
    printf( "\n" );
    sprintf( header_line[num_header_lines++], "total number of unique videos = %d", num_ranks );
    puts( header_line[num_header_lines-1] );
    sprintf( header_line[num_header_lines++], "total size of library = %.0f MB", library_duration * fileset_list[0]->file_MB_rate );
    puts( header_line[num_header_lines-1] );
    sprintf( header_line[num_header_lines++], "total size of viewed videos = %.0f MB", total_duration * fileset_list[0]->file_MB_rate );
    puts( header_line[num_header_lines-1] );
    sprintf( header_line[num_header_lines++], "percentage of single-request videos = %.1f %%", 100.0 * num_onetimers / num_ranks );
    puts( header_line[num_header_lines-1] );
    sprintf( header_line[num_header_lines++], "average views per video = %.1f", (double) num_sessions / num_ranks );
    puts( header_line[num_header_lines-1] );
    sprintf( header_line[num_header_lines++], "average duration of videos = %.1f s", total_duration / num_ranks );
    puts( header_line[num_header_lines-1] );
    sprintf( header_line[num_header_lines++], "average size of videos = %.1f MB", average_video_MB );
    puts( header_line[num_header_lines-1] );
    printf( "\n" );

    /* chart amount of memory required to store these numbers of videos */
    /* make one of the sizes equal to the number of videos requested more than once */
    static int chart_num_videos[] = { 5, 10, 50, 100, 500, 5000, 0, 0  };
    i = 0;
    while (chart_num_videos[i] != 0) {
	i++;
    }
    chart_num_videos[i] = total_num_multi;

    i = 0;
    printf( "Top Video Popularity:\n" );
    printf( "# files  size (MB)  percent of sessions\n" );
    while (chart_num_videos[i] > 0) {
	total = 0;
	for (j=0;j<chart_num_videos[i];++j) {
	    if (j >= num_ranks) {
		break;
	    }
	    total += session_viewstats[0].rank_views[j].num_views;
	}
	printf( "%7d  %9.1f  %5.1f\n", j, j * average_video_MB, 100.0 * total / num_sessions );
        i++;
    }
    printf( "\n" );

    /* printf( "total time of sessions = %.0f s\n", total_session_time ); */
    sprintf( header_line[num_header_lines++], "maximum session time = %.0f s", max_session );
    puts( header_line[num_header_lines-1] );
    sprintf( header_line[num_header_lines++], "maximum number of client requests in session = %.0f", max_session / fileset_list[0]->li->file_seconds_per_request );
    puts( header_line[num_header_lines-1] );
    /* printf( "total time of unique requests = %.0f s\n", total_unique_session_time ); */
    sprintf( header_line[num_header_lines++], "total number of client requests = %d", num_client_chunks_requested );
    puts( header_line[num_header_lines-1] );

    double chunks_per_session;
    chunks_per_session = (double) num_client_chunks_requested / num_sessions;
    printf( "\n" );
    sprintf( header_line[num_header_lines++], "average duration of session = %.1f s", total_session_time / num_sessions );
    puts( header_line[num_header_lines-1] );
    sprintf( header_line[num_header_lines++], "average size of session = %.1f MB", chunks_per_session * fileset_list[0]->li->client_MB_per_request );
    puts( header_line[num_header_lines-1] );
    sprintf( header_line[num_header_lines++], "average client requests per session = %.3f", chunks_per_session );
    puts( header_line[num_header_lines-1] );

    assert( num_header_lines <= MAX_HEADER_LINES );

    create_log_files( session_info, num_videos );

    printf( "\n" );
    static int req_sizes[] = { 5, 10, 20, 50, 100, 200, 500, 750, 900, 0 };
    i = 0;
    printf( "Experiment statistics:\n" );
    printf( "req/s   Mbps   Time (minutes)\n" );
    while (req_sizes[i] > 0) {
	printf( "%5d %6.0f %7.1f\n", req_sizes[i],
		    req_sizes[i] * fileset_list[0]->li->client_MB_per_request * 8,
		    (double) num_client_chunks_requested / req_sizes[i] / 60 );
        i++;
    }

    return( 0 );
}

/* Helper function for the qsort() routine.  We want to sort the chunk uses
 * in descending order, so return a negative number when the first item is
 * larger than the second.
 */
typedef struct {
    int	file_number;	/* id number for this file */
    int	owner_rank;	/* rank of video that uses this chunk */
    int	num_uses;	/* number of times this chunk is used */
    int	order;		/* order that chunks are seen the first time */
} request_info_struct;
static int compare_chunk_uses( const void *c1, const void *c2 )
{
    return( ((request_info_struct *) c2)->num_uses - ((request_info_struct *) c1)->num_uses );
}

/* Helper function for the qsort() routine.  This sorts chunk id
 * ascending order.
 */
static int compare_file_numbers( const void *c1, const void *c2 )
{
    return( ((request_info_struct *) c1)->file_number - ((request_info_struct *) c2)->file_number );
}

/* Helper function for the qsort() routine.  This sorts chunks in the order that they
 * were first viewed.
 */
static int compare_chunk_order( const void *c1, const void *c2 )
{
    return( ((request_info_struct *) c1)->order - ((request_info_struct *) c2)->order );
}

/* avoid making client chunks that are shorter than this number of bytes */
#define MINIMUM_CLIENT_CHUNK 100

/* Output a series of range requests needed to transfer the specified number of
 * bytes.  The num_session_requests value counts the number of client chunks that
 * have been used for the session, and is used to control when pacing occurs.
 */
static void output_chunk_requests( FILE *f, fileset_info_struct *fsi, int rank, int *num_session_requests,
				    int file_number, int request_num_bytes, int request_offset,
				    int timeout, int pace_time )
{
    int offset, end;
    int chunk_end;
    char *basename;
    int client_byte_request_size;

    /* Note: there are two ways to interpret request offsets, either all requests are offset    */
    /* by this amount and are the same size as when the offset is 0, or the first request is    */
    /* offset, and the rest start on multiples of the client_byte_request_size (and the sizes	*/
    /* of the first and last requests will be different than if the offset were 0.		*/
#ifdef FIXED_OFFSET
    offset = 0;
#else
    offset = request_offset;
    request_num_bytes += request_offset;
#endif

    if (rank < fsi->li->num_warm_files)
	basename = fsi->li->log_warm_basename;
    else
	basename = fsi->li->log_cold_basename;
    client_byte_request_size = fsi->li->client_MB_per_request * BYTES_PER_MB;

    /* assume HTTP ranges include the endpoints */
    end = offset + client_byte_request_size - 1;

    /* output series of range requests using the client chunk size */
    do {

	/* adjust size of final chunk */
	if (end >= request_num_bytes - MINIMUM_CLIENT_CHUNK) {
	    end = request_num_bytes - 1;
	    chunk_end = end;
	}
#ifdef VBR_CHUNKS
	else {
	    /* adjust end of chunk to change chunk size by +- VBR_FACTOR */
	    chunk_end = end + (2 * VBR_FACTOR * random() / (1.0 + INT_MAX) - VBR_FACTOR) * client_byte_request_size;

	    /* if chunk extends beyond end of file, adjust size */
	    if (chunk_end > request_num_bytes - MINIMUM_CLIENT_CHUNK) {

		/* make chunk half as big, if possible */
		chunk_end = (chunk_end + offset) / 2;
		if (chunk_end > request_num_bytes - MINIMUM_CLIENT_CHUNK) {
		    chunk_end = request_num_bytes - 1;
		}
	    }
	}
#else
	chunk_end = end;
#endif
//printf( "chunk: offset %d chunk_end %d end %d blk_size %.2f %%\n", offset, chunk_end, end, 100.0 * (chunk_end - offset) / client_byte_request_size );

	/* make name like: /affinity_set_small2/100Mb-02011.txt	*/
	/* NOTE: Apache reports a "Bad Request" if the leading '/' is missing from URIs */
	(*num_session_requests)++;
#ifdef FIXED_OFFSET
	if (*num_session_requests > num_buffering_requests)
	    fprintf( f, "/%s-%0*d.mp4 pace_time=%d timeout=%d headers='Range: bytes=%d-%d'\n",
				basename, fsi->li->fname_num_digits, file_number, pace_time, timeout,
				offset + request_offset, chunk_end + request_offset );
        else
	    fprintf( f, "/%s-%0*d.mp4 timeout=%d headers='Range: bytes=%d-%d'\n",
				basename, fsi->li->fname_num_digits, file_number, timeout,
				offset + request_offset, chunk_end + request_offset );
#else
	if (*num_session_requests > num_buffering_requests)
	    fprintf( f, "/%s-%0*d.mp4 pace_time=%d timeout=%d headers='Range: bytes=%d-%d'\n",
				basename, fsi->li->fname_num_digits, file_number, pace_time, timeout,
				offset, chunk_end );
        else
	    fprintf( f, "/%s-%0*d.mp4 timeout=%d headers='Range: bytes=%d-%d'\n",
				basename, fsi->li->fname_num_digits, file_number, timeout,
				offset, chunk_end );
#endif
	offset = chunk_end + 1;
	end += client_byte_request_size;
    } while (offset < request_num_bytes);
}

static int create_log_files( session_info_struct *session_info, int total_num_videos )
{
    int *num_log_requests;
    char out_fname[200];
    int file_number;
    FILE *f;
    int i, j;
    int s;
    request_info_struct *request_info;
    int single_chunk;
    int num_chunks_used;
    logfile_info_struct *log_info;

    if ((num_log_requests = calloc( num_log_files, sizeof( int ) )) == NULL) {
	printf( "Unable to allocate num_log_requests array.\n" );
	exit( 1 );
    }

    /* compute number of requests in each log file */
    printf( "client requests per log file: " );
    s = 0;
    for (j=0; j < num_log_sessions; ++j) {
	for (i=0; i < num_log_files; ++i) {
	    num_log_requests[i] += ceil( session_info[s].duration / session_info[s].fsi->li->file_seconds_per_request );
	    s++;
	}
    }
    for (i=0; i < num_log_files; ++i) {
	printf( " %d", num_log_requests[i] );
    }
    printf( "\n" );

    /* array to determine the popularity of different videos */
    if ((request_info = calloc( total_num_videos, sizeof( request_info_struct ) )) == NULL) {
	printf( "Unable to allocate request_info array.\n" );
	exit( 1 );
    }
    for (i=0;i<total_num_videos;++i) {
	request_info[i].file_number = i;
	request_info[i].owner_rank = fileset_list[0]->video_rank[i];
    }


    /* create log files to hold the requests. */
    double timeout;
    int request_bytes;
    int request_offset;
    int video_order = 0;
    int num_session_requests;
    double sess_time;
    for (i=0; i < num_log_files; ++i) {

 	/* create a name like: cl-zipf4-2500-10-300-00.log */
	snprintf( out_fname, sizeof( out_fname ), "%s-%s-%d-%.0f-%.0f-%d-%02d.log", log_basename, video_quality_str,
				num_videos, 5000.0 / fileset_list[0]->li->client_MB_per_request,
				fileset_list[0]->li->file_seconds_per_request, num_log_sessions, i );
        f = fopen( out_fname, "w" );
	if (f == NULL) {
	    printf( "Error opening file %d\n", errno );
	    exit( 1 );
	}

	/* add header to log file */
	for (j=0; j < num_header_lines; ++j) {
	    fprintf( f, "# %s\n", header_line[j] );
	}
	fprintf( f, "# number of chunks in log file = %d\n", num_log_requests[i] );
	fprintf( f, "# average chunks per session in log file = %.2f\n",
				(double) num_log_requests[i] / num_log_sessions );


	/* go through session information, finding sessions for this client */
	s = 0;
	for (;;) {

	    /* find next session with matching client */
	    //while (session_info[s].client_id != i) {
		if (s >= num_sessions) {

		    /* break out of this loop and outer loop */
		    goto next_client;
		}
	    //}

	    /* determine video file number and time for session */
	    file_number = session_info[s].fi->file_number;
	    sess_time = session_info[s].duration;

	    /* adjust timeout based on session time */
	    if (timeout_per_request > sess_time) {
		timeout = sess_time;
	    }
	    else {
		timeout = timeout_per_request;
	    }
	    if (timeout < min_timeout)
		timeout = min_timeout;

	    /* determine size of session in bytes as well as the file size */
	    /* and adjust offset accordingly.				   */
	    request_bytes = sess_time * session_info[s].fsi->file_MB_rate * BYTES_PER_MB;
	    num_session_requests = 0;
#if defined( FIXED_OFFSET ) | defined( MIDPOINT_OFFSET ) | defined( ENDPOINT_OFFSET )
	    int file_size;
	    file_size = session_info[s].fi->size;
#endif
#if defined( FIXED_OFFSET )
	    if (file_size - request_bytes > FIXED_OFFSET_BYTES) {
		request_offset = FIXED_OFFSET_BYTES;
	    }
	    else {
		request_offset = 0;
	    }
#elif defined( MIDPOINT_OFFSET )
	    request_offset = (file_size - request_bytes) / 2;
#elif defined( ENDPOINT_OFFSET )
	    request_offset = file_size - request_bytes;
#else
	    request_offset = 0;
#endif

	    /* output a sequence of range requests to read this video */
	    output_chunk_requests( f, session_info[s].fsi, session_info[s].rank,
					&num_session_requests, file_number, request_bytes,
					request_offset, (int) timeout, (int) timeout_per_request );
	    request_info[file_number].num_uses++;
	    request_info[file_number].order = ++video_order;

	    /* mark end of session */
	    fputs( "\n", f );

	    /* look at next session */
	    s++;
	}
next_client:
	fclose( f );
    }

    /* sort the chunk array by order first viewed */
    qsort( request_info, total_num_videos, sizeof( request_info[0] ), compare_chunk_order );

    /* output file consisting of all one-timer chunks, in the order they occur */
    f = fopen( "one_time_chunks.log", "w" );
    if (f == NULL) {
	printf( "Error opening file %d\n", errno );
	exit( 1 );
    }
    for (i=0;i<total_num_videos;++i) {
	if (request_info[i].num_uses == 1) {
	    file_number = request_info[i].file_number;
	    log_info = fileset_list[0]->li;
	    fprintf( f, "%s-%0*d.txt\n", log_info->log_cold_basename, log_info->fname_num_digits, file_number );
	}
    }
    fclose( f );

    /* sort the chunk array by number of uses */
    qsort( request_info, total_num_videos, sizeof( request_info[0] ), compare_chunk_uses );

    /* output chunk information for plotting and compute the fraction of */
    /* all requests that appear in the warming file.			 */
    int total_num_requests;
    f = fopen( "chunk_use.txt", "w" );
    if (f == NULL) {
	printf( "Error opening file %d\n", errno );
	exit( 1 );
    }
    fprintf( f, "# chunk_id chunk_frequency video_rank\n" );
    total_num_requests = 0;
    single_chunk = total_num_videos;
    num_chunks_used = total_num_videos;
    for (i=0;i<total_num_videos;++i) {
	fprintf( f, "%5d %4d %5d\n", request_info[i].file_number, request_info[i].num_uses,
					request_info[i].owner_rank );

	/* determine when the single use chunks start */
	if (request_info[i].num_uses == 1) {
	    if (single_chunk == total_num_videos)
		single_chunk = i;
	}
	if (request_info[i].num_uses == 0) {
	    if (num_chunks_used == total_num_videos)
		num_chunks_used = i;
	}
	total_num_requests += request_info[i].num_uses;
    }
    fclose( f );
    printf( "number of chunks viewed once = %d\n", total_num_videos - single_chunk );

    /* output file with all the warm chunks */
    /* create a name like: fs-zipf4-2500-10-12000 */
    int num_warm_chunks;
    num_warm_chunks = fileset_list[0]->li->num_warm_files;
    snprintf( out_fname, sizeof( out_fname ), "%s-%d-%.0f-%d-%d-warm", preload_basename,
			    num_videos, 5000.0 / fileset_list[0]->li->client_MB_per_request, num_log_sessions,
			    num_warm_chunks );
    f = fopen( out_fname, "w" );
    if (f == NULL) {
	printf( "Error opening file %d\n", errno );
	exit( 1 );
    }

    /* sort the warm chunks by chunk id to make preloading faster */
    int num_warm_requests = 0;
    qsort( request_info, num_warm_chunks, sizeof( request_info[0] ), compare_file_numbers );
    for (i=0; i < num_warm_chunks; ++i) {
	file_number = request_info[i].file_number;
	log_info = fileset_list[0]->li;
	fprintf( f, "%s-%0*d.txt\n", log_info->log_warm_basename, log_info->fname_num_digits, file_number );
	num_warm_requests += request_info[i].num_uses;
    }
    fclose( f );
    printf( "number of chunks to preload = %d\n", num_warm_chunks );
    printf( "percent of chunk requests preloaded = %.1f %%", 100.0 * num_warm_requests / total_num_requests );

    /* output file with all the cold chunks */
    snprintf( out_fname, sizeof( out_fname ), "%s-%d-%.0f-%d-%d-cold", preload_basename,
			    num_videos, 5000.0 / fileset_list[0]->li->client_MB_per_request, num_log_sessions,
			    num_warm_chunks );
    f = fopen( out_fname, "w" );
    if (f == NULL) {
	printf( "Error opening file %d\n", errno );
	exit( 1 );
    }

    /* sort the cold chunks by chunk id */
    if (num_warm_chunks < num_chunks_used) {
	qsort( request_info + num_warm_chunks, num_chunks_used - num_warm_chunks, sizeof( request_info[0] ), compare_file_numbers );
	for (i=num_warm_chunks; i < num_chunks_used; ++i) {
	    file_number = request_info[i].file_number;
	    log_info = fileset_list[0]->li;
	    fprintf( f, "%s-%0*d.txt\n", log_info->log_cold_basename, log_info->fname_num_digits, file_number );
	}
    }
    fclose( f );

    return( 0 );
}


