/**
 * Apply CodeMirror to all editor inputs
 * @returns undefined
 */
$(function() {
    "use strict"
    var $editors = $('.code-editor');
    
    $editors.each(function(i, node) {
        var $node = $(node);
        //get the code lang from class
        var lang = $node.attr('class').replace(/.*lang-([^ ]+)($| )/, '$1');
        var codemirror = CodeMirror(function(cm) {
            $(node).parent().append(cm);
        }, {
            //format newlines for codemirror
            value: $node.val().replace(/\\n/g, '\n'),
            mode: lang,
            readOnly: $node.hasClass('readOnly'),
            lineNumbers: true
        });
        
        //store for later use
        $node.data('codemirror', codemirror).hide();
    });

    //update POST value from code editor
    $('form').on('submit', function() {
        $editors.each(function(i, node) {
            var $node = $(node);
            //codemirror seems to strip newlines --> escape
            var val = $node.data('codemirror').getValue().replace(/\n/g, '\\n');
            //update input value
            $node.val(val);
        });
        
    });
});