// This allows to use jquitabs, jquibutton. (In case of name conflict, like Bootstrap.)
$.widget.bridge('jquitabs', $.ui.tabs);
$.widget.bridge('jquibutton', $.ui.button);
// And this fixes buttons in dialogs using Bootstrap.
var real_dialog = $.fn.dialog;
$.fn.dialog = function(){
        var d = real_dialog.apply(this, arguments);
        if (typeof d == "object" && d.jquery && d.hasClass("ui-dialog-content"))
                real_dialog.call(d, "widget").find(".ui-dialog-buttonpane button").addClass("btn");
        return d;
};