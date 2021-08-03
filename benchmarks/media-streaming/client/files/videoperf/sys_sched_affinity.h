#ifndef SYS_SCHED_AFFINITY_H
#define SYS_SCHED_AFFINITY_H

#ifdef HAVE_SCHED_AFFINITY
#ifdef __linux__

extern long sched_setaffinity_videoperf(pid_t pid, unsigned int len,
   unsigned long *cpu_mask);

extern long sched_getaffinity_videoperf(pid_t pid, unsigned int len,
   unsigned long *cpu_mask);

#endif /* HAVE_SCHED_AFFINITY */

#endif /* __linux__ */
#endif /* SYS_SCHED_AFFINITY_H */
