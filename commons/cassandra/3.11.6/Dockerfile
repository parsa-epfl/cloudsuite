FROM cloudsuite/java:openjdk11

ENV CASSANDRA_VERSION 3.11.6

RUN set -eux \
	&& groupadd -r cassandra --gid=999 \
	&& useradd -r -g cassandra --uid=999 cassandra

RUN set -eux \
	&& apt-get update \
	&& apt-get install -y --no-install-recommends procps python2 iproute2 numactl \
	&& rm -rf /var/lib/apt/lists/*

ENV CASSANDRA_HOME /opt/cassandra
ENV CASSANDRA_CONFIG /etc/cassandra
ENV PATH $CASSANDRA_HOME/bin:$PATH

ENV GPG_KEYS \
	514A2AD631A57A16DD0047EC749D6EEC0353B12C \
	A26E528B271F19B9E5D8E19EA278B781FE4B2BDA \
	A4C465FEA0C552561A392A61E91335D77E3E87CB

ENV CASSANDRA_SHA512 0e778f8fb4a050fde3ec174a9ca365e49ef437cd9e61280b6b4dcba950418a3d04a104bb41aed1add858e2acc2513cf7df4065ca5ca751dd1daf60e70adc4042

RUN set -eux; \
	savedAptMark="$(apt-mark showmanual)"; \
	apt-get update; \
	apt-get install -y --no-install-recommends ca-certificates dirmngr gnupg wget; \
	rm -rf /var/lib/apt/lists/*; \
	\
	ddist() { \
		local f="$1"; shift; \
		local distFile="$1"; shift; \
		local success=; \
		local distUrl=; \
		for distUrl in \
# https://issues.apache.org/jira/browse/INFRA-8753?focusedCommentId=14735394#comment-14735394
			'https://www.apache.org/dyn/closer.cgi?action=download&filename=' \
# if the version is outdated (or we're grabbing the .asc file), we might have to pull from the dist/archive :/
			https://www-us.apache.org/dist/ \
			https://www.apache.org/dist/ \
			https://archive.apache.org/dist/ \
		; do \
			if wget --progress=dot:giga -O "$f" "$distUrl$distFile" && [ -s "$f" ]; then \
				success=1; \
				break; \
			fi; \
		done; \
		[ -n "$success" ]; \
	}; \
	\
	ddist 'cassandra-bin.tgz' "cassandra/$CASSANDRA_VERSION/apache-cassandra-$CASSANDRA_VERSION-bin.tar.gz"; \
	echo "$CASSANDRA_SHA512 *cassandra-bin.tgz" | sha512sum --check --strict -; \
	\
	ddist 'cassandra-bin.tgz.asc' "cassandra/$CASSANDRA_VERSION/apache-cassandra-$CASSANDRA_VERSION-bin.tar.gz.asc"; \
	export GNUPGHOME="$(mktemp -d)"; \
	for key in $GPG_KEYS; do \
		# If you meet error about the keyserver, try to switch to ha.pool.sks-keyservers.net or pgp.mit.edu
		gpg --batch --keyserver keyserver.ubuntu.com --recv-keys "$key"; \
	done; \
	gpg --batch --verify cassandra-bin.tgz.asc cassandra-bin.tgz; \
	rm -rf "$GNUPGHOME"; \
	\
	apt-mark auto '.*' > /dev/null; \
	[ -z "$savedAptMark" ] || apt-mark manual $savedAptMark > /dev/null; \
	apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false; \
	\
	mkdir -p "$CASSANDRA_HOME"; \
	tar --extract --file cassandra-bin.tgz --directory "$CASSANDRA_HOME" --strip-components 1; \
	rm cassandra-bin.tgz*; \
	\
	[ ! -e "$CASSANDRA_CONFIG" ]; \
	mv "$CASSANDRA_HOME/conf" "$CASSANDRA_CONFIG"; \
	ln -sT "$CASSANDRA_CONFIG" "$CASSANDRA_HOME/conf"; \
	\
	mkdir -p "$CASSANDRA_CONFIG" /var/lib/cassandra /var/log/cassandra; \
	chown -R cassandra:cassandra "$CASSANDRA_CONFIG" /var/lib/cassandra /var/log/cassandra; \
	chmod 777 "$CASSANDRA_CONFIG" /var/lib/cassandra /var/log/cassandra; \
	ln -sT /var/lib/cassandra "$CASSANDRA_HOME/data"; \
	ln -sT /var/log/cassandra "$CASSANDRA_HOME/logs"; 

COPY jvm.options /opt/cassandra/conf/jvm.options

RUN update-alternatives --install /usr/bin/python python /usr/bin/python2.7 1
