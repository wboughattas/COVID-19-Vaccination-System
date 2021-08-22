<?php
function get_connection()
{
    $server_name = "localhost";
    $username = "root";
    $password = "";
    $database_name = "test";
    $conn = mysqli_connect($server_name, $username, $password, $database_name);
    if (!$conn) {
        die("Connection Failed:" . mysqli_connect_error());
    }
    return $conn;
}

function get_temp_queries()
{
    $table_names = array(
         "Infection History", "Vaccination", "Vaccination Facility",
        "Vaccine Information"
    );
    $table_to_query = array();
    $table_to_query["Person"] = array("query" => get_person_query(), "editEnabled" => true);
    $table_to_query["Employee"] = array("query" => get_employee_query(), "editEnabled" => true);
    $table_to_query["Facility"] = array("query" => get_facility_query(), "editEnabled" => true);
    $table_to_query["Vaccination Type"] = array("query" => get_vaccination_query(), "editEnabled" => true);
    $table_to_query["COVID Variants"] = array("query" => "SELECT * FROM Type_of_Infection", "editEnabled" => true);
    $table_to_query["Query Twelve"] = array("query" => get_query_twelve(), "editEnabled" => false);
    $table_to_query["Query Thirteen"] = array("query" => get_query_thirteen(), "editEnabled" => false);
    $table_to_query["Query Fourteen"] = array("query" => get_query_fourteen(), "editEnabled" => false);
    $table_to_query["Query Fifteen"] = array("query" => get_query_fifteen(), "editEnabled" => false);
    $table_to_query["Query Sixteen"] = array("query" => get_query_sixteen(), "editEnabled" => false);
    $table_to_query["Query Seventeen"] = array("query" => get_query_seventeen(), "editEnabled" => false);
    $table_to_query["Query Eighteen"] = array("query" => get_query_eighteen(), "editEnabled" => false);
    $table_to_query["Query Nineteen"] = array("query" => get_query_nineteen(), "editEnabled" => false);
    $table_to_query["Query Twenty"] = array("query" => get_query_twenty(), "editEnabled" => false);
    $table_to_query["Age Group"] = array("query" => get_age_group_query(), "editEnabled" => true);
    $table_to_query["Eligibility_Requirement"] = array("query" => get_requirement_query(), "editEnabled" => true);
    $table_to_query["Vaccination"] = array("query" => "SELECT * FROM Vaccination", "editEnabled" => true);

    return $table_to_query;
}

function get_requirement_query(){
    return "SELECT * FROM Eligibility_Requirement";
}

function get_age_group_query(){
    return "SELECT * FROM Age_Group";
}

function get_person_query()
{
    return "SELECT First_name, Last_name, SSN, Passport_number, Phone_number, Date_of_birth,
                    Email_address, Infected_in_past, Citizenship, Person.Medical_card_number,
    Date_of_infection, Type_of_infection, Location.Postal_code, Location.Address, Location.Province, Location.City
FROM ((Person LEFT JOIN `Infection_History` on
    Person.Medical_card_number = `Infection_History`.Medical_card_number)
        JOIN `Resides_At` on Person.Medical_card_number = `Resides_At`.Medical_card_number)
        JOIN `Location` on `Resides_At`.Address = `Location`.Address AND `Location`.Postal_code = `Resides_At`.Postal_code;";
}

function get_employee_query()
{
    return "SELECT Employee.EID, Person.SSN, Person.First_name, Person.Last_name, Person.Date_of_birth, Person.Medical_card_number,
       Person.Phone_number, Person.Citizenship, Person.Email_address, Employment.start_date_of_employment,
       Employment.end_date_of_employment, Location.Postal_code, Location.City, Location.Province, Location.Address,
       Employment.Facility_name
FROM (((Employee
    JOIN Person on Employee.Medical_card_number = Person.Medical_card_number)
           JOIN Employment ON Employee.EID = Employment.EID)
           JOIN Resides_At ON Resides_At.Medical_card_number = Person.Medical_card_number)
           JOIN Location ON Location.Address = Resides_At.Address AND Location.Postal_code = Resides_At.Postal_code;";
}

function get_facility_query()
{
    return "SELECT Name, Phone_number, Web_address, Type_of_facility, Location.Address, Street, City, Province, Location.Postal_code
FROM (Vaccination_Facility JOIN Located_At LA on Vaccination_Facility.Name = LA.Facility_name)
    JOIN Location ON LA.Postal_code=Location.Postal_code AND LA.Address=Location.Address;";
}

function get_vaccination_query() {
    return "SELECT * FROM Vaccine_Information;";
}

function get_query_twelve() {
    return "SELECT First_name,Last_name,Person.Date_of_birth,Email_address,Phone_number,City, v.Vaccination_date, v.Vaccine_name,Infected_in_past
FROM Person
join Vaccination v on Person.Medical_card_number = v.Medical_card_number
join Belongs_To b on Person.Medical_card_number = b.Medical_card_number
join Resides_At ra on b.Medical_card_number = ra.Medical_card_number
join Location l on ra.Address = l.Address and ra.Postal_code = l.Postal_code
WHERE GroupAgeID>=1 and GroupAgeID <=3
group by v.Medical_card_number
having count(v.Medical_card_number) =1;";
}

function get_query_thirteen() {
    return "SELECT First_name, Last_name, Date_of_birth, Email_address, Phone_number, City, Infected_in_past
FROM Person
         join Vaccination v on Person.Medical_card_number = v.Medical_card_number
         join Resides_At ra on Person.Medical_card_number = ra.Medical_card_number
         join Location l on ra.Address = l.Address and ra.Postal_code = l.Postal_code
group by v.Medical_card_number
having COUNT(DISTINCT v.Vaccine_Name) > 1;";
}
function get_query_fourteen() {
    return "SELECT First_name,
       Last_name,
       Date_of_birth,
       Email_address,
       Phone_number,
       City,
       count(distinct Date_of_infection) as Total_Number_Of_Infections
FROM Person
         join Infection_History ih on Person.Medical_card_number = ih.Medical_card_number
         join Vaccination v on Person.Medical_card_number = v.Medical_card_number
         join Resides_At ra on Person.Medical_card_number = ra.Medical_card_number
         join Location l on ra.Address = l.Address and ra.Postal_code = l.Postal_code
group by v.Medical_card_number
having count(distinct Type_of_infection) > 1;";
}


function get_query_fifteen() {
    return "SELECT Province, Vaccine_Name, sum(Capacity)
FROM Location
         join Located_At la on Location.Address = la.Address and Location.Postal_code = la.Postal_code
         join Vaccination_Facility vf on la.Facility_name = vf.Name
         join Storage s on vf.Name = s.Facility_name
group by Province, Vaccine_Name
order by Province asc, sum(Capacity) desc;";
}

function get_query_sixteen() {

    return "SELECT Province, Vaccine_Name, count(distinct p.Medical_card_number) as Total_People
from Location
         join Resides_At ra on Location.Address = ra.Address and Location.Postal_code = ra.Postal_code
         join Person p on ra.Medical_card_number = p.Medical_card_number
         join Vaccination v on p.Medical_card_number = v.Medical_card_number
where Vaccination_date > '2021-01-01'
  AND Vaccination_date < '2021-07-22'
group by Province, Vaccine_Name;";

}

function get_query_seventeen() {

    return "SELECT City, count(*) as Number_of_vaccines
FROM Location
         join Located_At la on Location.Address = la.Address and Location.Postal_code = la.Postal_code
         join Vaccination_Facility vf on la.Facility_name = vf.Name
         join Vaccination v on la.Facility_name = v.Facility_name
where Vaccination_date > '2021-01-01'
  and Vaccination_date < '2021-07-22'
group by City;";

}

function get_query_eighteen() {
return "SELECT Name, Phone_number, Web_address, Type_of_facility, emp_number,number_of_shipments_received, COALESCE(doses_received,0) as doses_received,
       COALESCE(doses_sent,0) as doses_sent, COALESCE(number_of_transfers_to,0) as number_of_transfers_to,
       COALESCE(number_of_transfers_from,0) as number_of_transfers_from,COALESCE(transfer_doses_received,0) as transfer_doses_received,
       COALESCE(transfer_doses_sent,0) as transfer_doses_sent, capacity, COALESCE(people_vacciated,0) as people_vacciated,
       COALESCE(total_doses_people_received,0) as total_doses_people_received

       from (select Name, Phone_number, Web_address, Type_of_facility from Vaccination_Facility
           group by Name) as vf,

            (select Shipment.To_facility_storage as To_name,
             sum(Number_of_vaccine_doses) as doses_received
        from Shipment
        group by Shipment.To_facility_storage) as dr

            left join
            (select Shipment.From_facility_storage as from_name,
             sum(Number_of_vaccine_doses) as doses_sent
        from Shipment
        group by Shipment.From_facility_storage) as ds on from_name=To_name

        join
           (select Facility_name,count(distinct EID) as emp_number from Employment group by Facility_name) as number_of_employees
            on Facility_name=To_name

        left join
            (select Shipment.To_facility_storage as Transfer_To_name,
             count(Number_of_vaccine_doses) as number_of_transfers_to
        from Shipment
        where From_facility_storage<>'BIG PHARMAS'
        group by Shipment.To_facility_storage) as transfer_to on To_name=Transfer_To_name

        left join
            (select Shipment.To_facility_storage as Shipments_Received_name,
             count(Number_of_vaccine_doses) as number_of_shipments_received
        from Shipment
        group by Shipment.To_facility_storage) as shipments_received on To_name=Shipments_Received_name

        left join
                (select Shipment.From_facility_storage as Transfer_From_name,
             count(Number_of_vaccine_doses) as number_of_transfers_from
        from Shipment
        group by Shipment.From_facility_storage) as transfer_from on To_name=Transfer_From_name

        left join
            (select Shipment.From_facility_storage as tr_from_name,
             sum(Number_of_vaccine_doses) as transfer_doses_sent
        from Shipment
            where From_facility_storage<>'BIG PHARMAS'
        group by Shipment.From_facility_storage) as tr_from on tr_from_name=To_name

        left join (select Shipment.To_facility_storage as tr_to_name,
             sum(Number_of_vaccine_doses) as transfer_doses_received
        from Shipment
        where Shipment.From_facility_storage<>'BIG PHARMAS'
        group by Shipment.To_facility_storage) as tr_to on To_name=tr_to_name

        join (select Storage.Facility_name, sum(Capacity) as capacity
        from Storage
        group by Facility_name) as cap on cap.Facility_name=To_name

        left join (select Facility_name,count(distinct Medical_card_number) as people_vacciated
                from Vaccination group by Facility_name) as ppl_vaccinated on To_name=ppl_vaccinated.Facility_name

        left join (select Facility_name,count(Medical_card_number) as total_doses_people_received
                from Vaccination group by Facility_name) as doses_ppl_received on To_name=doses_ppl_received.Facility_name

where To_name=Name;";
}

function get_query_nineteen() {
    return "SELECT e.EID,
       p.SSN,
       p.First_name,
       p.Last_name,
       p.Date_of_birth,
       p.Medical_card_number,
       p.Phone_number,
       City,
       Province,
       l.Postal_code,
       Citizenship,
       Email_address,
       start_date_of_Employment,
       end_date_of_Employment
FROM Person p
         join Employee e on p.Medical_card_number = e.Medical_card_number
         join Resides_At ra on p.Medical_card_number = ra.Medical_card_number
         join Location l on ra.Address = l.Address and ra.Postal_code = l.Postal_code
         join Employment e2 on e.EID = e2.EID
where e2.Facility_name = 'Stade Olympique/SAQ';";
}

function get_query_twenty() {
    return "SELECT e.EID, p.First_name, p.Last_name, p.Date_of_birth, p.Phone_number, City, e2.Facility_name
FROM Person p
         join Employee e on p.Medical_card_number = e.Medical_card_number
         join Resides_At ra on p.Medical_card_number = ra.Medical_card_number
         join Location l on ra.Address = l.Address and ra.Postal_code = l.Postal_code
         join Employment e2 on e.EID = e2.EID
         left join Vaccination v on p.Medical_card_number = v.Medical_card_number
group by e.EID
having count(v.Medical_card_number) <= 1;";
}

function get_queries()
{
    return array(
        get_query_one(), get_query_two(), get_query_three(), get_query_four(), get_query_five(),
        get_query_six()
    );
}

function get_query_one()
{
    return "SELECT Person.First_name, Person.Last_name, Person.Date_of_birth, Person.Email_address, 
    Person.Phone_number, Person.City, " . "Vaccination.Vaccination_date, Vaccination.Vaccine_name, 
    Person.Infected_in_past FROM Person JOIN Vaccination ON Person.Medical_card_number=Vaccination.Medical_card_number 
    JOIN `Age Group` ON Person.Date_of_birth=`Age Group`.Date_of_birth WHERE `Age Group`.Group_age>= 4 AND 
    `Age Group`.Group_age <= 10 AND Vaccination.Dose_number=1;";
}

function get_query_two()
{
    return "SELECT Name, `Vaccination Facility`.`Address`, `Vaccination Facility`.`City`, 
    `Vaccination Facility`.`Province`, " . "`Vaccination Facility`.Phone_number, `Web_address`, `Type_of_facility`
    FROM `Vaccination Facility` 
    WHERE `Province` = 'QC';";
}

function get_query_three()
{
    return "SELECT Person.First_name,Person.Last_name," . "
       Person.Date_of_birth ,Person.Email_address,
       Person.Phone_number, Vaccination.Vaccination_date,
       Vaccination.Vaccine_name, `Age Group`.Group_age
        FROM Person
        JOIN Vaccination ON Person.Medical_card_number=Vaccination.Medical_card_number
        JOIN `Age Group` ON Person.Date_of_birth=`Age Group`.Date_of_birth
        JOIN `Vaccination Facility` ON Vaccination_facility_name = `Vaccination Facility`.Name
        WHERE Vaccination.Vaccination_facility_name LIKE '%Olympic Stadium%'
        AND (`Vaccination Facility`.`City` = 'Montreal' OR `Vaccination Facility`.`City` = 'Montréal')
        AND Vaccination.Vaccination_date LIKE '2021-01%' ;";
}


function get_query_four()
{
    return "SELECT VI.Vaccine_name, VI.Vaccine_approval_date, VI.Vaccine_status, count(DISTINCT V.Medical_card_number)
    " . " FROM `Vaccine Information` VI, Vaccination V, `Vaccination Facility` VF
    WHERE V.Vaccine_name=VI.Vaccine_name AND VF.Name = V.Vaccination_facility_name AND VF.Province = 'QC'
    GROUP BY VI.Vaccine_name;   ";
}

function get_query_five()
{
    return "SELECT First_name,Last_name, P.Date_of_birth,
       Email_address,Telephone_number,City," . "
       V.Vaccination_date, V.Vaccine_name, VI.Vaccine_date_of_suspension
FROM Person P 
JOIN Vaccination V ON P.Medical_card_number=V.Medical_card_number
JOIN `Vaccine Information` VI ON V.Vaccine_name = VI.Vaccine_name
WHERE VI.Vaccine_status='SUSPENDED';";
}

function get_query_six()
{
    return "SELECT City, count(DISTINCT V.Medical_card_number) AS 'Number of people vaccinated'
    FROM Person P
    JOIN Vaccination V ON P.Medical_card_number=V.Medical_card_number
    GROUP BY City;";
}

function populate_db($debug_mode)
{
    $conn = get_connection();
    $queries = array(
        "INSERT INTO `Vaccination Facility` (`Name`, `Address`, `Phone_number`, `Web_address`, `Type of Facility`) VALUES ('Stade Olympique/SAQ', '4545, ave Pierre-de-Coubertin, Montréal, H1V 0B2', '5146444545', 'https://ciusss-estmtl.gouv.qc.ca/covid-19/Vaccination-contre-la-covid-19/clinique-de-Vaccination-du-stade-olympiquesaq', 'special installment');
INSERT INTO `Vaccination Facility` (`Name`, `Address`, `Phone_number`, `Web_address`, `Type of Facility`) VALUES ('Aréna Martin-Brodeur', '5300, boul. Robert, Saint-Léonard, H1R 1P9', '5146444545', 'https://ciusss-estmtl.gouv.qc.ca/covid-19/Vaccination-contre-la-covid-19/clinique-de-Vaccination-de-larena-martin-brodeur', 'special installment');
INSERT INTO `Vaccination Facility` (`Name`, `Address`, `Phone_number`, `Web_address`, `Type of Facility`) VALUES ('Pôle de Vaccination Rivière-des-Prairies Transcontinental – Énergir', '8000 Avenue Blaise-Pascal, Montréal, QC H1E 2S7', '5146444545', 'https://ciusss-estmtl.gouv.qc.ca/covid-19/Vaccination-contre-la-covid-19/pole-de-Vaccination-transcontinental-energir-riviere-des-prairies', 'special installment');
INSERT INTO `Vaccination Facility` (`Name`, `Address`, `Phone_number`, `Web_address`, `Type of Facility`) VALUES ('Pôle de Vaccination Saint-Michel', '8333, 2e Avenue Montréal, H1Z 4N9', '5146444545', 'https://ciusss-estmtl.gouv.qc.ca/covid-19/Vaccination-contre-la-covid-19/pole-de-Vaccination-de-saint-michel', 'special installment');
INSERT INTO `Vaccination Facility` (`Name`, `Address`, `Phone_number`, `Web_address`, `Type of Facility`) VALUES ('Aréna Bob Birnie', '58, av. Maywood, Pointe-Claire, H9R 0A7', '5146444545', 'https://ciusss-ouestmtl.gouv.qc.ca/en/users-info/covid-19-care-and-services/covid-19-Vaccination/', 'special installment');
INSERT INTO `Vaccination Facility` (`Name`, `Address`, `Phone_number`, `Web_address`, `Type of Facility`) VALUES ('Centre civique de Dollard-des-Ormeaux', '12001, boul. De Salaberry, Dollard-des-Ormeaux, H9B 2A7', '5146444545', 'https://ciusss-ouestmtl.gouv.qc.ca/en/users-info/covid-19-care-and-services/covid-19-Vaccination/', 'special installment');
INSERT INTO `Vaccination Facility` (`Name`, `Address`, `Phone_number`, `Web_address`, `Type of Facility`) VALUES ('Centre sportif Dollard-St-Laurent', '707, 75e avenue, LaSalle, H8R 3Y2', '5146444545', 'https://ciusss-ouestmtl.gouv.qc.ca/en/users-info/covid-19-care-and-services/covid-19-Vaccination/', 'special installment');
INSERT INTO `Vaccination Facility` (`Name`, `Address`, `Phone_number`, `Web_address`, `Type of Facility`) VALUES ('Centre communautaire Gerry-Robertson', '9665, boul. Gouin Ouest, Montréal, H8Y 1R4', '5146444545', 'https://ciusss-ouestmtl.gouv.qc.ca/en/users-info/covid-19-care-and-services/covid-19-Vaccination/', 'special installment');
INSERT INTO `Vaccination Facility` (`Name`, `Address`, `Phone_number`, `Web_address`, `Type of Facility`) VALUES ('West Island Drive-Thru Vaccination', '12000 English Avenue, Dorval, H9P 1B4', '5146444545', 'https://ciusss-ouestmtl.gouv.qc.ca/en/users-info/covid-19-care-and-services/covid-19-Vaccination/', 'special installment');
INSERT INTO `Vaccination Facility` (`Name`, `Address`, `Phone_number`, `Web_address`, `Type of Facility`) VALUES ('Palais des congrès', '1001, place Jean-Paul Riopelle, Montréal, H2Z 1H5', '5146444545', 'https://cisss-outaouais.gouv.qc.ca/language/en/covid19-en/covid-19-Vaccination/', 'special installment');
INSERT INTO `Vaccination Facility` (`Name`, `Address`, `Phone_number`, `Web_address`, `Type of Facility`) VALUES ('CLSC de la Visitation', '1705, De la Visitation street, Montréal, H2L 3C3', '5146444545', 'https://ciusss-centresudmtl.gouv.qc.ca/etablissement/clsc-de-la-visitation-et-gmfu-des-faubourgs', 'clinic');
INSERT INTO `Vaccination Facility` (`Name`, `Address`, `Phone_number`, `Web_address`, `Type of Facility`) VALUES ('Site de Vaccination de Pointe-Saint-Charles', '2115, rue du Centre, Montréal H3K 1J5', '5146444545', 'https://ccpsc.qc.ca/en/', 'special installment');
INSERT INTO `Vaccination Facility` (`Name`, `Address`, `Phone_number`, `Web_address`, `Type of Facility`) VALUES ('Aréna Bill-Durnan', '4988, Vézina street, Montréal, H3W 1C1', '5146444545', 'https://www.ciussswestcentral.ca/programs-and-services/lifestyle-habits-and-prevention/Vaccination/covid-19-Vaccination/', 'special installment');
INSERT INTO `Vaccination Facility` (`Name`, `Address`, `Phone_number`, `Web_address`, `Type of Facility`) VALUES ('Université de Montréal, MIL Campus', '1375, Thérèse-Lavoie-Roux ave, Montreal, H2V 0B3', '5146444545', 'https://infocovid19.umontreal.ca/toutes-les-communications/nouvelle/news/detail/News/site-de-Vaccination-au-campus-mil/', 'special installment');
INSERT INTO `Vaccination Facility` (`Name`, `Address`, `Phone_number`, `Web_address`, `Type of Facility`) VALUES ('Clinique de Vaccination d\'Ahuntsic', '800 Boul Henri-Bourassa O, Montréal, QC H3L 1P5', '5146444545', 'https://www.ciusssnordmtl.ca/soins-et-services/coronavirus-covid-19/Vaccination-contre-la-covid-19/', 'clinic');
INSERT INTO `Vaccination Facility` (`Name`, `Address`, `Phone_number`, `Web_address`, `Type of Facility`) VALUES ('Clinique de Vaccination de Montréal-Nord', '11201, blvd. Lacordaire, Montréal, H1G 4J7', '5146444545', 'https://www.ciusssnordmtl.ca/soins-et-services/coronavirus-covid-19/Vaccination-contre-la-covid-19/', 'clinic');
INSERT INTO `Vaccination Facility` (`Name`, `Address`, `Phone_number`, `Web_address`, `Type of Facility`) VALUES ('Clinique de Vaccination Christophe-Colomb', '7355, av. Christophe-Colomb, Montréal, H2R 2S5', '5146444545', 'https://www.ciusssnordmtl.ca/soins-et-services/coronavirus-covid-19/Vaccination-contre-la-covid-19/', 'clinic');
INSERT INTO `Vaccination Facility` (`Name`, `Address`, `Phone_number`, `Web_address`, `Type of Facility`) VALUES ('Clinique de Vaccination de Saint-Laurent', '300 Avenue Sainte-Croix, Saint-Laurent, QC H4N 3K4', '5146444545', 'https://www.ciusssnordmtl.ca/soins-et-services/coronavirus-covid-19/Vaccination-contre-la-covid-19/', 'clinic');
INSERT INTO `Vaccination Facility` (`Name`, `Address`, `Phone_number`, `Web_address`, `Type of Facility`) VALUES ('Centre de Vaccination CAE', '8585, chemin Côte-de-Liesse, Saint-Laurent, H4T 1G6', '5146444545', 'https://www.cae.com/fr/cae-Vaccination-centre-fr/', 'special installment');",
        "INSERT INTO `Vaccine Information` (`Vaccine_name`, `Vaccine_approval_date`, `Vaccine_short_description`, `Vaccine_status`, `Minimum_allowed_group_age`) VALUES ('Moderna COVID-19', '2020-11-23', 'The Moderna COVID-19 vaccine (mRNA-1273) is used to prevent SARS-CoV-2 (approved for >18 y/o)', 'SAFE', '7');
INSERT INTO `Vaccine Information` (`Vaccine_name`, `Vaccine_approval_date`, `Vaccine_short_description`, `Vaccine_status`, `Minimum_allowed_group_age`) VALUES ('Pfizer-BioNTech COVID-19', '2020-11-09', 'The Pfizer-BioNTech COVID-19 mRNA vaccine (Tozinameran or BNT162b2) is used to protect against COVID-19 (approved for >12 y/o)', 'SAFE', '8');
INSERT INTO `Vaccine Information` (`Vaccine_name`, `Vaccine_approval_date`, `Vaccine_short_description`, `Vaccine_status`, `Minimum_allowed_group_age`) VALUES ('AstraZeneca/COVISHIELD COVID-19', '2021-02-26', 'The AstraZeneca COVID-19 vaccine (ChAdOx1-S) is used to prevent COVID-19 (approved for >18 y/o)', 'SAFE', '7');
INSERT INTO `Vaccine Information` (`Vaccine_name`, `Vaccine_approval_date`, `Vaccine_short_description`, `Vaccine_status`, `Minimum_allowed_group_age`) VALUES ('Janssen (Johnson & Johnson) COVID-19', '2021-03-05', 'The Janssen COVID-19 vaccine (Ad26.COV2.S) is used to prevent COVID-19 (approved for >18y/o)', 'SAFE', '7');
INSERT INTO `Vaccine Information` (`Vaccine_name`, `Vaccine_short_description`, `Vaccine_status`) VALUES ('Novavax COVID-19', 'The Novavax COVID-19 vaccine (NVX-CoV2373) is under review', 'UNDER REVIEW');
INSERT INTO `Vaccine Information` (`Vaccine_name`, `Vaccine_short_description`, `Vaccine_status`, `Vaccine_date_of_suspension`) VALUES ('Pendopharm COVID-19', 'The Pendopharm Division of Pharmascience Inc vaccine (ColchicineFootnote) is cancelled by sponsor', 'SUSPENDED', '2021-06-07');",
        "INSERT INTO Vaccination (`Vaccine_name`, `Dose_id_number`, `Vaccination_date`, `Vaccination_Facility_name`, `Medical_card_number`, `Shot_number`) VALUES ('AstraZeneca/COVISHIELD COVID-19', '1', '2021-2-12', 'Aréna Bill-Durnan', 'aKXWd7nRui', '1');
INSERT INTO Vaccination (`Vaccine_name`, `Dose_id_number`, `Vaccination_date`, `Vaccination_Facility_name`, `Medical_card_number`, `Shot_number`) VALUES ('Janssen (Johnson & Johnson) COVID-19',	'2', '2021-1-17', 'Aréna Bob Birnie', 'lG6Kxk_erZ',	'1');
INSERT INTO Vaccination (`Vaccine_name`, `Dose_id_number`, `Vaccination_date`, `Vaccination_Facility_name`, `Medical_card_number`, `Shot_number`) VALUES ('Moderna COVID-19',	'3', '2021-04-18', 'Aréna Martin-Brodeur', 'nYzRoXnPj9', '1');
INSERT INTO Vaccination (`Vaccine_name`, `Dose_id_number`, `Vaccination_date`, `Vaccination_Facility_name`, `Medical_card_number`, `Shot_number`) VALUES ('Novavax COVID-19',	'4', '2021-1-23', 'Centre communautaire Gerry-Robertson', 'lM6_qT-lMW',	'1');
INSERT INTO Vaccination (`Vaccine_name`, `Dose_id_number`, `Vaccination_date`, `Vaccination_Facility_name`, `Medical_card_number`, `Shot_number`) VALUES ('Pendopharm COVID-19', '5',	'2021-03-15', 'Centre de Vaccination CAE', 'wq_jxOx0DO', '1');
INSERT INTO Vaccination (`Vaccine_name`, `Dose_id_number`, `Vaccination_date`, `Vaccination_Facility_name`, `Medical_card_number`, `Shot_number`) VALUES ('Pfizer-BioNTech COVID-19',	'6', '2021-4-16', 'Centre sportif Dollard-St-Laurent', 'RXxr2K8UMo', '1');
INSERT INTO Vaccination (`Vaccine_name`, `Dose_id_number`, `Vaccination_date`, `Vaccination_Facility_name`, `Medical_card_number`, `Shot_number`) VALUES ('AstraZeneca/COVISHIELD COVID-19', '7',	'2021-5-30', 'Clinique de Vaccination Christophe-Colomb', 'aKXWd7nRui',	'2');
INSERT INTO Vaccination (`Vaccine_name`, `Dose_id_number`, `Vaccination_date`, `Vaccination_Facility_name`, `Medical_card_number`, `Shot_number`) VALUES ('Janssen (Johnson & Johnson) COVID-19',	'8', '2021-1-8', 'Clinique de Vaccination d\'Ahuntsic',	'lG6Kxk_erZ', '2');
INSERT INTO Vaccination (`Vaccine_name`, `Dose_id_number`, `Vaccination_date`, `Vaccination_Facility_name`, `Medical_card_number`, `Shot_number`) VALUES ('Moderna COVID-19', '9', '2021-6-10', 'Clinique de Vaccination de Montréal-Nord', 'nYzRoXnPj9', '2');
INSERT INTO Vaccination (`Vaccine_name`, `Dose_id_number`, `Vaccination_date`, `Vaccination_Facility_name`, `Medical_card_number`, `Shot_number`) VALUES ('Novavax COVID-19', '10', '2021-4-15', 'Clinique de Vaccination de Saint-Laurent', 'lM6_qT-lMW', '2');
INSERT INTO Vaccination (`Vaccine_name`, `Dose_id_number`, `Vaccination_date`, `Vaccination_Facility_name`, `Medical_card_number`, `Shot_number`) VALUES ('Pendopharm COVID-19', '11', '2021-2-6', 'CLSC de la Visitation', 'wq_jxOx0DO', '2');
INSERT INTO Vaccination (`Vaccine_name`, `Dose_id_number`, `Vaccination_date`, `Vaccination_Facility_name`, `Medical_card_number`, `Shot_number`) VALUES ('Pfizer-BioNTech COVID-19', '12', '2021-3-6', 'Palais des congrès', 'RXxr2K8UMo', '2');
INSERT INTO Vaccination (`Vaccine_name`, `Dose_id_number`, `Vaccination_date`, `Vaccination_Facility_name`, `Medical_card_number`, `Shot_number`) VALUES ('AstraZeneca/COVISHIELD COVID-19', '13', '2021-2-23', 'Pôle de Vaccination Rivière-des-Prairies Transcontinental – Énergir', 'aKXWd7nRui', '3');
INSERT INTO Vaccination (`Vaccine_name`, `Dose_id_number`, `Vaccination_date`, `Vaccination_Facility_name`, `Medical_card_number`, `Shot_number`) VALUES ('Janssen (Johnson & Johnson) COVID-19', '14', '2021-2-7', 'Pôle de Vaccination Saint-Michel', 'lG6Kxk_erZ', '3');
INSERT INTO Vaccination (`Vaccine_name`, `Dose_id_number`, `Vaccination_date`, `Vaccination_Facility_name`, `Medical_card_number`, `Shot_number`) VALUES ('Moderna COVID-19', '15', '2021-2-24', 'Site de Vaccination de Pointe-Saint-Charles', 'nYzRoXnPj9', '3');
INSERT INTO Vaccination (`Vaccine_name`, `Dose_id_number`, `Vaccination_date`, `Vaccination_Facility_name`, `Medical_card_number`, `Shot_number`) VALUES ('Novavax COVID-19', '16' ,'2021-6-14', 'Stade Olympique/SAQ', 'lM6_qT-lMW', '3');
INSERT INTO Vaccination (`Vaccine_name`, `Dose_id_number`, `Vaccination_date`, `Vaccination_Facility_name`, `Medical_card_number`, `Shot_number`) VALUES ('Pendopharm COVID-19', '17', '2021-5-5', 'Université de Montréal, MIL Campus','wq_jxOx0DO', '3');
INSERT INTO Vaccination (`Vaccine_name`, `Dose_id_number`, `Vaccination_date`, `Vaccination_Facility_name`, `Medical_card_number`, `Shot_number`) VALUES ('Pfizer-BioNTech COVID-19', '18', '2021-2-6', 'West Island Drive-Thru Vaccination', 'RXxr2K8UMo', '3');",
        "INSERT INTO Person (`First_name`, `Last_name`, `Date_of_birth`, `Medical_card_number`, `Telephone_number`, `Address`, `City`, `Province`, `Postal_code`, `Citizenship`, `Email_address`, `Infected_in_past`) VALUES ('Robert', 'J McAnally', '2009-7-9', 'aKXWd7nRui', '9096273613', '2447 Paradise Lane', 'Montreal', 'Quebec', 'H1M 3D2', 'Canadian', 'rickie1971@yahoo.com', 1);
INSERT INTO Person (`First_name`, `Last_name`, `Date_of_birth`, `Medical_card_number`, `Telephone_number`, `Address`, `City`, `Province`, `Postal_code`, `Citizenship`, `Email_address`, `Infected_in_past`) VALUES ('Gordon', 'Divine', '1962-01-04', 'lG6Kxk_erZ', '6508819649', '1795 Duck Creek Road', 'Laval', 'Quebec', 'R5K M0O', 'American', 'jody1973@gmail.com', 1);
INSERT INTO Person (`First_name`, `Last_name`, `Date_of_birth`, `Medical_card_number`, `Telephone_number`, `Address`, `City`, `Province`, `Postal_code`, `Citizenship`, `Email_address`, `Infected_in_past`) VALUES ('Ray', 'Collier', '2000-10-08', 'nYzRoXnPj9', '8172189038', '3910 Loving Acres Road', 'Longueil', 'Quebec', 'QW2 RT4', 'Canadian', 'nolan_conro3@hotmail.com', 0);
INSERT INTO Person (`First_name`, `Last_name`, `Date_of_birth`, `Medical_card_number`, `Telephone_number`, `Address`, `City`, `Province`, `Postal_code`, `Citizenship`, `Email_address`, `Infected_in_past`) VALUES ('Ronald', 'Logan', '2011-09-22', 'lM6_qT-lMW', '6176992392', '4082 Valley View Drive', 'Montreal', 'Quebec', 'S2B E11', 'Colombian', 'ryleigh1977@gmail.com', 0);
INSERT INTO Person (`First_name`, `Last_name`, `Date_of_birth`, `Medical_card_number`, `Telephone_number`, `Address`, `City`, `Province`, `Postal_code`, `Citizenship`, `Email_address`, `Infected_in_past`) VALUES ('Floyd', 'Woodburn', '1948-05-30', 'wq_jxOx0DO', '6032373824', '3925 Shearwood Forest Drive', 'Laval', 'Quebec', 'BE1 45U', 'English', 'ashley1999@gmail.com', 1);
INSERT INTO Person (`First_name`, `Last_name`, `Date_of_birth`, `Medical_card_number`, `Telephone_number`, `Address`, `City`, `Province`, `Postal_code`, `Citizenship`, `Email_address`, `Infected_in_past`) VALUES ('Ryan', 'Thompson', '1993-09-22', 'RXxr2K8UMo', '2692168552', '3281 Garrett Street', 'Longueil', 'Quebec', 'T7U 0Q1', 'Algerian', 'vickie.litt@hotmail.com', 0);
INSERT INTO Person (`First_name`, `Last_name`, `Date_of_birth`, `Medical_card_number`, `Telephone_number`, `Address`, `City`, `Province`, `Postal_code`, `Citizenship`, `Email_address`, `Infected_in_past`) VALUES ('Marco', 'White', '2005-11-11', 'ALNwQGV6dp', '6266077964', '4946 Nickel Road', 'Montreal', 'Quebec', 'U7O 8PX', 'South African', 'jeffery2005@yahoo.com', 0);
INSERT INTO Person (`First_name`, `Last_name`, `Date_of_birth`, `Medical_card_number`, `Telephone_number`, `Address`, `City`, `Province`, `Postal_code`, `Citizenship`, `Email_address`, `Infected_in_past`) VALUES ('Lewis', 'Johnson', '1960-12-31', 'JwJKKCynB1', '8317592935', '1138 Cemetery Street', 'Laval', 'Quebec', 'QVH 8OL', 'Canadian', 'nicolette.torp@yahoo.com', 1);
INSERT INTO Person (`First_name`, `Last_name`, `Date_of_birth`, `Medical_card_number`, `Telephone_number`, `Address`, `City`, `Province`, `Postal_code`, `Citizenship`, `Email_address`, `Infected_in_past`) VALUES ('Mark', 'Chatman', '1977-01-31', 'w42ZkkHMA9', '8476922614', '2382 Rebecca Street', 'Longueil', 'Quebec', 'Z7M 6H6', 'Canadian', 'laria.gutkows@yahoo.com', 0);
INSERT INTO Person (`First_name`, `Last_name`, `Date_of_birth`, `Medical_card_number`, `Telephone_number`, `Address`, `City`, `Province`, `Postal_code`, `Citizenship`, `Email_address`, `Infected_in_past`) VALUES ('Beverly', 'Meas', '1992-05-01', 'ukWrq7Z6c5', '7277763071', '825 Kenwood Place', 'Montreal', 'Quebec', 'K9L 6H3', 'Italian', 'thelma.simon@yahoo.com', 1);",
        "INSERT INTO `Infection History` (`Medical_card_number`, `Date_of_infection`) VALUES ('aKXWd7nRui', '2020-01-13');
INSERT INTO `Infection History` (`Medical_card_number`, `Date_of_infection`) VALUES ('lG6Kxk_erZ', '2020-12-08');
INSERT INTO `Infection History` (`Medical_card_number`, `Date_of_infection`) VALUES ('wq_jxOx0DO', '2020-10-05');
INSERT INTO `Infection History` (`Medical_card_number`, `Date_of_infection`) VALUES ('aKXWd7nRui', '2020-06-16');
INSERT INTO `Infection History` (`Medical_card_number`, `Date_of_infection`) VALUES ('lG6Kxk_erZ', '2020-04-01');
INSERT INTO `Infection History` (`Medical_card_number`, `Date_of_infection`) VALUES ('aKXWd7nRui', '2020-05-29');",
        "INSERT INTO `Age Group` (`Date_of_birth`) VALUES ('2009-7-9');
INSERT INTO `Age Group` (`Date_of_birth`) VALUES ('1962-1-4');
INSERT INTO `Age Group` (`Date_of_birth`) VALUES ('2000-10-8');
INSERT INTO `Age Group` (`Date_of_birth`) VALUES ('2011-9-22');
INSERT INTO `Age Group` (`Date_of_birth`) VALUES ('1962-1-4');
INSERT INTO `Age Group` (`Date_of_birth`) VALUES ('1993-9-22');
INSERT INTO `Age Group` (`Date_of_birth`) VALUES ('2005-11-11');
INSERT INTO `Age Group` (`Date_of_birth`) VALUES ('1960-12-30');
INSERT INTO `Age Group` (`Date_of_birth`) VALUES ('1977-1-31');
INSERT INTO `Age Group` (`Date_of_birth`) VALUES ('1992-5-1');",
        "UPDATE `Age Group` SET Age = DATE_FORMAT(FROM_DAYS(TO_DAYS(NOW())-TO_DAYS(`Date_of_birth`)), '%Y')+0;",

    );

    foreach ($queries as $to_insert) {
        $all_sub_queries = explode(';', $to_insert);
        foreach ($all_sub_queries as $each_query) {
            if ($each_query === "") break;
            $query_result = $conn->query($each_query);
            if ($debug_mode) {
                if ($query_result === TRUE) {
                    echo "New record created successfully";
                } else {
                    echo "Error Occured";
                    //echo "Error: " . "<br>" . $conn->error;
                }
            }
        }
    }
    $conn->close();
}
