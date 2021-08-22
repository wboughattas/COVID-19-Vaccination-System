<?php
require __DIR__ . '/sql_connection.php';

// used for debugging
function print_post() {
    $val = "";
    foreach($_POST as $key => $value) {
        $val = $val . "KEY: $key VALUE: $value ";
    }
    setcookie('message', $val, time() + 5, "/");
}

function create_insert($arr, $table_name) {
    $params = "";
    $values = "";
    foreach ($arr as $value) {
        $the_value = $value["value"];
        if ($value["custom_value"]) {
            $the_value = $value["custom_value"];
        }

        $params = $params . $value["value"] . ", ";
        if ($value["noquotes"]) {
            $values = $values . $_POST[$the_value] . ", ";
        } else {
            $values = $values . "'" . $_POST[$the_value] . "', ";
        }

    }
    // remove last comma
    $params = substr($params, 0, -2);
    $values = substr($values, 0, -2);
    return "INSERT INTO $table_name ($params) VALUES ($values);";
}

function create_update($arr, $table_name, $keys) {
    $updates = "";
    $where = "";
    foreach ($arr as $value) {
        $the_value = $value["value"];
        if ($value["custom_value"]) {
            $the_value = $value["custom_value"];
        }

        if ($value["noquotes"]) {
            $updates = $updates . $value["value"] . " = " . $_POST[$the_value] . ", ";
            if (in_array($value["value"], $keys)) {
                $where = $where . $value["value"] . " = " . $_POST["old_" . $the_value] . " AND ";
            }
        } else {
            $updates = $updates . $value["value"] . " = '" . $_POST[$the_value] . "', ";
            if (in_array($value["value"], $keys)) {
                $where = $where . $value["value"] . " = '" . $_POST["old_" . $the_value] . "' AND ";
            }
        }

    }
    // remove last comma
    $updates = substr($updates, 0, -2);
    $where = substr($where, 0, -4);
    return "UPDATE $table_name SET $updates WHERE $where;";
}

function create_delete($arr, $table_name, $keys) {
    $where = "";
    foreach ($arr as $value) {
        $the_value = $value["value"];
        if ($value["custom_value"]) {
            $the_value = $value["custom_value"];
        }

        if (in_array($value["value"], $keys)) {
            if ($value["noquotes"]) {
                $where = $where . $value["value"] . " = " . $_POST[$the_value] . " AND ";
            } else {
                $where = $where . $value["value"] . " = '" . $_POST[$the_value] . "' AND ";
            }
        }
    }
    // remove last comma
    $where = substr($where, 0, -4);
    return "DELETE FROM $table_name WHERE $where;";
}




function define_no_quotes($arr, $no_quotes) {
    $result = array();
    foreach($arr as $value) {
        $no_quote = false;
        if (in_array($value, $no_quotes)) {
            $no_quote = true;
        }
        array_push($result, array("value" => $value, "noquotes" => $no_quote));
    }
    return $result;
}

function oldKeysMatch($table_name, $keys){
    foreach ($keys as $key){
        if($_POST[$key]!=$_POST["old_" . $key]){
            setcookie('message', 'Attributes forming the keys have been modified. No tuple modified', time() + 5, "/");
            return false;
        }
    }
    return true;
}

function checkUnique($table_name, $keys){
    $query = "SELECT COUNT(*) AS amount FROM $table_name WHERE ";
    foreach ($keys as $key){

        $query = $query . "$key = '${_POST[$key]}' AND ";
    }
    $query = substr($query, 0, -4);
    $query = $query . ";";
    $conn = get_connection();
    $result = ($conn->query($query))->fetch_assoc();

    if($result['amount']==0) {
        return true;
    }
    else{
        setcookie('message', $query, time() + 5, "/");
        //setcookie('message', 'Key is not unique. No tuple created', time() + 5, "/");
        return false;
    }
}

$global_belongs_to_params = define_no_quotes(array('Medical_card_number','GroupAgeID'),array('GroupAgeID'));
$global_belongs_to_keys = array('Medical_card_number');
$global_eligibility_req_params = define_no_quotes(array('Province','GroupAgeID'),array('GroupAgeID'));
$global_eligibility_req_keys = array('Province');
$global_shipment_params = define_no_quotes(array("Number_of_vaccine_doses", "Reception_date", "Vaccine_name", "From_facility_storage", "To_facility_storage"),array("Number_of_vaccine_doses"));
$global_shipment_keys = array("Reception_date", "Vaccine_name", "From_facility_storage", "To_facility_storage");
$global_vaccination_params = define_no_quotes(array("Vaccine_name","Vaccination_date","Facility_name","Medical_card_number","Dose_number","Vaccine_administrator_ID"),array("Dose_number"));
$global_vaccination_keys = array("Medical_card_number","Dose_number");


if ($_POST["PopulateDB"]) {
    // populating DB
    populate_db(false);
    setcookie('message', 'Population Inserted Successfully!', time() + 5, "/");
}

if ($_POST["Person"]) {
    $method = $_POST["Person"];
    $keys = array('Medical_card_number');
    $person_headers = array("First_name", "Last_name", "SSN", "Passport_number", "Phone_number", "Date_of_birth",
        "Email_address", "Infected_in_past", "Citizenship", "Medical_card_number");
    $person_params = define_no_quotes($person_headers, array('Infected_in_past'));

    $inf_history_headers = array("Medical_card_number", "Date_of_infection", "Type_of_infection");
    $inf_history_params = define_no_quotes($inf_history_headers, array());

    $location_params = define_no_quotes(array("Address", "Province", "Postal_code", "City"), array());

    $resides_params = define_no_quotes(array("Medical_card_number", "Address", "Postal_code"), array());

    if ($method == "CREATE" && checkUnique('Person', array('Medical_card_number'))) {
        $k = create_insert($person_params, 'Person');
        $new_query = create_insert($person_params, 'Person');
        $conn = get_connection();
        $conn->query($new_query);

        $med_card_query = create_insert($inf_history_params, 'Infection_History');
        $conn = get_connection();
        $conn->query($med_card_query);


        $location = create_insert($location_params, 'Location');
        $conn = get_connection();
        $conn->query($location);

        $resides_query = create_insert($resides_params, 'Resides_At');
        $conn = get_connection();
        $conn->query($resides_query);
        setcookie('message', 'Created Person!', time() + 5, "/");
    }

    if ($method == "EDIT" && oldKeysMatch('Person', $keys)) {

        $new_query = create_update($person_params, 'Person', $keys);
        $conn = get_connection();
        $conn->query($new_query);

        $infection_query = create_update($inf_history_params, 'Infection_History', array('Medical_card_number', 'Date_of_infection'));
        $conn = get_connection();
        $conn->query($infection_query);

        $location_query = create_update($location_params, 'Location', array('Address', 'Postal_code'));
        $conn = get_connection();
        $conn->query($location_query);

        $resides_query = create_update($resides_params, 'Resides_At',
            array('Address', 'Postal_code', 'Medical_card_number'));
        $conn = get_connection();
        $conn->query($resides_query);
        setcookie('message', 'Successfully updated!', time() + 5, "/");
    }

    if ($method == "DELETE") {

        $infection_query = create_delete($inf_history_params, 'Infection_History', array('Medical_card_number', 'Date_of_infection'));
        $conn = get_connection();
        $conn->query($infection_query);

        $resides_query = create_delete($resides_params, 'Resides_At',
            array('Address', 'Postal_code', 'Medical_card_number'));
        $conn = get_connection();
        $conn->query($resides_query);

        $new_query = create_delete($person_params, 'Person', $keys);
        $conn = get_connection();
        $conn->query($new_query);

        $location_query = create_delete($location_params, 'Location', array('Address', 'Postal_code'));
        $conn = get_connection();
        $conn->query($location_query);

        //belongs to delete if exists

        setcookie('message', 'Successfully DELETED!', time() + 5, "/");
    }
}

if ($_POST["Employee"]) {
    $method = $_POST["Employee"];

    $person_params = define_no_quotes(array("Medical_card_number", "First_name", "Last_name", "Phone_number", "Citizenship",
        "Email_address", "Date_of_birth", "SSN"), array('Infected_in_past'));
    $employee_params = define_no_quotes(array("Medical_card_number", "EID"), array());
    $employment_params = define_no_quotes(array("EID", "start_date_of_employment", "end_date_of_employment",
        "Facility_name"), array());
    $resides_params = define_no_quotes(array("Medical_card_number", "Address", "Postal_code"), array());
    $location_params = define_no_quotes(array("Address", "Province", "Postal_code", "City"), array());

    if ($method == "CREATE") {

        $new_query = create_insert($person_params, 'Person');
        $conn = get_connection();
        $conn->query($new_query);

        $employee_query = create_insert($employee_params, 'Employee');
        $conn = get_connection();
        $conn->query($employee_query);

        $location_query = create_insert($location_params, 'Location');
        $conn = get_connection();
        $conn->query($location_query);

        $employment_query = create_insert($employment_params, 'Employment');
        $conn = get_connection();
        $conn->query($employment_query);

        $resides_query = create_insert($resides_params, 'Resides_At');
        $conn = get_connection();
        $conn->query($resides_query);

        setcookie('message', $employment_query, time() + 5, "/");
    }
    if ($method == "EDIT") {
        $new_query = create_update($person_params, 'Person', array('Medical_card_number', "Date_of_birth"));
        $conn = get_connection();
        $conn->query($new_query);

        $employee_query = create_update($employee_params, 'Employee', array("EID", "Medical_card_number"));
        $conn = get_connection();
        $conn->query($employee_query);

        $location_query = create_update($location_params, 'Location', array("Address", "Postal_code"));
        $conn = get_connection();
        $conn->query($location_query);

        $employment_query = create_update($employment_params, 'Employment', array("EID",
            "start_date_of_employment","Facility_name"));
        $conn = get_connection();
        $conn->query($employment_query);

        $resides_query = create_update($resides_params, 'Resides_At', array("Medical_card_number", "Address",
            "Postal_code"));
        $conn = get_connection();
        $conn->query($resides_query);

        setcookie('message', "Successfully Edited!", time() + 5, "/");
    }
    if ($method == "DELETE") {
        $new_query = create_delete($person_params, 'Person', array('Medical_card_number', "Date_of_birth"));
        $conn = get_connection();
        $conn->query($new_query);

        $employee_query = create_delete($employee_params, 'Employee', array("EID", "Medical_card_number"));
        $conn = get_connection();
        $conn->query($employee_query);

        $location_query = create_delete($location_params, 'Location', array("Address", "Postal_code"));
        $conn = get_connection();
        $conn->query($location_query);

        $employment_query = create_delete($employment_params, 'Employment', array("EID",
            "start_date_of_employment","Facility_name"));
        $conn = get_connection();
        $conn->query($employment_query);

        $resides_query = create_delete($resides_params, 'Resides_At', array("Medical_card_number", "Address",
            "Postal_code"));
        $conn = get_connection();
        $conn->query($resides_query);
        setcookie('message', "Successfully deleted!", time() + 5, "/");
    }
}

if ($_POST["Facility"]) {
    $method = $_POST["Facility"];

    $facility_params = define_no_quotes(array("Name", "Phone_number", "Web_address", "Type_of_facility"), array());
    $location_params = define_no_quotes(array("Address", "Street", "City", "Province", "Postal_code"), array());
    $located_at_params = define_no_quotes(array("Address", "Postal_code", "Facility_name"), array());
    $located_at_params[2]["custom_value"] = "Name";

    if ($method == "CREATE") {
        $facility_query = create_insert($facility_params, 'Vaccination_Facility');
        $conn = get_connection();
        $conn->query($facility_query);

        $location_query = create_insert($location_params, 'Location');
        $conn = get_connection();
        $conn->query($location_query);

        $located_at_query = create_insert($located_at_params, 'Located_At');
        $conn = get_connection();
        $conn->query($located_at_query);

        setcookie('message', "Created Public Health Facility!", time() + 5, "/");
    }

    if ($method == "EDIT") {
        $facility_query = create_update($facility_params, 'Vaccination_Facility', array("Name"));
        $conn = get_connection();
        $conn->query($facility_query);

        $location_query = create_update($location_params, 'Location', array("Address", "Postal_code"));
        $conn = get_connection();
        $conn->query($location_query);

        $located_at_query = create_update($located_at_params, 'Located_At',
            array("Address", "Postal_code", "Facility_name"));
        $conn = get_connection();
        $conn->query($located_at_query);

        setcookie('message', "Edited Public Health Facility", time() + 5, "/");
    }

    if ($method == "DELETE") {

        $located_at_query = create_delete($located_at_params, 'Located_At',
            array("Address", "Postal_code", "Facility_name"));
        $conn = get_connection();
        $conn->query($located_at_query);

        $facility_query = create_delete($facility_params, 'Vaccination_Facility', array("Name"));
        $conn = get_connection();
        $conn->query($facility_query);

        $location_query = create_delete($location_params, 'Location', array("Address", "Postal_code"));
        $conn = get_connection();
        $conn->query($location_query);

        setcookie('message', "Deleted Public Health Facility", time() + 5, "/");
    }
}


if ($_POST["Vaccination_Type"]) {
    $method = $_POST["Vaccination_Type"];
    $vacc_type_params = define_no_quotes(array("Vaccine_name", "Vaccine_approval_date", "Vaccine_short_description",
        "Vaccine_status", "Vaccine_date_of_suspension", "Minimum_allowed_group_age"), array('Minimum_allowed_group_age'));

    if ($method == "CREATE") {
        $vacc_info_query = create_insert($vacc_type_params, 'Vaccine_Information');
        $conn = get_connection();
        $conn->query($vacc_info_query);
        setcookie('message', $vacc_info_query, time() + 5, "/");
    }
    if ($method == "EDIT") {
        $vacc_info_query = create_update($vacc_type_params, 'Vaccine_Information', array("Vaccine_name"));
        $conn = get_connection();
        $conn->query($vacc_info_query);
        setcookie('message', "Updated Vaccination Type Successfully!", time() + 5, "/");

    }
    if ($method == "DELETE") {
        $vacc_info_query = create_delete($vacc_type_params, 'Vaccine_Information', array("Vaccine_name"));
        $conn = get_connection();
        $conn->query($vacc_info_query);
        setcookie('message', "Deleted Vaccination Type Successfully!", time() + 5, "/");
    }
}

if ($_POST["COVID_Variants"]) {
    $method = $_POST["COVID_Variants"];
    $covid_variant_params = define_no_quotes(array("Type_of_infection"), array());

    if ($method == "CREATE") {
        $covid_variant_query = create_insert($covid_variant_params, 'Type_of_Infection');
        $conn = get_connection();
        $conn->query($covid_variant_query);
        setcookie('message', $covid_variant_query, time() + 5, "/");
    }

    if ($method == "EDIT") {
        $covid_variant_query = create_update($covid_variant_params, 'Type_of_Infection',
            array("Type_of_infection"));
        $conn = get_connection();
        $conn->query($covid_variant_query);
        setcookie('message', $covid_variant_query, time() + 5, "/");
    }

    if ($method == "DELETE") {
        $covid_variant_query = create_delete($covid_variant_params, 'Type_of_Infection',
            array("Type_of_infection"));
        $conn = get_connection();
        $conn->query($covid_variant_query);
        setcookie('message', $covid_variant_query, time() + 5, "/");
    }
}

if ($_POST["Age_Group"]) {
    $method = $_POST["Age_Group"];
    $keys = array('GroupAgeID');
    $age_group_params = define_no_quotes(array("GroupAgeID", "MinAge", "MaxAge"), array("GroupAgeID", "MinAge", "MaxAge"));


    if ($method == "CREATE" && checkUnique('Age_Group', $keys)) {
        $age_group_query = create_insert($age_group_params, 'Age_Group');
        $conn = get_connection();
        $conn->query($age_group_query);
        setcookie('message', "Created new Age Group", time() + 5, "/");

    }

    if ($method == "EDIT" && oldKeysMatch('Age_Group',$keys )) {
        $age_group_query = create_update($age_group_params, 'Age_Group',$keys);
        $conn = get_connection();
        $conn->query($age_group_query);
        setcookie('message', "Edited Age Group", time() + 5, "/");

    }

    if ($method == "DELETE") {
        /*
        $belongs_to_query = create_delete($global_belongs_to_params, 'Belongs_To', $global_belongs_to_keys);
        $conn = get_connection();
        $conn->query($belongs_to_query);

        setcookie('message', $belongs_to_query, time() + 5, "/");

        $province_query = create_delete($global_eligibility_req_params, 'eligibility_requirement',$global_eligibility_req_keys);
        $conn = get_connection();
        $conn->query($province_query);
        */

        $age_query = create_delete($age_group_params, 'Age_Group', $keys);
        $conn = get_connection();
        $conn->query($age_query);

        setcookie('message', "Deleted Age Group", time() + 5, "/");
    }
}

if ($_POST["Eligibility_Requirement"]) {
    $method = $_POST["Eligibility_Requirement"];
    $table_name = 'Eligibility_Requirement';
    if ($method == "CREATE" && checkUnique($table_name, $global_eligibility_req_keys)) {
        $eligible_query = create_insert($global_eligibility_req_params, $table_name);
        $conn = get_connection();
        $conn->query($eligible_query);

        setcookie('message', "Created new Eligibility", time() + 5, "/");

    }

    if ($method == "EDIT" && oldKeysMatch($table_name,$global_eligibility_req_keys )) {
        $eligible_query = create_update($global_eligibility_req_params, $table_name,$global_eligibility_req_keys);
        $conn = get_connection();
        $conn->query($eligible_query);
        setcookie('message', "Edited Eligibility", time() + 5, "/");

    }

    if ($method == "DELETE") {

        $eligible_query = create_delete($global_eligibility_req_params, $table_name,$global_eligibility_req_keys);
        $conn = get_connection();
        $conn->query($eligible_query);
        setcookie('message', "Remove Eligibility", time() + 5, "/");
    }
}

if ($_POST["Shipment"]) {
    $table_name = 'Shipment';
    $method = $_POST[$table_name];

    if ($method == "CREATE" && checkUnique($table_name, $global_shipment_keys)) {

        $wasim_query = "INSERT INTO Shipment (From_facility_storage, To_facility_storage, Vaccine_name, Number_of_vaccine_doses, Reception_date)
SELECT '${_POST['From_facility_storage']}', '${_POST['To_facility_storage']}', '${_POST['Vaccine_name']}', '${_POST['Number_of_vaccine_doses']}', '${_POST['Reception_date']}'
FROM Storage
WHERE
    Storage.Facility_name = '${_POST['From_facility_storage']}'
  AND Storage.Vaccine_name = '${_POST['Vaccine_name']}'
  AND '${_POST['Number_of_vaccine_doses']}' <= Storage.Capacity;";
        $conn = get_connection();
        $conn->query($wasim_query);

        $da_query = "SELECT count(*) as count FROM Shipment WHERE From_facility_storage = '${_POST['From_facility_storage']}' 
                                           AND Vaccine_name = '${_POST['Vaccine_name']}' AND 
                                             To_facility_storage = '${_POST['To_facility_storage']}' AND
                                             Reception_date = '${_POST['Reception_date']}';";
        $conn = get_connection();
        $result = $conn->query($da_query);

        $row = $result->fetch_assoc();
        if ($row['count'] > 0) {
            $update_cap_query = "CALL updateCapacity('${_POST["From_facility_storage"]}', '${_POST["To_facility_storage"]}', '${_POST["Vaccine_name"]}', '${_POST["Number_of_vaccine_doses"]}');";
            $conn = get_connection();
            $conn->query($update_cap_query);
            setcookie('message', $wasim_query, time() + 5, "/");
        }
    }
}

if ($_POST["Vaccination"]) {
    $table_name = 'Vaccination';
    $method = $_POST[$table_name];

    if ($method == "CREATE" && checkUnique($table_name, $global_vaccination_keys)) {
        $wasim_query = "INSERT INTO Vaccination (Vaccine_name, Vaccination_date, Facility_name, Medical_card_number, Dose_number,
                         Vaccine_administrator_ID)
SELECT DISTINCT '${_POST['Vaccine_name']}', '${_POST['Vaccination_date']}', '${_POST['Facility_name']}', '${_POST['Medical_card_number']}', '${_POST['Dose_number']}',
                         '${_POST['Vaccine_administrator_ID']}'
FROM Storage,
     Employment,
     Belongs_To,
     Age_Group,
     Eligibility_Requirement,
     Location,
     Located_At,
     Vaccination_Facility,
     Person
WHERE Storage.Facility_name = 'Stade Olympique/SAQ'
  AND Storage.Vaccine_name = 'AstraZeneca/COVISHIELD COVID-19'
  AND Storage.Capacity >= 1
  AND Employment.EID = 'EID2'
  AND '2021-1-12' >= start_date_of_employment
  AND '2021-1-12' <= end_date_of_employment
  AND Person.Medical_card_number = 'aKXWd7nRui'
  AND Belongs_To.Medical_card_number = Person.Medical_card_number
  AND Belongs_To.GroupAgeID = Age_Group.GroupAgeID
  AND Location.Province = Eligibility_Requirement.Province
  AND Located_At.Postal_code = Location.Postal_code
  AND Located_At.Address = Location.Address
  AND Located_At.Facility_name = 'Stade Olympique/SAQ'
  AND Vaccination_Facility.Name = Located_At.Facility_name
  AND Age_Group.GroupAgeID <= Eligibility_Requirement.GroupAgeID;";
        $vaccination_query = create_insert($global_vaccination_params, $table_name);
        $conn = get_connection();
        $conn->query($wasim_query);

        $da_query = "SELECT count(*) as count FROM Vaccination WHERE Medical_card_number = '${_POST['Medical_card_number']}' 
                            AND Dose_number = '${_POST['Dose_number']}'";
        $conn = get_connection();
        $result = $conn->query($da_query);

        $row = $result->fetch_assoc();
        if ($row['count'] > 0) {
            $update_cap_query = "CALL updateCapacityAfterVaccination('${_POST["Vaccine_name"]}', '${_POST["Facility_name"]}');";
            $conn = get_connection();
            $conn->query($update_cap_query);
        }
        setcookie('message', $da_query, time() + 5, "/");
    }
}


$page = '/comp-353';
header('Location: ' . $page, true, 303);
exit;
