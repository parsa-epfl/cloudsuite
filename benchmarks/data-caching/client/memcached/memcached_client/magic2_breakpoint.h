#ifndef _magic2_breakpoint_h_
#define _magic2_breakpoint_h_

#define MAGIC2(num, var1)        __asm__ __volatile__              \
                                 (                                 \
                                        "mov %0, %%l0\n\t"         \
                                        "sethi " #num ", %%g0\n\t" \
                                        :                          \
                                        : "r"(var1)                \
                                        : "%l0"                    \
                                 );

#endif
