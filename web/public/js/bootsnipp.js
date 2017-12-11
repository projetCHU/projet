/* HTML5 Sortable (http://farhadi.ir/projects/html5sortable)
 * Released under the MIT license.
 */(function(a){var b,c=a();a.fn.sortable=function(d){var e=String(d);return d=a.extend({connectWith:!1},d),this.each(function(){if(/^enable|disable|destroy$/.test(e)){var f=a(this).children(a(this).data("items")).attr("draggable",e=="enable");e=="destroy"&&f.add(this).removeData("connectWith items").off("dragstart.h5s dragend.h5s selectstart.h5s dragover.h5s dragenter.h5s drop.h5s");return}var g,h,f=a(this).children(d.items),i=a("<"+(/^ul|ol$/i.test(this.tagName)?"li":"div")+' class="sortable-placeholder">');f.find(d.handle).mousedown(function(){g=!0}).mouseup(function(){g=!1}),a(this).data("items",d.items),c=c.add(i),d.connectWith&&a(d.connectWith).add(this).data("connectWith",d.connectWith),f.attr("draggable","true").on("dragstart.h5s",function(c){if(d.handle&&!g)return!1;g=!1;var e=c.originalEvent.dataTransfer;e.effectAllowed="move",e.setData("Text","dummy"),h=(b=a(this)).addClass("sortable-dragging").index()}).on("dragend.h5s",function(){b.removeClass("sortable-dragging").show(),c.detach(),h!=b.index()&&f.parent().trigger("sortupdate",{item:b}),b=null}).not("a[href], img").on("selectstart.h5s",function(){return this.dragDrop&&this.dragDrop(),!1}).end().add([this,i]).on("dragover.h5s dragenter.h5s drop.h5s",function(e){return!f.is(b)&&d.connectWith!==a(b).parent().data("connectWith")?!0:e.type=="drop"?(e.stopPropagation(),c.filter(":visible").after(b),!1):(e.preventDefault(),e.originalEvent.dataTransfer.dropEffect="move",f.is(this)?(d.forcePlaceholderSize&&i.height(b.outerHeight()),b.hide(),a(this)[i.index()<a(this).index()?"after":"before"](i),c.not(i).detach()):!c.is(this)&&!a(this).children(d.items).length&&(c.detach(),a(this).append(i)),!1)})})}})(jQuery);

/*
HTML Clean for jQuery
Anthony Johnston
http://www.antix.co.uk

version 1.4.0

$Revision: 99 $

requires jQuery http://jquery.com

Use and distibution http://www.opensource.org/licenses/bsd-license.php

2010-04-02 allowedTags/removeTags added (white/black list) thanks to David Wartian (Dwartian)
2010-06-30 replaceStyles added for replacement of bold, italic, super and sub styles on a tag
2012-04-30 allowedAttributes added, an array of attributed allowed on the elements
2013-02-25 now will push non-inline elements up the stack if nested in an inline element
2013-02-25 comment element support added, removed by default, see AllowComments in options
*/
(function ($) {
    $.fn.htmlClean = function (options) {
        // iterate and html clean each matched element
        return this.each(function () {
            if (this.value) {
                this.value = $.htmlClean(this.value, options);
            } else {
                this.innerHTML = $.htmlClean(this.innerHTML, options);
            }
        });
    };

    // clean the passed html
    $.htmlClean = function (html, options) {
        options = $.extend({}, $.htmlClean.defaults, options);
        options.allowEmpty = tagAllowEmpty.concat(options.allowEmpty);

        var tagsRE = /(<(\/)?(\w+:)?([\w]+)([^>]*)>)|<!--(.*?--)>/gi;
        var attrsRE = /([\w\-]+)\s*=\s*(".*?"|'.*?'|[^\s>\/]*)/gi;

        var tagMatch;
        var root = new Element();
        var stack = [root];
        var container = root;

        if (options.bodyOnly) {
            // check for body tag
            if (tagMatch = /<body[^>]*>((\n|.)*)<\/body>/i.exec(html)) {
                html = tagMatch[1];
            }
        }
        html = html.concat("<xxx>"); // ensure last element/text is found
        var lastIndex;

        while (tagMatch = tagsRE.exec(html)) {
            var tag = tagMatch[6]
                ? new Tag("--", null, tagMatch[6], options)
                : new Tag(tagMatch[4], tagMatch[2], tagMatch[5], options);

            // add the text
            var text = html.substring(lastIndex, tagMatch.index);
            if (text.length > 0) {
                var child = container.children[container.children.length - 1];
                if (container.children.length > 0
                        && isText(child = container.children[container.children.length - 1])) {
                    // merge text
                    container.children[container.children.length - 1] = child.concat(text);
                } else {
                    container.children.push(text);
                }
            }
            lastIndex = tagsRE.lastIndex;

            if (tag.isClosing) {
                // find matching container
                if (popToTagName(stack, [tag.name])) {
                    stack.pop();
                    container = stack[stack.length - 1];
                }
            } else {
                // create a new element
                var element = new Element(tag);

                // add attributes
                var attrMatch;
                while (attrMatch = attrsRE.exec(tag.rawAttributes)) {

                    // check style attribute and do replacements
                    if (attrMatch[1].toLowerCase() == "style"
                        && options.replaceStyles) {

                        var renderParent = !tag.isInline;
                        for (var i = 0; i < options.replaceStyles.length; i++) {
                            if (options.replaceStyles[i][0].test(attrMatch[2])) {

                                if (!renderParent) {
                                    tag.render = false;
                                    renderParent = true;
                                }
                                container.children.push(element); // assumes not replaced
                                stack.push(element);
                                container = element; // assumes replacement is a container
                                // create new tag and element
                                tag = new Tag(options.replaceStyles[i][1], "", "", options);
                                element = new Element(tag);
                            }
                        }
                    }

                    if (tag.allowedAttributes != null
                            && (tag.allowedAttributes.length == 0
                            || $.inArray(attrMatch[1], tag.allowedAttributes) > -1)) {
                        element.attributes.push(new Attribute(attrMatch[1], attrMatch[2]));
                    }
                }
                // add required empty ones
                $.each(tag.requiredAttributes, function () {
                    var name = this.toString();
                    if (!element.hasAttribute(name)) element.attributes.push(new Attribute(name, ""));
                });

                // check for replacements
                for (var repIndex = 0; repIndex < options.replace.length; repIndex++) {
                    for (var tagIndex = 0; tagIndex < options.replace[repIndex][0].length; tagIndex++) {
                        var byName = typeof (options.replace[repIndex][0][tagIndex]) == "string";
                        if ((byName && options.replace[repIndex][0][tagIndex] == tag.name)
                                || (!byName && options.replace[repIndex][0][tagIndex].test(tagMatch))) {

                            // set the name to the replacement
                            tag.rename(options.replace[repIndex][1]);

                            repIndex = options.replace.length; // break out of both loops
                            break;
                        }
                    }
                }

                // check container rules
                var add = true;
                if (!container.isRoot) {
                    if (container.tag.isInline && !tag.isInline) {
                        if (add = popToContainer(stack)) {
                            container = stack[stack.length - 1];
                        }
                    } else if (container.tag.disallowNest && tag.disallowNest
                                && !tag.requiredParent) {
                        add = false;
                    } else if (tag.requiredParent) {
                        if (add = popToTagName(stack, tag.requiredParent)) {
                            container = stack[stack.length - 1];
                        }
                    }
                }

                if (add) {
                    container.children.push(element);

                    if (tag.toProtect) {
                        // skip to closing tag
                        var tagMatch2;
                        while (tagMatch2 = tagsRE.exec(html)) {
                            var tag2 = new Tag(tagMatch2[4], tagMatch2[1], tagMatch2[5], options);
                            if (tag2.isClosing && tag2.name == tag.name) {
                                element.children.push(RegExp.leftContext.substring(lastIndex));
                                lastIndex = tagsRE.lastIndex;
                                break;
                            }
                        }
                    } else {
                        // set as current container element
                        if (!tag.isSelfClosing && !tag.isNonClosing) {
                            stack.push(element);
                            container = element;
                        }
                    }
                }
            }
        }

        // render doc
        return $.htmlClean.trim(render(root, options).join(""));
    };

    // defaults
    $.htmlClean.defaults = {
        // only clean the body tagbody
        bodyOnly: true,
        // only allow tags in this array, (white list), contents still rendered
        allowedTags: [],
        // remove tags in this array, (black list), contents still rendered
        removeTags: ["basefont", "center", "dir", "font", "frame", "frameset", "iframe", "isindex", "menu", "noframes", "s", "strike", "u"],
        // array of [attributeName], [optional array of allowed on elements] e.g. [["id"], ["style", ["p", "dl"]]] // allow all elements to have id and allow style on 'p' and 'dl'
        allowedAttributes: [],
        // array of attribute names to remove on all elements in addition to those not in tagAttributes e.g ["width", "height"]
        removeAttrs: [],
        // array of [className], [optional array of allowed on elements] e.g. [["aClass"], ["anotherClass", ["p", "dl"]]]
        allowedClasses: [],
        // format the result
        format: false,
        // format indent to start on
        formatIndent: 0,
        // tags to replace, and what to replace with, tag name or regex to match the tag and attributes
        replace: [
            [["b", "big"], "strong"],
            [["i"], "em"]
        ],
        // styles to replace with tags, multiple style matches supported, inline tags are replaced by the first match blocks are retained
        replaceStyles: [
            [/font-weight:\s*bold/i, "strong"],
            [/font-style:\s*italic/i, "em"],
            [/vertical-align:\s*super/i, "sup"],
            [/vertical-align:\s*sub/i, "sub"]
        ],
        allowComments: false,
        allowEmpty: []
    };

    function applyFormat(element, options, output, indent) {
        if (element.tag.format && output.length > 0) {
            output.push("\n");
            for (var i = 0; i < indent; i++) output.push("\t");
        }
    }

    function render(element, options) {
        var output = [], empty = element.attributes.length == 0, indent = 0;

        if (element.tag.isComment) {
            if (options.allowComments) {
                output.push("<!--");
                output.push(element.tag.rawAttributes);
                output.push(">");

                if (options.format) applyFormat(element, options, output, indent - 1);
            }
        } else {

            // don't render if not in allowedTags or in removeTags
            var renderTag
                = element.tag.render
                    && (options.allowedTags.length == 0 || $.inArray(element.tag.name, options.allowedTags) > -1)
                    && (options.removeTags.length == 0 || $.inArray(element.tag.name, options.removeTags) == -1);

            if (!element.isRoot && renderTag) {

                // render opening tag
                output.push("<");
                output.push(element.tag.name);
                $.each(element.attributes, function () {
                    if ($.inArray(this.name, options.removeAttrs) == -1) {
                        var m = RegExp(/^(['"]?)(.*?)['"]?$/).exec(this.value);
                        var value = m[2];
                        var valueQuote = m[1] || "'";

                        // check for classes allowed
                        if (this.name == "class" && options.allowedClasses.length > 0) {
                            value =
                            $.grep(value.split(" "), function (c) {
                                return $.grep(options.allowedClasses, function (a) {
                                    return a == c
                                        || (a[0] == c && (a.length == 1 || $.inArray(element.tag.name, a[1]) > -1));
                                }).length > 0;
                            })
                            .join(" ");
                        }

                        if (value != null && (value.length > 0 || $.inArray(this.name, element.tag.requiredAttributes) > -1)) {
                            output.push(" ");
                            output.push(this.name);
                            output.push("=");
                            output.push(valueQuote);
                            output.push(value);
                            output.push(valueQuote);
                        }
                    }
                });
            }

            if (element.tag.isSelfClosing) {
                // self closing
                if (renderTag) output.push(" />");
                empty = false;
            } else if (element.tag.isNonClosing) {
                empty = false;
            } else {
                if (!element.isRoot && renderTag) {
                    // close
                    output.push(">");
                }

                indent = options.formatIndent++;

                // render children
                if (element.tag.toProtect) {
                    outputChildren = $.htmlClean.trim(element.children.join("")).replace(/<br>/ig, "\n");
                    output.push(outputChildren);
                    empty = outputChildren.length == 0;
                } else {
                    var outputChildren = [];
                    for (var i = 0; i < element.children.length; i++) {
                        var child = element.children[i];
                        var text = $.htmlClean.trim(textClean(isText(child) ? child : child.childrenToString()));
                        if (isInline(child)) {
                            if (i > 0 && text.length > 0
                        && (startsWithWhitespace(child) || endsWithWhitespace(element.children[i - 1]))) {
                                outputChildren.push(" ");
                            }
                        }
                        if (isText(child)) {
                            if (text.length > 0) {
                                outputChildren.push(text);
                            }
                        } else {
                            // don't allow a break to be the last child
                            if (i != element.children.length - 1 || child.tag.name != "br") {
                                if (options.format) applyFormat(child, options, outputChildren, indent);
                                outputChildren = outputChildren.concat(render(child, options));
                            }
                        }
                    }
                    options.formatIndent--;

                    if (outputChildren.length > 0) {
                        if (options.format && outputChildren[0] != "\n") applyFormat(element, options, output, indent);
                        output = output.concat(outputChildren);
                        empty = false;
                    }
                }

                if (!element.isRoot && renderTag) {
                    // render the closing tag
                    if (options.format) applyFormat(element, options, output, indent - 1);
                    output.push("</");
                    output.push(element.tag.name);
                    output.push(">");
                }
            }

            // check for empty tags
            if (!element.tag.allowEmpty && empty) { return []; }
        }

        return output;
    }

    // find a matching tag, and pop to it, if not do nothing
    function popToTagName(stack, tagNameArray) {
        return pop(
            stack,
            function (element) {
                return $.inArray(element.tag.nameOriginal, tagNameArray) > -1;
            });
    }

    function popToContainer(stack) {
        return pop(
            stack,
            function (element) {
                return element.isRoot || !element.tag.isInline;
            });
    }

    function pop(stack, test, index) {
        index = index || 1;
        var element = stack[stack.length - index];
        if (test(element)) {
            return true;
        } else if (stack.length - index > 0
                && pop(stack, test, index + 1)) {
            stack.pop();
            return true;
        }
        return false;
    }

    // Element Object
    function Element(tag) {
        if (tag) {
            this.tag = tag;
            this.isRoot = false;
        } else {
            this.tag = new Tag("root");
            this.isRoot = true;
        }
        this.attributes = [];
        this.children = [];

        this.hasAttribute = function (name) {
            for (var i = 0; i < this.attributes.length; i++) {
                if (this.attributes[i].name == name) return true;
            }
            return false;
        };

        this.childrenToString = function () {
            return this.children.join("");
        };

        return this;
    }

    // Attribute Object
    function Attribute(name, value) {
        this.name = name;
        this.value = value;

        return this;
    }

    // Tag object
    function Tag(name, close, rawAttributes, options) {
        this.name = name.toLowerCase();
        this.nameOriginal = this.name;
        this.render = true;

        this.init = function () {
            if (this.name == "--") {
                this.isComment = true;
                this.isSelfClosing = true;
                this.format = true;
            } else {
                this.isComment = false;
                this.isSelfClosing = $.inArray(this.name, tagSelfClosing) > -1;
                this.isNonClosing = $.inArray(this.name, tagNonClosing) > -1;
                this.isClosing = (close != undefined && close.length > 0);

                this.isInline = $.inArray(this.name, tagInline) > -1;
                this.disallowNest = $.inArray(this.name, tagDisallowNest) > -1;
                this.requiredParent = tagRequiredParent[$.inArray(this.name, tagRequiredParent) + 1];
                this.allowEmpty = options && $.inArray(this.name, options.allowEmpty) > -1;

                this.toProtect = $.inArray(this.name, tagProtect) > -1;

                this.format = $.inArray(this.name, tagFormat) > -1 || !this.isInline;
            }
            this.rawAttributes = rawAttributes;
            this.requiredAttributes = tagAttributesRequired[$.inArray(this.name, tagAttributesRequired) + 1];

            if (options) {
                if (!options.tagAttributesCache) options.tagAttributesCache = [];
                if ($.inArray(this.name, options.tagAttributesCache) == -1) {
                    var cacheItem = tagAttributes[$.inArray(this.name, tagAttributes) + 1].slice(0);

                    // add extra ones from options
                    for (var i = 0; i < options.allowedAttributes.length; i++) {
                        var attrName = options.allowedAttributes[i][0];
                        if ((
                            options.allowedAttributes[i].length == 1
                                || $.inArray(this.name, options.allowedAttributes[i][1]) > -1
                        ) && $.inArray(attrName, cacheItem) == -1) {
                            cacheItem.push(attrName);
                        }
                    }

                    options.tagAttributesCache.push(this.name);
                    options.tagAttributesCache.push(cacheItem);
                }

                this.allowedAttributes = options.tagAttributesCache[$.inArray(this.name, options.tagAttributesCache) + 1];
            }
        };

        this.init();

        this.rename = function (newName) {
            this.name = newName;
            this.init();
        };

        return this;
    }

    function startsWithWhitespace(item) {
        while (isElement(item) && item.children.length > 0) {
            item = item.children[0];
        }
        if (!isText(item)) return false;
        var text = textClean(item);
        return text.length > 0 && $.htmlClean.isWhitespace(text.charAt(0));
    }
    function endsWithWhitespace(item) {
        while (isElement(item) && item.children.length > 0) {
            item = item.children[item.children.length - 1];
        }
        if (!isText(item)) return false;
        var text = textClean(item);
        return text.length > 0 && $.htmlClean.isWhitespace(text.charAt(text.length - 1));
    }
    function isText(item) { return item.constructor == String; }
    function isInline(item) { return isText(item) || item.tag.isInline; }
    function isElement(item) { return item.constructor == Element; }
    function textClean(text) {
        return text
            .replace(/ |\n/g, " ")
            .replace(/\s\s+/g, " ");
    }

    // trim off white space, doesn't use regex
    $.htmlClean.trim = function (text) {
        return $.htmlClean.trimStart($.htmlClean.trimEnd(text));
    };
    $.htmlClean.trimStart = function (text) {
        return text.substring($.htmlClean.trimStartIndex(text));
    };
    $.htmlClean.trimStartIndex = function (text) {
        for (var start = 0; start < text.length - 1 && $.htmlClean.isWhitespace(text.charAt(start)); start++);
        return start;
    };
    $.htmlClean.trimEnd = function (text) {
        return text.substring(0, $.htmlClean.trimEndIndex(text));
    };
    $.htmlClean.trimEndIndex = function (text) {
        for (var end = text.length - 1; end >= 0 && $.htmlClean.isWhitespace(text.charAt(end)); end--);
        return end + 1;
    };
    // checks a char is white space or not
    $.htmlClean.isWhitespace = function (c) { return $.inArray(c, whitespace) != -1; };

    // tags which are inline
    var tagInline = [
        "a", "abbr", "acronym", "address", "b", "big", "br", "button",
        "caption", "cite", "code", "del", "em", "font",
        "hr", "i", "input", "img", "ins", "label", "legend", "map", "q",
        "s", "samp", "select", "option", "param", "small", "span", "strike", "strong", "sub", "sup",
        "tt", "u", "var"];
    var tagFormat = ["address", "button", "caption", "code", "input", "label", "legend", "select", "option", "param"];
    var tagDisallowNest = ["h1", "h2", "h3", "h4", "h5", "h6", "p", "th", "td", "object"];
    var tagAllowEmpty = ["th", "td"];
    var tagRequiredParent = [
        null,
        "li", ["ul", "ol"],
        "dt", ["dl"],
        "dd", ["dl"],
        "td", ["tr"],
        "th", ["tr"],
        "tr", ["table", "thead", "tbody", "tfoot"],
        "thead", ["table"],
        "tbody", ["table"],
        "tfoot", ["table"],
        "param", ["object"]
        ];
    var tagProtect = ["script", "style", "pre", "code"];
    // tags which self close e.g. <br />
    var tagSelfClosing = ["area", "base", "br", "col", "command", "embed", "hr", "img", "input", "keygen", "link", "meta", "param", "source", "track", "wbr"];
    // tags which do not close
    var tagNonClosing = ["!doctype", "?xml"];
    // attributes allowed on tags
    var tagAttributes = [
            ["class"],  // default, for all tags not mentioned
            "?xml", [],
            "!doctype", [],
            "a", ["accesskey", "class", "href", "name", "title", "rel", "rev", "type", "tabindex"],
            "abbr", ["class", "title"],
            "acronym", ["class", "title"],
            "blockquote", ["cite", "class"],
            "button", ["class", "disabled", "name", "type", "value"],
            "del", ["cite", "class", "datetime"],
            "form", ["accept", "action", "class", "enctype", "method", "name"],
            "iframe", ["class", "height", "name", "sandbox", "seamless", "src", "srcdoc", "width"],
            "input", ["accept", "accesskey", "alt", "checked", "class", "disabled", "ismap", "maxlength", "name", "size", "readonly", "src", "tabindex", "type", "usemap", "value"],
            "img", ["alt", "class", "height", "src", "width"],
            "ins", ["cite", "class", "datetime"],
            "label", ["accesskey", "class", "for"],
            "legend", ["accesskey", "class"],
            "link", ["href", "rel", "type"],
            "meta", ["content", "http-equiv", "name", "scheme", "charset"],
            "map", ["name"],
            "optgroup", ["class", "disabled", "label"],
            "option", ["class", "disabled", "label", "selected", "value"],
            "q", ["class", "cite"],
            "script", ["src", "type"],
            "select", ["class", "disabled", "multiple", "name", "size", "tabindex"],
            "style", ["type"],
            "table", ["class", "summary"],
            "th", ["class", "colspan", "rowspan"],
            "td", ["class", "colspan", "rowspan"],
            "textarea", ["accesskey", "class", "cols", "disabled", "name", "readonly", "rows", "tabindex"],
            "param", ["name", "value"],
            "embed", ["height", "src", "type", "width"]
        ];
    var tagAttributesRequired = [[], "img", ["alt"]];
    // white space chars
    var whitespace = [" ", " ", "\t", "\n", "\r", "\f"];

})(jQuery);

/**
 * $.fn.getComments() is used to extract the html comments from a HTML elements. <br/>
 * [Project Page] (https://github.com/LarryBattle/jQuery.getComments)
 *
 * @author Larry Battle <http://bateru.com/news/contact-me>
 * @license MIT
 * @version 0.1, June 11, 2012
 */
$(function(){var getCommentsFromEl=function(el,asArray){var result,$el=$(el).contents();result=$el.filter(function(){return this.nodeType==8;});if(asArray){result=$.makeArray(result.map(function(){return this.nodeValue;}));}
return result;};$.fn.getComments=function(asArray){return getCommentsFromEl(this,asArray);};$.fn.getComments.version="0.1";});









/*! FormBuilder v 1.0 by icytin. Created 2014-05-27
*/
/**
* FORMBUILDER. Adapté par LOUIS MARCHAND
**/
var FormBuilder = function ($) {
    //== Private variables ==

    //== Initializer ==
    $(function () {
      init();
    });

    //== Private functions ==
    var init = function() {
      SourceHandler.init();
      TargetHandler.init();
      ComponentActionHandler.init();
      initTextArea();
    }

    /*INTIALISATION DE LA ZONE DE CRÉATION DU FORMULAIRE ('Aperçu du questionnaire')*/
    var initTextArea = function() {
      $('#formSource').on('change', function() {
        $(this).css('height', 'auto' );
        $(this).height( this.scrollHeight );
      });
    }

    // ==== Source ====
    var SourceHandler = function() {

      var initDraggable = function() {

        var sourceSortableIn;
        $(".sourceHolder").sortable({
          connectWith: ".connectedSortable",
          over: function(e, ui) { sourceSortableIn = true; },
          out: function(e, ui) { sourceSortableIn = false; },
          helper: function (e, div) {
            this.copyHelper = div.clone().insertAfter(div);
            $(this).data('copied', false);
            $(contextMenu).hide();

            return div.clone();
          },
          stop: function () {
            var copied = $(this).data('copied');
            if (!copied) {
              this.copyHelper.remove();
            }
            if(!copied && (!sourceSortableIn || sourceSortableIn !== undefined)) {
              if($('div.active').find('label:contains("' + this.copyHelper.find('label').html() + '")').length === 0) {
                $('div.active').append(this.copyHelper.clone());
              }
            }
            this.copyHelper = null;
          }
        });
      }

      return {
        init: function() {
          initDraggable();
        }
      };
    }(jQuery);

    // ==== Target ====
    var TargetHandler = function() {

      var initDraggable = function() {
        var targetSortableIn;
        $("#target").sortable({
            over: function(e, ui) { targetSortableIn = true; },
            out: function(e, ui) { targetSortableIn = false; },
            receive: function (e, ui) {
                ui.sender.data('copied', true);
                MarkupHandler.refresh();
            },
            helper: function (e, div) {
              $(contextMenu).hide();
              return div;
            },
            beforeStop: function (event, ui) {
              if (!targetSortableIn) {
                ui.item.remove();
              }
            },
            stop: function () {
              this.copyHelper = null;
              MarkupHandler.refresh();
            },
            cancel: ".ui-state-disabled"
        });
      }

      return {
        init: function() {
          initDraggable();
          MenuHandler.initContextMenu('#formVisualizer form', '#target, legend' );
        }
      };
    }(jQuery);





    // ==== Menu ====
    var MenuHandler = function() {

      var $target;

      return {
        initContextMenu: function(section, action) {

          if(section === undefined || action === undefined || section.length === 0 || action.length === 0) {
            alert('Context menu plugin is not correctly initialized');
            return;
          }

          var $contextMenu = $("#contextMenu");

          $(section).on("contextmenu", action, function(e) {
            if($('.highLight').length !== 0){ // No menu available in edit mode
              return false;
            }

            $target = $(e.target);
            if(($target.parents('div.form-group').length === 0 && !$target.hasClass('form-group'))) {
              // hide all
              $('#edit').hide();
              $('#validationRules').hide();
              $('#contextMenu li.divider').hide();

              // special case
              if($target.is('legend')) {
                $('#edit').show();
              }
            }
            else {
              $('#edit').show();
              $('#validationRules').show();
              $('#contextMenu li.divider').show();
            }

            $contextMenu.css({
              display: "block",
              left: e.pageX,
              top: e.pageY
            });
            return false;
          });

          $contextMenu.on("click", "a", function(e) {
            switch($(this).prop('id'))
            {
              case 'edit':
                ComponentActionHandler.edit($target);
                break;
              case 'validationRules':
                alert("TODO: Handle validation rules");
                break;
              case 'clearForm':

                $('#confirm-modal').modal();
                $('#confirmButton').unbind('click').click(function() {
                  $(section).find('#target').html('');
                  $(section).find('legend').html('New Form');
                  MarkupHandler.refresh();
                });

                break;
              default:
                break;
            }

            $contextMenu.hide();
          });


            //BUG CLIQUE DROIT : À CORRIGER
            /*
            $(document).click(function(event) {
              if(!$(event.target).closest(contextMenu).length) {
                if($(contextMenu).is(":visible")) {
                  $(contextMenu).hide();
                }
              }
            });
            */
        }
      };
    }(jQuery);

    var GeneralSortableHandler = function() {
      return {
        setSortableSection: function(sectionSelector, sortable) {
          if(sortable) {
            $(sectionSelector).removeClass('ui-state-disabled');
            $('.form-group, .form-group label').css('cursor', 'pointer');
          }
          else {
            $(sectionSelector).addClass('ui-state-disabled');
            $('.form-group, .form-group label').css('cursor', 'default');
          }
        }
      };
    }(jQuery);

    var ComponentActionHandler = function() {

      var currentTarget;

      return {
        init: function() {
          $('#formVisualizer').on('click', function(e) {
            var $target = $(e.target);
            if($target.prop('id') === 'editConfirm') {
              TemplateHandler.getActionFunction();
            }
            else if($target.prop('id') === 'editAbort') {

            }

            if($target.prop('id') === 'editAbort' || $target.prop('id') === 'editConfirm') {
              GeneralSortableHandler.setSortableSection('#target .form-group', true);
              $('#editForm').remove();
              $('.highLight').removeClass('highLight');
              MarkupHandler.refresh();
            }

            if($target.is('input')) { // Add those that should not be disabled..
              return;
            }

            e.preventDefault();
            e.stopPropagation();
            return false;

          });
        },
        edit: function(target) {
          var t = target.hasClass('form-group') ? target : target.parents('div.form-group');
          currentTarget = t;
          var cs = t.getComments(true);
          var c = cs.length === 0 ? undefined : cs[0].trim().toUpperCase();
          switch(c)
          {
            /* Saisis ========================= */
            case 'SAISIE MONOLIGNE':
              InputHandler.textInput(t);
              break;
            case 'SAISIE MULTILIGNE':
              InputHandler.textArea(t);
            break;
            /* Choix =============== */
            case 'CHOIX UNIQUE':
            case 'CHOIX MULTIPLES':
            break;
            /* Listes ========================= */
            case 'LISTE CHOIX UNIQUE':
            case 'LISTE CHOIX MULTIPLES':
              SelectHandler.init(t);
            break;
            /* Titre ========================== */
            case 'SECTION' :
              SectionHandler.init(t);
            break;
            default:
              // Special cases
              if(target.is('legend')) {
                OtherComponentsHandler.legend(target);
              }
            break;
          }
        },
        getCurrentTarget: function() {
          return currentTarget;
        }
      }
    }(jQuery);


    /**
      REGLAGES CLIQUE DROIT SUR ELEMENTS 'SELECT' : liste de choix.
    **/
    var SelectHandler = function() {
      var initGeneral = function(target) {
        TemplateHandler.getEditForm(target.find('label').html()).insertAfter(target);
        $('#editForm').append(TemplateHandler.getInput('labelInput', 'Question', target.find('label').html()));


        //inscire les valeurs de références dans la liste déroulante.
            //À l'avenir.
            //Récupérer les valeurs de références dans la base mongodb
            //en attendant, on le fait en dur. les valeurs sont inscrites dans la balise div 'set_valref'
        //LE SELECT pour les valeurs de référence
        var $valRefs = $(document.getElementById('set_valref')).attr('value').split(',');
        //TESTS POUR INCLURE DES VALEURS DE RÉFÉRENCES.
        $('#editForm').append(TemplateHandler.createSelectboxValRef('labelvalref','Valeurs de références',$valRefs));

        //LE TYPE DE GRAPHIQUE ASSOCIÉ
        $('#editForm').append(TemplateHandler.createSelectBoxGraph('graphtype','Graphique associé'));

        //le tableau dynamic pour les réponses.
        var scores = $('#set_scores').attr('value');
        $('#editForm').append(TemplateHandler.createTableDynamicIncrement('le_tab','test tableau','Réponses',scores));

        //GESTION DU BOUTON VIDER DU TABLEAU DE RÉPONSE.
        document.getElementById('buttonClear').addEventListener('click',function(){
          $('#tabRep').html('<table id="tabRep" name="tabRep" ></table>');//ON REMPLACE LE TABLEAU PAR UN TABLEAU VIERGE
        });



        //GESTION DU BOUTON AJOUT LIGNE TABLEAU DE RÉPONSE.
        //EN fonction des scores cochés dans le widget "chexkboxesScore" on affiche un tableau pour indique le nom des réponses et les scores de chaque réponse.
        document.getElementById('buttonAddRow').addEventListener('click',function(){
          var $le_tab = document.getElementById('tabRep');                        //LE TABLEAU DES RÉPONSES
          var nb_row = document.getElementsByClassName("row_reponse").length;     //LE NOMBRE DE LIGNE DÉJÀ PRÉSENTE DANS LE TABLEAU

          if(null== document.getElementById("tabRep_header_row")){               //Si le tableau ne contient pas encore de noms de colonnes(aucune ligne de réponse n'a été ajoutée, le tableau est vierge)
              $('<tr id="tabRep_header_row" name="tabRep_header_row">').appendTo($le_tab);                    //On ajoute la ligne header du tableau
              $('<th name="tabRep_header_col_rep" class="tabRep_header_col">Intitulé</th>').appendTo($le_tab); //on ajoute la colonnne réponse

              var scores = $('#set_scores').attr('value');    //on récupère la balise score (elle contien dans son attribut value la liste des scores pour le questionnaire)
              scores = scores.trim().split(';');              //on split et on découpe sur les ;
              if(scores.length==1 && scores[0]=="")           //si la chaine est ""
                scores=null;                                  //on met à null
              $.each(scores,function(i){                      //pour chaque score
                  scores[i]=scores[i].trim();                 //on trim la chaîne
                  if(scores[i]!="")                           //on vérifie que la chaîne n'est pas vide
                    $('<th name="tabRep_header_col_score" class="tabRep_header_col" >'+scores[i]+'</th>').appendTo($le_tab);  //on ajoute une colonne dans le tableau pour ce score
              });
              $('</tr>').appendTo($le_tab);                   //on ferme la ligne header.
          };//fin du test pour l'ajout de l'entete avec les bonnes colonnes en fonction des scores cochés


          $('<tr class="row_reponse" id="row_reponse'+nb_row+'">').appendTo($le_tab);    //on ajoute le debu de la nouvelle ligne de réponse.
          $('<td><input class="tab_cell_rep" type="text" id="'+nb_row+'0"></td>').appendTo($le_tab);//on ajoute la colonne "réponse" ayant pour indice (nb_row,0) avec nbrow=numéro de la nouvelle ligne

          var header_scores =  $("[name='tabRep_header_col_score']"); //on récupère les scores présent dans les colonnes du header du tableau
          $.each(header_scores,function(i){                            //pour chacun on met une colonne dans la nouvelle ligne
            var ind_col = i+1;                                         //indice colonne de la case du tbleau
            $('<td><input class="tab_cell_score" type="text" id="'+nb_row+''+ind_col+'"></td>').appendTo($le_tab);//la nouvelle colonne avec pour indice (nb_row,ind_col) avec nbrow=numéro de la nouvelle ligne.
          });
          $('</tr>').appendTo($le_tab);                      //on ferme la balise de la nouvelle ligne ajoutée
        });//FIN AJOUT LIGNE



        //PRÉCHARGEMENT DES VALEURS.
        //les valeurs de références
        var $preselectedValRef = $(target.find('val')).text();       //la valeur déjà choisie
        if($preselectedValRef != '' ){
          document.getElementById('labelvalref').value=$preselectedValRef;//préchargement dans le menu
        }
        //le type de graph choisi
        var $preselectedGraph = $(target.find('graph')).text();       //la valeur déjà choisie
        if($preselectedGraph != '' ){
          document.getElementById('graphtype').value=$preselectedGraph;//préchargement dans le menu
        }

              //PRÉCHARGEMENT DES RÉPONSES DU TABLEAU
              var options = target.find('select option');     //les options (réponses qui existent déjà)
              var vOpt = '', vVal = '', length = options.length; //les autres variables.
              var $le_tab = document.getElementById('tabRep');//le tableau
              //on écrit l'entête du tableau.
              $('<tr id="tabRep_header_row" name="tabRep_header_row">').appendTo($le_tab);                    //On ajoute la ligne header du tableau
              $('<th name="tabRep_header_col_rep" class="tabRep_header_col">Intitulé</th>').appendTo($le_tab); //on ajoute la colonnne réponse

              var scores = $('#set_scores').attr('value');    //on récupère la balise score (elle contien dans son attribut value la liste des scores pour le questionnaire)
              scores = scores.trim().split(';');              //on split et on découpe sur les ;
              if(scores.length==1 && scores[0]=="")           //si la chaine est ""
                scores=null;                                  //on met à null
              $.each(scores,function(i){                      //pour chaque score
                  scores[i]=scores[i].trim();                 //on trim la chaîne
                  if(scores[i]!="")                           //on vérifie que la chaîne n'est pas vide
                    $('<th name="tabRep_header_col_score" class="tabRep_header_col" >'+scores[i]+'</th>').appendTo($le_tab);  //on ajoute une colonne dans le tableau pour ce score
              });
              $('</tr>').appendTo($le_tab);                   //on ferme la ligne header.


              $.each(options, function(i, val) {              //pour chaque réponse à la question
                vOpt = $(val).html().trim();                 //on récupère les label des réponses
                vVal = $(val).prop('value');                 //on récupère les labels des values des réponses.
                  //on ajoute le debut de la ligne de réponse
                $('<tr class="row_reponse">').appendTo($le_tab);
                  //la colonne réponse avec sa valeur
                $('<td><input class="tab_cell_rep" type="text" id="'+i+'0" value="'+vOpt+'"></td>').appendTo($le_tab);

                  //pour chaque valuation dans l'attribut value de la réponse (pour chaque score)
                var tabVal = vVal.trim().split(";");      //on traite la chaine de l'attribut value qui contient les valeurs des scores.
                $.each(tabVal,function(j,valu){
                  valu = valu.trim();
                    //on ajoute la case du score dans le tableau
                  $('<td><input class="tab_cell_score" type="text" id="'+i+''+(j+1)+'" value="'+valu+'"></td>').appendTo($le_tab);
                });
                  //fin de la ligne de réponse
                $('</tr>').appendTo($le_tab);
              });//fin each sur les réponses
      };//initGeneral fin


      //RETOUR DE LA FONCTION
      return {
        init: function(target) {
          initGeneral(target);
          TemplateHandler.init(function() {
            SelectHandler.defaultApplyCode(ComponentActionHandler.getCurrentTarget());
          });
        },
        defaultApplyCode: function(target) {
          var $editForm = $('#editForm');
          if($editForm.find('#idInput').val() !== '') {
            target.find('table').attr('id', $editForm.find('#idInput').val()).attr('name', $editForm.find('#idInput').val());
          }
          if($('#labelInput').val() !== '') {
            target.find('label:first').html($('#labelInput').val());
          }

          //APPARENCE ET ALIGNEMENT
          TemplateHandler.updateSize(target.find('label'), 4);
          TemplateHandler.updateSize(target.find('select').parents('div[class^="col-"]').first(), 7);

          //TABLEAU DE RÉPONSE
          target.find('select').html('');
          //la zone de test pour le tableau de réponses
          var scores_header =  $("[name='tabRep_header_col_score']"); //le nombre de colonne de score
          var nb_row = document.getElementsByClassName("row_reponse").length;//le nombre de ligne de réponses dans le tableau
          for (var i=0; i < nb_row; i++){                             //pour chaque réponse dans le tableau
            var v0 = $('#'+i+''+0).val();   //le nom de la réponse
            var score='';                   //le score de la réponse
            var indLigne=i;                 //l'indice de ligne de la réponse dans le tableau
            $.each(scores_header,function(indColonne){  //pour chaque colonne de score
              if(indColonne==scores_header.length-1)
                score+=$('#'+indLigne+''+(indColonne+1)).val();
              else
                score+=$('#'+indLigne+''+(indColonne+1)).val()+' ; ';//on stocke la valeur du score dans la var score
            });
            $option = $('<option>' + v0 + '</option>');     //on ajoute l'option
            $option.prop('value', ''+score);                //on regle la propriété value à "score"
            target.find('select').append($option);          //et on ajoute dans le code html de la zone markup(zone de finalisation)
          }//for end


          //LA DIV DE LA QUESTION
          var $la_bonne_div = target.find('div');
          if(target.find('textarea').length!=0){                                //si la question est un textarea, on choisi la bonne div
            $la_bonne_div = $la_bonne_div.find('div');}
          //RETOUR DE LA VALEUR DE RéFéRENCE
          var listValRef = document.getElementById('labelvalref');              //liste des valeurs.
          var selectedRef= listValRef.options[listValRef.selectedIndex].value;  //valeur sélectionnée
          //on renseigne la valeur de références
          $(target.find('val')).remove();
          $la_bonne_div.append('<val name="valref" style="display:none">'+selectedRef+'</val>');
          //RETOUR DU GRAPH CHOISI
          var listGraph = document.getElementById('graphtype');                 //liste des graphs
          var selectedGraph = listGraph.options[listGraph.selectedIndex].value; //le graph selectionné
          //on renseigne le graph choisi
          $(target.find('graph')).remove();                                     //on reitre la balise graph
          $la_bonne_div.append('<graph style="display:none" >'+selectedGraph+'</graph>');//on ajoute la balise graph mise à jour

       }//defaultApplyCode end

      };
    }(jQuery);



    /**
      REGLAGES CLIQUE DROIT SUR ELEMENT 'Section'.
    **/
    var SectionHandler = function() {
      //CHARGEMENT DES ÉLÉMENTS DU MENU.
      var initGeneral = function(target) {
          TemplateHandler.getEditForm(target.find('label').html()).insertAfter(target);
          //Text de la section
          $('#editForm').append(TemplateHandler.createSectionTextArea('Section_text','Nom de la section :'));

          //PRÉCHARGEMENT DES VALEURS
          $(document.getElementById('Section_title')).attr('value',target.find('label').text());
          $(document.getElementById('Section_text')).append(target.find('textarea').text());
      };
      //RETOUR DE LA FONCTION
      return {
        init: function(target) {
          initGeneral(target);
          TemplateHandler.init(function() {
            SectionHandler.defaultApplyCode(ComponentActionHandler.getCurrentTarget());
          });
        },
        defaultApplyCode: function(target) {
          var SectionText = $(document.getElementById('Section_text')).prop('value');       //récupèrer le texte.
          var texte = '<textarea id="textarea" name="textInput">'+SectionText+'</textarea>';//on réécrit la zone de texte de la section

          target.find('div').html('');                                                      //on vide la div de la "SECTION"
          target.find('div').html(texte);                                                   //le texte dans la balise div de la target
       }//defaultApplyCode end
      };
    }(jQuery);



    /**
      REGLAGES CLIQUE DROIT SUR ELEMENT INPUT : champ saisie classique, textarea
    **/
    var InputHandler = function() {
      //INTIALISATION DES ÉLÉMÉNTS DU MENU
      var initGeneralInputForm = function(target) {
          TemplateHandler.getEditForm(target.find('label').html()).insertAfter(target);
          var inputSelector = 'input[type="text"], input[type="password"]', typeOfInput = 'input';
          if(target.find('textarea').length !== 0) {
            inputSelector = 'textarea';
            typeOfInput = 'textarea';
          }

          //LE LABEL pour le nom de la question
          $('#editForm').append(TemplateHandler.getInput('labelInput', 'Question', target.find('label').html()));

          //LE SELECT pour les valeurs de référence
          var $valRefs = $(document.getElementById('set_valref')).attr('value').split(',');
          //TESTS POUR INCLURE DES VALEURS DE RÉFÉRENCES.
          $('#editForm').append(TemplateHandler.createSelectboxValRef('labelvalref','Valeurs de références',$valRefs));

          //LE TYPE DE GRAPHIQUE ASSOCIÉ
          $('#editForm').append(TemplateHandler.createSelectBoxGraph('graphtype','Graphique associé'));

          //LE TYPE de valeur authorisé/attendu.
          //seulement pour les champs text pas les textareas
          if(target.find('textarea').length === 0){
            $("#editForm").append(TemplateHandler.createSelectBoxDataConstraint('datatype','Type de réponse'));
            //si le choix est number, alors afficher les contraintes suppléméentaire.
            document.getElementById('datatype').addEventListener('click',function(){
              //on vérifie que les contraintes ne soient pas déjà affichées.
                var listDataType = document.getElementById('datatype');                   //list des types de data
                var selectedType = listDataType.options[listDataType.selectedIndex].value;//le type de data sélectionné
                if(selectedType == 'number' && document.getElementById('dataconstraints') == null){//alors on affiche les contraintes supplémentaires
                  $("#ConstraintElements").append(TemplateHandler.createDataConstraints('dataconstraints','Contraintes'));
                }//end if
                else if (selectedType == 'text' && document.getElementById('dataconstraints') != null){//sinon on retire les contraintes supplémentaires
                  $(document.getElementById('dataconstraints')).remove();
                }
            });//end listener
          }

          //PRÉCHARGEMENT DES DONNÉES
            //LA VALEUR DE RÉFÉRENCE
            var $preselectedValRef = $(target.find('val')).text();       //la valeur déjà choisie
            if($preselectedValRef != '' ){
              document.getElementById('labelvalref').value=$preselectedValRef;//préchargement dans le menu
            }
            //LE TYPE DE GRAPH
            var $preselectedGraph = $(target.find('graph')).text();       //la valeur déjà choisie
            if($preselectedGraph != '' ){
              document.getElementById('graphtype').value=$preselectedGraph;//préchargement dans le menu
            }
            //LES CONTRAINTES
            //À FAIRE
      };
      //RETOUR DE LA FONCTION
      return {
        //POUR LE CHAMP TEXT EN LIGNE
        textInput: function(target) {
          initGeneralInputForm(target);
          TemplateHandler.init(function() {
            InputHandler.defaultInputApplyCode(ComponentActionHandler.getCurrentTarget());
          });
        },
        //POUR LA ZONE DE TEXT
        textArea: function(target) {
          initGeneralInputForm(target);
          TemplateHandler.init(function() {
            InputHandler.defaultInputApplyCode(ComponentActionHandler.getCurrentTarget());
          });
        },
        //LE RETOUR PAR DÉFAUT
        defaultInputApplyCode: function(target) {
          var inputSelector = target.find('textarea').length === 0 ? 'input[type="text"], input[type="password"]' : 'textarea';
          var $editForm = $('#editForm');
          if($editForm.find('#idInput').val() !== '') {
            target.find(inputSelector).attr('id', $editForm.find('#idInput').val()).attr('name', $editForm.find('#idInput').val());
          }
          if($('#labelInput').val() !== '') {
            target.find('label').html($('#labelInput').val());
          }

          //la div de la question
          var $la_bonne_div = target.find('div');
          if(target.find('textarea').length!=0){      //si la question est un textarea, on choisi la bonne div
            $la_bonne_div = $la_bonne_div.find('div');
          }

          //RETOUR DE LA VALEUR DE RéFéRENCE
          var listValRef = document.getElementById('labelvalref');            //liste des valeurs.
          var selectedRef= listValRef.options[listValRef.selectedIndex].value;//valeur sélectionnée
          //on renseigne la valeur de références
          $(target.find('val')).remove();
          $la_bonne_div.append('<val name="valref" style="display:none">'+selectedRef+'</val>');
          //RETOUR DU TYPE DE GRAPH
          var listGraph = document.getElementById('graphtype');                 //liste des graphs
          var selectedGraph = listGraph.options[listGraph.selectedIndex].value; //le graph selectionné
          //on renseigne le graph choisi
          $(target.find('graph')).remove();                                     //on reitre la balise graph
          $la_bonne_div.append('<graph style="display:none" >'+selectedGraph+'</graph>');//on ajoute la balise graph mise à jour


          //RETOUR DES CONTRAINTES DE DONNÉES
          //les contraintes ne sont pas applicables pour le textarea
          //listDataType sera donc vide dans le cas d'un textarea
          var listDataType="null";
          var selectedType="null";
          if($(document.getElementById('datatype')).length!=0){
            listDataType = document.getElementById('datatype');                   //list des types de data
            var selectedType = listDataType.options[listDataType.selectedIndex].value;//le type de data sélectionné
          }
          //on renseign le type de données attendu
          if(selectedType == 'number'){
            var min = document.getElementById('minConstraint').value;
            var max = document.getElementById('maxConstraint').value;
            var step = document.getElementById('stepConstraint').value;

            $(target.find('input')).attr('type','number');            //on efface la balise constraints si déjà présente
            $(target.find('constraints')).remove();                   //on règle le type input à 'number'

            target.find('div').append('<constraints style="display:none"></constraints>');//on ajoute la balise constraints
            target.find('constraints').append('max='+max+';');
            target.find('constraints').append('min='+min+';');
            target.find('constraints').append('step='+step);
          }
          else if(selectedType == 'text'){
            $(target.find('constraints')).remove();                   //on retire la balise constraints si déjà présente
            $(target.find('input')).attr('type','text');              //on règle le type input à 'number'
          }//end if
        }
      };
    }(jQuery);

    var OtherComponentsHandler = function() {
      return {
        legend: function(target) {
          TemplateHandler.getEditForm(target.html()).insertAfter(target);
          $('#editForm h3').append(TemplateHandler.getInput('legendInput', 'Titre', target.html()));
          TemplateHandler.init(function() {
            var t = $('#editForm #legendInput').val();
            if(t !== '') {
              $('#formVisualizer legend').html(t);
            }
          });
        }
      }
    }(jQuery);


    /**
      DONNE TOUTES LES METHODES DISPONIBLES DANS L MENU CONTEXTUEL POUR AFFICHER DES ÉLÉMENTS SUPPLÉMENTAIRES ÉVENTUELLEMEMNT.
    **/
    var TemplateHandler = function() {
      return {
        init: function(func) {
          var $buttons = $('<div id="editActionSection" class="pull-right">' + '<button id="editConfirm" class="btn btn-primary">Modifier</button>' + '<button id="editAbort" class="btn btn-default">Annuler</button>' + '</div><div class="row"></div>');
          $('#editForm').append($buttons);
          GeneralSortableHandler.setSortableSection('#target .form-group, #editForm', false);
          ComponentActionHandler.getCurrentTarget().addClass('highLight');
          $('#editForm').find('input:first').focus();
          this.getActionFunction = func;
        },
        getEditForm: function(title) {
          var $form = $('<form id="editForm"></form>');
          var $title = $('<h3>' + ('Réglages - ' + title) + '</h3>');
          $form.append($title);
          return $form;
        },
        getInput: function(id, title, val) {
          return $('<div class="form-group"><label class="col-sm-4 control-label" for="' + id + '">' + title + '</label><div class="col-sm-7"><input id="' + id + '" name="' + id + '" type="text"  class="form-control" value="' +val+ '"></div></div>');
        },
        getSize: function(target, elementOfInterest) {
          switch(elementOfInterest)
          {
            case 'button':
            case 'select':
            case 'textarea':
            case 'input':
              return target.find(elementOfInterest).parents('div[class^="col-"]').first().attr("class").match(/col-[\w-]*\b/)[0].split('-')[2];
            case 'label':
              return target.find('label').attr("class").match(/col-[\w-]*\b/)[0].split('-')[2];
            default:
              return undefined;
          }
        },
        updateSize: function(target, size) {
          target.removeClass(target.attr("class").match(/col-[\w-]*\b/)[0]).addClass('col-sm-' + size);
        },
        /** Créer une checkbox sur une seule ligne dans le menu contextuel. **/
        // Example of use: TemplateHandler.createCheckboxInline('checkboxes', 'Required', ['Yes I do', 'yes'], ['Hell no', 'no']);
        createCheckboxInline: function(groupName, label, checkBoxes, checkboxValues) {
          var $checkBoxInlineSection = $('<div class="form-group">' + '<!-- Inline checkbox -->' + '<label class="col-sm-4 control-label">' + label + '</label>' + '<div class="col-sm-7">' + '</div>' + '</div>');

          $.each(checkBoxes, function(i, val) {
            var checkboxInline = $('<label for="checkboxes-' + i + '" class="checkbox-inline"><input id="checkboxes-' + i + '" name="' + groupName + '" value="' + checkboxValues[i] + '" type="checkbox">' + val + '</input></label>');
            $checkBoxInlineSection.find('.col-sm-7').append(checkboxInline);
          });

          var $helpBlock = $('<p class="help-block"></p>');
          $checkBoxInlineSection.find('.col-sm-7').append($helpBlock);

          return $checkBoxInlineSection;
        },
        /** Créer une liste déroulante dans le menu contextuel. **/
        createSelectboxSingle: function(id, label, options, selected, values) {
          var $selectBoxSection = $('<div class="form-group"><!-- Select single --><label class="col-sm-4 control-label" for="' + id + '">' + label + '</label><div class="col-sm-7"></div></div>');
          var $singleSelect = $('<select id="' + id + '" name="' + id + '" class="form-control"></select>');
          var $helpBlock = $('<p class="help-block"></p>');

          $.each(options, function(i, val) {
            var $option = $('<option>' + val + '</option>');
            if(selected.toLowerCase() === val.toString().toLowerCase()) {
              $option.attr('selected', 'selected');
            }
            $singleSelect.append($option);
          });

          $selectBoxSection.find('.col-sm-7').append($singleSelect).append($helpBlock);
          return $selectBoxSection;
        },
        /** Créer une zone de text dans le menu contextuel. **/
        createTextArea: function(id, label, options, defaultText) {
          $textAreaSection = $('<div class="form-group" style="cursor: default;"><!-- Textarea --><label class="col-sm-4 control-label" for="textarea" style="cursor: default;">' + label + '</label><div class="col-sm-7"></div></div>');
          $textArea = $('<textarea id="' + id + '" name="' + id + '" class="form-control" cols="60" rows="' + options.length + '">' + (defaultText === undefined ? '' : defaultText) + '</textarea>');
          var v = '', length = options.length;
          $.each(options, function(i, val) {
            v += $(val).hasClass('divider') ? '---' : $(val).find('a').text();
            if(i !== length - 1) {
              v += '\r\n';
            }
          });

          $textArea.val(v);
          $textAreaSection.find('.input-group').append($textArea);
          return $textAreaSection;
        },
        getActionFunction: function() {
        },
        /**
        MES FONCTIONS
        **/
        /**
        Créer un tableau modulable pour renseigner les réponses possibles à une questions.
        **/
        createTableDynamicIncrement: function(id,groupName, label){
          //début du widget menu
          var $createTableDynamicIncrementSelection = $('<div class="form-group">' + '<!-- Table autoIncrement -->' + '<label class="col-sm-4 control-label">' + label + '</label>' + '<div class="col-sm-7">' + '</div>' + '</div>');
          //debut du tableau
          var entete = $('<table id="tabRep" name="tabRep" >');
          $createTableDynamicIncrementSelection.find('.col-sm-7').append(entete);

          //on ferme le tableau
          $createTableDynamicIncrementSelection.find('.col-sm-7').append('</table>');
          //on ajoute le bouton 'ajouter une ligne de réponse'
          $createTableDynamicIncrementSelection.find('.col-sm-7').append('<input id="buttonAddRow" type="button" class="buttonAddRow" name="buttonAddRow" value="Ajouter" >');
          //on ajoute le bouton 'supprimer une ligne de réponse'
          $createTableDynamicIncrementSelection.find('.col-sm-7').append('<input id="buttonClear" type="button" class="buttonClear" name="buttonClear" value="Vider" >');

          return $createTableDynamicIncrementSelection;
        },
        /**
        créer une titre pour une section
        **/
        createSectionTitle: function(id, label){
          //début du widget
          var $createSectionTitleSelection = $('<div class="form-group">' + '<!-- TextAreaSection -->' + '<label class="col-sm-4 control-label">' + label + '</label>' + '<div class="col-sm-7">' + '</div>' + '</div>');
          //la zone de text
          var inputTitle = $('<input id="'+id+'" name="textInput" type="text"  class="form-control">');

          $createSectionTitleSelection.find('.col-sm-7').append(inputTitle);

          return $createSectionTitleSelection;
        },
        /**
        créer une zone de texte pour une section
        **/
        createSectionTextArea: function(id,label){
          //début du widget
          var $createSectionTextAreaSelection = $('<div class="form-group">' + '<!-- TextAreaSection -->' + '<label class="col-sm-4 control-label">' + label + '</label>' + '<div class="col-sm-7">' + '</div>' + '</div>');
          //la zone de text
          var textarea = $('<textarea id="'+id+'" name="SectionTextArea" value="blabla"></textarea>');

          $createSectionTextAreaSelection.find('.col-sm-7').append(textarea);

          return $createSectionTextAreaSelection;
        },
        /**
        créer une list déroulante pour le choix des valeurs de références
        **/
        createSelectboxValRef: function(id,label,options){
          //début du widget.
          var $createSelectboxValRefSelection = $('<div class="form-group">' + '<!-- selectboxValRef -->' + '<label class="col-sm-4 control-label">' + label + '</label>' + '<div class="col-sm-7">' + '</div>' + '</div>');
          //remplir la liste déroulante
          var $singleSelect = $('<select id="' + id + '" name="' + id + '" class="form-control" ></select>');
          $.each(options, function(i,val){
            var $option = $('<option>'+val+'</option>');
            $singleSelect.append($option);
          });

          $createSelectboxValRefSelection.find('.col-sm-7').append($singleSelect);

          return $createSelectboxValRefSelection;
        },
        /**
        créer une list déroulante pour le choix de type de graph
        **/
        createSelectBoxGraph: function(id,label){
          //début du widget.
          var $createSelectBoxGraphSelection = $('<div class="form-group">' + '<!-- selectboxValRef -->' + '<label class="col-sm-4 control-label">' + label + '</label>' + '<div class="col-sm-7">' + '</div>' + '</div>');
          //remplir la liste des graph disponible
          var $singleSelect = $('<select id="'+id+'" name="'+id+'" class="form-control"></select>');
          $singleSelect.append('<option value="NONE" selected="true">Aucun</option>');
          $singleSelect.append('<option value="donut">Camembert</option>');
          $singleSelect.append('<option value="column">Bâtons</option>');
          $createSelectBoxGraphSelection.find('.col-sm-7').append($singleSelect);
          return $createSelectBoxGraphSelection;
        },
        /**
        créer une liste déroulante pour choisir le type d'entré attendu.
        **/
        createSelectBoxDataConstraint: function(id,label){
          //début du widget.
          var $createSelectBoxDataConstraintSelection = $('<div id="ConstraintElements"><div class="form-group">' + '<!-- selectboxDataType -->' + '<label class="col-sm-4 control-label">' + label + '</label>' + '<div class="col-sm-7">' + '</div>' + '</div></div>');
          //la liste déroulante.
          var $singleSelect = $('<select id="'+id+'" name="'+id+'" class="form-control"></select>');
          $singleSelect.append('<option value="text">Texte</option>');
          $singleSelect.append('<option value="number">Nombre</option>');
          $createSelectBoxDataConstraintSelection.find('.col-sm-7').append($singleSelect);
          return $createSelectBoxDataConstraintSelection;
        },
        /**
        En lien avec createSelectBoxDataConstraint. Créer les contraintes sur les nombres
        **/
        createDataConstraints: function(id,label){
          //début du widget.
          var $createDataConstraintsSelection = $('<div class="form-group" id="'+id+'">' + '<!-- DataConstraints -->' + '<label class="col-sm-4 control-label">' + label + '</label>' + '<div class="col-sm-7">' + '</div>' + '</div>');
          //contrainte sur le pallier
          var $stepConstraint = $('<div><label class="col-sm-4 control-label">Pallier</label>');
          $stepConstraint.append('<input type="number" id="stepConstraint" class="DataConstraints" min="1"></div>');
          //contrainte sur la valeur max,
          var $maxConstraint = $('<div><label class="col-sm-4 control-label">Max</label>');
          $maxConstraint.append('<input type="number" id="maxConstraint" class="DataConstraints" min="1"></div>');
          // et min.
          var $minConstraint = $('<div><label class="col-sm-4 control-label">Min</label>');
          $minConstraint.append('<input type="number" id="minConstraint" class="DataConstraints" min="0"></div>');
          //ajouts
          $createDataConstraintsSelection.find('.col-sm-7').append($maxConstraint);
          $createDataConstraintsSelection.find('.col-sm-7').append($minConstraint);
          $createDataConstraintsSelection.find('.col-sm-7').append($stepConstraint);
          return $createDataConstraintsSelection;
        }
      };
    }(jQuery);

    // ==== Markup ====
    var MarkupHandler = function() {
      return {
        refresh: function() {
          // Build the generated source form
          var ef = $('#formVisualizer form').clone();
          ef.find('div[style]').removeAttr('style')
          ef.find('#editForm').remove();
          ef.find('.highLight').removeClass('highLight');
          var form = $('<form class="' + ef.attr('class') + '"></form>');
          form.append($(ef).find('legend')); // Legend

          var divs = $('#target div.form-group');
          $.each(divs, function() {
            var groupCopy = $(this).clone();
            form.append($('<div></div>').html(groupCopy).html().trim()) // Sections
          });

          // Refresh markup section
          var el = $('<div></div>').html(form);
          $('#formSource').val(el.html()); // Add the markup
          l = $.htmlClean($('#formSource').val(), {format: true, allowComments: true, allowedAttributes: [["id"], ["style"], ["for"], ["name"], ["class"], ["type"]] } );
          $('#formSource').val(l);
          $('#formSource').trigger('change');
        }
      }
    }(jQuery);

    //== Public interface ==
    return {

    }
}(jQuery);
