(function ($) {
    var menu,
    trigger,
    content,
	ro = false,
    hash;
    var defaults = {
        menuStyle: {
            listStyle: "none",
            padding: "1px",
            margin: "0px",
            backgroundColor: "#fff",
            border: "1px solid #999",
            width: "100px"
        },
        itemStyle: {
            margin: "0px",
            color: "#000",
            display: "block",
            cursor: "default",
            padding: "3px",
            border: "1px solid #fff",
            backgroundColor: "#fff"
        },
        itemHoverStyle: {
            border: "1px solid #0a246a",
            backgroundColor: "#b6bdd2"
        }
    };

    $.fn.contextMenu = function (id, options,ro) {
        options = options || defaults;
        if (!menu) {
            menu = $("<div id='jqContextMenu'></div>").hide().css({
                position: "absolute",
                zIndex: "500"
            }).appendTo("body").bind("click",
            function (e) {
                e.stopPropagation();
            });
        }
        hash = hash || [];
        hash.push({
            id: id,
            menuStyle: $.extend({},
            defaults.menuStyle, options.menuStyle || {}),
            itemStyle: $.extend({},
            defaults.itemStyle, options.itemStyle || {}),
            itemHoverStyle: $.extend({},
            defaults.itemHoverStyle, options.itemHoverStyle || {}),
            bindings: options.bindings || {},
			readOnly: ro
        });
        var index = hash.length - 1;
        $(this).bind("contextmenu",
        function (e) {
            display(index, this, e);
            return false;
        });
        $(this).bind("click",
        function (e) {
            display(index, this, e);
            return false;
        });
        return this;
    };

    function display(index, trigger, e) {
        cur = hash[index];
        content = $(cur.id).find("ul:first").clone(true);
		if (cur.readOnly == 1){
  		  content.find("#deleteFolder").remove();
		  content.find("#editFolder").remove();
		}
        content.css(cur.menuStyle).find("li").css(cur.itemStyle).hover(function () {
            $(this).css(cur.itemHoverStyle);
        },
        function () {
            $(this).css(cur.itemStyle);
        }).find("img").css({
            verticalAlign: "middle",
            paddingRight: "2px"
        });
        menu.html(content);
        $.each(cur.bindings,
        function (id, func) {
            $(id, menu).bind("click",
            function () {
                hide();
                func(trigger);
            });
        });
        menu.css({
            "left": e.pageX,
            "top": e.pageY
        }).show();
        $(document).one("click", hide);
    }

    function hide() {
        menu.hide();
    }

    $.contextMenu = {
        defaults: function (userDefaults) {
            $.each(userDefaults,
            function (i) {
                $.extend(defaults[i], this);
            });
        }
    };

})(jQuery);
$(function () {
    $("div.contextMenu").hide();
});