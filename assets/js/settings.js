
/*
    select2 settings
*/
$.fn.select2.defaults.set("theme", "bootstrap4");


/*
    datatables settings
*/
// set defaults
$.extend(true, $.fn.dataTable.defaults, {
	"pageLength": 50,
    conditionalPaging: true,
	"lengthMenu": [[50, 100, -1], [50, 100, "All"]],
	language: {
        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/nl-NL.json',
    },
});
$.fn.dataTable.Buttons.defaults.dom.button.className = 'btn';