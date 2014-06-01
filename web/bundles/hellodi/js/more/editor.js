$(function () {
    $('.text_editor').htmlarea({
        toolbar: [
            ["bold", "italic", "underline", "strikethrough"],
            ["justifyleft", "justifycenter", "justifyright"],
            ["h1", "h2", "h3"],
            ["increasefontsize", "decreasefontsize"],
            ["orderedList  ", "unorderedList  "],
            ["horizontalrule", "superscript", "subscript"],
            ["indent", "outdent"],
            ["forecolor"],
            [
                {
                    css: "BorderButton Button",
                    text: "Add Border Div",
                    action: function () {
                        var html = this.getRange().htmlText;
                        this.iframe[0].contentWindow.focus();
                        var r = this.getRange();
                        if ($.browser.msie) {
                            r.pasteHTML('<div style="border:2px solid black">' + html + '</div>');
                        } else {
                            var newnode = $(this.iframe[0].contentDocument.createElement("div")).css("border", "2px solid black")[0];
                            r.surroundContents(newnode);
                        }
                        r.collapse(false);
                        r.select();
                    }
                },
                {
                    css: "SpanBorderButton Button",
                    text: "Add Border Span",
                    action: function () {
                        var html = this.getRange().htmlText;
                        this.iframe[0].contentWindow.focus();
                        var r = this.getRange();
                        if ($.browser.msie) {
                            r.pasteHTML('<span style="border:2px solid black">' + html + '</span>');
                        } else {
                            var newnode = $(this.iframe[0].contentDocument.createElement("span")).css("border", "2px solid black")[0];
                            r.surroundContents(newnode);
                        }
                        r.collapse(false);
                        r.select();
                    }
                },
                {
                    css: "RemoveStyleButton Button",
                    text: "Remove Style",
                    action: function () {
                        this.iframe[0].contentWindow.focus();
                        var node = $.browser.msie
                            ? this.getRange().parentElement()
                            : this.getRange().startContainer.parentNode;
                        var highlightedText = node.innerHTML;
                        if (node.tagName.toLowerCase() != 'body' && node.tagName.toLowerCase() != 'html') {
                            var newNode = this.iframe[0].contentDocument.createTextNode(highlightedText);
                            $(newNode).insertBefore(node);
                            $(node).remove();
                        }
                        node.collapse(false);
                        node.select();
                    }
                }
            ],
            [
                {
                    css: "PinButton notIMTU Button",
                    text: "Add Pin tag",
                    action: function () {
                        this.pasteHTML('&#123;&#123;pin&#125;&#125;');
                    }
                },
                {
                    css: "SerialButton notIMTU Button",
                    text: "Add Serial tag",
                    action: function () {
                        this.pasteHTML('&#123;&#123;serial&#125;&#125;');
                    }
                },
                {
                    css: "ExpireButton notIMTU Button",
                    text: "Add Expire tag",
                    action: function () {
                        this.pasteHTML('&#123;&#123;expire&#125;&#125;');
                    }
                },
                {
                    css: "duplicateButton notIMTU Button",
                    text: "Add duplicate tag",
                    action: function () {
                        this.pasteHTML('&#123;&#123;duplicate&#125;&#125;');
                    }
                }
            ],
            [
                {
                    css: "recievernumberButton IMTU Button",
                    text: "Add Receiver Number tag",
                    action: function () {
                        this.pasteHTML('&#123;&#123;receiver_number&#125;&#125;');
                    }
                },
                {
                    css: "valuesentButton IMTU Button",
                    text: "Add ValueSent tag",
                    action: function () {
                        this.pasteHTML('&#123;&#123;value_sent&#125;&#125;');
                    }
                },
                {
                    css: "valuepaidButton IMTU Button",
                    text: "Add ValuePaid tag",
                    action: function () {
                        this.pasteHTML('&#123;&#123;value_paid&#125;&#125;');
                    }
                },
                {
                    css: "tranidButton IMTU Button",
                    text: "Add Transaction Id tag",
                    action: function () {
                        this.pasteHTML('&#123;&#123;tran_id&#125;&#125;');
                    }
                }
            ],
            [
                {
                    css: "printDateButton Button",
                    text: "Add print Date tag",
                    action: function () {
                        this.pasteHTML('&#123;&#123;print_date&#125;&#125;');
                    }
                },
                {
                    css: "entityNameButton Button",
                    text: "Add Entity Name tag",
                    action: function () {
                        this.pasteHTML('&#123;&#123;entity_name&#125;&#125;');
                    }
                },
                {
                    css: "operatorButton Button",
                    text: "Add Operator tag",
                    action: function () {
                        this.pasteHTML('&#123;&#123;operator&#125;&#125;');
                    }
                }
            ],
            [
                {
                    css: "entityadrs1Button Button",
                    text: "Add Entity Address Line 1 tag",
                    action: function () {
                        this.pasteHTML('&#123;&#123;entity_address1&#125;&#125;');
                    }
                },
                {
                    css: "entityadrs2Button Button",
                    text: "Add Entity Address Line 2 tag",
                    action: function () {
                        this.pasteHTML('&#123;&#123;entity_address2&#125;&#125;');
                    }
                },
                {
                    css: "entityadrs3Button Button",
                    text: "Add Entity Address Line 3 tag",
                    action: function () {
                        this.pasteHTML('&#123;&#123;entity_address3&#125;&#125;');
                    }
                }
            ]
        ]
    });

    $('.jHtmlArea').attr('style', 'width:100%');
    $('.jHtmlArea .ToolBar').append('<div style="clear: both"></div>').attr('style', 'width:100%');

    $('.jHtmlArea iframe').attr('style', 'min-height: 300px;width: 7.6cm;');
    $('.jHtmlArea iframe body').attr('style', 'width: 7.2cm;word-wrap:break-word;');

    $('.jHtmlArea .BorderButton').html('Border');
    $('.jHtmlArea .SpanBorderButton').html('V Border');
    $('.jHtmlArea .RemoveStyleButton').html('Remove Border');

    $('.jHtmlArea .PinButton').html('Pin');
    $('.jHtmlArea .ExpireButton').html('Expire');
    $('.jHtmlArea .SerialButton').html('Serial');
    $('.jHtmlArea .duplicateButton').html('Duplicate');

    $('.jHtmlArea .printDateButton').html('Print Date');
    $('.jHtmlArea .entityNameButton').html('Entity Name');
    $('.jHtmlArea .operatorButton').html('Operator');
    $('.jHtmlArea .entityadrs1Button').html('Address Line 1');
    $('.jHtmlArea .entityadrs2Button').html('Address Line 2');
    $('.jHtmlArea .entityadrs3Button').html('Address Line 3');

    $('.jHtmlArea .recievernumberButton').html('Receiver Number').hide();
    $('.jHtmlArea .valuesentButton').html('Value Sent').hide();
    $('.jHtmlArea .valuepaidButton').html('Value Paid').hide();
    $('.jHtmlArea .tranidButton').html('Transaction ID').hide();
});