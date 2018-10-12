# Overview

cerc-web is the web interface to the Climate Equity Reference Calculator. It is mainly written in PHP with some user interface elements written in javascript to achieve dynamic interactivity.

# Installation

This repository contains all files of the web interface, however, there are several databases to obtain and/or create and you also need to obtain or compile an executable of the calculator "engine."
Instead of installing your own version of the software, you can also simply head over to https://calculator.climateequityreference.org where the authors maintain a fully functional installation of the calculator.

1. Install the web interface files: Download or check out the files in repository and this put them on any php-capable web server.
2. Obtain and/or create databases.  
2a. Obtain a "core" database. The authors maintain the calculator's "core" database as an SQLite database which is published in the Harvard Dataverse, here: https://doi.org/10.7910/DVN/O3H22Z. Within the Dataverse data repository you will want to find the .sql3 file and download it. For most purposes we recommend the most recent version, but you can also pick any other version or create a core database yourself. This might be of interest for example if you want to assess a different mitigation pathway or effort sharing between entities other than the countries provided (for example, @krueschan has created a core database for Canadian provinces). Contact us if this is of interest.  
2b. Put the core database on the same webserver, since it's SQLite, it is fully contained in a single file. Make sure the file's location and permissions allows the php process of your server to have read/write access to it.   
2c. Create a "pledge" database. The pledge database is a MySQL database, so you need a MySQL server that can be accessed from the webserver where you put the web interface code. The repository (at /public_html/databasemanager/pledgedb/db/pledgedb_new.sql) has an sql file which can be run on your MySQL server to create the necessary database structure. From time to time we dump our own current pledge database in the same folder in the repository. Contact us if you want our most recent version, but you can also run cerc-web with an empty pledge database.  
2d. Create a "helptext" database. The helptext database is a MySQL database. Similar to the pledge database, an sql file exists to assist you in creating this database on your server (public_html/databasemanager/helpdb/db/help_db.sql).   
3. Obtain and/or compile the Climate Equity Reference calculator "engine," which is also released as open source package and can be obtained here: http://gdrs.sourceforge.net
4. Rename the config.php.new file to config.php and enter the relevant information, in particular, the location of the engine and the core database file as well as the connection data to the two MySQL files you created above. Typically, this will also involve the creation of temporary folders and such; make sure to set those folders' permissions such that the php process on your server has read/write access to them.
5. Done.

# Contact and trouble shooting

If you run into issues, feel free to create a ticket on Github, or contact us via www.climateequityreference.org
