FROM cloudsuite/debian:base-os

# Install sshd
RUN apt-get update \
    && apt-get install -y gcc-7 g++-7 openssh-server git autotools-dev autoconf make

RUN update-alternatives --install /usr/bin/gcc gcc /usr/bin/gcc-7 7 \
    && update-alternatives --install /usr/bin/g++ g++ /usr/bin/g++-7 7 \
    && update-alternatives --config gcc \
    && update-alternatives --config g++

RUN git clone https://github.com/JoeDog/siege.git \
    && cd siege \
    && git checkout tags/v4.0.3rc3 \
    && ./utils/bootstrap \
    && automake --add-missing \
    && ./configure \
    && make \
    && make uninstall \
    && make install \
    && cd .. \
    && rm -rf siege

# Modify `sshd_config`
RUN sed -i -e 's/.*PermitEmptyPasswords.*/PermitEmptyPasswords yes/' -e 's/.*PermitRootLogin.*/PermitRootLogin yes/' -e 's/^UsePAM yes/UsePAM no/' /etc/ssh/sshd_config
RUN /etc/init.d/ssh start

# Delete root password (set as empty)
RUN passwd -d root

CMD ["/usr/sbin/sshd", "-D"]