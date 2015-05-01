<?php

namespace DiscourseApi;

class Client
{
    const VERSION = '0.0.1';
    const USERAGENT = 'https://github.com/xxx/xxx - DiscourseApi/0.0.1';
    private $key = null;
    private $username = null;
    private $base = null;
    private $client = null;
    private $ip_passthrough = false;

    public function __construct($base, $username, $key, $ip_passthrough = false)
    {
        if (empty($base) || !isset($base)) {
            throw new \Exception('A valid base URL is required. Example: https://test.discourse.local');
        }

        if (empty($username) || !isset($username)) {
            throw new \Exception('A valid username is required and must match the API key owner.');
        }

        if (empty($key) || !isset($key)) {
            throw new \Exception('A valid key is required and must match the API user.');
        }

        $this->key = $key;
        $this->username = $username;
        $this->base = $base;
        $this->ip_passthrough = $ip_passthrough;
        $this->client = new \GuzzleHttp\Client();
    }

    public function request($method, $path, $data = [], $is_json = true)
    {
        if ($this->client === null) {
            throw new \Exception('Improperly loaded or Guzzle not found!');
        }

        $request = $this->client->createRequest($method, "{$this->base}{$path}");
        $request->setHeader('User-Agent', self::USERAGENT);
        if ($this->ip_passthrough === true) {
            $request->setHeader('X-Forwarded-For', $_SERVER['REMOTE_ADDR']);
        }

        $query = $request->getQuery();
        $query->set('api_key', $this->key);
        $query->set('api_username', $this->username);
        $postBody = $request->getBody();

        if (!empty($data)) {
            foreach ($data as $k => $v) {
                $postBody->setField($k, $v);
            }
        }

        $response = $this->client->send($request);
        if ($is_json) {
            return $response->json();
        } else {
            return $response;
        }
    }

    private function challenge()
    {
        try {
            $challenge = $this->request('GET', '/users/hp.json');
            if (!empty($challenge['value']) && !empty($challenge['challenge'])) {
                return $challenge;
            } else {
                throw new \Exception('Unable to get a user-challenge; please try again or check your Discourse install.');
            }
        } catch (\Exception $e) {
            throw new \Exception('Unable to get a user-challenge; please try again or check your Discourse install. (Network)');
        }
    }

    // Thread and modifications
    public function createTopic($title, $text, $categoryId, $reply = 0)
    {
        $data = [
          'title' => $title,
          'raw' => $text,
          'category' =>  $categoryId,
          'archetype' => 'regular',
          'reply_to_post_number' => $reply,
        ];

        return $this->request('POST', '/posts', $data);
    }

    public function createPost($topicId, $text, $categoryId, $linkedTopic = 0)
    {
        $data = [
        'topic_id' => (int) $topicId,
        'raw' => $text,
        'category' => (int) $categoryId,
        'archetype' => 'regular',
        'reply_to_post_number' => (int) $linkedTopic,
        'nested_post' => true,
    ];

        return $this->request('POST', '/posts', $data);
    }

    public function like($postId)
    {
        $postId = (int) $postId;
        if ($postId === 0 || empty($postId)) {
            throw new \Exception('A valid post ID is required.');
        }
        $data = ['id' => (int) $postId, 'post_action_type_id' => 2, 'flag_topic' => false];
        $req = $this->request('POST', '/post_actions', $data, false);

        if ($req->getStatusCode() === 200) {
            return ['success' => true, 'liked_post_id' => $postId];
        } else {
            return ['success' => false];
        }
    }

    public function deletePost($postId)
    {
        $postId = (int) $postId;
        if ($postId === 0 || empty($postId)) {
            throw new \Exception('A valid post ID is required.');
        }

        return $this->request('DELETE', '/posts/'.$postId);
    }

    public function deleteTopic($topicId)
    {
        $topicId = (int) $topicId;
        if ($topicId === 0 || empty($topicId)) {
            throw new \Exception('A valid post ID is required.');
        }

        return $this->request('DELETE', '/t/'.$topicId);
    }

    public function recoverPost($postId)
    {
        $postId = (int) $postId;
        if ($postId === 0 || empty($postId)) {
            throw new \Exception('A valid post ID is required.');
        }

        return $this->request('PUT', '/posts/'.$postId.'/recover');
    }

    public function recoverTopic($topicId)
    {
        $topicId = (int) $topicId;
        if ($topicId === 0 || empty($topicId)) {
            throw new \Exception('A valid post ID is required.');
        }

        return $this->request('PUT', '/t/'.$topicId.'/recover');
    }

    // Group (requires user ID, NOT name)
    public function removeUserFromGroup($userId, $groupId)
    {
        $groupId = (int) $groupId;
        $userId = (int) $userId;
        if ($userId === 0 || empty($userId)) {
            throw new \Exception('A valid user ID is required (not username!).');
        }
        if ($groupId === 0 || empty($groupId)) {
            throw new \Exception('A valid group ID is required.');
        }

        $req = $this->request('DELETE', '/admin/users/'.$userId.'/groups/'.$groupId, [], false);
        if ($req->getStatusCode() === 200) {
            return ['success' => true];
        } else {
            return ['success' => false];
        }
    }

    public function addUserToGroup($userId, $groupId)
    {
        $groupId = (int) $groupId;
        $userId = (int) $userId;
        if ($userId === 0 || empty($userId)) {
            throw new \Exception('A valid user ID is required (not username!).');
        }
        if ($groupId === 0 || empty($groupId)) {
            throw new \Exception('A valid group ID is required.');
        }

        return $this->request('POST', '/admin/users/'.$userId.'/groups', ['group_id' => $groupId]);
    }

    public function setUserPrimaryGroup($userId, $groupId)
    {
        $groupId = (int) $groupId;
        $userId = (int) $userId;
        if ($userId === 0 || empty($userId)) {
            throw new \Exception('A valid user ID is required (not username!).');
        }
        if ($groupId === 0 || empty($groupId)) {
            throw new \Exception('A valid group ID is required.');
        }

        $req = $this->request('PUT', '/admin/users/'.$userId.'/primary_group', ['primary_group_id' => $groupId], false);
        if ($req->getStatusCode() === 200) {
            return ['success' => true];
        } else {
            return ['success' => false];
        }
    }

    // User
    public function getUserByUsername($username, $stats = false)
    {
        $stats = (bool) $stats;
        if ($username === 0 || empty($username)) {
            $username = $this->username;
        }

        return $this->request('GET', '/users/'.$username.'.json?stats='.$stats);
    }

    public function getUserBadges($username, $group = false)
    {
        $group = (bool) $group;
        if ($username === 0 || empty($username)) {
            $username = $this->username;
        }

        return $this->request('GET', '/user-badges/'.$username.'.json?grouped='.$group);
    }

    public function createUser($userName, $password, $email, $fullName = '')
    {
        $challenge = $this->challenge();
        $user = [
          'username' => $userName,
          'email' => $email,
          'password' => $password,
          'password_confirmation' => $challenge['value'],
          'challenge' => strrev($challenge['challenge']),
          'name' => $fullName,
        ];

        return $this->request('POST', '/users', $user);
    }

    public function deleteUser($uid)
    {
        $uid = (int) $uid;
        if ($uid === 0 || empty($uid)) {
            throw new \Exception('A valid user ID is required, you can use getUserByUsername(username).');
        }

        return $this->request('DELETE', '/admin/users/'.$uid.'.json');
    }

    public function anonymizeUser($uid)
    {
        $uid = (int) $uid;
        if ($uid === 0 || empty($uid)) {
            throw new \Exception('A valid user ID is required, you can use getUserByUsername(username).');
        }

        return $this->request('PUT', '/admin/users/'.$uid.'/anonymize.json');
    }

    // Generics
    public function getAbout()
    {
        return $this->request('GET', '/about.json');
    }

    // Admin
    public function getFlags($type = 'active', $offset = 0)
    {
        $valid_types = ['old', 'active'];
        if (!in_array($type, $valid_types)) {
            throw new \Exception('Flags must be one of: '.implode(', ', $valid_types));
        }

        return $this->request('GET', '/admin/flags/'.$type.'.json?offset='.(int) $offset);
    }

    public function setSetting($key, $value)
    {
        return $this->request('PUT', '/admin/users/'.$key, [$key => $value]);
    }

    // Forum
    public function getCategories()
    {
        return $this->request('GET', '/categories.json');
    }

    public function getCategory($slug, $sort = 'latest', $page = 0)
    {
        $valid_sorts = ['latest', 'new', 'unread', 'top'];
        if (!in_array($sort, $valid_sorts)) {
            throw new \Exception('Sort must be one of: '.implode(', ', $valid_sorts));
        }

        return $this->request('GET', '/c/'.$slug.'/l/'.$sort.'.json?page='.(int) $page);
    }

    public function getPost($topicId, $postId = 1)
    {
        $topicId = (int) $topicId;
        $postId = (int) $postId;
        if ($topicId === 0 || empty($topicId)) {
            throw new \Exception('A valid topic ID is required.');
        }

        return $this->request('GET', '/posts/by_number/'.$topicId.'/'.$postId.'.json');
    }
    public function getTopic($topicId)
    {
        $topicId = (int) $topicId;
        if ($topicId === 0 || empty($topicId)) {
            throw new \Exception('A valid topic ID is required.');
        }

        return $this->request('GET', '/t/'.$topicId.'.json');
    }

    // Search
    public function searchInForum($query, $context = false)
    {
        $context = (bool) $context;

        return $this->request('GET', '/search/query.json?term='.$query.'&include_blurbs='.$context);
    }

    public function searchInTopic($query, $context = false, $topicId)
    {
        $context = (bool) $context;
        $topicId = (int) $topicId;
        if ($topicId === 0 || empty($topicId)) {
            throw new \Exception('A valid topic ID is required.');
        }

        return $this->request('GET', '/search/query.json?term='.$query.'&include_blurbs='.$context.'&search_context[type]=topic&search_context[id]='.$topicId);
    }

    public function searchInCategory($query, $context = false, $categoryId)
    {
        $context = (bool) $context;
        $categoryId = (int) $categoryId;
        if ($categoryId === 0 || empty($categoryId)) {
            throw new \Exception('A valid category ID is required.');
        }

        return $this->request('GET', '/search/query.json?term='.$query.'&include_blurbs='.$context.'&search_context[type]=category&search_context[id]='.$categoryId);
    }
}
