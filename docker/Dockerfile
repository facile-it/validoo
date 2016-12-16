FROM php:7.1

RUN echo 'debconf debconf/frontend select Noninteractive' | debconf-set-selections
RUN apt-get update
RUN apt-get install -y curl \
    git \
    zsh \
    ssh \
    supervisor \
    sudo \
    nano \
 ## PHP-EXT
    libzip-dev \
    && docker-php-ext-install -j5 zip mbstring \
    && apt-get clean

COPY config/sudoers /etc/sudoers
COPY config/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY config/php.ini /usr/local/etc/php/conf.d/

#COMPOSER
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /usr/local/bin

RUN useradd -ms /bin/zsh validoo
RUN usermod -aG sudo validoo
USER validoo

WORKDIR /home/validoo/project

#Zsh minimal installation
RUN git clone --depth=1 git://github.com/robbyrussell/oh-my-zsh.git  ~/.oh-my-zsh \
    && sudo chsh -s /bin/zsh
ADD config/.zshrc /root/
ADD config/.zshrc /home/validoo/

ENV TERM xterm-256color
CMD sudo /usr/bin/supervisord
