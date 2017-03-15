(function() {

    tinymce.PluginManager.add('pushortcodes', function( editor )
    {
        var shortcodeValues = [];
        jQuery.each(shortcodes_button, function(i)
        {
            shortcodeValues.push({text: unescape( shortcodes_button[i].text ), value:i, content: unescape( shortcodes_button[i].content )});
        });

        editor.addButton('pushortcodes', {
            type: 'listbox',
            text: 'WPShop',
            onselect: function(e) {
				console.log( e.control.settings );
                var v = e.control.settings.text;
                tinyMCE.activeEditor.selection.setContent( unescape( v ) );
            },
            values: shortcodeValues
        });
    });
})();
