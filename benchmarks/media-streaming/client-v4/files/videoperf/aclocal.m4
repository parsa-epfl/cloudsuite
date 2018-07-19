AC_DEFUN(AC_TYPE_LONG_LONG,
[AC_CACHE_CHECK(for long long type, ac_cv_type_long_long,
  [AC_TRY_COMPILE(, [unsigned long long x, y, z; x = y/z],
		  ac_cv_type_long_long=yes, ac_cv_type_long_long=no)])
if test $ac_cv_type_long_long = yes; then
  AC_DEFINE(u_wide,unsigned long long)
else
  AC_DEFINE(u_wide,unsigned long)
fi
])
