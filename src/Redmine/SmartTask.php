<?php

namespace Redmine;

class SmartTask
{
    /**
     * @var $client Client
     */
    protected $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param $project string
     * @param $title string
     * @param $description string
     * @param $author string
     * @param $attachments \Fetch\Attachment[]
     */
    public function create($project, $subject, $description, $author, $attachments = array())
    {
        /**
         * @var $projectApi Api\Project
         */
        $projectApi     = $this->client->api('project');

        /**
         * @var $userApi Api\User
         */
        $userApi        = $this->client->api('user');

        /**
         * @var $issueApi Api\Issue
         */
        $issueApi       = $this->client->api('issue');

        /**
         * @var $attachmentApi Api\Attachment
         */
        $attachmentApi  = $this->client->api('attachment');

        if (!($projectId = $projectApi->getIdByName($project))) {
            throw new \Error\Config('Configured ' . $project . ' not found in Redmine.');
        }

        if (!($user = $userApi->getIdByUsername($author))) {
            list($firstname, $lastname) = explode('@', $author);

            $userId = $userApi->create(array(
                'login'     => $author,
                'lastname'  => $lastname,
                'firstname' => $firstname,
                'mail'      => $author
            ))->id;
        }

        $issue = $issueApi->create(array(
            'project_id'        => $projectId,
            'subject'           => $subject,
            'description'       => $description,
            'author_id'         => $userId
        ));

        foreach ($attachments as $attachment) {
            $upload = $attachmentApi->upload($attachment->getData());

            $issueApi->attach($issue->id, array(
                'token'        => $upload->upload->token,
                'description'  => $attachment->getFileName(),
                'content_type' => $attachment->getMimeType()
            ));
        }

        $issueApi->setIssueStatus($issue->id, 'Odpowiedź/uszczegółowienie');

        return true;
    }
}