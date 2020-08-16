// Javascript functions for eTask module

/**
 * Set tooltip to the title atribute of links in the head of eTask table
 */
YUI().use("yui2-yahoo-dom-event", "yui2-animation", "yui2-container",
	function(Y) {
	var YAHOO = Y.YUI2;
	var elements = YAHOO.util.Dom.getElementsByClassName('etasktooltip', 'a');
	var tooltip = new YAHOO.widget.Tooltip("tooltip", {
		context: elements, autodismissdelay: 60000
	});
});

/**
 * Show and hide grade to pass form
 */
function toggle(id){
	// id of element
	var id = id;
	// style of element
	var style = document.getElementById(id).style.display;

	YUI().use('node', function(Y) {
		if(style=='none'){
			Y.all('.gradesettings').hide();
		}
		Y.one('#'+id).toggleView();
	});
}
