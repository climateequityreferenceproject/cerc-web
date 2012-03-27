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
        <script type="text/javascript" src="js/tinymce/jscripts/tiny_mce/tiny_mce.js" ></script>
        <script type="text/javascript" />
            tinyMCE.init({
                mode : "textareas",
                theme : "advanced",
                plugins : "spellchecker,preview,save", 

                // Theme options - button# indicated the row# only
                theme_advanced_buttons1 : "save,newdocument,|,undo,redo,|,cut,copy,paste,|,bold,italic,sub,sup,|,outdent,indent,|,formatselect",
                theme_advanced_buttons2 : "bullist,numlist,|,link,unlink,anchor,|,code,spellchecker,charmap",
                theme_advanced_buttons3 : "",
                theme_advanced_toolbar_location : "top",
                theme_advanced_toolbar_align : "left",
                theme_advanced_statusbar_location : "bottom",
                theme_advanced_resizing : true,
                save_onsavecallback : "ajaxSave"
            });
            
            function ajaxSave() {
                var ed = tinyMCE.get('content');

                // Do you ajax call here, window.setTimeout fakes ajax call
                ed.setProgressState(1); // Show progress
                window.setTimeout(function() {
                        ed.setProgressState(0); // Hide progress
                        alert(ed.getContent());
                }, 3000);
            }

        </script >

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
