#!/bin/bash

# Download datasets
wget --progress=bar:force -O - $INDEX_URL |
    tar zxvf - -C /download
