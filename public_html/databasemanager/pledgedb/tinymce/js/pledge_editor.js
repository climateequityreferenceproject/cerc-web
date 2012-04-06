tinyMCE.init({
    mode : "textareas",
    theme : "advanced",
    plugins : "spellchecker,preview",
    // Next three lines remove paragraphs -- don't want them for these short entries
    force_br_newlines : true,
    force_p_newlines : false,
    forced_root_block : '', // Needed for TinyMCE 3.x
    theme_advanced_buttons1 : "undo,redo,|,cut,copy,paste,|,bold,italic,sub,sup,|,link,unlink,|,code,spellchecker,charmap",
    theme_advanced_buttons2 : "",
    theme_advanced_buttons3 : "",
    theme_advanced_toolbar_location : "top",
    theme_advanced_toolbar_align : "left",
    theme_advanced_statusbar_location : "none",
    // From http://stackoverflow.com/questions/9845211/change-tinymce-input-height-from-textarea-height
    setup : function(ed) {
        ed.onInit.add(function(ed, evt) {
            var new_val = '30px';

            // adjust table element
            var elem = document.getElementById(ed.id + '_tbl');
            elem.style.height = new_val;

            // adjust iframe element
            var iframe = document.getElementById(ed.id + '_ifr');
            iframe.style.height = new_val;
        });
   }
});


