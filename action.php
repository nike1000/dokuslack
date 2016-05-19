<?php

if (!defined('DOKU_INC')) {
    die();
}

if (!defined('DOKU_LF')) {
    define('DOKU_LF', "\n");
}

if (!defined('DOKU_TAB')) {
    define('DOKU_TAB', "\t");
}

if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}

class action_plugin_dokuslack extends DokuWiki_Action_Plugin {

    function register(&$controller) {
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'handleAction');
    }

    function handleAction(&$event, $param) {

        if (isset($event->data['save'])) {
            $this->sendSlackNotification();
        }

        return;
    }

    private function sendSlackNotification() {
        global $SUM;
        global $INFO;
        global $conf;

        $notificacion = array();

        $nombreCompleto = $INFO['userinfo']['name'];
        $nombreArticulo = ucwords(strtolower(str_replace('_', ' ', $INFO['id'])));

        $notificacion['channel'] = '#' . $this->getConf('dokuslack_message_channel');
        $notificacion['username'] = $this->getConf('dokuslack_message_username');

        $notificacion['attachments'] = array();

        $attachments1 = array(
            'fallback' => $nombreCompleto . ' ha editado un artículo en la Wiki.',
            'pretext' => $nombreCompleto . ' ha editado un artículo en la Wiki.',
            'title' => $nombreArticulo,
            'title_link' => $this->getRealURL(),
            'color' => $this->getConf('dokuslack_message_color'),
            'thumb_url' => $this->getConf('dokuslack_message_thumb'),
            'fields' => array()
        );

        $fields1 = array(
            'value' => $this->summary($_REQUEST['wikitext'], 200),
            'short' => true
        );

        array_push($attachments1['fields'], $fields1);
        array_push($notificacion['attachments'], $attachments1);

        $url = $this->getConf('dokuslack_webhook_url');
        $json = json_encode($notificacion);

        $fields = array(
            'payload' => urlencode($json)
        );

        //url-ify the data for the POST
        foreach ($fields as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        }
        rtrim($fields_string, '&');

        $ch = curl_init();
        $proxy = $conf['proxy'];

        if (isset($proxy['host']) && $proxy['host'] != "") {
            $proxyAddress = $proxy['host'] . ':' . $proxy['port'];

            curl_setopt($ch, CURLOPT_PROXY, $proxyAddress);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            if (!empty($proxy['user']) && !empty($proxy['pass'])) {
                $proxyAuth = $proxy['user'] . ':' . conf_decodeString($proxy['port']);
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyAuth);
            }
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);

        if ($result === false) {
            echo 'cURL error when posting Wiki save notification to Slack: ' . curl_error($ch);
        }

        curl_close($ch);
    }

    private function getRealURL() {

        global $INFO;
        global $conf;

        $page = $INFO['id'];

        switch ($conf['userewrite']) {
            case 0:
                $url = DOKU_URL . "doku.php?id=" . $page;
                break;
            case 1:
                if ($conf['useslash']) {
                    $page = str_replace(":", "/", $page);
                }
                $url = DOKU_URL . $page;
                break;
            case 2:
                if ($conf['useslash']) {
                    $page = str_replace(":", "/", $page);
                }
                $url = DOKU_URL . "doku.php/" . $page;
                break;
        }
        return $url;
    }

    private function summary($str, $limit = 100, $strip = false) {
        $str = ($strip == true) ? strip_tags($str) : $str;
        if (strlen($str) > $limit) {
            $str = substr($str, 0, $limit - 3);
            return (substr($str, 0, strrpos($str, ' ')) . '...');
        }
        return trim($str);
    }

}
