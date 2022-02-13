const myView = document.getElementsByClassName("my-view")[0];
const emptyDiv = document.createElement('div');
const databaseColumns = ["id", "theme_slug", "date_check", "in_svn", "is_blocked"];
const updateAction = document.getElementsByClassName("update-action")[0];
const securityAlerts = document.getElementsByClassName("security-alerts")[0];

const securityMessage = "<p><strong>**DATE** - Danger:</strong> you might be encountering a strong security breach for the theme **THEME** update." 
+ " We advise you to block the update and contact the developer in charge of your theme as soon as possible as you might be under a theme confusion attack!</p>";

const toggleBlockOnTheme = (event) => {
	event.preventDefault();
	const index = event.currentTarget.parentNode.classList[0];
	const themeSlug = document.getElementsByClassName(index + " theme_slug")[0].innerHTML;
	const blocked = (event.currentTarget.innerHTML === "No") ? 0 : 1;
	const body = {
		theme_slug: themeSlug,
		blocked: blocked
	};
	const headers = new Headers({
		'X-WP-Nonce': wpApiSettings.nonce,
		'Content-Type': "application/json"
	});

	const options = {
		method: 'PUT',
		headers: headers,
		body: JSON.stringify(body)
	};
	fetch("http://localhost:8000/wp-json/stop_confusion/v1/theme/block", options)
	.then((response) => {
		return response.json();
	})
	.then((response) => {
		removeAllRowsFromTable(myView);
		printDataToFront(response);
	});
}

const addEventListenerToBlocked = () => {
	const blocked = document.getElementsByClassName("update-blocked");
	for (let i = 0; i < blocked.length; i++) {
		blocked[i].addEventListener("click", toggleBlockOnTheme);
	}
}

const handleData = (column, row) => {
	let data = row[column];
	if (column === "in_svn"  || column === "is_blocked") {
		data = (Number(row[column]) === 1) ? "Yes" : "No";
	}
	if (column === "is_blocked") {
		data = '<a href="#" class="update-blocked">' + data + '</a>'
	}
	return data;
}

const getSecurityAlerts = () => {
	const headers = new Headers({
		'X-WP-Nonce': wpApiSettings.nonce
	});

	const options = {
		method: 'GET',
		headers: headers
	};

	fetch("http://localhost:8000/wp-json/stop_confusion/v1/themes/threat", options)
	.then((response) => {
		return response.json();
	})
	.then((response) => {
		console.log(response);
		response.forEach((theme) => {
			const message = securityMessage.replace("**THEME**", theme.theme_slug).replace("**DATE**", theme.date_check);
			securityAlerts.innerHTML += message;
		});
	});
}

const printDataToFront = (data) => {
	if (typeof data.security_threat !== 'undefined' && data.security_threat === true) {
		getSecurityAlerts();
		data = data.rows;
	}
	data.forEach((row, rowIndex) => {
		const newRow = myView.insertRow(rowIndex + 1);
		databaseColumns.forEach((column, index) => {
			const cell = newRow.insertCell(index);
			cell.innerHTML = handleData(column, row);
			cell.classList.add(rowIndex, column);
		});
	});
	addEventListenerToBlocked();
}

const removeAllRowsFromTable = (table) => {
	const length = table.rows.length;
	for (let i = 0; i < length; i++) {
		if (i === 0) {
			continue;
		}
		table.deleteRow(1);
	}
}

const updateThemeScan = (event) => {
	event.preventDefault();
	const headers = new Headers({
		'X-WP-Nonce': wpApiSettings.nonce
	});

	const options = {
		method: 'PUT',
		headers: headers
	};
	fetch("http://localhost:8000/wp-json/stop_confusion/v1/themes", options)
	.then((response) => {
		return response.json();
	})
	.then((response) => {
		removeAllRowsFromTable(myView);
		printDataToFront(response);
	});
}

const headers = new Headers({
	'X-WP-Nonce': wpApiSettings.nonce
});

const options = {
	method: 'GET',
	headers: headers
};

fetch("http://localhost:8000/wp-json/stop_confusion/v1/themes", options)
.then((response) => {
	return response.json();
})
.then((response) => {
	printDataToFront(response);
	getSecurityAlerts();
});

updateAction.addEventListener("click", updateThemeScan);