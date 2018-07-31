<?php
/**
 * Created by PhpStorm.
 * User: muntashir
 * Date: 9/11/17
 * Time: 12:52 AM
 */

namespace ADACT\App;

use \ADACT\Config;


class Session implements \SessionHandlerInterface {
    /** @var Session */
    private static $_instance;
    /**
     * @var \mysqli
     */
    private $_db;

    /**
     * Session constructor.
     * @throws \Exception
     */
    public function __construct(){
        @$this->_db = new \Mysqli(Config::MYSQL_HOST, Config::MYSQL_USER, Config::MYSQL_PASS, Config::MYSQL_DB, Config::MYSQL_PORT);
        if ($this->_db->connect_error) {
            error_log("Unable to connect to mysql.");
            throw new \Exception("Unable to connect to MySQL");
        }
        @$this->_db->set_charset("utf8");
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public static function start() {
        if(empty(self::$_instance)) {
            try {
                self::$_instance = new self();
                session_set_save_handler(self::$_instance, true);
                return session_start();
            } catch (\Exception $exception){
                throw new \Exception("Cannot start session. Reason: " . $exception->getMessage());
            }
        }
        return true;
    }

    /**
     * @throws \Exception
     */
    public static function save() {
        if(empty(self::$_instance)) {
            throw new \Exception("You cannot save a session before starting the session");
        }
        self::$_instance->write(session_id(),session_encode());
    }

    /**
     * @param string $save_path
     * @param string $name
     * @return bool
     * @throws \Exception
     */
    public function open($save_path, $name) {
        return true;
    }

    public function close() {
        return true;
    }
    public function read($id) {
        if($stmt = $this->_db->prepare('SELECT data FROM active_sessions WHERE session_id = ?')){
            $stmt->bind_param('s', $id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows === 1){
                $stmt->bind_result($data);
                $stmt->fetch();
                return $data;
            }
        }
        return '';
    }
    public function write($id, $data) {
        $access_time = time();
        $user_id = 0;
        if($stmt = $this->_db->prepare('SELECT user_id FROM active_sessions WHERE session_id = ?')){
            $stmt->bind_param('s', $id);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows === 1){
                $stmt->bind_result($user_id);
                $stmt->fetch();
            }
        }

        // Set query
        if($stmt = $this->_db->prepare('REPLACE INTO active_sessions(user_id, session_id, data, time) VALUES (?, ?, ?, ?)')){
            $stmt->bind_param('issi', $user_id, $id , $data, $access_time);
            if($stmt->execute()) return true;
        }
        return false;
    }
    public function destroy($id) {
        if($stmt = $this->_db->prepare('DELETE FROM active_sessions WHERE session_id = ?')){
            $stmt->bind_param('s', $id);
            if($stmt->execute()) return true;
        }
        return true;
    }
    public function gc($max_lifetime) {
        $old = time() - $max_lifetime;

        if($stmt = $this->_db->prepare('DELETE * FROM active_sessions WHERE time < ?')){
            $stmt->bind_param('i', $old);
            if($stmt->execute()) return true;
        }
        return true;
    }
}