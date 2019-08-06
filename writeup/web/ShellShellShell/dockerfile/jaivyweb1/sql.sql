CREATE database jaivyctf;
use jaivyctf;
create table ctf_users (id int PRIMARY KEY AUTO_INCREMENT,username char(100),password char(100),ip char(50),is_admin char(10),allow_diff_ip char(10));
create table ctf_user_signature (id int PRIMARY KEY AUTO_INCREMENT,username char(100),userid int,signature text,mood text);
insert into ctf_users( `username`,`password`,`ip`,`is_admin`,`allow_diff_ip` ) values ( 'admin','c991707fdf339958eded91331fb11ba0','127.0.0.1','1', '0');
CREATE USER 'jaivy'@'localhost' IDENTIFIED BY 'jaivypassword666';
grant all privileges on `jaivyctf`.* to 'jaivy'@'%' identified by 'jaivypassword666';

