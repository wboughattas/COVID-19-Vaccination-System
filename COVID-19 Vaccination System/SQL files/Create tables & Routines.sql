CREATE TABLE Person
(
    First_name          varchar(100),
    Last_name           varchar(100),
    Date_of_birth       date,
    SSN                 bigint,
    Passport_number     bigint,
    Medical_card_number varchar(100),
    Phone_number        varchar(100),
    Citizenship         varchar(100),
    Email_address       varchar(100),
    Infected_in_past    bit,
    PRIMARY KEY (Medical_card_number),
    UNIQUE (SSN, Passport_number)
);

CREATE TABLE Type_of_Infection
(
    Type_of_infection varchar(100),
    PRIMARY KEY (Type_of_infection)
);

CREATE TABLE Age_Group
(
    GroupAgeID tinyint DEFAULT 0,
    MinAge     tinyint,
    MaxAge     tinyint,
    PRIMARY KEY (GroupAgeID)
);

CREATE TABLE Belongs_To
(
    Medical_card_number varchar(100),
    GroupAgeID          tinyint,
    PRIMARY KEY (Medical_card_number),
    FOREIGN KEY (Medical_card_number) REFERENCES Person (Medical_card_number),
    FOREIGN KEY (GroupAgeID) REFERENCES Age_Group (GroupAgeID)
);

CREATE TABLE Infection_History
(
    Medical_card_number varchar(100),
    Date_of_infection   date,
    Type_of_infection   varchar(100) DEFAULT 'UNKNOWN',
    PRIMARY KEY (Medical_card_number, Date_of_infection),
    FOREIGN KEY (Medical_card_number) REFERENCES Person (Medical_card_number),
    FOREIGN KEY (Type_of_infection) REFERENCES Type_of_Infection(Type_of_infection)
);

CREATE TABLE Location
(
    Address     varchar(100),
    Street      varchar(100),
    City        varchar(100),
    Province    varchar(2),
    Postal_code varchar(7),
    PRIMARY KEY (Address, Postal_code)
);

CREATE TABLE Resides_At
(
    Address             varchar(100),
    Postal_code         varchar(7),
    Medical_card_number varchar(100),
    PRIMARY KEY (Medical_card_number),
    FOREIGN KEY (Medical_card_number) REFERENCES Person (Medical_card_number),
    FOREIGN KEY (Address, Postal_code) REFERENCES Location (Address, Postal_code)
);


CREATE TABLE Vaccination_Facility
(
    Name             varchar(100),
    Phone_number     bigint,
    Web_address      varchar(200),
    Type_of_facility varchar(100),
    PRIMARY KEY (Name)
);


CREATE TABLE Located_At
(
    Address       varchar(100),
    Postal_code   varchar(7),
    Facility_name varchar(100),
    PRIMARY KEY (Facility_name),
    FOREIGN KEY (Facility_name) REFERENCES Vaccination_Facility (Name),
    FOREIGN KEY (Address, Postal_code) REFERENCES Location (Address, Postal_code)
);

CREATE TABLE Vaccine_Information
(
    Vaccine_name               varchar(100),
    Vaccine_approval_date      date,
    Vaccine_short_description  varchar(200),
    Vaccine_status             varchar(100),
    Vaccine_date_of_suspension date,
    Minimum_allowed_group_age  tinyint,
    PRIMARY KEY (Vaccine_name)
);

CREATE TABLE Storage
(
    Capacity      int DEFAULT 0,
    Facility_name varchar(100),
    Vaccine_name  varchar(100),
    CONSTRAINT alwaysPositive CHECK(Capacity>=0),
    PRIMARY KEY (Facility_name, Vaccine_name),
    FOREIGN KEY (Facility_name) REFERENCES Vaccination_Facility (Name),
    FOREIGN KEY (Vaccine_name) REFERENCES Vaccine_Information (Vaccine_name)
);

CREATE TABLE Shipment
(
    Number_of_vaccine_doses int,
    Reception_date          date,
    Vaccine_name            varchar(100),
    From_facility_storage   varchar(100),
    To_facility_storage     varchar(100),
    PRIMARY KEY (Reception_date, Vaccine_name, From_facility_storage, To_facility_storage),
    FOREIGN KEY (Vaccine_name, From_facility_storage) REFERENCES Storage (Vaccine_name, Facility_name),
    FOREIGN KEY (Vaccine_name, To_facility_storage) REFERENCES Storage (Vaccine_name, Facility_name)
);

CREATE TABLE Employee
(
    EID                 varchar(100),
    Medical_card_number varchar(100),
    PRIMARY KEY (EID),
    FOREIGN KEY (Medical_card_number) REFERENCES Person (Medical_card_number)
);

CREATE TABLE Manages
(
    EID           varchar(100),
    Facility_name varchar(100),
    PRIMARY KEY (EID),
    FOREIGN KEY (EID) REFERENCES Employee (EID),
    FOREIGN KEY (Facility_name) REFERENCES Vaccination_Facility (Name)
);

CREATE TABLE Employment
(
    start_date_of_employment date,
    end_date_of_employment   date,
    Facility_name            varchar(100),
    EID                      varchar(100),
    PRIMARY KEY (start_date_of_employment, Facility_name, EID),
    FOREIGN KEY (EID) REFERENCES Employee (EID),
    FOREIGN KEY (Facility_name) REFERENCES Vaccination_Facility (Name)
);

CREATE TABLE Vaccination
(
    Vaccine_name             varchar(100),
    Vaccination_date         date,
    Facility_name            varchar(100),
    Medical_card_number      varchar(100),
    Dose_number              int,
    Vaccine_administrator_ID varchar(100),
    PRIMARY KEY (Medical_card_number, Dose_number),
    FOREIGN KEY (Medical_card_number) REFERENCES Person (Medical_card_number),
    FOREIGN KEY (Facility_name, Vaccine_name) REFERENCES Storage (Facility_name, Vaccine_name),
    FOREIGN KEY (Vaccine_administrator_ID) REFERENCES Employment (EID)
);

CREATE TABLE Eligibility_Requirement
(
    Province   varchar(2),
    GroupAgeID tinyint DEFAULT 0,
    PRIMARY KEY (Province),
    FOREIGN KEY (GroupAgeID) REFERENCES Age_Group (GroupAgeID)
);
