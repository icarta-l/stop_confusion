const myView = document.getElementsByClassName("my-view")[0];
const emptyDiv = document.createElement('div');
const databaseColumns = ["id", "theme_slug", "date_check", "in_svn", "is_authorized"];
const updateAction = document.getElementsByClassName("update-action")[0];
const securityAlerts = document.getElementsByClassName("security-alerts")[0];

const securityMessage = "<p><strong>**DATE** - Danger:</strong> you might be encountering a strong security breach for the theme <strong><em>**THEME**</em></strong> update." 
+ " We advise you to block the update and contact the developer in charge of your theme as soon as possible as you might be under a theme confusion attack!</p>";

const toggleAuthorizationOnTheme = (event) => {
	event.preventDefault();
	const index = event.currentTarget.parentNode.classList[0];
	const themeSlug = document.getElementsByClassName(index + " theme_slug")[0].innerHTML;
	const authorized = (event.currentTarget.innerHTML === "No") ? 0 : 1;
	const body = {
		theme_slug: themeSlug,
		authorized: authorized
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
	fetch(wpApiSettings.root + "stop_confusion/v1/theme/authorization", options)
	.then((response) => {
		return response.json();
	})
	.then((response) => {
		removeAllRowsFromTable(myView);
		printDataToFront(response);
	});
}

const addEventListenerToAuthorized = () => {
	const authorized = document.getElementsByClassName("update-authorized");
	for (let i = 0; i < authorized.length; i++) {
		authorized[i].addEventListener("click", toggleAuthorizationOnTheme);
	}
}

const handleData = (column, row) => {
	let data = row[column];
	if (column === "in_svn"  || column === "is_authorized") {
		data = (Number(row[column]) === 1) ? "Yes" : "No";
	}
	if (column === "is_authorized") {
		data = '<a href="#" class="update-authorized">' + data + '</a>';
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

	fetch(wpApiSettings.root + "stop_confusion/v1/themes/threat", options)
	.then((response) => {
		return response.json();
	})
	.then((response) => {
		console.log(response);
		response.forEach((row) => {
			const alertRow = myView.getElementsByClassName('theme_slug-' + row.theme_slug)[0].parentNode;
			alertRow.classList.add('alert');
			securityAlerts.innerHTML += securityMessage.replace("**DATE**", row.date_check).replace("**THEME**", row.theme_slug);
		});
	});
}

const printDataToFront = (data) => {
	if (typeof data.security_threat !== 'undefined') {
		if (data.security_threat === true) {
			getSecurityAlerts();
		}
		data = data.rows;
	}
	data.forEach((row, rowIndex) => {
		const newRow = myView.insertRow(rowIndex + 1);
		databaseColumns.forEach((column, index) => {
			const cell = newRow.insertCell(index);
			cell.innerHTML = handleData(column, row);
			cell.classList.add(rowIndex, column);
			if (column === "theme_slug") {
				cell.classList.add(column + '-' + row[column]);
			}
		});
	});
	addEventListenerToAuthorized();
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
	fetch(wpApiSettings.root + "stop_confusion/v1/themes", options)
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

fetch(wpApiSettings.root + "stop_confusion/v1/themes", options)
.then((response) => {
	return response.json();
})
.then((response) => {
	printDataToFront(response);
	getSecurityAlerts();
});

updateAction.addEventListener("click", updateThemeScan);