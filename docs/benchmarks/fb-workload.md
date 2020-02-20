# Facebook Workload

The facebook benchmark readme file can be found in [README.md](https://github.com/facebookarchive/oss-performance/blob/v2019.02.13.00/README.md)

### Starting the database server ####
To start the database server, you have to first `pull` the server image. To `pull` the server image use the following command:

    $ docker pull cloudsuite/web-serving:db_server

The following command will start the database server:

    $ docker run -dt --net=host --name=mysql_server cloudsuite/web-serving:db_server ${WEB_SERVER_IP}

The ${WEB_SERVER_IP}  parameter is mandatory. It sets the IP of the web server. If you are using the host network, the web server IP is the IP of the machine that you are running the web_server container on. If you create your own network you can use the name that you are going to give to the web server (we called it web_server in the following commands).


### Starting the facebook workload benchmark ####
To start the facebook workload benchmark you have to first `pull` the server image. To `pull` the server image use the following command:

    $ docker pull cloudsuite/fb-workload:server

The following command will start the facebook server:

    $ docker run --sysctl 'net.ipv4.tcp_tw_reuse=1' -it --entrypoint=bash cloudsuite/fb-workload:server
    $ docker exec -it <container-id> bash
    $ hhvm perf.php --mediawiki --db-host:<WEB_SERVER_IP> --db-username:<username> --db-password:<password> --php5=/usr/bin/php-cgi
