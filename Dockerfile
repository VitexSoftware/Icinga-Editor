FROM debian:latest

ENV APACHE_DOCUMENT_ROOT /usr/share/icinga-editor/
env DEBIAN_FRONTEND=noninteractive

RUN apt update ; apt install -y wget libapache2-mod-php; echo "deb http://repo.vitexsoftware.cz buster main" | tee /etc/apt/sources.list.d/vitexsoftware.list ; wget -O /etc/apt/trusted.gpg.d/vitexsoftware.gpg http://repo.vitexsoftware.cz/keyring.gpg
RUN apt-get update && apt-get install -y locales php7.3-sqlite apache2 aptitude  cron locales-all && rm -rf /var/lib/apt/lists/* \
    && localedef -i cs_CZ -c -f UTF-8 -A /usr/share/locale/locale.alias cs_CZ.UTF-8
ENV LANG cs_CZ.utf8

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN apt update

RUN aptitude -y install icinga-editor-sqlite flexibee-matcher flexibee-reminder flexibee-contract-invoices flexibee-digest

RUN phinx seed:run -c /usr/lib/icinga-editor/phinx-adapter.php
RUN a2ensite icinga-editor

RUN rm -f /var/www/html/index.html
COPY src/ /var/www/html/
RUN ln -s /var/www/html/ /var/www/src
COPY composer.json /var/www/composer.json

RUN composer install --no-dev --no-plugins --no-scripts  -d /var/www/

ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2

CMD ["/usr/sbin/apachectl","-DFOREGROUND"]
