drop table if exists my_test_table;

CREATE TABLE if not exists `my_test_table` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL DEFAULT '',
  `last_name` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

insert into my_test_table (first_name, last_name) values ('Test', 'Person');
insert into my_test_table (first_name, last_name) values ('Lee', 'Blue');
insert into my_test_table (first_name, last_name) values ('Emily', 'Blue');
insert into my_test_table (first_name, last_name) values ('Joey', 'Beninghove');
insert into my_test_table (first_name, last_name) values ('Bobby', 'Smith');