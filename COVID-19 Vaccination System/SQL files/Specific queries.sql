-- query 12
SELECT First_name,
       Last_name,
       Person.Date_of_birth,
       Email_address,
       Phone_number,
       City,
       v.Vaccination_date,
       v.Vaccine_Name,
       Infected_in_past
FROM Person
         join Vaccination v on Person.Medical_card_number = v.Medical_card_number
         join Belongs_To b on Person.Medical_card_number = b.Medical_card_number
         join Resides_At ra on b.Medical_card_number = ra.Medical_card_number
         join Location l on ra.Address = l.Address and ra.Postal_code = l.Postal_code
WHERE GroupAgeID >= 1
  and GroupAgeID <= 3
group by v.Medical_card_number
having count(v.Medical_card_number) = 1;


-- query 13
SELECT First_name, Last_name, Date_of_birth, Email_address, Phone_number, City, Infected_in_past
FROM Person
         join Vaccination v on Person.Medical_card_number = v.Medical_card_number
         join Resides_At ra on Person.Medical_card_number = ra.Medical_card_number
         join Location l on ra.Address = l.Address and ra.Postal_code = l.Postal_code
group by v.Medical_card_number
having COUNT(DISTINCT v.Vaccine_Name) > 1;

-- query 14: Get the details of all the people who got vaccinated and have been infected with at least two different types of Vaccination
SELECT First_name,
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
having count(distinct Type_of_infection) > 1;

-- query 15: display for each province, the number of vaccines available for each vaccine type inside that province
SELECT Province, Vaccine_Name, sum(Capacity) as Capacity
FROM Location
         join Located_At la on Location.Address = la.Address and Location.Postal_code = la.Postal_code
         join Vaccination_Facility vf on la.Facility_name = vf.Name
         join Storage s on vf.Name = s.Facility_name
group by Province, Vaccine_Name
order by Province asc, Capacity desc;

-- query 16
SELECT Province, Vaccine_Name, count(distinct p.Medical_card_number) as Total_People
from Location
         join Resides_At ra on Location.Address = ra.Address and Location.Postal_code = ra.Postal_code
         join Person p on ra.Medical_card_number = p.Medical_card_number
         join Vaccination v on p.Medical_card_number = v.Medical_card_number
where Vaccination_date > '2021-01-01'
  AND Vaccination_date < '2021-07-22'
group by Province, Vaccine_Name;

-- query 17
SELECT City, count(*) as Number_of_vaccines
FROM Location
         join Located_At la on Location.Address = la.Address and Location.Postal_code = la.Postal_code
         join Vaccination_Facility vf on la.Facility_name = vf.Name
         join Vaccination v on la.Facility_name = v.Facility_name
where Vaccination_date > '2021-01-01'
  and Vaccination_date < '2021-07-22'
group by City;

-- query 18
SELECT Name, Phone_number, Web_address, Type_of_facility, emp_number,number_of_shipments_received, COALESCE(doses_received,0) as doses_received,
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

where To_name=Name;

-- query 19
SELECT e.EID,
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
where e2.Facility_name = 'Stade Olympique/SAQ';

-- query 20
SELECT e.EID, p.First_name, p.Last_name, p.Date_of_birth, p.Phone_number, City, e2.Facility_name
FROM Person p
         join Employee e on p.Medical_card_number = e.Medical_card_number
         join Resides_At ra on p.Medical_card_number = ra.Medical_card_number
         join Location l on ra.Address = l.Address and ra.Postal_code = l.Postal_code
         join Employment e2 on e.EID = e2.EID
         left join Vaccination v on p.Medical_card_number = v.Medical_card_number
group by e.EID
having count(v.Medical_card_number) <= 1;
