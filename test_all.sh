set +e

for i in $(ls test*); do
	./$i
done
