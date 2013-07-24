<?php
	/*
		Copyright 2013 Ashvala Vinay and Vladimir Jimenez
		
		Permission is hereby granted, free of charge, to any person obtaining
		a copy of this software and associated documentation files (the
		"Software"), to deal in the Software without restriction, including
		without limitation the rights to use, copy, modify, merge, publish,
		distribute, sublicense, and/or sell copies of the Software, and to
		permit persons to whom the Software is furnished to do so, subject to
		the following conditions:
		
		The above copyright notice and this permission notice shall be
		included in all copies or substantial portions of the Software.
		
		THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
		EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
		MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
		NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
		LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
		OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
		WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
	*/

class database
{
	public $dbc;
	public $hostname;
	public $database;
	public $username;
	public $password;
	
	public $result;
	public $num_rows;
    
    function __construct()
    {
		$this->hostname = "localhost";
		$this->database = "mysql_db";
		$this->username = "your_mysql_username";
		$this->password = "your_mysql_password";
		
    	$this->dbc = new mysqli($this->hostname, $this->username, $this->password, $this->database);
    	
    	if ($this->dbc->connect_errno)
    	{
	    	return "Are you brad? You seem to have messed up the DB connection :(";
	    }
    }
	
	function __destruct()
	{
        $this->closeConnection();
    }
    
    function closeConnection()
    {
        mysqli_close($this->dbc);
    }
    
    function query($query)
    {
	    if ($query == '')
	    	return;
 
        $this->result = mysqli_query($this->dbc, $query) OR $this->throw_error(mysqli_error($this->dbc), __LINE__);
        $this->num_rows = mysqli_num_rows($this->result);
 
        if ($this->num_rows > 1)
        {
            $tmp_array = array();
            
            while ($row = mysqli_fetch_assoc($this->result))
            {
                array_push($tmp_array, $row);
            }
 
            $this->result = $tmp_array;
            return $this->result;
 
        }
        else
        {
            $this->result = mysqli_fetch_assoc($this->result);
            return $this->result;
        }
    }
    
    function throw_error($message, $line = 0)
    {
        $line = (!empty($line)) ? $line = ' on line ' . $line : '' ;
        die ('There was an error on line' . $line .' in class "' . __CLASS__ . '": ' . $message);
    }
}