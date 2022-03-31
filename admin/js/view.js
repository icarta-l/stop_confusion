const myView = document.getElementsByClassName("my-view")[0];
const emptyDiv = document.createElement('div');
const databaseColumns = ["id", "theme_slug", "date_check", "in_svn", "is_authorized"];
const updateAction = document.getElementsByClassName("update-action")[0];
const securityAlerts = document.getElementsByClassName("security-alerts")[0];

const securityMessage = "<p><strong>**DATE** - Danger:</strong> you might be encountering a strong security breach for the theme <strong><em>**THEME**</em></strong> update." 
+ " We advise you to block the update and contact the developer in charge of your theme as soon as possible as you might be under a theme confusion attack!</p>";

const addEventListenerToAuthorized = () => {
	const authorized = document.getElementsByClassName("update-authorized");
	for (let i = 0; i < authorized.length; i++) {
		authorized[i].addEventListener("click", toggleAuthorizationOnTheme);
	}
}

/**
 * Prepare Data
 */

 const getFetchOptions = (method = "GET", isJson = false) => {
 	const headers = new Headers({
 		'X-WP-Nonce': wpApiSettings.nonce
 	});

 	if (isJson) {
 		headers["Content-Type"] = "application/json";
 	}

 	const options = {
 		method: method,
 		headers: headers
 	};

 	return options;
 }

 const getTargetizedScannedThemeInfo = (event) => {
 	const index = event.currentTarget.parentNode.classList[0];
 	const themeSlug = document.getElementsByClassName(index + " theme_slug")[0].innerHTML;
 	const authorized = (event.currentTarget.innerHTML === "No") ? 0 : 1;
 	const body = {
 		theme_slug: themeSlug,
 		authorized: authorized
 	};
 	return body;
 }

/**
 * Parse Data
 */

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

 const parseThemeData = (row, rowIndex) => {
 	const newRow = myView.insertRow(rowIndex + 1);
 	databaseColumns.forEach((column, index) => {
 		assignDataToHTMLTable(column, index, newRow, row, rowIndex);
 	});
 }

/**
 * Print Data
 */

 const printDataToFront = (data) => {
 	if (typeof data.security_threat !== 'undefined') {
 		if (data.security_threat === true) {
 			getSecurityAlerts();
 		}
 		data = data.rows;
 	}
 	data.forEach(parseThemeData);
 	addEventListenerToAuthorized();
 }

 const assignDataToHTMLTable = (column, index, newRow, row, rowIndex) => {
 	const cell = newRow.insertCell(index);
 	cell.innerHTML = handleData(column, row);
 	cell.classList.add(rowIndex, column);
 	if (column === "theme_slug") {
 		cell.classList.add(column + '-' + row[column]);
 	}
 	if (column === "in_svn" && Number(row[column]) === 0) {
 		const lowAlertRow = myView.getElementsByClassName('theme_slug-' + row.theme_slug)[0].parentNode;
 		lowAlertRow.classList.add('low-alert');
 	}
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

 const printSecurityAlert = (row) => {
 	const alertRow = myView.getElementsByClassName('theme_slug-' + row.theme_slug)[0].parentNode;
 	alertRow.classList.add('alert');
 	securityAlerts.innerHTML += securityMessage.replace("**DATE**", row.date_check).replace("**THEME**", row.theme_slug);
 }

/**
 * Confirm authorization choice
 */

 const confirmAuthorizationChoice = (body, event) => {
       if (body.authorized !== 0 || event.currentTarget.parentNode.parentNode.classList.item(0) !== "alert") {
              return true;
       }
       return confirm("Are you sure you want to authorize updates for this theme? This might provoke a severe security breach!");
}

/**
 * HTTP Requests
 */

 const updateThemeScan = (event) => {
 	event.preventDefault();

 	fetch(wpApiSettings.root + "stop_confusion/v1/themes", getFetchOptions('PUT'))
 	.then((response) => {
 		return response.json();
 	})
 	.then((response) => {
 		removeAllRowsFromTable(myView);
 		printDataToFront(response);
 	});
 }

 const getSecurityAlerts = () => {
 	fetch(wpApiSettings.root + "stop_confusion/v1/themes/threat", getFetchOptions())
 	.then((response) => {
 		return response.json();
 	})
 	.then((response) => {
 		console.log(response);
             securityAlerts.innerHTML = '';
             response.forEach(printSecurityAlert);
      });
 }

 const toggleAuthorizationOnTheme = (event) => {
 	event.preventDefault();

 	const body = getTargetizedScannedThemeInfo(event);
 	const options = getFetchOptions("PUT", true);
 	options.body = JSON.stringify(body);

       if (confirmAuthorizationChoice(body, event) !== true) {
              return;
       }

       fetch(wpApiSettings.root + "stop_confusion/v1/theme/authorization", options)
       .then((response) => {
           return response.json();
    })
       .then((response) => {
           removeAllRowsFromTable(myView);
           printDataToFront(response);
           getSecurityAlerts();
    });
}

const getScannedThemeInfo = () => {
     fetch(wpApiSettings.root + "stop_confusion/v1/themes", getFetchOptions())
     .then((response) => {
           return response.json();
    })
     .then((response) => {
           printDataToFront(response);
           getSecurityAlerts();
    });
}

 /**
  * Run Script
  */

  getScannedThemeInfo();

  updateAction.addEventListener("click", updateThemeScan);