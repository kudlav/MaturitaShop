var buttonPrint = document.getElementById('print');
buttonPrint.onclick = printOrder;

function printOrder() {
	var content = document.getElementsByClassName("show-cards");
	var winPrint = window.open('', '', 'left=0,top=0,width=800,height=900,toolbar=0,scrollbars=0,status=0');
	winPrint.document.open('text/html');
	winPrint.document.write('<html><head>');
	winPrint.document.write('<link rel="stylesheet" href="/css/admin/style.css">');
	winPrint.document.write('<link rel="stylesheet" href="/css/front/style.css">');
	winPrint.document.write('</head><body>');
	winPrint.document.write(content[0].innerHTML);
	winPrint.document.write('</body></html>');
	winPrint.document.close();
	winPrint.onload = function () {
		winPrint.focus();
		winPrint.print();
		winPrint.close();
	};
}
