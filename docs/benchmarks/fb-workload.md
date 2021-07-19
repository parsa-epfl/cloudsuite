# Facebook Workload

The bechmark tests maily collects RPS with MediaWiki, the main page is the Barack Obama page from Wikipedia; this is based on the Wikimedia Foundation using it as a benchmark, and finding it fairly representative of Wikipedia. The benchmarking tool performs a sanity check once the engine and webserver have started accepting traffic to ensure that the framework is sending reasonable responses on the URLs being benchmarked.

This bechmark configures and runs nginx webserver, siege client, and PHP5/PHP7/HHVM over FastCGI as the server engine.

The script will run 300 warmup requests, then as many requests as possible in 1 minute.

The facebook benchmark readme file can be found in [fb-readme.md](https://github.com/facebookarchive/oss-performance/blob/v2019.02.13.00/README.md)

### Preparing the VM
Facebook-Mediawiki workload needs specific network parameters to be set. Set the below params in `/etc/sysctl.conf` file as `root`.
```
net.ipv4.tcp_tw_reuse = 1
net.core.somaxconn = 1024
net.ipv4.ip_local_port_range = 1024 65000
kernel.perf_event_paranoid = -1
```

Execute
```
sudo systemctl daemon-reload \
	&& sudo sysctl -p
```

Also if the CPU's speed is set to `ondemand` change it to `performance` by executing the below command as `root`
```
sudo -i

for file in /sys/devices/system/cpu/cpu*/cpufreq/scaling_governor; do
	echo performance > $file
done
```

More info on CPU frequency can be found in [fb-cpufreq.md](https://github.com/facebookarchive/oss-performance/blob/v2019.02.13.00/cpufreq.md)

### Starting the database server ####
To start the database server, you have to first `pull` the server image. To `pull` the server image use the following command:
```
docker pull cloudsuite/mysql:mariadb-10.3
```
The following command will start the database server:
```
docker run -dt --net=host cloudsuite/mysql:mariadb-10.3
```

### Starting the siege client ####
To start the siege client, you have to first `pull` the siege image. To `pull` the siege image use the following command:
```
docker pull cloudsuite/siege:4.0.3rc3
```
The following command will start the siege client:
```
docker run --name=siege -dt --net=host cloudsuite/siege:4.0.3rc3 <HOST_IP_OF_FB_SERVER> <HOSTNAME_OF_FB_SERVER>
```
---
**NOTE**

HOST_IP_OF_FB_SERVER = IP addr of the Host Machine where the fb-workload:server docker will be started
HOSTNAME_OF_FB_SERVER = Hostname of the Host Machine where the fb-workload:server docker will be started

If siege is started on the same host as of fb-workload:server, then HOST_IP_OF_FB_SERVER, HOSTNAME_OF_FB_SERVER can be skipped

---

### Starting the facebook workload benchmark ####
To start the facebook workload benchmark you have to first `pull` the server image. To `pull` the server image use the following command:
```
docker pull cloudsuite/fb-oss-performance:2019.02.13
``` 
Create a file `cmd.sh` on the host which contains the command to run mediawiki workload. An example is present in [cmd.sh](../../benchmarks/fb-oss-performance/files/cmd.sh)
```
chmod +x cmd.sh
```

To run the benchmark using HHVM edit the below lines in `cmd.sh`, with their private IP's
```
MYSQL_IP
SIEGE_IP
```

You can also use any one of the below parameters in `cmd.sh`
```
--hhvm=$HHVM_BIN
--php5=$PHP_CGI_BIN
--php=$PHP_FPM7_BIN
```

More info on parameters which can be provided to the facebook worload can be found in [fb-readme.md](https://github.com/facebookarchive/oss-performance/blob/v2019.02.13.00/README.md)
For information about the hhvm performance [hhvm-blog](https://hhvm.com/blog/9293/lockdown-results-and-hhvm-performance) can be referred.

The following command will start the facebook workload:

```
docker run --net=host --name=fb -v /<path>/cmd.sh:/oss-performance/cmd.sh cloudsuite/fb-oss-performance:2019.02.13
```
