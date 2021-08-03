FROM cloudsuite/spark:2.3.1

# Copy files
COPY benchmark /root/benchmark
COPY files /root/

WORKDIR /root

# Build the benchmark using sbt
RUN apt-get update \
    && apt-get install gnupg -y --no-install-recommends \
    && echo "deb http://dl.bintray.com/sbt/debian /" | tee -a /etc/apt/sources.list.d/sbt.list \
    && apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv 2EE0EA64E40A89B84B2DF73499E82A75642AC823 \
    && apt-get update -y \
    && apt-get install -y sbt \
    && rm -rf /var/lib/apt/lists/* \
    && sed -i "s/EDGES_FILE/\/data\/edges\.csv/g" benchmark/src/main/scala/GraphAnalytics.scala \
    && (cd benchmark && sbt package) \
    && mkdir -p /benchmarks \
    && mv benchmark/target/scala-2.10/*.jar benchmark/run_benchmark.sh /benchmarks \
    && rm -r benchmark \
    && apt-get purge -y --auto-remove sbt \
    && rm -r .sbt .ivy2 \
    && chmod +x /root/entrypoint.sh


ENTRYPOINT ["/root/entrypoint.sh"]