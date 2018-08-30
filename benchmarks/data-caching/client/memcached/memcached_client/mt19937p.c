/* A C-program for MT19937: Real number version                */
/*   genrand() generates one pseudorandom real number (double) */
/* which is uniformly distributed on [0,1]-interval, for each  */
/* call. sgenrand(seed) set initial values to the working area */
/* of 624 words. Before genrand(), sgenrand(seed) must be      */
/* called once. (seed is any 32-bit integer except for 0).     */
/* Integer generator is obtained by modifying two lines.       */
/*   Coded by Takuji Nishimura, considering the suggestions by */
/* Topher Cooper and Marc Rieffel in July-Aug. 1997.           */

/* This library is free software; you can redistribute it and/or   */
/* modify it under the terms of the GNU Library General Public     */
/* License as published by the Free Software Foundation; either    */
/* version 2 of the License, or (at your option) any later         */
/* version.                                                        */
/* This library is distributed in the hope that it will be useful, */
/* but WITHOUT ANY WARRANTY; without even the implied warranty of  */
/* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.            */
/* See the GNU Library General Public License for more details.    */
/* You should have received a copy of the GNU Library General      */
/* Public License along with this library; if not, write to the    */
/* Free Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA   */ 
/* 02111-1307  USA                                                 */

/* Copyright (C) 1997 Makoto Matsumoto and Takuji Nishimura.       */
/* When you use this, send an email to: matumoto@math.keio.ac.jp   */
/* with an appropriate reference to your work.                     */

#include<stdio.h>
#include "mt19937p.h"

/* Period parameters */  
#define N 624
#define M 397
#define MATRIX_A 0x9908b0df   /* constant vector a */
#define UPPER_MASK 0x80000000 /* most significant w-r bits */
#define LOWER_MASK 0x7fffffff /* least significant r bits */

/* Tempering parameters */   
#define TEMPERING_MASK_B 0x9d2c5680
#define TEMPERING_MASK_C 0xefc60000
#define TEMPERING_SHIFT_U(y)  (y >> 11)
#define TEMPERING_SHIFT_S(y)  (y << 7)
#define TEMPERING_SHIFT_T(y)  (y << 15)
#define TEMPERING_SHIFT_L(y)  (y >> 18)

/* initializing the array with a NONZERO seed */
void
sgenrand(unsigned long seed, struct mt19937p* config)
{
    /* setting initial seeds to mt[N] using         */
    /* the generator Line 25 of Table 1 in          */
    /* [KNUTH 1981, The Art of Computer Programming */
    /*    Vol. 2 (2nd Ed.), pp102]                  */
	config->mti = N+1;
	config->mag01[0] = 0x0;
	config->mag01[1] = MATRIX_A;
    config->mt[0]= seed & 0xffffffff;
    for (config->mti=1; config->mti<N; config->mti++)
        config->mt[config->mti] = (69069 * config->mt[config->mti-1]) & 0xffffffff;
}

//double /* generating reals */
unsigned long  /* for integer generation */
genrand(struct mt19937p* config)
{
    unsigned long y;
    //static unsigned long mag01[2]={0x0, MATRIX_A};
    /* mag01[x] = x * MATRIX_A  for x=0,1 */

    if (config->mti >= N) { /* generate N words at one time */
        int kk;

/*        if (config->mti == N+1)*/   /* if sgenrand() has not been called, */
/*            sgenrand(4357);*/ /* a default initial seed is used   */

        for (kk=0;kk<N-M;kk++) {
            y = (config->mt[kk]&UPPER_MASK)|(config->mt[kk+1]&LOWER_MASK);
            config->mt[kk] = config->mt[kk+M] ^ (y >> 1) ^ config->mag01[y & 0x1];
        }
        for (;kk<N-1;kk++) {
            y = (config->mt[kk]&UPPER_MASK)|(config->mt[kk+1]&LOWER_MASK);
            config->mt[kk] = config->mt[kk+(M-N)] ^ (y >> 1) ^ config->mag01[y & 0x1];
        }
        y = (config->mt[N-1]&UPPER_MASK)|(config->mt[0]&LOWER_MASK);
        config->mt[N-1] = config->mt[M-1] ^ (y >> 1) ^ config->mag01[y & 0x1];

        config->mti = 0;
    }
  
    y = config->mt[config->mti++];
    y ^= TEMPERING_SHIFT_U(y);
    y ^= TEMPERING_SHIFT_S(y) & TEMPERING_MASK_B;
    y ^= TEMPERING_SHIFT_T(y) & TEMPERING_MASK_C;
    y ^= TEMPERING_SHIFT_L(y);

    //return ( (double)y / (unsigned long)0xffffffff ); /* reals */
    return y;  /* for integer generation */
}
