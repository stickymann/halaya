<?php
/**
 *  Scheduler class for Handshake to DacEasy Interface automation. 
 *
 * $Id: scheduler.php 2014-03-04 09:15:46 dnesbit $
 *
 * @package		Handshake to DacEasy Interface
 * @module	    hndshkif
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license      
 */

// THIS NEEDS TO BE RUN ON THE PHP CGI, PREFERABLY FROM THE CMDLINE, NOT THE APACHE MODULE
// 4 SPACE TABS. "\t" -> "    "

/************************************************************************************
 * 
 * Scheduler 0.01
 * http://www.phpsex.com/classes/class.Scheduler.php.txt
 * 
 * 
 * Ryan Flynn (ryan@ryanflynn.com | www.parseerror.com | www.phpsex.com)
 * irc.dal.net->#php->pizza_milkshake
 * 10 JUL 2002
 * 
 *
 * I HAVE COMPLETELY REWRITTEN THIS CLASS. THIS VERSION DOES NOT WORK THE
 * SAME AS 0.5.1 FROM JUN 2001. THIS IS MUCH BETTER.
 * 
 * THIS IS A 0.01 RELEASE. ASSUME NOTHING. SUSPECT EVERYTHING. IF YOU'RE
 * HAVING PROBLEMS, SEE IF YOU CAN GET THIS SIMPLE EXAMPLE TO WORK. IF NOT,
 * TRY TO FIGURE OUT WHY, AND EMAIL ME. THE MORE YOU TELL ME THE MORE I CAN
 * HELP YOU.
 * 
 * Update History:
 * 10 JUL 2002 - Version 0.01 done. Took me about 4 hours.
 * So far it parses some sexy date rules, runs stuff and 
 * optionally writes to an uber-cheesey logfile
 *
 * 
 * WHY: This class is for those who are not lucky enough to have access to
 * cron[tab] on *nix systems
 * 
 * 
 * WARNING: this is for running commands on your *nix server.
 * Running just any commands can be very, very dangerous and
 * can do alot of damage. Be careful!
 * 
 * DATE SYNTAX EXAMPLES:
 * 
 *	Remember:
 *		 - Whitespace (space, tab, newline) - delimited fields
 *       - Single values, sets, ranges, wildcards
 * 
 * SECOND	MINUTE				HOUR		DAY		MONTH
 * *		*					*			*		*		(every second)
 * 0,30 	*					*			*		*		(every 30 seconds)
 * 0		0,10,20,30,40,50	*			*		*		(every 10 minutes)
 * 0		0					*			*		*		(beginning of every hour)
 * 0		0					0,6,12,18	*	 	*		(at midnight, 6am, noon, 6pm)
 * 0		0					0			1-7&Fri	*		(midnight, first Fri of the month)
 * 0		0					0			1-7!Fri	*		(midnight, first Mon-Thu,Sat-Sun of the month)
 * 
 * 
 * Example usage:
 * 
 * require "class.Scheduler.php";
 * $bob = new Scheduler();
 * $bob->setLogFile("bob.log", 0755) or die("Don't have access to bob.log");
 * // run this command every 5 minutes
 * $bob->addTask("perl somescript.pl", "0 0,5,10,15,20,25,30,35,40,45,50,55 * * *");
 * // run this command midnight of the first Friday of odd numbered months
 * $bob->addTask("php -q somescript.php", "0 0 0 1-7&Fri 1,3,5,7,9,11");
 * // also run this command midnight of the second Thursday and Saturday of the even numbered months
 * $bob->addTask("php -q somescript.php", "0 0 0 8-15&Thu,8-15&Sat 2,4,6,8,10,12");
 * $bob->run();
 * 
 ***************************************************************************************************/

set_time_limit(0);

class Scheduler {

	var $tasks = array();

	var $uid_counter = 1; // every time a task is added it will get a fresh uid even if immediately removed
	var $logfile = false;
	var $chmod = false;

	function Scheduler(){ }

	function setLogFile($file, $chmod = 0666){
	// returns false if can't touch or chmod
		$this->logfile = $file;
		$this->chmod = $chmod;
		return (touch($file) && chmod($file, $chmod));
	}

	function addTask($cmd, $rules){

		$ds = new SchedulerDate($rules);

		//print_r($ds);

		$this->uid_counter++;

		$this->tasks[] =
			array(
				"uid" => $this->uid_counter,
				"rules" => $ds,
				"cmd" => $cmd
			);

		return $this->uid_counter;
	}

	function removeTask($uid){

		$found = 0;
		for ($i = 0; $i < sizeof($this->tasks); $i++)
			if ($this->tasks["uid"] == $uid){
				$found = $i;
				array_splice($this->tasks, $found); // nuke entry
				break;
			}
		return $found;
	}

	function run(){
		
		if (!sizeof($this->tasks))
			die("Give me some tasks with the ->addTask() method before you ask me to run anything.");

		while (1){
			
			$t = time();

			// check each task's candidacy
			foreach ($this->tasks as $task)
				if ($task['rules']->nowMatches())
					$this->runcmd($task);

			// wait til the next second
			while (time() == $t)
				usleep(100000);
		}
	}


	/***** internal functions ******/

	function runcmd(&$task){

		exec($task["cmd"],$output);
		if ($this->logfile)
		{
			$this->writeLog($task["uid"], $task["cmd"]);
			foreach($output as $index => $value)
			{
				$this->writeLog($task["uid"], $value);
			}
		}
	}

	function writeLog($uid, $msg){
		if (!($f = fopen($this->logfile, 'a'))){
			echo "ERROR: Cannot write to logfile '".$this->logfile."'. Make sure user '".$_ENV["USER"]."' has write permissions on the file.";
			return;
		}
		$stamp = date('Y-m-d H:i:s');
		fwrite($f, "[$stamp] ran #$uid: $msg\n");
		fclose($f);
	}

}

class SchedulerDate {
	var $legalDays = array('MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN');
	var $sec;
	var $min;
	var $hour;
	var $day;
	var $month;

	function SchedulerDate($raw){
		$raw = strtoupper($raw); // this'll work for now, Mon -> MON, tUe -> TUE
		$this->parse($raw);
		//print_r($this->day);
	}

	function nowMatches(){
		if (
			$this->monthMatches() &&
			$this->monthMatches() &&
			$this->dayMatches() &&
			$this->hourMatches() &&
			$this->minMatches() &&
			$this->secMatches()
		)
			return true;
		return false;
	}

	function monthMatches(){
		if ($this->month == '*')
			return true;

		$currentmonth = '-'.date('n').'-';

		if(strpos($this->month, $currentmonth) !== false)
			return true;

		return false;
	}

	function dayMatches(){

		if ($this->day == '*')
			return true;

		$currentdaynum = '-'.date('j').'-';
		$currentdaytxt = '-'.strtoupper(date('D')).'-';

		foreach ($this->day as $day)
			if (strpos($day["not"], $currentdaytxt) !== false){
				// do nothing
			} else {
				$v1 = strpos($day["value"], $currentdaynum);
				$v2 = strpos($day["and"], $currentdaytxt);
	
				if ($day["and"] && ($v1 && $v2))
					return true;
				else if (!$day["and"] && $v1)
					return true;
			}

		return false;

	}

	function hourMatches(){
		if ($this->hour == '*')
			return true;

		$currenthour = '-'.date('G').'-';

		if (strpos($this->hour, $currenthour) !== false)
			return true;

		return false;
	}

	function minMatches(){
		
		if ($this->min == '*')
			return true;

		$currentmin = '-'.intval(date('i')).'-';

		if (strpos($this->min, $currentmin) !== false)
			return true;

		return false;
	}

	function secMatches(){

		if ($this->sec == '*')
			return true;

		$currentsec = '-'.intval(date('s')).'-';

		if (strpos($this->sec, $currentsec) !== false)
			return true;

		return false;
	}

	function parse($str){

		$s = array();

		list($s["sec"], $s["min"], $s["hour"], $s["day"], $s["month"]) = split("[\n\t ]+", $str);

		foreach ($s as $k=>$v)
			if (strpos($v, '*') !== false)
				$s[$k] = array('*');
			else if (!$this->generallyDecentSyntax($v))
				die("Illegal syntax in '$v'\n");
			else
				$s[$k] = explode(",", $s[$k]);

		//print_r($s);

		if ($s["sec"][0] == '*'){
			$this->sec = '*';
		} else {
			for ($i = 0; $i < sizeof($s["sec"]); $i++)
				if ($this->isRange($s["sec"][$i]))
					$s["sec"][$i] = $this->expandRange($this->rangeVals($s["sec"][$i]));
			$this->sec = '-'.join('-', $s["sec"]).'-';
		}

		if ($s["min"][0] == '*'){
			$this->min = '*';
		} else {
			for ($i = 0; $i < sizeof($s["min"]); $i++)
				if ($this->isRange($s["min"][$i]))
					$s["min"][$i] = $this->expandRange($this->rangeVals($s["min"][$i]));
			$this->min = '-'.join('-', $s["min"]).'-';
		}

		if ($s["hour"][0] == '*'){
			$this->hour = '*';
		} else {
			for ($i = 0; $i < sizeof($s["hour"]); $i++)
				if ($this->isRange($s["hour"][$i]))
					$s["hour"][$i] = $this->expandRange($this->rangeVals($s["hour"][$i]));
			$this->hour = '-'.join('-', $s["hour"]).'-';
		}

		// day is gonna be hard
		if ($s["day"][0] == '*'){
			$this->day = '*';
		} else {
			for ($i = 0; $i < sizeof($s["day"]); $i++){
				$tmp = array();
				if (($char = $this->isCond($s["day"][$i])) !== false){
					if ($char == '&'){
						list($tmp["value"], $tmp["and"]) = explode($char, $s["day"][$i]);
						if ($this->isRange($tmp["and"]))
							$tmp["and"] = $this->expandRange($this->rangeVals($tmp["and"]));
					} else {
						list($tmp["value"], $tmp["not"]) = explode($char, $s["day"][$i]);
						if ($this->isRange($tmp["not"]))
							$tmp["not"] = $this->expandRange($this->rangeVals($tmp["not"]));
					}
				}else{
					$tmp = array("value" => $s["day"][$i]);
				}
				
				$s["day"][$i] = $tmp;

				if ($this->isRange($s["day"][$i]["value"]))
					$s["day"][$i]["value"] = $this->expandRange($this->rangeVals($s["day"][$i]["value"]));
			}
			$this->day = $s["day"]; // no join
		}

		if ($s["month"][0] == '*'){
			$this->month = '*';
		} else {
			for ($i = 0; $i < sizeof($s["month"]); $i++)
				if ($this->isRange($s["month"][$i]))
					$s["month"][$i] = $this->expandRange($this->rangeVals($s["month"][$i]));
			$this->month = '-'.join('-', $s["month"]).'-';
		}

	}

	function isCond($s){
		if (strpos($s, '&') !== false)
			return '&';
		else if (strpos($s, '!') !== false)
			return '!';
		else
			return false;
	}

	function isRange($s){
		if (preg_match('/^\w+\-\w+/', $s))
			return true;
		else
			return false;
	}

	function isCondRange($s){
		if (isCond($s) && isRange($s))
			return true;
		else
			return false;
	}

	function isCondVal($s){
		if (isCond($s) && !isRange($s))
			return true;
		else
			return false;
	}

	function rangeVals($s){
		//echo "rangeVals: '$s'\n";
		return explode('-', $s);
	}

	function expandRange($l, $h = ""){
		// expand range from M-F -> "-M-T-W-R-F-" and 1-5 -> "-1-2-3-4-5-"

		if (is_array($l))
			list($l, $h) = $l;

		//echo "expandRange: $l $h\n";

		if ($this->isDigit($l)){
			if ($this->isAlpha($h))
				die("Invalid range '$l-$h' ... can't mix letters and numbers.");
			else if(!$this->isDigit($h))
				die("Invalid value '$h' in range '$l-$h'");

			// currently there is no possible reason to need to do a range beyond 0-59 for anything
			if ($l < 0)
				$l = 0;
			else if ($l > 59)
				$l = 59;
			
			if ($h < 0)
				$h = 0;
			else if ($h > 59)
				$h = 59;

			if ($l > $h){
				$tmp = $l;
				$l = $h;
				$h = $tmp;
				unset($tmp);
			}

			// for some reason range() is fucking up w/o the explicit intval()s. weird.
			return '-'.join('-', range(intval($l), intval($h))).'-';

		} else if ($this->isAlpha($l)){
			if ($this->isDigit($h))
				die("Invalid range '$l-$h' ... can't mix letters and numbers.");
			else if (!$this->isAlpha($h))
				die("Invalid value '$h' in range '$l-$h'");

			$d1 = $this->dayValue($l);
			$d2 = $this->dayValue($h);

			if ($d1 > $d2){
				$tmp = $d1;
				$d1 = $d2;
				$d2 = $tmp;
				unset($tmp);
			}

			$r = '-';
			for ($i = $d1; $i <= $d2; $i++)
				$r .= $this->legalDays[$i] . '-';
			
			return $r;

		} else { //invalid
			die("Invalid value '$l' in range '$l-$h'");
		}
	}

	function dayValue($s){
		for ($i = 0; $i < sizeof($this->legalDays); $i++)
			if ($this->legalDays[$i] == $s)
				return $i;

		return -1;
	}

	function isDigit($s){
		if (preg_match('/^\d+$/', $s))
			return true;
		else
			return false;
	}

	function isAlpha($s){
		if ($this->isLegalDay($s))
			return true;
		else
			return false;
	}

	function isLegalDay($s){
		if (in_array($s, $this->legalDays))
			return true;
		else
			return false;
	}

	function generallyDecentSyntax($s){
		if ($s == '*' || preg_match('/^\d+(-\d+)?([!&][A-Z\*]+(-[A-Z\*]+)?)?(,\d+(-\d+)?([!&][A-Z\*]+(-[A-Z\*]+)?)?)*$/', $s))
			return true;
		return false;
	}

}

	/***
	$bob = new Scheduler();
	$bob->setLogFile("/tmp/bob.log", 0755);
	$bob->addTask("echo \"wazaaaaa\\n\" >> somefile", "0,5,10,15,20,25,30,35,40,45,50,55 * * * *");
	$bob->run();
	***/

?>
