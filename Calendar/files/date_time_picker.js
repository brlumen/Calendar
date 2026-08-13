/* 
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Other/javascript.js to edit this template
 */

$(document).ready(function () {
    // Проверяем, находимся ли мы на странице calendar_user_page
    if (window.location.href.indexOf('plugin.php?page=Calendar/calendar_user_page') !== -1) {
        $('#view_month_date_select').data("DateTimePicker").viewMode('months');
//          $('#view_month_date_select').data("DateTimePicker").format('MM/YYYY');
    }
});
