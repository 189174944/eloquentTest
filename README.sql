CREATE TABLE part_tab (c1 int default NULL, c2 varchar(30) default NULL, c3 date default NULL) engine=myisam
PARTITION BY RANGE (year(c3)) (PARTITION p0 VALUES LESS THAN (1995),
PARTITION p1 VALUES LESS THAN (1996) DATA DIRECTORY = '/data/testpartition'
    INDEX DIRECTORY = '/data/testpartition' ,
PARTITION p2 VALUES LESS THAN (1997) DATA DIRECTORY = '/data/testpartition'
    INDEX DIRECTORY = '/data/testpartition',
PARTITION p3 VALUES LESS THAN MAXVALUE DATA DIRECTORY = '/data/testpartition'
    INDEX DIRECTORY = '/data/testpartition' );