 CREATE TABLE part_tab ( c1 int default NULL, c2 varchar(30) default NULL, c3 date default NULL) engine=myisam   
PARTITION BY RANGE (year(c3)) (PARTITION p0 VALUES LESS THAN (1995),  
PARTITION p1 VALUES LESS THAN (1996) DATA DIRECTORY = 'D:/mysql/data/data'
    INDEX DIRECTORY = 'D:/mysql/data/idx' , PARTITION p2 VALUES LESS THAN (1997) DATA DIRECTORY = 'D:/mysql/data/data'
    INDEX DIRECTORY = 'D:/mysql/data/idx',
PARTITION p11 VALUES LESS THAN MAXVALUE DATA DIRECTORY = 'D:/mysql/data/data'
    INDEX DIRECTORY = 'D:/mysql/data/idx' );