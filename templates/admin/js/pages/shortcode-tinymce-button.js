(function() {

    tinymce.PluginManager.add('pushortcodes', function( editor )
    {
        var shortcodeValues = [];
        jQuery.each(shortcodes_button, function(i)
        {
            shortcodeValues.push({text: unescape( shortcodes_button[i] ), value:i});
        });

        editor.addButton('pushortcodes', {
            type: 'listbox',
            text: 'WPShop',
            onselect: function(e) {
                var v = e.control.settings.text;
                tinyMCE.activeEditor.selection.setContent( unescape( v ) );
            },
            values: shortcodeValues
        });
    });
})();
