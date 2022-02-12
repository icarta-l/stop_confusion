const myView = document.getElementsByClassName("my-view")[0];
const emptyDiv = document.createElement('div');
const databaseColumns = ["id", "theme_slug", "date_check", "in_svn", "is_blocked"];
const updateAction = document.getElementsByClassName("update-action")[0];

const printDataToFront = (data) => {
	data.forEach((row, index) => {
		const newRow = myView.insertRow(index + 1);
		databaseColumns.forEach((column, index) => {
			let data = row[column];
			if (column === "in_svn"  || column === "is_blocked") {
				data = (Number(row[column]) === 1) ? "Yes" : "No";
			}
			newRow.insertCell(index).appendChild(document.createTextNode(data));
		});
	});
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
});

updateAction.addEventListener("click", updateThemeScan);