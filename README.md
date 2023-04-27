# MySQLDBCompare
MySql Table and Procedure Compare tool

Language : PHP


**MySQL DB Table and Procedure Compare**

1) DB1 - Real DB

2) DB2 - Development DB

3) DB1 is Table/Procedure exist & DB2 is Table/Procedure not exist >> 'Be careful' text print

4) DB1 is Table/Procedure not exist & DB2 is Table/Procedure not exist  >> 'Create Table & Procedure SQL' print

5) 'Drop Table & Procedure' not print (not generated)

<br>

**DB port usage**
 
ex) 192.168.1.1:3306

<br>
Verified version (프로그램 동작 테스트)

  1)  PHP 5.6.33 + MySQL 5.1.39 (MyISAM) => OK
  2)  PHP 7.4.33 + MySQL 8.0.22 (InnoDB) => OK 


<br><br><br>

<p>
<img src="https://user-images.githubusercontent.com/18298589/234766851-8c54762d-11e8-443e-8a39-c5d74c599e8e.png"><br>

<img src="https://user-images.githubusercontent.com/18298589/234766896-75da2b6d-f7e7-4e5d-bb99-27dda02e7c9c.png">
</p>



**text diff php source**

- Created by Stephen Morley (class.Diff.php)

http://code.stephenmorley.org/php/diff-implementation/
