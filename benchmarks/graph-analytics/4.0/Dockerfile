FROM --platform=linux/amd64 cloudsuite/spark:2.4.5 as build
     
# Build the benchmark using sbt
RUN apt-get update \
    && apt-get install gnupg curl -y --no-install-recommends \
    && echo "deb https://repo.scala-sbt.org/scalasbt/debian /" | tee -a /etc/apt/sources.list.d/sbt.list \
    && curl -sL "https://keyserver.ubuntu.com/pks/lookup?op=get&search=0x2EE0EA64E40A89B84B2DF73499E82A75642AC823" | apt-key add \
    && apt-get update -y \
    && apt-get install -y sbt \
    && rm -rf /var/lib/apt/lists/* 

# Copy files
COPY benchmark /root/benchmark
COPY files /root/

RUN cd /root/benchmark \
    && sed -i "s/EDGES_FILE/\/data\/edges\.csv/g" /root/benchmark/src/main/scala/GraphAnalytics.scala \
    && sbt package \
    && mkdir -p /benchmarks 

RUN mv /root/benchmark/target/scala-2.11/*.jar /root/benchmark/run_benchmark.sh /benchmarks \
    && rm -r /root/benchmark \
    && apt-get purge -y --auto-remove sbt \
    && rm -r /root/.sbt /root/.ivy2 \
    && chmod +x /root/entrypoint.sh

FROM cloudsuite/spark:2.4.5
RUN mkdir -p /benchmarks /root
COPY --from=build /benchmarks /benchmarks
COPY --from=build /root/entrypoint.sh /root/entrypoint.sh
WORKDIR /root
ENTRYPOINT ["/root/entrypoint.sh"]
