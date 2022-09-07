FROM --platform=linux/amd64 cloudsuite/spark:2.4.5 as build

# Benchmark files
COPY movielens-als /root/movielens-als

WORKDIR /root

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
    && mkdir -p /benchmarks/movielens-als \
    && mv /root/movielens-als/target/scala-2.11/*.jar /root/movielens-als/run_benchmark.sh /benchmarks/movielens-als \
    && rm -r /root/movielens-als \
    && apt-get purge -y --auto-remove sbt \
    && rm -r /root/.sbt /root/.cache

COPY files /root/

FROM cloudsuitetest/spark:2.4.5
RUN mkdir -p /benchmarks/movielens-als /root
COPY --from=build /benchmarks/movielens-als /benchmarks/movielens-als
COPY --from=build /root/entrypoint.sh /root/entrypoint.sh
ENTRYPOINT ["/root/entrypoint.sh"]
