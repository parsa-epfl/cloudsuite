# Cassandra Server #

## Single Node
Check the configuration parameters: conf/cassandra.yaml contains default values for the Cassandra parameters. First, ensure that the paths for the following parameters point to the directories where you have write permission. 
```
data_file_directories, commitlog_directory, and saved_caches_directory
```
Run the test command listed below, if there are no errors, then your installation was likely successful.
```
cassandra -f
```
## Multi Node
You need to configure each Cassandra instance properly to communicate with each other. The way that a Cassandra node is designed to communicate with other nodes is through the Gossip protocol. Each Cassandra node should know at least one reliable Cassandra node called the seed. You can find more details at this website: http://wiki.apache.org/cassandra/GettingStarted.

i. Configure the seed for each node in the conf/cassandra.yaml file.

ii. Configure the listen_address and rpc_address in conf/cassandra.yaml to the hostname (or IP of the node).
