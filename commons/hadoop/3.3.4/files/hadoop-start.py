#!/usr/bin/env python3

import socket
def get_ip():
    s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
    s.settimeout(0)
    try:
        # doesn't even have to be reachable
        s.connect(('8.8.8.8', 1))
        IP = s.getsockname()[0]
    except Exception:
        IP = '127.0.0.1'
    finally:
        s.close()
    return IP

import argparse

parser = argparse.ArgumentParser()

parser.add_argument("--master-ip", "-a", help="The IP for Hadoop master. If no value provided, the script tries to find the primary IP address by itself.", default=get_ip())
parser.add_argument("--master", help="Set node as Hadoop Master", action='store_true')
parser.add_argument("--slave", help="Set node as Hadoop Slave", action='store_true')

parser.add_argument("--yarn-cores", help="YARN: Max cores allowed to the cluster", default=8)
parser.add_argument("--yarn-mem", help="YARN: Max memory allowed to the cluster, should be the addition of number of workers*memory per worker", default=4096)

parser.add_argument("--hdfs-block-size", help="HDFS: Size of each block", default=128)

parser.add_argument("--mapreduce-mem", help="MAP_REDUCE: memory allocated forboth map and reduce phases", default=2048)
parser.add_argument("--mapreduce-java-mem", help="MAP_REDUCE: memory allocated for JVM", default=1848)

args = parser.parse_args()
if not args.master and not args.slave:
    print("ERROR: Please specify if master or slave with --master or --slave.")
    exit

import os
import os.path as path
import pwd
import shutil

os.environ["JAVA_HOME"] = os.path.dirname(os.path.dirname(os.path.realpath(shutil.which("javac"))))

HADOOP_CONF_DIR = os.environ["HADOOP_CONF_DIR"]
HADOOP_HOME = os.environ["HADOOP_HOME"]
HADOOP_LOG_DIR = os.environ["HADOOP_LOG_DIR"]
USER = pwd.getpwuid(os.getuid())[0]


# Backup the original configuration files
if not path.exists(f"{HADOOP_CONF_DIR}/yarn-site.xml.bak"):
    shutil.copy(f"{HADOOP_CONF_DIR}/yarn-site.xml", f"{HADOOP_CONF_DIR}/yarn-site.xml.bak")
if not path.exists(f"{HADOOP_CONF_DIR}/core-site.xml.bak"):
    shutil.copy(f"{HADOOP_CONF_DIR}/core-site.xml", f"{HADOOP_CONF_DIR}/core-site.xml.bak")
if not path.exists(f"{HADOOP_CONF_DIR}/hdfs-site.xml.bak"):
    shutil.copy(f"{HADOOP_CONF_DIR}/hdfs-site.xml", f"{HADOOP_CONF_DIR}/hdfs-site.xml.bak")
# if not path.exists(f"{HADOOP_CONF_DIR}/mapred-site.xml.bak"):
#     shutil.copy(f"{HADOOP_CONF_DIR}/mapred-site.xml", f"{HADOOP_CONF_DIR}/mapred-site.xml.bak")
if not path.exists(f"{HADOOP_CONF_DIR}/hadoop-env.sh.bak"):
    shutil.copy(f"{HADOOP_CONF_DIR}/hadoop-env.sh", f"{HADOOP_CONF_DIR}/hadoop-env.sh.bak")

with open(f"{HADOOP_CONF_DIR}/yarn-site.xml", "w") as yarn_site_file:
    with open(f"{HADOOP_CONF_DIR}/yarn-site.xml.template") as template:
        yarn_site_template = template.read()
        yarn_site_file.write(yarn_site_template.format(
            MASTER_IP = args.master_ip,
            YARN_MEM = args.yarn_mem,
            YARN_CORES = args.yarn_cores,
        ))

with open(f"{HADOOP_CONF_DIR}/core-site.xml", "w") as core_site_file:
    with open(f"{HADOOP_CONF_DIR}/core-site.xml.template") as template:
        core_site_template = template.read()
        core_site_file.write(core_site_template.format(
            MASTER_IP = args.master_ip
        ))

with open(f"{HADOOP_CONF_DIR}/hdfs-site.xml", "w") as hdfs_site_file:
    with open(f"{HADOOP_CONF_DIR}/hdfs-site.xml.template") as template:
        hdfs_site_template = template.read()
        hdfs_site_file.write(hdfs_site_template.format(
                HDFS_BLOCK_SIZE = args.hdfs_block_size
            ))

 
with open(f"{HADOOP_CONF_DIR}/mapred-site.xml", "w") as mapred_site_file:
    with open(f"{HADOOP_CONF_DIR}/mapred-site.xml.template") as template:
        mapred_site_template = template.read()
        mapred_site_file.write(mapred_site_template.format(
            MAPREDUCE_MAP_MEM = args.mapreduce_mem,
            MAPREDUCE_REDUCE_MEM = args.mapreduce_mem,
            MAPREDUCE_JAVA_MAP_MEM = args.mapreduce_java_mem,
            MAPREDUCE_JAVA_REDUCE_MEM = args.mapreduce_java_mem,
        ))

with open(f"{HADOOP_CONF_DIR}/hadoop-env.sh", "w") as hadoop_env_file:
    hadoop_env_file.write(f"HADOOP_CLIENT_OPTS='-Xmx{args.yarn_mem}m'\n"
            f"HADOOP_OPTS='-Xmx{args.yarn_mem}m'\n")
 

os.system("service ssh start")


if args.master:
    print("HDFS NameNode start:")
    os.system(f"{HADOOP_HOME}/bin/hdfs namenode -format cc")
    os.system(f"{HADOOP_HOME}/bin/hdfs --daemon start namenode")
    print("Yarn ResourceManager start:")
    os.system(f"{HADOOP_HOME}/bin/yarn --daemon start resourcemanager")
    print("Yarn ProxyServer and HistoryServer start:")
    os.system(f"{HADOOP_HOME}/bin/yarn --daemon start proxyserver")
    os.system(f"{HADOOP_HOME}/bin/mapred --daemon start historyserver")
 
elif args.slave:
    print("HDFS DataNode start")
    os.system(f"{HADOOP_HOME}/bin/hdfs --daemon start datanode")
    print("YARN NodeManager start")
    os.system(f"{HADOOP_HOME}/bin/yarn --daemon start nodemanager")

print("DONE launch")
print("Log files:")
os.system(f"cat {HADOOP_LOG_DIR}/*")
os.system(f"tail -f {HADOOP_LOG_DIR}/*")
