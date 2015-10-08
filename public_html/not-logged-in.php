<?php
/* This requires that the .htaccess file in the dev calculator root directory contains this line after the htpasswd stuff
ErrorDocument 401 /not-logged-in.php
 */ ?>

<html>
<head>
<title>401 Authorization Required</title>
<META HTTP-EQUIV="refresh" CONTENT="15;URL=http://calculator.climateequityreference.org">
</head>
<body>
<h2> 401 Authorization Required</h2>
The site that you tried to access is for developers only. <br>
The public version of the Climate Equity Reference Calculator is can be found at <a href='http://calculator.climateequityreference.org'>http://calculator.climateequityreference.org</a> (we will take you there shortly).<br>
If you have been directed here by a link, please <a href='http://climateequityreference.org/ticket'>let us know about it</a>.
</body>
</html>