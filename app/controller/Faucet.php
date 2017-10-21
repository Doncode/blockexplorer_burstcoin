<?php

class ControllerFaucet extends Controller
{
    /**
     * Faucet
     */
    function index()
    {
        // Lade das JS Captcha
        $this->smarty->assign('jsCaptcha', true);

        // Ermittle die Gesamtsumme an Payouts
        $getFaucetPayouts = $this->db->query("SELECT SUM(amount) AS burstcoins, COUNT(*) AS burstfee FROM " . DB_PRE . "faucet");
        $this->smarty->assign('totalPayouts', $getFaucetPayouts[0]['burstcoins'] + $getFaucetPayouts[0]['burstfee']);

        // Ermittle den Kontostand vom Faucet
        $getBalance = $this->db->query("SELECT unconfirmedBalanceNQT FROM " . DB_PRE . "chain_accounts WHERE account=" . FAUCET_ID);
        $this->smarty->assign('faucetBalance', $getBalance[0]['unconfirmedBalanceNQT']);

        $this->template = 'Faucet/index';
    }

    /**
     * Faucet Anfrage überprüfen
     */
    function check()
    {
        // check BURST address
        $burstAddress = [];
        if (isset($_POST['address']) AND !empty($_POST['address'])) {
            $burstAddress = explode('-', $_POST['address']);
        }

        // check recaptcha
        if (defined('RECAPTCHA_SECRET') && isset($_POST['g-recaptcha-response']) AND !empty($_POST['g-recaptcha-response'])) {
            $url = "https://www.google.com/recaptcha/api/siteverify?secret=" . RECAPTCHA_SECRET . '&response=' . $_POST['g-recaptcha-response'] . "&remoteip=" . $_SERVER['REMOTE_ADDR'];
            $handle = file_get_contents($url);
            $content = json_decode($handle, true);
        }

        if (defined('COINHIVE_COM_SECRET')) {
            $post_context = stream_context_create([
                'http' => [
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method' => 'POST',
                    'content' => http_build_query([
                        'secret' => COINHIVE_COM_SECRET, // <- Your secret key
                        'token' => $_POST['coinhive-captcha-token'],
                        'hashes' => COINHIVE_COM_HASHES
                    ])
                ]
            ]);
            $url = 'https://api.coinhive.com/token/verify';
            $coin_hive_resp = json_decode(file_get_contents($url, false, $post_context));
        }


        $getBalance = $this->db->query("SELECT unconfirmedBalanceNQT FROM " . DB_PRE . "chain_accounts WHERE account=" . FAUCET_ID);

        $getFaucet = $this->db->query("SELECT ts_faucet, faucetid FROM " . DB_PRE . "faucet WHERE remote_addr='" . $this->db->escapeString($_SERVER['REMOTE_ADDR']) . "' OR recipient='" . $this->db->escapeString($_POST['address']) . "' ORDER BY faucetid DESC");
        $reload = time() - 3600;
        $countFaucet = $this->db->query("SELECT COUNT(*) AS faucet FROM " . DB_PRE . "faucet WHERE recipient='" . $this->db->escapeString($_POST['address']) . "'");
        $buttonJS = "<script>$('#faucetBtn').attr('disabled',false).text('Claim');</script>";
        if (!isset($_POST['address']) OR empty($_POST['address'])) {
            echo $this->bootstrap->alert('Please enter your Burst address.' . $buttonJS, 'danger');
        } elseif (count($burstAddress) != 5 OR strtolower($burstAddress[0]) != "burst" OR strlen($_POST['address']) != 26) {
            echo $this->bootstrap->alert('Please enter a valid Burst address.' . $buttonJS, 'danger');
        } elseif ($countFaucet[0]['faucet'] >= 4) {
            echo $this->bootstrap->alert('This address exceed the limit.' . $buttonJS, 'danger');
        } elseif ($getBalance[0]['unconfirmedBalanceNQT'] < 5) {
            echo $this->bootstrap->alert('Not enough Burstcoins in faucet wallet.' . $buttonJS, 'danger');
        } elseif (isset($getFaucet[0]) AND $getFaucet[0]['ts_faucet'] > $reload) {
            $minutes = $getFaucet[0]['ts_faucet'] - $reload;
            $minutes = ceil($minutes / 60);
            echo $this->bootstrap->alert('This faucet is only for new user of Burst. It is not possible to claim several times.' . $buttonJS,
                'danger');
        } elseif (isset($coin_hive_resp) && (!$coin_hive_resp or !$coin_hive_resp->success)) {
            echo $this->bootstrap->alert('You entered a wrong captcha.', 'danger');
        } elseif (defined('RECAPTCHA_SECRET') and (!isset($content) OR !isset($_POST['g-recaptcha-response']) OR empty($_POST['g-recaptcha-response']) OR !isset($content['success']) OR $content['success'] != 1)) {
            echo $this->bootstrap->alert('You entered a wrong captcha.', 'danger');

        } else {
            $amount = 1;
            $escapedAddress = $this->db->escapeString(trim($_POST['address']));
            $userBalance = $this->db->query("SELECT unconfirmedBalanceNQT FROM " . DB_PRE . "chain_accounts WHERE accountRS='{$escapedAddress}'");
            if ($userBalance && $userBalance[0]['unconfirmedBalanceNQT'] > 1) {
                echo $this->bootstrap->alert("You already have {$userBalance[0]['unconfirmedBalanceNQT']} coin(s) in your wallet." . $buttonJS,
                    'danger');
                exit;
            }


            // Logge den Faucet Aufruf
            $this->db->query(
                "INSERT INTO " . DB_PRE . "faucet " .
                "(recipient, remote_addr, amount, ts_faucet) " .
                "VALUES " .
                "('" . $this->db->escapeString($_POST['address']) . "', '" . $this->db->escapeString($_SERVER['REMOTE_ADDR']) . "', '" . $amount . "', '" . time() . "')");
            $faucetid = $this->db->getId();

            // Sende die Burstcoins
            $options = array(
                'http' => array(
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method' => 'POST',
                    'content' => http_build_query(array(
                        'requestType' => 'sendMoney',
                        'recipient' => $_POST['address'],
                        'amountNQT' => $amount . '00000000',
                        'secretPhrase' => FAUCET_PW,
                        'feeNQT' => '100000000',
                        'deadline' => '24'
                    )),
                ),
            );
            $context = stream_context_create($options);
            $result = file_get_contents("http://" . BURST_API . "/burst", false, $context);
            $content = json_decode($result, true);

            if (isset($content['transaction'])) {
                // Hinterlege die Transaktions-ID
                $this->db->query("UPDATE " . DB_PRE . "faucet SET transaction='" . $content['transaction'] . "' WHERE faucetid='" . $faucetid . "'");

                echo $this->bootstrap->alert("You receive $amount Burst in a few minutes.<script>$('#faucetBtn').remove(); $('.g-recaptcha').remove();</script>",
                    'success');
            } else {
                echo $this->bootstrap->alert('Sorry, an error occurred. Please try again later.' . $buttonJS, 'danger');
            }
        }

        exit();
    }
}
