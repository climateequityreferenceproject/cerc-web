#!/bin/bash
** This file can be used to install cerc-web into a devilbox docker container
** from your devilbox directory, run ./shell.sh (or shell.bat) and then
** execute this file. You might have to create a cerc-web hostname in your
** hosts file. Refer to devilbox documentation for that. http://devilbox.org


mkdir /shared/httpd/cerc-web
cd /shared/httpd/cerc-web
git clone https://github.com/climateequityreferenceproject/cerc-web
ln -s cerc-web/public_html/ htdocs

mysql -u root -h 127.0.0.1 -p  < /shared/httpd/cerc-web/htdocs/databasemanager/pledgedb/db/pledge.sql
mysql -u root -h 127.0.0.1 -p  < /shared/httpd/cerc-web/htdocs/databasemanager/helpdb/db/help_db.sql
mysql -u root -h 127.0.0.1 -p -e 'CREATE USER "cerp"@"%" IDENTIFIED BY "cerp";'
mysql -u root -h 127.0.0.1 -p -e 'GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, CREATE TEMPORARY TABLES, CREATE VIEW, EVENT, TRIGGER, SHOW VIEW, CREATE ROUTINE, ALTER ROUTINE, EXECUTE ON `pledges\_cerp`.* TO "cerp"@"%";'
mysql -u root -h 127.0.0.1 -p -e 'GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, CREATE TEMPORARY TABLES, CREATE VIEW, EVENT, TRIGGER, SHOW VIEW, CREATE ROUTINE, ALTER ROUTINE, EXECUTE ON `help\_db`.* TO "cerp"@"%";'

mkdir /shared/httpd/cerc-web/cerc_data
mkdir /shared/httpd/cerc-web/cerc_data/databases
mkdir /shared/httpd/cerc-web/cerc_data/sessions
mkdir /shared/httpd/cerc-web/cerc_data/sessions/user-dbs
mkdir /shared/httpd/cerc-web/htdocs/tmp/

svn checkout https://svn.code.sf.net/p/gdrs/code/ /shared/httpd/cerc-web/gdrscode
cd /shared/httpd/cerc-web/gdrscode/gdrsclib
make CONF=Development clean
make CONF=Development
make CONF=Public clean
make CONF=Public

cd /shared/httpd/cerc-web
git clone https://github.com/climateequityreferenceproject/cerc-coredb

cd /shared/httpd/cerc-web/
mkdir PEAR
mkdir PEAR/HTTP
mkdir PEAR/temp
cd PEAR/temp
git clone https://github.com/pear/pear-core
git clone https://github.com/pear/HTTP_Request
git clone https://github.com/pear/Net_Socket
git clone https://github.com/pear/Net_URL
mv pear-core/PEAR.php ..
mv HTTP_Request/Request* ../HTTP
mv Net_Socket/N* ..
mv Net_URL/URL.php ../Net
cd ..
rm -Rf temp
pwd

cd /shared/httpd/cerc-web/htdocs/
rm config.php
echo '<?php' >> config.php
echo '$core_db = "/shared/httpd/cerc-web/cerc-coredb/cerc-coredb.sql3";' >> config.php
echo '$core_db_dev = "/shared/httpd/cerc-web/cerc-coredb/cerc-coredb.sql3";' >> config.php
echo '$user_db_store = "/shared/httpd/cerc-web/cerc_data/sessions/user-dbs";' >> config.php
echo '$svg_tmp_dir = "/shared/httpd/cerc-web/htdocs/tmp";' >> config.php
echo '$param_log_file_name = "/shared/httpd/cerc-web/cerc_data/sessions/param_log.txt";' >> config.php
echo '$xls_tmp_dir = $user_db_store;' >> config.php
echo '$xls_file_slug = "cerc_all_output_";' >> config.php
echo '$xls_copyright_notice = "Climate Equity Reference Project Online Calculator (https://calculator.climateequityreference.org)";' >> config.php
echo '$calc_engine_path = "/shared/httpd/cerc-web/gdrscode/gdrsclib/dist/Public/GNU-Linux-x86/gdrsclib";' >> config.php
echo '$calc_engine_path_dev = "/shared/httpd/cerc-web/gdrscode/gdrsclib/dist/Development/GNU-Linux-x86/gdrsclib";' >> config.php
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
echo '$path = "/shared/httpd/cerc-web/PEAR";' >> config.php
echo 'set_include_path(get_include_path() . PATH_SEPARATOR . $path);' >> config.php
echo 'ini_set("display_errors", 0);' >> config.php
