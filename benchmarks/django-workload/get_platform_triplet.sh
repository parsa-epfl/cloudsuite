#!/bin/bash

cat >> conftest.c <<EOF
#undef linux
#undef i386
#undef unix
# if defined(__linux__)
# if defined(__x86_64__) && defined(__LP64__)
        x86_64-linux-gnu
# elif defined(__x86_64__) && defined(__ILP32__)
        x86_64-linux-gnux32
# elif defined(__i386__)
        i386-linux-gnu
# elif defined(__ia64__)
        ia64-linux-gnu
# else
#  error unknown platform triplet
# endif
#elif defined(__FreeBSD_kernel__)
# if defined(__LP64__)
        x86_64-kfreebsd-gnu
# elif defined(__i386__)
        i386-kfreebsd-gnu
# else
#   error unknown platform triplet
# endif
#elif defined(__gnu_hurd__)
        i386-gnu
#else
# error unknown platform triplet
#endif
EOF

gcc -E conftest.c >conftest.out 2>/dev/null
grep -v '^#' conftest.out | grep -v '^ *$' | tr -d '    '
rm -rf conftest.c conftest.out
