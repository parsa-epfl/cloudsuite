FROM --platform=linux/amd64 cloudsuite/spark:3.3.2 as build

WORKDIR /root

# Benchmark files
COPY movielens-als /root/movielens-als
COPY files /root/

ENV SCALA_VERSION 2.13

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


RUN cd /root/movielens-als \
    && sbt package \
    && mv /root/movielens-als/target/scala-${SCALA_VERSION}/*.jar /root/movielens-als/run_benchmark.sh /root \
    && apt-get purge -y --auto-remove sbt \
    && rm -r /root/movielens-als \
    && rm -r /root/.sbt /root/.cache \
    && chmod +x /root/entrypoint.sh

FROM cloudsuite/spark:3.3.2
# copy the old file
COPY --from=build /root/movielens-als-2.0.jar /root
COPY --from=build /root/run_benchmark.sh /root
COPY /files/entrypoint.sh /root/entrypoint.sh

ENV BENCHMARK_JAR /root/movielens-als-2.0.jar
ENTRYPOINT ["/root/entrypoint.sh"]
