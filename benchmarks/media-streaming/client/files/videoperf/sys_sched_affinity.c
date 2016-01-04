#ifdef HAVE_SCHED_AFFINITY
#ifdef __linux__

#include <unistd.h>
#include <sys/syscall.h>


long
sched_setaffinity(pid_t pid, unsigned int len, unsigned long *cpu_mask)
{
  return syscall(SYS_sched_setaffinity, pid, len, cpu_mask);
}


long
sched_getaffinity(pid_t pid, unsigned int len, unsigned long *cpu_mask)
{
  return syscall(SYS_sched_getaffinity, pid, len, cpu_mask);
}


#endif /* __linux__ */
#endif /* HAVE_SCHED_AFFINITY */
