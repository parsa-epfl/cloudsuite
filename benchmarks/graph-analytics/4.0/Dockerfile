FROM --platform=linux/amd64 cloudsuitetest/spark:2.4.5 as build
     
# Build the benchmark using sbt
RUN set -ex \
    && apt-get update \
    && apt-get install apt-transport-https curl gnupg -y --no-install-recommends \
    && echo "deb https://repo.scala-sbt.org/scalasbt/debian all main" | tee /etc/apt/sources.list.d/sbt.list \
    && echo "deb https://repo.scala-sbt.org/scalasbt/debian /" | tee /etc/apt/sources.list.d/sbt_old.list \
    && curl -sL "https://keyserver.ubuntu.com/pks/lookup?op=get&search=0x2EE0EA64E40A89B84B2DF73499E82A75642AC823" | gpg --no-default-keyring --keyring gnupg-ring:/etc/apt/trusted.gpg.d/scalasbt-release.gpg --import \
    && chmod 644 /etc/apt/trusted.gpg.d/scalasbt-release.gpg \
    && apt-get update -y \
    && apt-get install -y --no-install-recommends sbt \
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
    && rm -r /root/.sbt /root/.cache \
    && chmod +x /root/entrypoint.sh

FROM cloudsuitetest/spark:2.4.5
RUN mkdir -p /benchmarks /root
COPY --from=build /benchmarks /benchmarks
COPY --from=build /root/entrypoint.sh /root/entrypoint.sh
WORKDIR /root
ENTRYPOINT ["/root/entrypoint.sh"]
