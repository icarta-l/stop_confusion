const myView = document.getElementsByClassName("my-view")[0];
const emptyDiv = document.createElement('div');
const databaseColumns = ["id", "theme_slug", "date_check", "in_svn"];

const printDataToFront = (data) => {
	data.forEach((row, index) => {
		const newRow = myView.insertRow(index + 1);
		databaseColumns.forEach((column, index) => {
			let data = row[column];
			if (column === "in_svn") {
				data = (Number(row[column]) === 1) ? "Yes" : "No";
			}
			newRow.insertCell(index).appendChild(document.createTextNode(data));
		});
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