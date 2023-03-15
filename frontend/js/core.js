document.addEventListener('DOMContentLoaded', () => {
	document.querySelectorAll('.navbar-burger').forEach(el => {
		el.addEventListener('click', () => {
			console.log("navbar click", el);
			const target = document.getElementById(el.dataset.target);
			if (target !== null) {
				el.classList.toggle('is-active');
				target.classList.toggle('is-active');
			}
		});
	});
});