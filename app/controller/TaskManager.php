<?php

/**
 * @property array cycledata
 * @property \Loader load
 * @property Mysqli db
 * @property bool|mysqli_result taskdata
 * @property ModelWallet model_wallet
 */
class ControllerTaskManager extends Controller
{
    private $currentTask;

    /**
     * Sucht nach offenen Tasks und starte diese.
     */
    function index()
    {
        header('Content-type: text/plain');
        print_r($_GET);
        // Prüfe auf Zyklus
        if (isset($_GET['action']) AND $_GET['action'] == "cycle") {
            echo 1;
            $this->cycledata = $this->db->query("SELECT * FROM ".DB_PRE."tasks_cycle WHERE ts_nextcycle<'".time()."'");
//            $this->cycledata = $this->db->query("SELECT * FROM ".DB_PRE."tasks_cycle");

//                        echo "SELECT * FROM ".DB_PRE."tasks_cycle WHERE ts_nextcycle<'".time()."'";
//            print_r($this->cycledata);
            //print_r($this->db);
            // var_dump(isset($this->cycledata[0]));
            // var_dump($this->cycledata[0]);
            //if (isset($this->cycledata[0])) {

            foreach ($this->cycledata AS $cycledata) {
                echo ' foreach '.$cycledata['ID'].PHP_EOL;
                print_r($cycledata);
                $nextcycle = time() + $cycledata['cycleinterval'];
                $this->db->query("UPDATE ".DB_PRE."tasks_cycle SET ts_lastcycle='".time()."', ts_nextcycle='".$nextcycle."' WHERE cycleid='".$cycledata['cycleid']."'");
                $startNewTask = 1;
                // Prüfe ob Task bereits ausgeführt wird
                if ($cycledata['taskonce'] == 1) {
                    $this->countTasks = $this->db->query("SELECT COUNT(*) AS taskRunning FROM ".DB_PRE."tasks_list WHERE taskid='".$cycledata['taskid']."' AND statusid<>'2'");
                    if ($this->countTasks[0]['taskRunning'] != 0) {
                        $startNewTask = 0;
                    }
                    print_r($this->countTasks);
                }
                // Lege eine neue Task an
                if ($startNewTask == 1) {
                    // Lege eine Aufgabe an
                    $res = $this->db->query("INSERT INTO ".DB_PRE."tasks_list (taskid) VALUES ('".$cycledata['taskid']."')");
                    echo "INSERT INTO ".DB_PRE."tasks_list (taskid) VALUES ('".$cycledata['taskid']."')", PHP_EOL;
                }
            }
            //}
            // Führe eine einzelne Task aus
        } else {
            $limit = isset($_GET['limit']) ? intval($_GET['limit']): 10;
            $taskId = isset($_GET['taskid']) ? ' AND taskid='.intval($_GET['taskid']): '';
            $this->taskdata = $this->db->query("SELECT * FROM ".DB_PRE."tasks_list WHERE statusid='0' {$taskId} ORDER BY tasklistid DESC LIMIT {$limit}");
            print_r($this->taskdata);
            $tasks = [];
            $ids = [];
            foreach ($this->taskdata as $taskData) {
                // Setze Task auf "in Arbeit"
                if ($taskData['ts_start'] == 0) {
                    $this->taskStartTime = time();
                } else {
                    $this->taskStartTime = $taskData['ts_start'];
                }

                // Ausführen der Task
                $tasks[] = $taskData;
                $ids[] = $taskData['tasklistid'];
            }
            if(count($ids)) {
                $ids = implode(',', $ids);
                echo $ids,PHP_EOL;
                echo "UPDATE ".DB_PRE."tasks_list SET statusid='1', ts_start='".time()."', ts_update='".time()."' WHERE ts_start=0 AND tasklistid IN ($ids)",PHP_EOL;
                echo "UPDATE ".DB_PRE."tasks_list SET statusid='1', ts_update='".time()."' WHERE ts_start<>0 AND tasklistid IN ($ids)",PHP_EOL;
                $this->db->query("UPDATE ".DB_PRE."tasks_list SET statusid='1', ts_start='".time()."', ts_update='".time()."' WHERE ts_start=0 AND tasklistid IN ($ids)");
                $this->db->query("UPDATE ".DB_PRE."tasks_list SET statusid='1', ts_update='".time()."' WHERE ts_start<>0 AND tasklistid IN ($ids)");
                foreach ($tasks as $task) {
                    $taskname = 'task'.$task['taskid'];
                    $this->currentTask = $task;
                    $this->$taskname();
                }
            }
        }
        echo "index end\n";
        exit;
    }

    /**
     * Aufgabe: Prüft auf neuen Block.
     */
    function task1()
    {
        echo "task1\n";
        // Lade Module
        $this->load->model('Wallet');

        // Auslesen der Blockchain-Info
        if ($walletData = $this->model_wallet->request('getBlockchainStatus')) {
            print_r($walletData);
            $statsdata = $this->db->query("SELECT blocks FROM ".DB_PRE."stats");
            // Prüfe ob neuer Block vorhanden ist
            if ($walletData['numberOfBlocks'] > $statsdata[0]['blocks']) {
                // Aktualisiere die Anzahl der Blocks in der Statistik
                $this->db->query("UPDATE ".DB_PRE."stats SET blocks='".$walletData['numberOfBlocks']."'");

                // Lese den höchsten Block aus für den eine Task angelegt wurde
                $taskstatus = $this->db->query("SELECT referenceid FROM ".DB_PRE."tasks_list WHERE taskid='2' ORDER BY referenceid DESC LIMIT 1");
                if (isset($taskstatus[0]['referenceid'])) {
                    $newestBlock = $walletData['numberOfBlocks'] - 1;
                    $lastBlock = $taskstatus[0]['referenceid'] + 1;
                    // Lege neue Tasks für ein oder mehrere Blöcke zum auslesen an
                    for ($i = $lastBlock; $i <= $newestBlock; $i++) {
                        $this->db->query(
                            "INSERT INTO ".DB_PRE."tasks_list ".
                            "(taskid, referenceid) ".
                            "VALUES ".
                            "('2', '".$i."')");
                    }
                }
            }
        }

        // Task abschließen
        $this->task_finish();
    }

    /**
     * Aufgabe: Importiert einen neuen Block und Transaktionen.
     */
    function task2()
    {
        echo 'task2 syncBlock', PHP_EOL;
        // Lade Module
        $this->load->model('Wallet');
        print_r($this->currentTask);

        if ($this->model_wallet->syncBlock($this->currentTask['referenceid'])) {
            // Task abschließen
            $this->task_finish();
        }
    }

    /**
     * Aufgabe: Aktualisiere den Bitcoin/Burstcoin Kurs.
     */
    function task3()
    {
        echo "task3\n";
        $fields = [];
        $url = 'https://bittrex.com/api/v1.1/public/getmarketsummary?market=btc-burst';
        $content = file_get_contents($url);
        if ($content) {
            $content = json_decode($content, true);
            if (isset($content['result'][0]['Last'])) {
                $burstBTC = number_format(floatval($content['result'][0]['Last']), 8, '.', '');
                $fields[] = 'burstBTC=' . $burstBTC;
            }
        } else {
            echo "can`t open $url\n";
        }

        $url = 'https://blockchain.info/ru/ticker';
        $content = file_get_contents($url);
        if ($content) {
            $content = json_decode($content, true);
            if (isset($content['USD']['last'])) {
                $btcUSD = number_format(floatval($content['USD']['last']), 2, '.', '');
                $fields[] = 'btcUSD=' . $btcUSD;
                $fields[] = 'btcUSDts=' . time();
            }
            if (isset($content['EUR']['last'])) {
                $btcEUR = number_format(floatval($content['EUR']['last']), 2, '.', '');
                $fields[] = 'btcEUR=' . $btcEUR;
                $fields[] = 'btcEURts=' . time();
            }
        } else {
            echo "can`t open $url\n";
        }

        if (count($fields)) {
            $fields = implode(', ', $fields);
            $sql = "UPDATE " . DB_PRE . "stats SET {$fields}";
            $this->db->query($sql);
            echo "$sql\n";
        } else {
            echo "No data for update\n";
        }

        $this->task_finish();
    }

    /**
     * Aufgabe: Prüfe auf zu synchronisierende Blöcke.
     */
    function task4()
    {
        echo "task4\n";
//        // Lade Module
//        $this->load->model('Wallet');
//
//        // Lese die letzte Blockhöhe aus
//        $this->getBlockHeight = $this->db->query("SELECT blocks FROM ".DB_PRE."stats");
//        $syncUntilBlock = $this->getBlockHeight[0]['blocks' + 1];
//        $syncFromBlock = $this->getBlockHeight[0]['blocks' - 500];
//
//        // Lese die zu synchronisierenden Blöcke aus
////                $this->syncBlocks = $this->db->query("SELECT blockid, height FROM ".DB_PRE."chain_blocks WHERE height<'".$syncUntilBlock."' AND resynced='0' ORDER BY height ASC LIMIT 100");
////                $this->syncBlocks = $this->db->query("SELECT blockid, height FROM ".DB_PRE."chain_blocks WHERE height<'".$syncUntilBlock."' ORDER BY height ASC LIMIT 5000");
////                if (isset($this->syncBlocks[0])) {
////                        foreach ($this->syncBlocks AS $syncBlock) {
//        for ($h = $syncFromBlock; $h < $syncUntilBlock; $h++) {
//// for ($h = 351480; $h < 351502; $h++) {
//            // Synchronisiere den Block
////                                if ($this->model_wallet->syncBlock($syncBlock['height'])) {
//            if ($this->model_wallet->syncBlock($h)) {
//                $this->db->query("UPDATE ".DB_PRE."chain_blocks SET resynced='1' WHERE blockid='".$syncBlock['blockid']."'");
//            }
//        }
        $this->task_finish();
    }


    /**
     * Aufgabe: Prüfe ob Surfbarlink IDs mit einem Account synchronisiert werden müssen.
     */
    function task5()
    {
        echo "task5\n";

//        return;
//        $getAccounts = $this->db->query("SELECT COUNT(*) AS withoutEBID, ts_create FROM ".DB_PRE."surfbar WHERE ebid='0' ORDER BY surfbarid ASC");
//        if ($getAccounts[0]['withoutEBID'] > 0) {
//            $ts_account = $getAccounts[0]['ts_create'] - 7200;
//            $curl = curl_init();
//            curl_setopt_array($curl, array(
//                CURLOPT_SSL_VERIFYPEER => false,
//                CURLOPT_RETURNTRANSFER => true,
//                CURLOPT_USERPWD => SURFBAR_API,
//                CURLOPT_URL => 'https://www.ebesucher.de/api/visitor_exchange.json/surflinks?activeSince='.$ts_account,
//            ));
//            $content = curl_exec($curl);
//            curl_close($curl);
//            $content = json_decode($content, true);
//            if (!empty($content)) {
//                foreach ($content AS $surflink) {
//                    $surfbarid = str_replace('burstcoinbiz.user', '', $surflink['fullName']);
//                    $this->db->query("UPDATE ".DB_PRE."surfbar SET ebid='".$this->db->escapeString($surflink['id'])."' WHERE surfbarid='".$this->db->escapeString($surfbarid)."'");
//                }
//            }
//        }

        // Task abschließen
        $this->task_finish();
    }

    /**
     * Aufgabe: Fordere einen neuen Surfbar Report an.
     */
    function task6()
    {
        echo "task6\n";

//        $curl = curl_init();
//        $data = array("from" => "1", "to" => time() + 7200);
//        $data_string = json_encode($data);
//        curl_setopt_array($curl, array(
//            CURLOPT_SSL_VERIFYPEER => false,
//            CURLOPT_RETURNTRANSFER => true,
//            CURLOPT_USERPWD => SURFBAR_API,
//            CURLOPT_CUSTOMREQUEST => "POST",
//            CURLOPT_POSTFIELDS => $data_string,
//            CURLOPT_HTTPHEADER => array(
//                'Content-Type: application/json',
//                'Content-Length: '.strlen($data_string),
//            ),
//            CURLOPT_URL => 'https://www.ebesucher.de/api/visitor_exchange.json/account/surflink_earnings_report',
//        ));
//        $content = curl_exec($curl);
//        curl_close($curl);
//        $content = json_decode($content, true);
//        if (!isset($content['error'])) {
//            $this->db->query("UPDATE ".DB_PRE."stats SET ebesucherReport='".$this->db->escapeString($content)."'");
//        }

        // Task abschließen
        $this->task_finish();
    }

    /**
     * Aufgabe: Löschen von alten und stehengebliebenen Aufgaben.
     */
    function task7()
    {
        echo "task7\n";
        // Lösche alte Aufgaben
        $ts_end = time() - 172800; // 2 Tage
        $this->db->query("DELETE FROM ".DB_PRE."tasks_list WHERE ts_end>'0' AND ts_end<'".$ts_end."'");

        // Prüfe auf stehengebliebene Aufgaben
        $ts_update = time() - 10800; // 3 Stunden
        $this->db->query("DELETE FROM ".DB_PRE."tasks_list WHERE statusid='1' AND ts_update>'0' AND ts_update<'".$ts_update."'");

        // Task abschließen
        $this->task_finish();
    }

    /**
     * Aufgabe: Aktualisiere die Surfbar Punktestände.
     */
    function task8()
    {
        echo "task8\n";
//
//        return;
//        // Lese die Report ID aus
//        $getReport = $this->db->query("SELECT ebesucherReport FROM ".DB_PRE."stats");
//        if ($getReport[0]['ebesucherReport'] > 0) {
//            // Prüfe ob der Report fertiggestellt wurde
//            $curl = curl_init();
//            curl_setopt_array($curl, array(
//                CURLOPT_SSL_VERIFYPEER => false,
//                CURLOPT_RETURNTRANSFER => true,
//                CURLOPT_USERPWD => SURFBAR_API,
//                CURLOPT_URL => 'https://www.ebesucher.de/api/visitor_exchange.json/account/surflink_earnings_report/'.$getReport[0]['ebesucherReport'].'/status',
//            ));
//            $content = curl_exec($curl);
//            curl_close($curl);
//            $content = json_decode($content, true);
//            if (!empty($content) AND isset($content['isFinished']) AND $content['isFinished']) {
//                // Lese die Daten des Reports aus
//                $curl = curl_init();
//                curl_setopt_array($curl, array(
//                    CURLOPT_SSL_VERIFYPEER => false,
//                    CURLOPT_RETURNTRANSFER => true,
//                    CURLOPT_USERPWD => SURFBAR_API,
//                    CURLOPT_URL => 'https://www.ebesucher.de/api/visitor_exchange.json/account/surflink_earnings_report/'.$getReport[0]['ebesucherReport'],
//                ));
//                $content = curl_exec($curl);
//                curl_close($curl);
//                $content = json_decode($content, true);
//                if (!empty($content)) {
//                    foreach ($content AS $surfdata) {
//                        $this->db->query("UPDATE ".DB_PRE."surfbar SET surfpoints='".$this->db->escapeString($surfdata['value'])."' WHERE ebid='".$this->db->escapeString($surfdata['surflinkID'])."'");
//                    }
//                }
//            }
//        }

        // Task abschließen
        $this->task_finish();
    }

    /**
     * Setze eine Task auf beendet.
     */
    function task_finish()
    {
        $this->taskEndTime = time();
        $this->taskDuration = $this->taskEndTime - $this->taskStartTime;
        $this->db->query("UPDATE ".DB_PRE."tasks_list SET statusid='2', ts_update='".time()."', ts_end='".$this->taskEndTime."', duration='".$this->taskDuration."', stats_now='".$this->currentTask['stats_max']."' WHERE tasklistid='".$this->currentTask['tasklistid']."'");
        echo "task_finish\n\n\n";
    }
}