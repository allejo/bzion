import sys
import getpass
from subprocess import *
import os






if __name__ == "__main__":
    print """ 
              BZiON installer v0.1
              http://github.com/allejo/bzion

          """
    directory_name = raw_input("What's the directory name you want to use? ")
    call(["git", "clone", "https://github.com/allejo/bzion.git",directory_name])
    os.chdir(directory_name)
    print check_output("curl -sS https://getcomposer.org/installer | php", shell=True)
    call(["php", "composer.phar", "install", "--no-dev"])
    sanity_check = raw_input("Have you already created an empty database for BZiON? (y/n) ")
    if sanity_check == "y":
        database_name = raw_input("What's the database name? ")
        username = raw_input("What's the MySQL username? ")
        password = getpass.getpass(prompt="What's your MySQL password? ")
        command = "mysql --user="+ username + " --password="+password+ " " + database_name + " < DATABASE.sql"
        call(command, shell=True)
        
    if sanity_check == "n":
        database_name = raw_input("What do you want to call the database? ")
        username = raw_input("What's your MySQL username? ")
        password = getpass.getpass(prompt="What's your MySQL password? ")
        command = "mysqladmin --user=" + username + " -password="+password+" create " + database_name
        call(command, shell=True)
        command2 = "mysql --user="+ username + " --password="+password+ " " + database_name + " < DATABASE.sql"
        call(command2, shell=True)

    print "Configuring stuff..."
    
    
