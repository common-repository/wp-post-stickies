/*
(function() {
    tinymce.PluginManager.add('poststicky_button', function( editor, url ) {
        editor.addButton( 'poststicky_button', {
            text: 'PostSticky',
            icon: false,
            onclick: function() {
                editor.insertContent('[post-sticky type="stickynote" color="yellow" font="indie-flower" align="right"  header="{OPTIONAL - your heading}" body="{REQUIRED - your body text}" footer="{OPTIONAL - your footer text}"]');
            }
        });
    });
})();
*/

(function() {
    tinymce.PluginManager.add('poststicky_button', function( editor, url ) {
        editor.addButton( 'poststicky_button', {
            title: 'PostSticky',
            icon: 'icon poststickies-icon',
            onclick: function() {
                editor.insertContent('[post-sticky type="stickynote" color="yellow" font="indie-flower" align="right"  header="{OPTIONAL - your heading}" body="{REQUIRED - your body text}" footer="{OPTIONAL - your footer text}"]');
            }
        });
    });
})();
