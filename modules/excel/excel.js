function getTime() {
    var d = new Date();
    d = new Date(d.getTime() - 3000000);
    var date_format_str = d.getFullYear().toString()+"-"+((d.getMonth()+1).toString().length==2?(d.getMonth()+1).toString():"0"+(d.getMonth()+1).toString())+"-"+(d.getDate().toString().length==2?d.getDate().toString():"0"+d.getDate().toString())+"-"+(d.getHours().toString().length==2?d.getHours().toString():"0"+d.getHours().toString())+"-"+((parseInt(d.getMinutes()/5)*5).toString().length==2?(parseInt(d.getMinutes()/5)*5).toString():"0"+(parseInt(d.getMinutes()/5)*5).toString())+"-00";
    
    return date_format_str;
}

function tableToExcel(tableHtml) {
    console.log("tableHtml: ", tableHtml);
    var uri = 'data:application/vnd.ms-excel;base64,',
    template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>',
    base64 = function(s) {
        return window.btoa(unescape(encodeURIComponent(s)))
    },
    format = function(s, c) {
        console.log(s.replace(/{(\w+)}/g, function(m, p) {
            return c[p];
        }));
        return s.replace(/{(\w+)}/g, function(m, p) {
            return c[p];
        })
    }
    var toExcel = tableHtml;
    var ctx = {
        worksheet: name || '',
        table: toExcel
    };
    var link = document.createElement("a");
    link.download = window.location.pathname.replaceAll("/", "").trim()+"_"+getTime()+".xls";
    link.href = uri + base64(format(template, ctx));
    console.log(base64(format(template, ctx)));
    link.click();
}