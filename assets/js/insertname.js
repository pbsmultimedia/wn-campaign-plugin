// Register button on document ready and on backend render events
$(document).on('render', function() {
    if (!$.FroalaEditor) {
        return;
    }

    // Register icon and command (only once)
    if (!$.FroalaEditor._insertNameRegistered) {
        /*
        console.log($.FroalaEditor.DEFAULTS);

        $.FroalaEditor.DEFAULTS = $.extend($.FroalaEditor.DEFAULTS, {
            lineBreakerTags: ['figure','table','hr','iframe','form','dl', 'div.section', 'div.col-sm-6']
        });

        $.FroalaEditor.DEFAULTS = $.extend($.FroalaEditor.DEFAULTS, {
            fontFamily: {
                'Inter, sans-serif;': 'Inter',
                'Rift, sans-serif;': 'Rift',
            }
        });    
        
        $.FroalaEditor.DEFAULTS = $.extend($.FroalaEditor.DEFAULTS, {
            events: {
                'initialized': function() {
                    const editor = this;
                    console.log('Editor initialized:', editor.$el.attr('id'));
                    
                    editor.events.on('keydown', function(e) {
                        if (e.which === 8 || e.which === 46) {
                            console.log('Delete/backspace pressed in editor');
                            // Your deletion handling code here
                        }
                    });
                },
                'contentChanged': function () {
                    console.log('Content has changed!');
                }
            }
        });
        */  
        $.FroalaEditor.DefineIcon('insertName', { NAME: 'user' });
        $.FroalaEditor.RegisterCommand('insertName', {
            title: 'Insert recipient name',
            icon: 'insertName',

            callback: function() {
                this.html.insert('<span class="token" data-token="name" contenteditable="false">{{name}}</span>');
                this.events.on('keydown', function(e) {
                    console.log('Keydown in editor');
                });
            }
        });
        $.FroalaEditor._insertNameRegistered = true;
        $.FroalaEditor.pastePlain = true;
        // this should be the way, but makes no effect..
        /*
        $.FroalaEditor.DEFAULTS.events = {
            'initialized': function() {
                const editor = this;
                console.log('Editor initialized:', editor.$el.attr('id'));
                editor.events.on('keydown', function(e) {
                    // handle deletion
                    console.log('Keydown in editor:', editor.$el.attr('id'));
                });
            }
        };
        */   
        // This will be applied to all Froala editor instances
        $.extend($.FroalaEditor.DEFAULTS, {
            events: {
                'initialized': function() {
                    const editor = this;
                    console.log('Editor initialized:', editor.$el.attr('id'));
                    
                    editor.events.on('keydown', function(e) {
                        if (e.which === 8 || e.which === 46) {
                            console.log('Delete/backspace pressed in editor');
                            // Your deletion handling code here
                        }
                    });
                },
                'contentChanged': function () {
                    console.log('Content has changed!');
                }
            }
        });

    }

    console.dir($.FroalaEditor);

    // Hackish, but works..
    // Attach delete handler to Froala editors
    attachDeleteHandlers();

    function attachDeleteHandlers() {
        $('.fr-element').each(function() {
            const el = this;
            
            // Skip if already has handler
            if ($(el).data('token-delete-attached')) return;
            $(el).data('token-delete-attached', true);

            el.addEventListener('keydown', function(e) {
                if (e.key !== 'Backspace' && e.key !== 'Delete') return;

                const sel = window.getSelection();
                if (!sel || !sel.rangeCount) return;
                
                const range = sel.getRangeAt(0);
                if (!range.collapsed) return; // only when caret is collapsed (no selection)

                // Check if caret is inside a token
                let node = sel.anchorNode;
                if (node.nodeType === 3) node = node.parentNode; // text node -> element
                
                const $token = $(node).closest('span[data-token]');
                if ($token.length) {
                    // Inside token - remove it
                    e.preventDefault();
                    e.stopPropagation();
                    removeToken($token[0]);
                    return false;
                }

                // Check if token is adjacent to caret
                let container = range.startContainer;
                let offset = range.startOffset;

                if (container.nodeType === 3) { // Text node
                    if (e.key === 'Backspace' && offset === 0) {
                        // At start of text - check previous sibling
                        const prev = container.previousSibling;
                        if (prev && prev.nodeType === 1 && prev.matches && prev.matches('span[data-token]')) {
                            e.preventDefault();
                            e.stopPropagation();
                            removeToken(prev);
                            return false;
                        }
                    } else if (e.key === 'Delete' && offset === container.nodeValue.length) {
                        // At end of text - check next sibling
                        const next = container.nextSibling;
                        if (next && next.nodeType === 1 && next.matches && next.matches('span[data-token]')) {
                            e.preventDefault();
                            e.stopPropagation();
                            removeToken(next);
                            return false;
                        }
                    }
                } else { // Element node
                    if (e.key === 'Backspace') {
                        const prev = container.childNodes[offset - 1];
                        if (prev && prev.nodeType === 1 && prev.matches && prev.matches('span[data-token]')) {
                            e.preventDefault();
                            e.stopPropagation();
                            removeToken(prev);
                            return false;
                        }
                    } else if (e.key === 'Delete') {
                        const next = container.childNodes[offset];
                        if (next && next.nodeType === 1 && next.matches && next.matches('span[data-token]')) {
                            e.preventDefault();
                            e.stopPropagation();
                            removeToken(next);
                            return false;
                        }
                    }
                }
            }, true); // capture phase
        });
    }

    // Watch for new Froala editors being added to the DOM
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1) { // Element node
                    // Check if the node itself is .fr-element or contains one
                    if (node.classList && node.classList.contains('fr-element')) {
                        attachDeleteHandlers();
                    } else if (node.querySelectorAll) {
                        const elements = node.querySelectorAll('.fr-element');
                        if (elements.length > 0) {
                            attachDeleteHandlers();
                        }
                    }
                }
            });
        });
    });

    // Observe the entire document for new editors
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

    function removeToken(tokenElement) {
        const sel = window.getSelection();
        const placeholder = document.createTextNode('\u200B'); // zero-width space
        
        // Insert placeholder after token
        tokenElement.parentNode.insertBefore(placeholder, tokenElement.nextSibling);
        
        // Remove token
        tokenElement.remove();
        
        // Move caret to placeholder position
        const range = document.createRange();
        range.setStart(placeholder, 0);
        range.collapse(true);
        sel.removeAllRanges();
        sel.addRange(range);
    }
});