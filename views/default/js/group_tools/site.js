require(['elgg'], function (elgg) {
	// make group admin menu toggle
	elgg.ui.registerTogglableMenuItems("group-admin", "group-admin-remove");
	// make discussion open/close menu toggle
	elgg.ui.registerTogglableMenuItems("status-change-open", "status-change-close");
});