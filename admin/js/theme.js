const buttons = document.getElementsByClassName("button-link");
console.log(buttons);

const destroyPopUp = (event) => {
	event.currentTarget.parentNode.remove();
}

const printPopUp = () => {
	const popUp = document.createElement("div");
	popUp.classList.add('stop_confusion-pop-up');
	popUp.innerHTML = `<h2>Careful!</h2>
	<p>Updates have been found for some themes, please check if those themes are safe downloading as they might provoke
	a large security breach</p>
	<span class="stop_confusion-exit-cross">X</span>
	`;
	const exitCross = popUp.getElementsByClassName("stop_confusion-exit-cross")[0];
	exitCross.addEventListener("click", destroyPopUp);
	document.body.appendChild(popUp);
}

for (let i = 0; i < buttons.length; i++) {
	if (!buttons[i].classList.contains('wp-auth-check-close')) {
		console.log("Here!");
		printPopUp();
	}
}
