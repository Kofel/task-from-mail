<?php

require_once __DIR__ . '/../vendor/autoload.php';

if (in_array('--debug', $argv))
    define('DEBUG', true);

try {
    $config = Symfony\Component\Yaml\Yaml::parse(__DIR__ . '/../config/config.yml');

    $imap = new Fetch\Server(
        $config['imap']['domain'],
        isset($config['imap']['port']) ? (int)$config['imap']['port'] : 143
    );
    $imap->setAuthentication(
        $config['imap']['username'],
        $config['imap']['password']
    );

    $smartTask = new Redmine\SmartTask(
        new Redmine\Client(
            $config['redmine']['url'],
            $config['redmine']['token']
        )
    );

    $messages = $imap->search('UNREAD');

    /**
     * @var $message \Fetch\Message
     */
    foreach ($messages as $message) {
        $from = $message->getAddresses('from');

        $match = array();
        if (preg_match('^\S+@(\S+\.\S+)$', $from, $match)) {
            foreach ($config['projects'] as $project) {
                if ($project['sender_domain'] == $match[1]) {
                    $smartTask->create(
                        $project['id'],
                        $message->getSubject(),
                        $message->getMessageBody(false),
                        $from,
                        $message->getAttachments()
                    );
                }
            }
        }

        $message->setFlag('UNREAD', false); // TODO: Confirm UNREAD flag
    }
}
catch (Exception $e) {
    if (defined('DEBUG'))
        throw $e;
    else {
        echo $e->getMessage() . PHP_EOL;
        exit;
    }
}