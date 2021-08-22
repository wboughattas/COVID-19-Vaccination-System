function generateForm(data, row) {
    const fields = data["headers"];
    let field_html = "";

    let i;
    for (i = 0; i < fields.length; i++) {
        field_html += `<div class="grid-item${(i % 3) + 1}">
                            <label class="mdc-text-field mdc-text-field--filled">
                                <span class="mdc-text-field__ripple"></span>
                                <span class="mdc-floating-label" id="${fields[i]}">${fields[i]}</span>
                                <input class="mdc-text-field__input" type="text"
                                name="${fields[i]}" value="${row ? row[i] : ""}">
                                <span class="mdc-line-ripple"></span>
                            </label>
                        </div>`;
        if (row) field_html += `<input type="text" style="display:none" name="old_${fields[i]}" value="${row[i]}">`;
    }

    return `
    <form action="/comp-353/insert_db.php" method='POST'>
        <input type="text" name="${data['title']}" value="${row === undefined ? 'CREATE': 'EDIT'}" 
        style="display: none">
        <div id="the-form" class="mdc-elevation--z1">
            <div class="grid-container">
                ${field_html}
                <div class="grid-item3">
                    <div class="mdc-touch-target-wrapper">
                        <button class="mdc-button mdc-button--touch" type="submit">
                            <span class="mdc-button__ripple"></span>
                            <span class="mdc-button__label">Submit!</span>
                            <span class="mdc-button__touch"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    `;
}

function generateTable(data, replaceFunc) {
    const headers = data["headers"];
    const rows = data["data"];
    const rowHeader = generateRow(headers, data);
    replaceFunc = data["editEnabled"] ? replaceFunc : undefined;

    let tableData = "";
    for (let i = 0; i < rows.length; i++) {
        tableData += generateRow(rows[i], data, replaceFunc);
    }

    const createButton = `<tr><td colspan="1000">` + // set the colspan so high that it always covers the entire table
                    `<button onclick="${replaceFunc} ` + " `" +
                    generateForm(data).replaceAll('"', "'") + "` ; initMDC();\""  +
                    `class='mdc-button mdc-button--raised mdc-theme--secondary-bg' style='width:100%'>
                    <span class='mdc-button__ripple'></span>
                    <span class='mdc-button__label'>CREATE NEW!</span></button> </td></tr>`
    return `<table border='1' align='center'>
            ${replaceFunc ? createButton : ""}
            ${rowHeader}
            ${tableData}
            </table>`
}

function generateRow(row, data, replaceFunc) {
    let fullRow = "";
    for (let i = 0; i < row.length; i++) {
        fullRow += `<td> ${row[i]} </td>`;
    }
    if (replaceFunc) {
        fullRow += `<td> <button onclick="${replaceFunc} ` + " `" +
                    generateForm(data, row).replaceAll('"', "'") + "` ; initMDC();\" "  +
                    `class='mdc-button mdc-button--raised'>
                    <span class='mdc-button__ripple'></span>
                    <span class='mdc-button__label'>EDIT!</span></button> </td>`
        fullRow += generateDeleteButton(row, data["headers"], data["title"])
    }
    return `<tr>${fullRow}</tr>`
}

function generateDeleteButton(row, headers, title) {
    let inputs = '';
    for (let i = 0; i < row.length; i++) {
        inputs += `<input value='${row[i]}' name='${headers[i]}' style='display: none'>`;
    }
    inputs += `<input value='DELETE' name='${title}' style='display: none'>`;

    return `
    <td> <form action='/comp-353/insert_db.php' method='POST'>
    ${inputs}
    <button type='submit' 
    class='mdc-button mdc-button--raised' style='background-color: red'>
    <span class='mdc-button__ripple'></span>
    <span class='mdc-button__label'>DELETE!</span></button> 
    </form></td>
    `
}
