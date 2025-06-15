function toast(type, message) {
    const Toast = Swal.mixin({
        toast: true,
        position: 'bottom-left',
        showConfirmButton: false,
        timer: 3000,
        animation: false,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    })

    Toast.fire({
        icon: "" + type + "",
        title: "" + message + "",
    })
}

function showLoader(loaderClass) {
    $(loaderClass + '_button_text').addClass('d-none');
    $(loaderClass + '_load_data').removeClass('d-none');
}

function hideLoader(loaderClass) {
    setTimeout(function () {
        $(loaderClass + '_button_text').removeClass('d-none');
        $(loaderClass + '_load_data').addClass('d-none');

        clearValidation();

    }, 2000);
}

function clearValidation() {
    $('.form-control').removeClass('is-invalid border-danger');
    $('.invalid-feedback').text('');
}

function clearInput(){
    $('.form-control').val('');
    tinymce.get('description').setContent('');
}

function addRow(tableID) {
    var table = document.getElementById(tableID);
    var rowCount = table.rows.length;
    var row = table.insertRow(rowCount);
    var colCount = table.rows[0].cells.length;

    for (var i = 0; i < colCount; i++) {
        var newRow = row.insertCell(i);
        newRow.innerHTML = table.rows[0].cells[i].innerHTML;
        newRow.childNodes[0].value = "";

        newRow.style.textAlign = "center";
        newRow.style.verticalAlign = "middle";
    }
}


function deleteRow(row) {
    var table = document.getElementById("data");
    var rowCount = table.rows.length;
    if (rowCount > 1) {
        var rowIndex = row.parentNode.parentNode.rowIndex;
        document.getElementById("data").deleteRow(rowIndex);
    }
    else {
        toast('warning', 'Please specify at least one value.');
    }
}