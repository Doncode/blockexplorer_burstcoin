<?php


class Database
{
    private $MySQLiObj = null;
    public $queryStatus = null;
    public $numRows = null;
    public $queries = [];
    public $cache_dir = DIR_SYSTEM . '/app/cache/';

    /**
     * Verbindungsaufbau zum MySQL-Server und setzt die nötigen Einstellungen.
     */
    public function __construct()
    {
        $this->MySQLiObj = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);

        if (mysqli_connect_errno()) {
            ErrorHandler::writeLog('database', 'mysql connection error: can\'t connect to database');
            //header("Location: ".HTTP_ROOT."errors/db.htm");
            exit(mysqli_connect_error());
        }

        $this->query("SET NAMES ".DB_CHARSET);
    }

    /**
     * Schließt die Verbindung zum MySQL-Server.
     */
    public function __destruct()
    {
        $this->MySQLiObj->close();
        $sum = 0;
        foreach ($this->queries as $q) {
            $sum += $q['time'];
        }
        $this->queries['sum'] = $sum;
        if (DB_LOG_ALL || isset($_REQUEST['log'])) {
            ErrorHandler::writeLog('database_queries', print_r($this->queries, true).PHP_EOL.PHP_EOL);
        }

        //clear cache
        $dir = new DirectoryIterator($this->cache_dir);
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()) {
                if( time() - $fileinfo->getCTime() > DB_MAX_CACHE_TIME) {
                    unlink($fileinfo->getRealPath());
                }
            }
        }
    }

    /**
     * Führt einen Query aus und liefert Ergebnisse zurück.
     * @param string $query SQL-Query
     * @param boolean $resultset False liefert Ergebnisse als Array zurück und true liefert nur den Status des Queries (Default: false)
     * @return array|boolean Ergebnisse als Array oder Query-Status
     */
    public function query($query, $resultset = false)
    {
        $t10 = microtime(true);
        $result = $this->MySQLiObj->query($query);

        if (isset($this->MySQLiObj->error) AND !empty($this->MySQLiObj->error)) {
            ErrorHandler::writeLog('database',
                'mysql query error: '.$this->MySQLiObj->error.' (query: '.$query.')');
        }

        if ($resultset === true) {
            if ($result === false) {
                $this->queryStatus = false;
            } else {
                $this->queryStatus = true;
            }
            $this->numRows = $result->num_rows;

            return $result;
        }
        $data = $this->makeArrayResult($result);

        $t20 = microtime(true);
        $this->queries[] = [
            'query' => $query,
            'time' => $t20 - $t10,
            'error' => $this->MySQLiObj->error,
        ];

        return $data;
    }

    public function query_cache($query, $resultset = false, $cache_time = 0){
        $t10 = microtime(true);
        $cache_file = $this->cache_dir .md5($query . ":resultset={$resultset}") . '.json';
        if(isset($_REQUEST['log'])){
//            echo '<pre>';
//            var_dump($cache_file);
//            var_dump(file_exists($cache_file));
//            var_dump((time() - $cache_time));
//            var_dump(filemtime($cache_file));
//            echo "cache_time=$cache_time", PHP_EOL;
//            echo date ("F d Y H:i:s.", filemtime($cache_file)), PHP_EOL;
//            echo date ("F d Y H:i:s.",(time() - $cache_time)), PHP_EOL;
//            echo date ("F d Y H:i:s."), PHP_EOL;
//            echo '</pre>';
        }
        if ($cache_time && file_exists($cache_file) && (time() - $cache_time) < filemtime($cache_file)) {
            $data = json_decode(file_get_contents($cache_file), true);
            $t20 = microtime(true);
            $this->queries[] = [
                'query' => $query,
                'time' => $t20 - $t10,
                'error' => $this->MySQLiObj->error,
                'cache_file' => $cache_file,
                'from_cache' => 1,
            ];

        } else {
            $data = $this->query($query, $resultset);
            file_put_contents($cache_file, json_encode($data));
            $this->queries[count($this->queries)-1]['cache_file'] = $cache_file;
        }



        return $data;
    }

    /**
     * Liefert die ID eines neuen MySQL-Queries.
     * @return int MySQL Insert-ID
     */
    public function getId()
    {
        return $this->MySQLiObj->insert_id;
    }

    /**
     * Liefert die Fehlermeldung eines Queries.
     * @return string MySQL-Query Fehler
     */
    public function getError()
    {
        return $this->MySQLiObj->error;
    }

    /**
     * Bereinigt einen String von Zeichen wie NUL (ASCII 0), \n, \r, \, ', ", und Control-Z.
     * @param string $value String der bereinigt werden soll
     * @return string Liefert einen bereinigten String
     */
    public function escapeString($value)
    {
        return $this->MySQLiObj->real_escape_string($value);
    }

    /**
     * Erzeugt einen Array für die Results.
     * @param object $resultObj Query-Object
     * @return boolean|array Liefert Query-Status oder gibt Results als Array zurück sofern vorhanden
     */
    private function makeArrayResult($resultObj)
    {
        if ($resultObj === false) {
            $this->queryStatus = false;

            return false;
        }

        if ($resultObj === true) {
            $this->queryStatus = true;

            return true;
        } else {
            $this->numRows = $resultObj->num_rows;
            if ($resultObj->num_rows == 0) {
                $this->queryStatus = true;

                return array();
            } else {
                $array = array();

                while ($line = $resultObj->fetch_array(MYSQL_ASSOC)) {
                    array_push($array, $line);
                }

                $this->queryStatus = true;

                return $array;
            }
        }
    }
}