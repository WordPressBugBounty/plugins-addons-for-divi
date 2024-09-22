document.addEventListener('DOMContentLoaded', () => {
	const lastMenuItem = document.querySelector(
		'#toplevel_page_diviepic-plugins .wp-submenu li:last-child'
	);
	if (lastMenuItem) {
		lastMenuItem.classList.add('wp-last-item');
	}
});
