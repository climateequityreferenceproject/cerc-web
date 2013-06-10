<?php include_once 'includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Search and Manage Entries | <?php echo $site_title; ?></title>
	<link rel="stylesheet" href="style.css">
  </head>
  <body class="search_form">
  	<div class="wrapper">
    
        <h1>Search and Manage Entries</h1>
        <h2><?php echo $site_title; ?></h2>
    
        <h3><a href="?add">Add new entry &raquo;</a></h3>
        
        <h3>Find existing entries</h3>
    
        <form action="" method="get">

            <label for="text">Leave search field empty to find all entries. 
            <input type="text" name="text" id="text"></label>

            <input type="hidden" name="action" value="search">
            <input type="submit" value="Search" class="submit">
          
        </form>
        <!--<p><a href=".">Return to Search and Manage Entries Home</a></p>-->
	</div>
  </body>
</html>
