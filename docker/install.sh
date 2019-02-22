#!/bin/bash
** This file can be used to install cerc-web on a LEMP stack
** it assumes that sqlite3, git, subversion, make, and gcc are installed


cd /var/www
git clone https://github.com/climateequityreferenceproject/cerc-web
ln -s /var/www/cerc-web/public_html /var/www/html

mysql -u root -h 127.0.0.1 -proot < /var/www/html/databasemanager/pledgedb/db/pledge.sql
mysql -u root -h 127.0.0.1 -proot < /var/www/html/databasemanager/helpdb/db/help_db.sql
mysql -u root -h 127.0.0.1 -proot -e 'CREATE USER "cerp"@"%" IDENTIFIED BY "cerp";'
mysql -u root -h 127.0.0.1 -proot -e 'GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, CREATE TEMPORARY TABLES, CREATE VIEW, EVENT, TRIGGER, SHOW VIEW, CREATE ROUTINE, ALTER ROUTINE, EXECUTE ON `pledges\_cerp`.* TO "cerp"@"%";'
mysql -u root -h 127.0.0.1 -proot -e 'GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, CREATE TEMPORARY TABLES, CREATE VIEW, EVENT, TRIGGER, SHOW VIEW, CREATE ROUTINE, ALTER ROUTINE, EXECUTE ON `help\_db`.* TO "cerp"@"%";'

mkdir /var/www/cerc-web/cerc_data
mkdir /var/www/cerc-web/cerc_data/databases
mkdir /var/www/cerc-web/cerc_data/sessions
mkdir /var/www/cerc-web/cerc_data/sessions/user-dbs
mkdir /var/www/html/tmp/

svn checkout https://svn.code.sf.net/p/gdrs/code/ /var/www/cerc-web/gdrscode
cd /var/www/cerc-web/gdrscode/gdrsclib
make CONF=Development clean
make CONF=Development
make CONF=Public clean
make CONF=Public

cd /var/www/cerc-web
git clone https://github.com/climateequityreferenceproject/cerc-coredb

cd /var/www/html/
rm config.php
touch config.php
chown -R www-data:www-data /var/www

echo '<?php' >> config.php
echo '$core_db = "/var/www/cerc-web/cerc-coredb/cerc-coredb.sql3";' >> config.php
echo '$core_db_dev = "/var/www/cerc-web/cerc-coredb/cerc-coredb.sql3";' >> config.php
echo '$user_db_store = "/var/www/cerc-web/cerc_data/sessions/user-dbs";' >> config.php
echo '$svg_tmp_dir = "/var/www/html/tmp";' >> config.php
echo '$param_log_file_name = "/var/www/cerc-web/cerc_data/sessions/param_log.txt";' >> config.php
echo '$xls_tmp_dir = $user_db_store;' >> config.php
echo '$xls_file_slug = "cerc_all_output_";' >> config.php
echo '$xls_copyright_notice = "Climate Equity Reference Project Online Calculator (https://calculator.climateequityreference.org)";' >> config.php
echo '$calc_engine_path = "/var/www/cerc-web/gdrscode/gdrsclib/dist/Public/GNU-Linux-x86/gdrsclib";' >> config.php
echo '$calc_engine_path_dev = "/var/www/cerc-web/gdrscode/gdrsclib/dist/Development/GNU-Linux-x86/gdrsclib";' >> config.php
echo '$webcalc_version = "3.2.0";' >> config.php
echo '$pledge_db_config = array( ' >> config.php
echo '            "dbname" => "pledges_cerp",' >> config.php
echo '            "user" => "cerp",' >> config.php
echo '            "pwd" => "cerp",' >> config.php
echo '            "host" => "127.0.0.7"' >> config.php
echo '            );' >> config.php
echo '$help_db_config = array( ' >> config.php
echo '            "dbname" => "help_db",' >> config.php
echo '            "user" => "cerp",' >> config.php
echo '            "pwd" => "cerp",' >> config.php
echo '            "host" => "127.0.0.7"' >> config.php
echo '            );' >> config.php
echo '$URL_calc = "http://cerc-web.loc/";' >> config.php
echo '$URL_calc_dev = "http://dev.cerc-web.loc/";' >> config.php
echo '$URL_gloss = "http://cerc-web.loc/glossary.php";' >> config.php
echo '$URL_gloss_dev = "http://dev.cerc-web.loc/glossary.php";' >> config.php
echo '$URL_calc_api = "http://cerc-web.loc/api/";' >> config.php
echo '$URL_calc_api_dev = "http://dev.cerc-web.loc/api/";' >> config.php
echo '$helpdb_include_path = dirname(__FILE__) . "/databasemanager/helpdb/includes/";' >> config.php
echo '$TinyMCE_APIKey = "tmyem7mm54g5f2rpwcc5ks6y92kuhn5fliwk5a50kntvh7ke";' >> config.php
echo '$excel_download_header_rename = array(  ' >> config.php
echo '            "gdrs_alloc_MtCO2"       => "allocation_MtCO2",' >> config.php
echo '            "gdrs_r_MtCO2"           => "responsibility_MtCO2",' >> config.php
echo '            "gdrs_c_blnUSDMER"       => "capacity_blnUSDMER",' >> config.php
echo '            "gdrs_rci"               => "rci",' >> config.php
echo '            "gdrs_pop_mln_above_dl"  => "pop_mln_above_dl",' >> config.php
echo '            "gdrs_pop_mln_above_lux" => "pop_mln_above_lux",' >> config.php
echo '            "gdrs_c_frac"            => "c_frac",' >> config.php
echo '            "gdrs_r_frac"            => "r_frac"' >> config.php
echo '            );' >> config.php
echo '$dev_calc_creds = array ("user"=>"", "pass"=>"");' >> config.php
echo '$main_domain_host = "climateequityreference.org";' >> config.php
echo '$ga_tracking_code = "";' >> config.php
echo '' >> config.php
echo 'ini_set("display_errors", 1);' >> config.php
