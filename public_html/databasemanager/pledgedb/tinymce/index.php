<!DOCTYPE html>
<?php
if (isset($_POST['content'])) {
    $text = stripslashes($_POST['content']);
} else {
    $text = '';
}
?>
<html>
    <head>
        <script type="text/javascript" src="js/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
        <script type="text/javascript" src="js/pledge_editor.js"></script>

        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Tiny MCE Test</title>
    </head>
    <body>
        <form target="_self" method="POST">  
            <textarea name="content" cols="50" rows="15" > 
            <?php echo($text); ?>
            </textarea>
        </form>
    </body>
</html>
