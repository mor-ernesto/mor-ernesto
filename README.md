Steps to obfuscate data.
1.	Clone the repository from git into the server.
2.	Edit the file.xsl with the following format (view file.example.xls):
  Column 1: name of the table
  Column 2: name of the columns to be obfuscated
  Column 3. The type (varchar, numeric, date, email) of the column
  Column 4. id name of the table.
3.	Edit the config.php file with the database credentials and a secret token.
4.	Navigate to the folder “obfuscation” and run the following  command 
  php obfuscation.php
5.	If the result is OK, it means that the obfuscation was done successfully.
