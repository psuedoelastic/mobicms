/*!
 * mobiCMS Content Management System (http://mobicms.net)
 * For copyright and license information, please see the LICENSE.txt
 */

/*

 /* ========================================================================
 * Toggle Panel Plugin
 * Author: L!MP
 * ======================================================================== */

$(document).ready(function () {
    var toggleSlider = function () {
        $(".slider").toggleClass("close");
        $(".toogle-admin").toggleClass("admin");
    };

    $(".slider-button").click(function () {
        $.cookie('open') == undefined ? $.cookie('open', true) : $.removeCookie('open');

        toggleSlider();
    });

    if ($.cookie('open') != undefined) {
        toggleSlider();
    }
});
