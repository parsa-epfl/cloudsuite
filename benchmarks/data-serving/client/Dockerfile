FROM cloudsuite/cassandra:4.1.0

ENV YCSB_VERSION 0.17.0


RUN wget -q --show-progress --progress=bar:force https://github.com/brianfrankcooper/YCSB/releases/download/$YCSB_VERSION/ycsb-$YCSB_VERSION.tar.gz -O /ycsb-$YCSB_VERSION.tar.gz \
    && tar -xzf /ycsb-$YCSB_VERSION.tar.gz && rm /ycsb-$YCSB_VERSION.tar.gz && mv /ycsb-$YCSB_VERSION /ycsb

COPY setup_tables.txt /setup_tables.txt
COPY warmup.sh /warmup.sh
COPY load.sh /load.sh
RUN chmod +x /load.sh /warmup.sh



