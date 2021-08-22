<!DOCTYPE html>
<html>

<head>
    <link href="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <script src="https://unpkg.com/material-components-web@latest/dist/material-components-web.min.js">
    </script>
    <script src="./InputFields.js"></script>
    <title>
        COMP-353
    </title>
</head>

<body>
    <aside class="mdc-drawer mdc-drawer--dismissible">
        <div class="mdc-drawer__content">
            <ul class="mdc-list mdc-list--icon-list" id="drawer-list">
                <li class="mdc-list-item mdc-list-item--activated" aria-current="page" onclick="switchData(0)">
                    <span class="mdc-list-item__ripple"></span>
                    <span class="mdc-list-item__text">Query One</span>
                </li>
                <li class="mdc-list-item" onclick="switchData(1)">
                    <span class="mdc-list-item__ripple"></span>
                    <span class="mdc-list-item__text">Query Two</span>
                </li>
                <li class="mdc-list-item" onclick="switchData(2)">
                    <span class="mdc-list-item__ripple"></span>
                    <span class="mdc-list-item__text">Query Three</span>
                </li>
                <li class="mdc-list-item mdc-list-item" aria-current="page" onclick="switchData(3)">
                    <span class="mdc-list-item__ripple"></span>
                    <span class="mdc-list-item__text">Query Four</span>
                </li>
                <li class="mdc-list-item" onclick="switchData(4)">
                    <span class="mdc-list-item__ripple"></span>
                    <span class="mdc-list-item__text">Query Five</span>
                </li>
                <li class="mdc-list-item" onclick="switchData(5)">
                    <span class="mdc-list-item__ripple"></span>
                    <span class="mdc-list-item__text">Query Six</span>
                </li>
                <li class="mdc-list-item" onclick="submitInfo()">
                    <span class="mdc-list-item__ripple"></span>
                    <span class="mdc-list-item__text">Submit Data</span>
                </li>
            </ul>
        </div>
    </aside>

    <div class="mdc-drawer-app-content">
        <header class="mdc-top-app-bar app-bar" id="app-bar">
            <div class="mdc-top-app-bar__row">
                <section class="mdc-top-app-bar__section mdc-top-app-bar__section--align-start">
                    <button class="material-icons mdc-top-app-bar__navigation-icon mdc-icon-button">menu</button>
                    <span class="mdc-top-app-bar__title" id="app-bar-title"></span>
                </section>
            </div>
        </header>

        <main class="main-content" id="main-content">
            <div class="mdc-top-app-bar--fixed-adjust">

                <?php
                require __DIR__ . '/sql_connection.php';
                if (isset($_COOKIE['message'])) {
                    echo "<div id=\"info-bar\" style=\"width:100%; background:#F9AA33; padding: 20px;
                           font-size: 20px;\">" . $_COOKIE['message'] .
                        "</div>" . "<script>
                            const sleep = (ms) => new Promise(resolve => setTimeout(resolve, ms));
                            async function fadeAway() {
                                await sleep(3000);
                                const infoBar = document.getElementById('info-bar');
                                for (let i = 1; i <= 100; i++) {
                                    await sleep(50);
                                    infoBar.style.opacity = (1 - (i/100)).toString();
                                }
                                for (let i = 1; i <= 50; i++) {
                                    await sleep(15);
                                    infoBar.style.padding = (20 - (i * (20/50))) + \"px\";
                                    infoBar.style.fontSize = (20 - (i * (40/50))) + \"px\";
                                }
                            }
                            fadeAway();
                        </script>";
                }
                ?>
                <p id="title" style="text-align:center"></p>
                <div id="toPopulate">
                    Loading...
                </div>
            </div>
        </main>
    </div>

    <script>




        const data = <?php
                        $conn = get_connection();
                        $sql_queries = get_temp_queries();
                        $return_value = array();
                        foreach ($sql_queries as $title => $the_query) {
                            //$value = "<table border='1' align='center'>";
                            $query = $the_query["query"];
                            $result = $conn->query($query);
                            $title = $title;

                            $headers = array();
                            $data = array();
                            if ($result!=null && $result->num_rows > 0) {
                                $row = $result->fetch_assoc();
                                //$value = $value . "<tr>";
                                foreach ($row as $key => $val) {
                                    array_push($headers, $key);
                                    //$value = $value . "<td>" . $key . "</td>";
                                }
                                //$value = $value . "</tr>";
                                do {
                                    //
                                    //$value = $value . "<tr>";
                                    $the_row = array();
                                    foreach ($row as $key => $val) {
                                        array_push($the_row, $val);
                                        //$value = $value . "<td>" . $val . "</td>";
                                    }
                                    array_push($data, $the_row);
                                    //$value = $value . "</tr>";
                                } while ($row = $result->fetch_assoc());
                            }
                            //$value = $value . "</table>";
                            //array_push($return_value, $value);
                            $table = array("title" => $title, "headers" => $headers, "data" => $data,
                                "editEnabled" => $the_query["editEnabled"]);
                            array_push($return_value, $table);
                        }

                        echo json_encode($return_value);
                        ?>;
        let dataIndex = 0;
        document.getElementById("app-bar-title").innerHTML = data[0]["title"];
        document.getElementById("toPopulate").innerHTML = generateTable(data[0],
            "document.getElementById('toPopulate').innerHTML =");
        //document.getElementById("toPopulate").innerHTML = data[dataIndex];

        // initialize side bar tabs
        let tabs = "";
        for (let i = 0; i < data.length; i++) {
            tabs += `<li class="mdc-list-item" onclick="switchData(${i})" aria-current="page">
                        <span class="mdc-list-item__ripple" ></span>
                        <span class="mdc-list-item__text">${data[i]["title"]}</span>
                    </li>`
        }
        const forms = [{"title" : "Shipment", "headers" : ["Number_of_vaccine_doses", "Reception_date", "Vaccine_name", "From_facility_storage", "To_facility_storage"]}];
        for (let i = 0; i < forms.length; i++){
            tabs += `<li class="mdc-list-item" onclick="switchForms(${i})" aria-current="page">
                        <span class="mdc-list-item__ripple" ></span>
                        <span class="mdc-list-item__text">${forms[i]["title"]}</span>
                    </li>`
        }

        document.getElementById("drawer-list").innerHTML = tabs;

        function switchForms(index){
            document.getElementById("app-bar-title").innerHTML = forms[index]["title"];
            document.getElementById("toPopulate").innerHTML = generateForm(forms[index]);
            initMDC();
        }

        function switchData(index) {
            dataIndex = index;
            document.getElementById("app-bar-title").innerHTML = data[dataIndex]["title"];
            document.getElementById("toPopulate").innerHTML = generateTable(data[dataIndex],
                "document.getElementById('toPopulate').innerHTML =");
        }

        function submitInfo() {
            document.getElementById("title").innerHTML = "";
            document.getElementById("toPopulate").innerHTML = "<div style='height: 10em; position: relative;'>" +
                "<form action = '/comp-353/insert_db.php' method = 'POST' style='margin: 0; " +
                "position: absolute; top: 50%; left: 50%; margin-right: -50%; transform: translate(-50%, -50%);'>" +
                "<input name = 'PopulateDB' value='default' style='display: none'/>" +
                "<button class='mdc-button mdc-button--raised'>" +
                "<span class='mdc-button__ripple'></span>" +
                "<span class='mdc-button__label'>POPULATE DB!</span>" +
                "<span class='mdc - button__touch'></span> </button>" + " </form></div>";
        }
    </script>
    <script>
        function initMDC() {
            const topAppBarElement = document.querySelector('.mdc-top-app-bar');
            const topAppBar = mdc.topAppBar.MDCTopAppBar.attachTo(topAppBarElement);
            topAppBar.setScrollTarget(document.getElementById('main-content'));
            const MDCDrawer = mdc.drawer.MDCDrawer;
            const drawer = MDCDrawer.attachTo(document.querySelector('.mdc-drawer'));
            topAppBar.listen('MDCTopAppBar:nav', () => {
                drawer.open = !drawer.open;
            });
            const MDCList = mdc.list.MDCList;
            const list = MDCList.attachTo(document.querySelector('.mdc-list'));

            const MDCTextField = mdc.textField.MDCTextField;
            const x = document.getElementsByClassName('mdc-text-field');
            let i;
            for (i = 0; i < x.length; i++) {
                MDCTextField.attachTo(x[i]);
            }

            const MDCButton = mdc.ripple.MDCRipple;
            const buttons = document.getElementsByClassName('mdc-button');
            for (i = 0; i < buttons.length; i++) {
                MDCButton.attachTo(buttons[i]);
            }
        }
        initMDC();
    </script>
</body>

</html>

<style>
    html,
    body {
        margin: 0;
        padding: 0;
    }

    .mdc-list-item {
        padding: 10px;
    }

    #the-form {
        margin: auto;
        width: 800px;
        padding: 50px;
    }

    .grid-container {
        display: grid;
        grid-row-gap: 20px;
    }

    .grid-item {
        grid-column-start: 1;
        grid-column-end: 2;
    }
    .grid-item2 {
        grid-column-start: 2;
        grid-column-end: 3;
    }
    .grid-item3 {
        grid-column-start: 3;
        grid-column-end: 4;
    }

    .mdc-button {
        padding: 20px;
    }
</style>
