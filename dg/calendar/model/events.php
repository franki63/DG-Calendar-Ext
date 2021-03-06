<?php
/**
*
* @package phpBB Extension - DG Calendar
* @copyright (c) 2015 DG Kim
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace dg\calendar\model;

class events
{
	/**
	* phpBB auth
	* @var \phpbb\auth\auth
	*/
	protected $auth;

	/**
	* phpBB config
	* @var \phpbb\config\config
	*/
	protected $config;

	/**
	* phpBB db driver
	* @var \phpbb\db\driver_interface
	*/
	protected $db;
	
	/**
	* phpBB user
	* @var \phpbb\user
	*/
	protected $user;
	
	/**
	* Constructor
	* NOTE: The parameters of this method must match in order and type with
	* the dependencies defined in the services.yml file for this service.
	* @param \phpbb\auth\auth			$auth	phpBB auth object
	* @param \phpbb\config\config			$config	phpBB config
	* @param \phpbb\db\driver_interface		$db	phpBB database driver
	* @param \phpbb\user				$user	phpBB user object
	*/
	public function __construct($auth, $config, $db, $user, $events_table)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;
		$this->user = $user;
		
		define('CALENDAR_EVENTS_TABLE', $events_table);
	}

	/**
	* Get events in order
	*
	* @param int $month Month to get
	* @param int $day Day to get
	* @param int $year Year to get
	*/
	public function get_events($limit = false, $descending = false, $id = 0)
	{
		$events = array();
		
		if($id == 0) {
			if ($limit == false) {
				$sql_array = array(
					'SELECT'		=> '*',
					'FROM'		=> array(CALENDAR_EVENTS_TABLE => 'c'),
					'ORDER_BY'	=> 'post_time',
				);
				
				$sql = $this->db->sql_build_query('SELECT', $sql_array);
				$result = $this->db->sql_query($sql);
				
				while ($row = $this->db->sql_fetchrow($result))
				{
					$events[] = $row;
				}
			}
			else {
				if($descending == true) {
					$sql_array = array(
						'SELECT'		=> '*',
						'FROM'		=> array(CALENDAR_EVENTS_TABLE => 'c'),
						'ORDER_BY'	=> 'post_time DESC',
					);
				}
				else {
					$sql_array = array(
						'SELECT'		=> '*',
						'FROM'		=> array(CALENDAR_EVENTS_TABLE => 'c'),
						'ORDER_BY'	=> 'post_time ASC',
					);
				}
					
				$sql = $this->db->sql_build_query('SELECT', $sql_array);
				$result = $this->db->sql_query_limit($sql, $limit);
					
				while ($row = $this->db->sql_fetchrow($result))
				{
					$events[] = $row;
				}
			}
			return $events;
		}
		else {
			$sql_array = array(
				'SELECT'		=> '*',
				'FROM'		=> array(CALENDAR_EVENTS_TABLE => 'c'),
				'WHERE'		=> 'c.id = ' . $id,
			);
			
			$sql = $this->db->sql_build_query('SELECT', $sql_array);
			$result = $this->db->sql_query($sql);
			
			$event = $this->db->sql_fetchrow($result);
			
			return $event;
		}
	}

	/**
	* Get events by a date
	*
	* @param int $month Month to get
	* @param int $day Day to get
	* @param int $year Year to get
	*/
	public function get_events_of_day($month, $day, $year)
	{
		$events = array();
		
		$sql_array = array(
			'SELECT'		=> '*',
			'FROM'		=> array(CALENDAR_EVENTS_TABLE => 'c'),
			'WHERE'		=> 'c.month = ' . $month . ' AND c.day = ' . $day . ' AND c.year = ' . $year,
		);
			
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);
			
		while ($row = $this->db->sql_fetchrow($result))
		{
			$events[] = $row;
		}
			
		return $events;
	}
	
	/**
	* New event
	*
	* @param int $user_id User ID of poster
	* @param int $timestamp Posting timestamp
	* @param int $month Month of event
	* @param int $day Day of event
	* @param int $year Year of event
	* @param string $start Start time
	* @param string $end End time
	* @param string $title Title of event
	* @param string $description Event description
	*/
	public function add_event($user_id, $timestamp, $month, $day, $year, $start, $end, $title, $description)
	{
		$sql_array = array(
			'user_id'		=> $user_id,
			'post_time'		=> $timestamp,
			'month'			=> $month,
			'day'			=> $day,
			'year'			=> $year,
			'start'			=> $start,
			'end'			=> $end,
			'title'			=> $title,
			'description'	=> $description,
			'event_status'	=> 0,
		);
		
		$sql = 'INSERT INTO ' . CALENDAR_EVENTS_TABLE . ' ' . $this->db->sql_build_array('INSERT', $sql_array);
		$this->db->sql_query($sql);
	}
	
	/**
	* Edit event
	*
	* @param int $id ID of event
	* @param int $user_id User ID of editor
	* @param int $month Month of event
	* @param int $day Day of event
	* @param int $year Year of event
	* @param string $start Start time
	* @param string $end End time
	* @param string $title Title of event
	* @param string $description Event description
	*/
	public function edit_event($id, $user_id, $month, $day, $year, $start, $end, $title, $description)
	{
		$sql_array = array(
			'user_id'		=> $user_id,
			'month'			=> $month,
			'day'			=> $day,
			'year'			=> $year,
			'start'			=> $start,
			'end'			=> $end,
			'title'			=> $title,
			'description'	=> $description,
		);
		
		$sql = 'UPDATE ' . CALENDAR_EVENTS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_array) . ' WHERE `id` = ' . $id;
		$this->db->sql_query($sql);
	}
	
	/**
	* Delete event
	*
	* @param int $id The id of the event
	*/
	public function delete_event($id)
	{
		$sql = 'DELETE FROM ' . CALENDAR_EVENTS_TABLE . ' WHERE `id` = ' . $id;
		$this->db->sql_query($sql);
	}
	
	/**
	* Change status
	*
	* @param int $id The id of the event
	*/
	public function change_event_status($id, $status)
	{
		$sql = 'UPDATE ' . CALENDAR_EVENTS_TABLE . ' SET `event_status` = ' . $status . ' WHERE `id` = ' . $id;
		$this->db->sql_query($sql);
	}
}
