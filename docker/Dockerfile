FROM ubuntu:xenial
LABEL maintainer = "Ceecee Holz <cholz@climate.works>" \
      version = "3.2.1" \
      description = "This is an image of a complete Ubuntu LEMP stack with a complete installation of the cerc-web project with all dependencies and databases." \
      url = "http://github.com/climateequityreferenceproject/cerc-web/" \
      comment = "LEMP stack is based on Tonis Ormisson's dev-lemp (github.com/TonisOrmisson/dev-lemp)"

# 1. --------------------------------------------------------------------------
# update system and install generic tools including subversion, make and gcc
RUN apt update && apt-get install -y --no-install-recommends apt-utils systemd; \
    apt install -y nano wget net-tools git unzip curl iputils-ping telnet dnsutils nmap \
    software-properties-common apt-transport-https subversion make gcc


# 2. --------------------------------------------------------------------------
# install nginx, add lines to nginx.conf file and write default files for nginx
RUN echo "##### installing nginx #####";\
         apt install -y nginx; \
    echo "##### adding buffer size instructions to nginx.conf #####";\
         sed -i '/http {/ a fastcgi_read_timeout 300;' /etc/nginx/nginx.conf; \
         sed -i '/http {/ a fastcgi_send_timeout 300;' /etc/nginx/nginx.conf; \
         sed -i '/http {/ a fastcgi_connect_timeout 300;' /etc/nginx/nginx.conf; \
         sed -i '/http {/ a fastcgi_buffer_size 32k;' /etc/nginx/nginx.conf; \
         sed -i '/http {/ a fastcgi_buffers 8 16k;' /etc/nginx/nginx.conf; \
    echo "##### write out default file #####";\
         echo "server { listen 80 default_server; listen [::]:80 default_server; root /var/www/html; index index.php index.html index.htm; error_log /dev/stdout info; access_log /dev/stdout; sendfile off;" > /etc/nginx/sites-available/default; \
         echo "location / { sendfile off; try_files \$uri \$uri/ =404; }" >> /etc/nginx/sites-available/default; \
         echo "location ~ \.php$ { sendfile off; include snippets/fastcgi-php.conf; fastcgi_pass unix:/run/php/php7.2-fpm.sock; }" >> /etc/nginx/sites-available/default; \
         echo "location ~ /\.ht { deny all; } }" >> /etc/nginx/sites-available/default


# 3. --------------------------------------------------------------------------
# Install and start MySQL, allow mysql user connections from any host
RUN echo "##### install MySQL #####";\
         echo mysql-server mysql-server/root_password password root | debconf-set-selections;\
         echo mysql-server mysql-server/root_password_again password root | debconf-set-selections;\
         apt-get install -y mysql-server mysql-client libmysqlclient-dev;\
     echo "##### start MySQL #####";\
         sed -i -e"s/^bind-address\s*=\s*127.0.0.1/bind-address = 0.0.0.0/" /etc/mysql/mysql.conf.d/mysqld.cnf; \
         find /var/lib/mysql -type f -exec touch {} \; && service mysql start;\
     echo "##### allow mysql user connections from any host #####";\
         find /var/lib/mysql -type f -exec touch {} \; && service mysql start && service mysql start && mysql -uroot -proot mysql  -e "update user set host='%' where user='root' and host='localhost';flush privileges; CREATE DATABASE test;"


# 4. --------------------------------------------------------------------------
# Install cerc-web and core database and set up cerc-web MySQL databases and MySQL user, install and compile calculator engine
RUN echo "##### install cerc-web (release 3.2.1) from github and create required directories #####";\
         cd /var/www/;\
         rm -rf /var/www/html;\
         git clone https://github.com/climateequityreferenceproject/cerc-web; \
         git checkout tags/v3.2.1;\
         ln -s /var/www/cerc-web/public_html /var/www/html;\
         mkdir -p /var/www/cerc-web/cerc_data/sessions/user-dbs;\
         mkdir -p /var/www/cerc-web/tmp;\
    echo "##### get current core database from github #####";\
         cd /var/www/cerc-web/;\
         git clone https://github.com/climateequityreferenceproject/cerc-coredb;\
    echo "##### set up required databases on MySQL server #####";\
         find /var/lib/mysql -type f -exec touch {} \; && service mysql start && service mysql start && mysql -uroot -proot mysql < /var/www/html/databasemanager/pledgedb/db/pledge.sql;\
         find /var/lib/mysql -type f -exec touch {} \; && service mysql start && service mysql start && mysql -uroot -proot mysql < /var/www/html/databasemanager/helpdb/db/help_db.sql;\
    echo "##### write cerc-web's config.php file and create phpinfo.php for debuggin #####";\
         echo '<?php $core_db = "/var/www/cerc-web/cerc-coredb/cerc-coredb.sql3";$core_db_dev = "/var/www/cerc-web/cerc-coredb/cerc-coredb.sql3";$user_db_store = "/var/www/cerc-web/cerc_data/sessions/user-dbs";$svg_tmp_dir = "/var/www/cerc-web/tmp";$param_log_file_name = "/var/www/cerc-web/cerc_data/sessions/param_log.txt";$xls_tmp_dir = $user_db_store;$xls_file_slug = "cerc_all_output_";$xls_copyright_notice = "Climate Equity Reference Project Online Calculator (https://calculator.climateequityreference.org)";$calc_engine_path = "/var/www/cerc-web/gdrscode/gdrsclib/dist/Public/GNU-Linux-x86/gdrsclib";$calc_engine_path_dev = "/var/www/cerc-web/gdrscode/gdrsclib/dist/Development/GNU-Linux-x86/gdrsclib";$webcalc_version = "3.2.1";$pledge_db_config = array("dbname" => "pledges_cerp", "user" => "root", "pwd" => "root", "host" => "127.0.0.7" );$help_db_config = array("dbname" => "help_db", "user" => "root", "pwd" => "root", "host" => "127.0.0.7" );$URL_calc = "http://localhost/";$URL_calc_dev = "http://localhost/";$URL_gloss = "http://localhost/glossary.php";$URL_gloss_dev = "http://localhost/glossary.php";$URL_calc_api = "http://localhost/api/";$URL_calc_api_dev = "http://localhost/api/";$helpdb_include_path = dirname(__FILE__) . "/databasemanager/helpdb/includes/";$TinyMCE_APIKey = "tmyem7mm54g5f2rpwcc5ks6y92kuhn5fliwk5a50kntvh7ke";$excel_download_header_rename = array("gdrs_alloc_MtCO2" => "allocation_MtCO2", "gdrs_r_MtCO2" => "responsibility_MtCO2", "gdrs_c_blnUSDMER" => "capacity_blnUSDMER", "gdrs_rci" => "rci", "gdrs_pop_mln_above_dl"=> "pop_mln_above_dl", "gdrs_pop_mln_above_lux" => "pop_mln_above_lux", "gdrs_c_frac"=> "c_frac", "gdrs_r_frac"=> "r_frac");$dev_calc_creds = array ("user"=>"", "pass"=>"");$main_domain_host = "climateequityreference.org";$ga_tracking_code = "";//ini_set("display_errors", 1);' > /var/www/html/config.php;\
         echo "<?php phpinfo();" > /var/www/html/phpinfo.php;\
    echo "##### Install and compile cerc Engine (development and public release versions) #####";\
         svn checkout https://svn.code.sf.net/p/gdrs/code/ /var/www/cerc-web/gdrscode;\
         cd /var/www/cerc-web/gdrscode/gdrsclib;\
         make CONF=Development clean;\
         make CONF=Development;\
         make CONF=Public clean;\
         make CONF=Public


# 5. --------------------------------------------------------------------------
# install php and sqlite3 and start webserver
RUN echo "##### install php and sqlite3 #####";\
         LC_ALL=C.UTF-8  add-apt-repository ppa:ondrej/php;\
         DEBIAN_FRONTEND=noninteractive apt update && apt install -y php7.2 php7.2-fpm php7.2-cli php7.2-mysql php7.2-curl php7.2-gd \
         php7.2-imap php7.2-zip php7.2-ldap php7.2-xml php7.2-mbstring php7.2-intl php7.2-soap php7.2-bcmath sqlite3 php-sqlite3;\
    echo "##### start webserver #####";\
         service php7.2-fpm start;\
         service nginx restart


# 6. --------------------------------------------------------------------------
# fix permissions, create /start.sh, install dumb-init and add github as known host
RUN echo "##### install fix permissions (or rather: ownership - make php process own whole www directory) #####";\
         chown -R www-data:www-data /var/www;\
    echo "##### write out /start.sh file #####";\
         echo '#!/bin/bash' > /start.sh; \
         echo 'echo "Starting mysql ... "; find /var/lib/mysql -type f -exec touch {} \; && service mysql start' >> /start.sh; \
         echo 'status=$?; if [ $status -ne 0 ]; then echo "Failed to start mysql: $status"; exit $status; fi' >> /start.sh; \
         echo 'echo "Starting PHP-fpm ... "; service php7.2-fpm start' >> /start.sh; \
         echo 'status=$?; if [ $status -ne 0 ]; then echo "Failed to start PHP-fpm: $status"; exit $status; fi' >> /start.sh; \
         echo 'echo "Starting nginx in foreground ... Visit localhost:8080/index.php in your browser."; nginx -g "daemon off;"' >> /start.sh; \
         echo 'status=$?; if [ $status -ne 0 ]; then echo "Failed to start nginx: $status"; exit $status; fi' >> /start.sh;\
         chmod a+x /start.sh;\
    echo "##### install dumb-init #####";\
         wget https://github.com/Yelp/dumb-init/releases/download/v1.2.1/dumb-init_1.2.1_amd64.deb;\
         dpkg -i dumb-init_*.deb; rm dumb-init_*.deb;\
    echo "##### add github as known host #####";\
         mkdir /root/.ssh;\
         ssh-keyscan github.com >> /root/.ssh/known_hosts;\
         chmod -R 600 /root/.ssh

# Expose Ports
EXPOSE 443 80 3306

# Startup
WORKDIR /var/www/html
CMD ["dumb-init", "--", "/start.sh"]
