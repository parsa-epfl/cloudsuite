#!/bin/bash

service postgresql start

# Create the user called `cloudsuite`
sudo -u postgres psql -c "CREATE USER cloudsuite WITH PASSWORD 'cloudsuite';"
# Create a table named sbtest
sudo -u postgres psql -c "CREATE DATABASE sbtest;"
# Gave permission to this table
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE sbtest TO cloudsuite"

sudo -u postgres psql sbtest -c "GRANT ALL ON SCHEMA public TO cloudsuite;"

sudo -u postgres psql
