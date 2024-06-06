/* show/hide on checkbox click */
// eslint-disable-next-line no-unused-vars -- used on window on click
function showMe(box) {
	const chboxs = document.getElementsByName('mc_nuke_all_styles');
	let vis = 'none';
	for (let i = 0; i < chboxs.length; i++) {
		if (chboxs[i].checked) {
			vis = 'none';
		} else {
			vis = '';
		}
	}
	document.getElementById(box).style.display = vis;
}
