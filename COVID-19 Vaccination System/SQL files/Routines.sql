DELIMITER //
CREATE PROCEDURE updateCapacity(From_facility_storage varchar(100), To_facility_storage varchar(100), Name varchar(100),
                                Number_of_vaccine_doses int)
BEGIN
    UPDATE Storage
    SET Capacity = Storage.Capacity - Number_of_vaccine_doses
    WHERE Facility_name = From_facility_storage
      AND Storage.Vaccine_name = Name;
    UPDATE Storage
    SET Capacity = Storage.Capacity + Number_of_vaccine_doses
    WHERE Facility_name = To_facility_storage
      AND Storage.Vaccine_name = Name;
end //


DELIMITER //
CREATE PROCEDURE updateCapacityAfterVaccination(Name varchar(100), From_facility_storage varchar(100))
BEGIN
    UPDATE Storage
    SET Capacity = Storage.Capacity - 1
    WHERE Facility_name = From_facility_storage
      AND Storage.Vaccine_name = Name;
end //