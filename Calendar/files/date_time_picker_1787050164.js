/*
 * Month view date picker tweaks.
 */

$(document).ready(function () {
    // The month selector exists in the month view only; in the week view the
    // element is absent and the picker instance is undefined.
    var t_picker = $('#view_month_date_select').data('DateTimePicker');

    if (t_picker) {
        t_picker.viewMode('months');
    }
});
