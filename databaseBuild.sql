create table login
(
  id int auto_increment primary key,
	userId varchar(100) not null,
	jwt varchar(32) not null,
	loggedTime date not null
)
;

create table permission
(
  roleId varchar(100) not null,
	code varchar(100) not null,
	name varchar(100) not null
)
;

create table role
(
  id int auto_increment primary key,
	name varchar(100) not null
)
;

create table user
(
  id int auto_increment primary key,
	Username varchar(100) not null,
	Password varchar(100) not null,
	roleId int not null,
	fail int not null
)
;

INSERT INTO `straxus`."user" (`Username`, `Password`, `roleId`) VALUES ('Admin', 'asd', 1)
INSERT INTO `straxus`.`user` (`Username`, `Password`, `roleId`) VALUES ('User1', 'asd', 4)
INSERT INTO `straxus`.`user` (`Username`, `Password`, `roleId`) VALUES ('User2', 'asd', 3)
INSERT INTO `straxus`.`user` (`Username`, `Password`, `roleId`) VALUES ('User3', 'asd', 2)

INSERT INTO `straxus`.`permission` (`roleId`, `code`, `name`) VALUES ('1', 'admin', 'Adminisztrator')
INSERT INTO `straxus`.`permission` (`roleId`, `code`, `name`) VALUES ('1', 'user', 'Felhasznalo')
INSERT INTO `straxus`.`permission` (`roleId`, `code`, `name`) VALUES ('1', 'editor', 'Szerkeszto')
INSERT INTO `straxus`.`permission` (`roleId`, `code`, `name`) VALUES ('2', 'user', 'Felhasznalo')
INSERT INTO `straxus`.`permission` (`roleId`, `code`, `name`) VALUES ('2', 'editor', 'Szerkeszto')
INSERT INTO `straxus`.`permission` (`roleId`, `code`, `name`) VALUES ('3', 'user', 'Felhasznalo')
INSERT INTO `straxus`.`permission` (`roleId`, `code`, `name`) VALUES ('4', 'editor', 'Szerkeszto')

INSERT INTO `straxus`.`role` (`name`) VALUES ('Adminisztrator')
INSERT INTO `straxus`.`role` (`name`) VALUES ('Felhasznalo')
INSERT INTO `straxus`.`role` (`name`) VALUES ('Szerkeszto')
INSERT INTO `straxus`.`role` (`name`) VALUES ('Felhasznalo + Szerkeszto')